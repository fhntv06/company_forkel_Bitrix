<?php
/**
 * Webhook / Callback endpoint for online payment confirmation (FR-008 / INT-006).
 * Supports YooKassa JSON notification format and Beta Sandbox test confirmation.
 * Verifies HMAC-SHA256 signature (X-Webhook-Signature) against YOOKASSA_WEBHOOK_SECRET.
 * Logs safely to local/logs/payment_webhook.log without exposing secrets.
 */
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\Sale\Order;

header('Content-Type: application/json; charset=UTF-8');

$logWebhook = static function (string $status, string $msg): void {
    $dir = ($_SERVER['DOCUMENT_ROOT'] ?: '/var/www/html') . '/local/logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    @file_put_contents($dir . '/payment_webhook.log', '[' . date('Y-m-d H:i:s') . '] [' . $status . '] ' . $msg . PHP_EOL, FILE_APPEND | LOCK_EX);
};

$respond = static function (int $code, array $data): void {
    http_response_code($code);
    echo Json::encode($data);
    die();
};

if (!\Forkel\RateLimiter::check('payment_webhook', 60, 60)) {
    $logWebhook('RATE_LIMIT', 'Too many requests');
    $respond(429, ['ok' => false, 'error' => 'Too many requests']);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $respond(405, ['ok' => false, 'error' => 'Method not allowed']);
}

if (!Loader::includeModule('sale')) {
    $respond(500, ['ok' => false, 'error' => 'Sale module unavailable']);
}

$rawBody = (string)file_get_contents('php://input');
$payload = [];
if ($rawBody !== '') {
    try {
        $payload = Json::decode($rawBody);
    } catch (\Throwable $e) {
        $payload = $_POST;
    }
} else {
    $payload = $_POST;
}

$secret = \Forkel\Env::get('YOOKASSA_WEBHOOK_SECRET');
$sandbox = \Forkel\Env::get('PAYMENT_SANDBOX', '1') === '1';

if ($secret !== '') {
    $sig = (string)($_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '');
    $expected = hash_hmac('sha256', $rawBody, $secret);
    if (!hash_equals($expected, $sig)) {
        $logWebhook('BAD_SIGNATURE', 'Signature mismatch');
        $respond(403, ['ok' => false, 'error' => 'Invalid signature']);
    }
} elseif (!$sandbox) {
    $logWebhook('NO_SECRET', 'Webhook secret missing in non-sandbox mode');
    $respond(403, ['ok' => false, 'error' => 'Webhook secret not configured']);
} else {
    // In sandbox mode, require either valid sessid (browser test) or X-Sandbox-Token header
    $isSandboxTest = check_bitrix_sessid() || !empty($_SERVER['HTTP_X_SANDBOX_TEST']);
    if (!$isSandboxTest) {
        $logWebhook('SANDBOX_DENIED', 'Missing sessid or X-Sandbox-Test header');
        $respond(403, ['ok' => false, 'error' => 'Sandbox authorization required']);
    }
}

$event = (string)($payload['event'] ?? 'payment.succeeded');
$obj = is_array($payload['object'] ?? null) ? $payload['object'] : $payload;
$orderId = (int)($obj['metadata']['order_id'] ?? $obj['order_id'] ?? 0);
$paymentId = (int)($obj['metadata']['payment_id'] ?? $obj['payment_id'] ?? 0);
$externalId = (string)($obj['id'] ?? 'sandbox_' . time());

if ($orderId <= 0) {
    $logWebhook('BAD_PAYLOAD', 'Missing order_id');
    $respond(400, ['ok' => false, 'error' => 'Missing order_id']);
}

$order = Order::load($orderId);
if (!$order) {
    $logWebhook('NOT_FOUND', "Order #$orderId not found");
    $respond(404, ['ok' => false, 'error' => 'Order not found']);
}

if ($event === 'payment.succeeded') {
    $paymentCollection = $order->getPaymentCollection();
    $targetPayment = null;
    foreach ($paymentCollection as $p) {
        if ($paymentId > 0 && (int)$p->getId() === $paymentId) {
            $targetPayment = $p;
            break;
        }
        if (!$p->isPaid()) {
            $targetPayment = $p;
        }
    }
    if ($targetPayment) {
        $targetPayment->setPaid('Y');
        $targetPayment->setField('PS_STATUS', 'Y');
        $targetPayment->setField('PS_STATUS_CODE', 'succeeded');
        $targetPayment->setField('PS_INVOICE_ID', $externalId);
        $targetPayment->setField('PS_SUM', $targetPayment->getSum());
        $order->setField('STATUS_ID', 'P');
        $saveRes = $order->save();
        if (!$saveRes->isSuccess()) {
            $logWebhook('SAVE_ERR', "Order #$orderId: " . implode('; ', $saveRes->getErrorMessages()));
            $respond(500, ['ok' => false, 'error' => 'Order save failed']);
        }
        $logWebhook('PAID', "Order #$orderId payment #{$targetPayment->getId()} marked PAID (ext=$externalId)");
        $respond(200, ['ok' => true, 'order_id' => $orderId, 'status' => 'P', 'paid' => true]);
    }
    $logWebhook('NO_UNPAID', "Order #$orderId already paid");
    $respond(200, ['ok' => true, 'order_id' => $orderId, 'paid' => true]);
}

if ($event === 'payment.canceled') {
    $logWebhook('CANCELED', "Order #$orderId payment canceled (ext=$externalId)");
    $respond(200, ['ok' => true, 'order_id' => $orderId, 'canceled' => true]);
}

$logWebhook('IGNORED', "Event=$event order=#$orderId");
$respond(200, ['ok' => true, 'ignored' => true]);
