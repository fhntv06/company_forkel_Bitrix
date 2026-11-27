<?php
namespace Forkel;

use Bitrix\Main\Context;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PriceMaths;

/**
 * Интернет-эквайринг Альфа-Банка (Россия, REST API платформы RBS) — DEC-013 / TASK-019.
 *
 * Все параметры подключения читаются из окружения (backend/.env -> контейнер PHP), в БД и Git ключей нет:
 *   ALFABANK_TOKEN или ALFABANK_USER + ALFABANK_PASSWORD — доступ к API (логин вида xxx-api);
 *   ALFABANK_TEST_MODE=1 — тестовый контур банка (alfa.rbsuat.com), 0 — боевой;
 *   ALFABANK_CALLBACK_SECRET — ключ для проверки checksum в callback-уведомлениях банка;
 *   ALFABANK_API_URL / ALFABANK_TEST_API_URL — переопределение адресов API (если банк выдал другие);
 *   SITE_URL — публичный адрес сайта (https://...), нужен для адресов возврата и callback.
 * Если доступы не заданы и PAYMENT_SANDBOX=1 — включается песочница сайта (оплата подтверждается кнопкой).
 */
final class AlfaBank
{
    public const HANDLER = 'forkelalfabank';

    public const TEST_API_URL = 'https://alfa.rbsuat.com/payment/rest/';
    public const PROD_API_URL = 'https://payment.alfabank.ru/payment/rest/';

    /** orderStatus из getOrderStatusExtended.do */
    public const STATUS_REGISTERED = 0;
    public const STATUS_APPROVED = 1;
    public const STATUS_DEPOSITED = 2;
    public const STATUS_REVERSED = 3;
    public const STATUS_REFUNDED = 4;
    public const STATUS_ACS = 5;
    public const STATUS_DECLINED = 6;

    private const STATUS_CODES = [
        self::STATUS_REGISTERED => 'created',
        self::STATUS_APPROVED => 'approved',
        self::STATUS_DEPOSITED => 'deposited',
        self::STATUS_REVERSED => 'reversed',
        self::STATUS_REFUNDED => 'refunded',
        self::STATUS_ACS => 'acs',
        self::STATUS_DECLINED => 'declined',
    ];

    public static function isConfigured(): bool
    {
        return Env::get('ALFABANK_TOKEN') !== ''
            || (Env::get('ALFABANK_USER') !== '' && Env::get('ALFABANK_PASSWORD') !== '');
    }

    public static function isTestMode(): bool
    {
        return Env::get('ALFABANK_TEST_MODE', '1') !== '0';
    }

    /** Песочница сайта: банк не подключён, оплату можно подтвердить кнопкой (только для разработки). */
    public static function isSandbox(): bool
    {
        return !self::isConfigured() && Env::get('PAYMENT_SANDBOX', '1') === '1';
    }

    public static function hasCallbackSecret(): bool
    {
        return Env::get('ALFABANK_CALLBACK_SECRET') !== '';
    }

    public static function statusCode(int $status): string
    {
        return self::STATUS_CODES[$status] ?? ('status_' . $status);
    }

    /** Публичный адрес сайта без «/» в конце: SITE_URL или адрес текущего запроса. */
    public static function siteUrl(): string
    {
        $url = Env::get('SITE_URL');
        if ($url !== '') {
            return rtrim($url, '/');
        }
        $request = Context::getCurrent()->getRequest();
        $host = (string)$request->getHttpHost();
        if ($host === '') {
            return '';
        }
        return ($request->isHttps() ? 'https' : 'http') . '://' . $host;
    }

    public static function apiUrl(string $method): string
    {
        $base = self::isTestMode()
            ? Env::get('ALFABANK_TEST_API_URL', self::TEST_API_URL)
            : Env::get('ALFABANK_API_URL', self::PROD_API_URL);
        return rtrim($base, '/') . '/' . $method;
    }

    /**
     * Вызов метода API банка. Ключи в лог не пишутся.
     * @return array{ok: bool, data: array, error: string}
     */
    public static function call(string $method, array $params): array
    {
        if (!self::isConfigured()) {
            return ['ok' => false, 'data' => [], 'error' => 'Alfa-Bank credentials are not configured'];
        }
        $token = Env::get('ALFABANK_TOKEN');
        $auth = $token !== ''
            ? ['token' => $token]
            : ['userName' => Env::get('ALFABANK_USER'), 'password' => Env::get('ALFABANK_PASSWORD')];

        $http = new HttpClient(['socketTimeout' => 15, 'streamTimeout' => 30]);
        $body = $http->post(self::apiUrl($method), array_merge($auth, $params));
        if ($body === false) {
            return ['ok' => false, 'data' => [], 'error' => 'HTTP error: ' . implode('; ', $http->getError())];
        }
        if ($http->getStatus() >= 400) {
            return ['ok' => false, 'data' => [], 'error' => 'HTTP status ' . $http->getStatus()];
        }
        try {
            $data = Json::decode($body);
        } catch (\Throwable $e) {
            $data = null;
        }
        if (!is_array($data)) {
            return ['ok' => false, 'data' => [], 'error' => 'Invalid response from bank'];
        }
        $code = (string)($data['errorCode'] ?? '0');
        if ($code !== '' && $code !== '0') {
            return ['ok' => false, 'data' => $data, 'error' => (string)($data['errorMessage'] ?? ('Error ' . $code))];
        }
        return ['ok' => true, 'data' => $data, 'error' => ''];
    }

