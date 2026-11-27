<?php
/**
 * Уведомления об оплате (FR-008 / INT-006, DEC-013 / TASK-019).
 *
 * 1. Callback Альфа-Банка (GET/POST: mdOrder, orderNumber, operation, status[, checksum]):
 *    - доступы банка не заданы -> 403;
 *    - задан ALFABANK_CALLBACK_SECRET и checksum не совпал (или отсутствует) -> 403;
 *    - далее обработчик forkelalfabank перепроверяет статус через getOrderStatusExtended.do и отмечает
 *      оплату только при orderStatus = 2 (списание прошло) и совпадении суммы.
 * 2. Песочница Beta (доступы банка не заданы и PAYMENT_SANDBOX=1): POST JSON {"order_id": N}
 *    с действующим sessid и только для своего заказа (или администратора). Во всех остальных случаях -> 403.
 * Журнал: local/logs/payment_webhook.log (без ключей и персональных данных).
 */
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\Sale\Order;
use Bitrix\Sale\PaySystem;
use Forkel\AlfaBank;

header('Content-Type: application/json; charset=UTF-8');

$respond = static function (int $code, array $data): void {
    http_response_code($code);
    echo Json::encode($data);
    die();
};

if (!\Forkel\RateLimiter::check('payment_webhook', 60, 60)) {
    AlfaBank::log('RATE_LIMIT', 'Too many requests');
    $respond(429, ['ok' => false, 'error' => 'Too many requests']);
}

if (!Loader::includeModule('sale')) {
    $respond(500, ['ok' => false, 'error' => 'Sale module unavailable']);
}

$request = Application::getInstance()->getContext()->getRequest();

// --- 1. Callback Альфа-Банка ---
if ((string)$request->get('mdOrder') !== '' && (string)$request->get('orderNumber') !== '') {
    $mdOrder = substr(preg_replace('/[^a-zA-Z0-9-]/', '', (string)$request->get('mdOrder')), 0, 64);

    if (!AlfaBank::isConfigured()) {
        AlfaBank::log('NO_CREDENTIALS', "Callback mdOrder=$mdOrder rejected: ALFABANK_* not configured");
        $respond(403, ['ok' => false, 'error' => 'Payment gateway not configured']);
    }
    if (!AlfaBank::verifyCallback($request->toArray())) {
        AlfaBank::log('BAD_CHECKSUM', "Callback mdOrder=$mdOrder: checksum mismatch");
        $respond(403, ['ok' => false, 'error' => 'Invalid checksum']);
    }

    $item = PaySystem\Manager::searchByRequest($request);
    if ($item === false || $item['ACTION_FILE'] !== AlfaBank::HANDLER) {
        AlfaBank::log('NOT_FOUND', "Callback mdOrder=$mdOrder: pay system not found");
        $respond(404, ['ok' => false, 'error' => 'Payment not found']);
    }

    $service = new PaySystem\Service($item);
    $result = $service->processRequest($request);
    if (!$result->isSuccess()) {
        AlfaBank::log('CALLBACK_ERR', "mdOrder=$mdOrder: " . implode('; ', $result->getErrorMessages()));
        $respond(400, ['ok' => false, 'error' => 'Callback not accepted']);
    }
    AlfaBank::log('CALLBACK_OK', "mdOrder=$mdOrder operation=" . preg_replace('/[^a-z_]/i', '', (string)$request->get('operation')) . ' status=' . (int)$request->get('status'));
    $respond(200, ['ok' => true]);
}

// --- 2. Песочница Beta ---
if (!AlfaBank::isSandbox()) {
    AlfaBank::log('DENIED', 'Non-bank request outside sandbox mode');
    $respond(403, ['ok' => false, 'error' => 'Forbidden']);
}
if ($request->getRequestMethod() !== 'POST') {
    $respond(405, ['ok' => false, 'error' => 'Method not allowed']);
}
if (!check_bitrix_sessid()) {
    AlfaBank::log('SANDBOX_DENIED', 'Missing or invalid sessid');
    $respond(403, ['ok' => false, 'error' => 'Sandbox authorization required']);
}

try {
    $payload = Json::decode((string)file_get_contents('php://input'));
} catch (\Throwable $e) {
    $payload = [];
}
$orderId = (int)($payload['order_id'] ?? 0);
if ($orderId <= 0) {
    $respond(400, ['ok' => false, 'error' => 'Missing order_id']);
}

$order = Order::load($orderId);
global $USER;
$sessionOrders = array_map('intval', (array)($_SESSION['SALE_ORDER_ID'] ?? []));
$isOwner = $order && (
    ($USER->IsAuthorized() && (int)$order->getUserId() === (int)$USER->GetID())
    || in_array($orderId, $sessionOrders, true)
    || $USER->IsAdmin()
);
if (!$isOwner) {
    AlfaBank::log('SANDBOX_DENIED', "Order #$orderId: not found or not owned by current user");
    $respond(403, ['ok' => false, 'error' => 'Forbidden']);
}

$target = null;
foreach ($order->getPaymentCollection() as $payment) {
    if (!$payment->isPaid() && !$payment->isInner()) {
        $target = $payment;
        break;
    }
}
if (!$target) {
    $respond(200, ['ok' => true, 'order_id' => $orderId, 'paid' => true]);
}

$target->setFields([
    'PS_STATUS' => 'Y',
    'PS_STATUS_CODE' => 'sandbox',
    'PS_STATUS_DESCRIPTION' => 'Beta Sandbox: тестовая оплата без банка',
    'PS_INVOICE_ID' => 'sandbox_' . time(),
    'PS_SUM' => $target->getSum(),
    'PS_CURRENCY' => $target->getField('CURRENCY'),
    'PS_RESPONSE_DATE' => new \Bitrix\Main\Type\DateTime(),
]);
$paidResult = $target->setPaid('Y');
$saveResult = $paidResult->isSuccess() ? $order->save() : $paidResult;
if (!$saveResult->isSuccess()) {
    AlfaBank::log('SAVE_ERR', "Order #$orderId: " . implode('; ', $saveResult->getErrorMessages()));
    $respond(500, ['ok' => false, 'error' => 'Order save failed']);
}
AlfaBank::log('SANDBOX_PAID', "Order #$orderId payment #{$target->getId()} marked PAID (sandbox)");
$respond(200, ['ok' => true, 'order_id' => $orderId, 'paid' => true]);