    /**
     * Проверка checksum callback-уведомления (симметричный ключ, HMAC-SHA256).
     * Если ALFABANK_CALLBACK_SECRET не задан — возвращает true: статус всё равно перепроверяется запросом к API.
     */
    public static function verifyCallback(array $params): bool
    {
        $secret = Env::get('ALFABANK_CALLBACK_SECRET');
        if ($secret === '') {
            return true;
        }
        $checksum = (string)($params['checksum'] ?? '');
        if ($checksum === '') {
            return false;
        }
        unset($params['checksum']);
        ksort($params);
        $data = '';
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                return false;
            }
            $data .= $key . ';' . $value . ';';
        }
        return hash_equals(strtoupper(hash_hmac('sha256', $data, $secret)), strtoupper($checksum));
    }

    /** Номер заказа в банке: «<ID оплаты>_<ID платёжной системы>_<метка времени>» (уникален при повторных попытках). */
    public static function makeOrderNumber(Payment $payment): string
    {
        return $payment->getId() . '_' . $payment->getPaymentSystemId() . '_' . time();
    }

    /** @return array{0: int, 1: int} [ID оплаты, ID платёжной системы] */
    public static function parseOrderNumber(string $orderNumber): array
    {
        $parts = explode('_', $orderNumber);
        return [(int)($parts[0] ?? 0), (int)($parts[1] ?? 0)];
    }

    public static function amountInKopecks(Payment $payment): int
    {
        return (int)round(PriceMaths::roundPrecision((float)$payment->getSum()) * 100);
    }

    public static function isAmountCorrect(Payment $payment, array $statusData): bool
    {
        return (int)($statusData['amount'] ?? -1) === self::amountInKopecks($payment);
    }

    /** Поля PS_* оплаты по ответу getOrderStatusExtended.do. */
    public static function psFields(string $mdOrder, array $statusData): array
    {
        $status = (int)($statusData['orderStatus'] ?? -1);
        $fields = [
            'PS_INVOICE_ID' => $mdOrder,
            'PS_STATUS' => $status === self::STATUS_DEPOSITED ? 'Y' : 'N',
            'PS_STATUS_CODE' => self::statusCode($status),
            'PS_STATUS_DESCRIPTION' => mb_substr('Альфа-Банк: заказ ' . $mdOrder . ', orderStatus=' . $status, 0, 250),
            'PS_RESPONSE_DATE' => new DateTime(),
        ];
        if (isset($statusData['amount'])) {
            $fields['PS_SUM'] = ((int)$statusData['amount']) / 100;
            $fields['PS_CURRENCY'] = 'RUB';
        }
        return $fields;
    }

    /**
     * Сверка оплаты с банком (страница возврата покупателя): если банк подтвердил списание
     * (orderStatus = 2 и сумма совпадает) — оплата отмечается оплаченной.
     * @return string paid | pending | declined | unknown
     */
    public static function syncPayment(Payment $payment): string
    {
        if ($payment->isPaid()) {
            return 'paid';
        }
        $mdOrder = (string)$payment->getField('PS_INVOICE_ID');
        if ($mdOrder === '' || !self::isConfigured()) {
            return 'unknown';
        }
        $res = self::call('getOrderStatusExtended.do', ['orderId' => $mdOrder]);
        if (!$res['ok']) {
            self::log('SYNC_ERR', 'payment #' . $payment->getId() . ': ' . $res['error']);
            return 'unknown';
        }
        $data = $res['data'];
        [$paymentId] = self::parseOrderNumber((string)($data['orderNumber'] ?? ''));
        if ($paymentId !== (int)$payment->getId()) {
            self::log('SYNC_MISMATCH', 'payment #' . $payment->getId() . ' != bank orderNumber');
            return 'unknown';
        }
        $status = (int)($data['orderStatus'] ?? -1);
        $order = $payment->getOrder();

        if ($status === self::STATUS_DEPOSITED) {
            if (!self::isAmountCorrect($payment, $data)) {
                self::log('SYNC_SUM', 'payment #' . $payment->getId() . ': amount mismatch');
                return 'unknown';
            }
            $payment->setFields(self::psFields($mdOrder, $data));
            $paidRes = $payment->setPaid('Y');
            $saveRes = $paidRes->isSuccess() ? $order->save() : $paidRes;
            if (!$saveRes->isSuccess()) {
                self::log('SYNC_SAVE', 'order #' . $order->getId() . ': ' . implode('; ', $saveRes->getErrorMessages()));
                return 'unknown';
            }
            self::log('PAID', 'order #' . $order->getId() . ' payment #' . $payment->getId() . ' (return page, mdOrder=' . $mdOrder . ')');
            return 'paid';
        }
        if (in_array($status, [self::STATUS_DECLINED, self::STATUS_REVERSED], true)) {
            $payment->setFields(self::psFields($mdOrder, $data));
            $order->save();
            return 'declined';
        }
        return 'pending';
    }

    public static function log(string $status, string $message): void
    {
        $dir = ($_SERVER['DOCUMENT_ROOT'] ?: '/var/www/html') . '/local/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        @file_put_contents(
            $dir . '/payment_webhook.log',
            '[' . date('Y-m-d H:i:s') . '] [' . $status . '] ' . $message . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }
}
