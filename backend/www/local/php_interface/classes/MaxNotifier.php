<?php
namespace Forkel;

use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

/**
 * MAX Messenger notification service (INT-008 / FR-014).
 * Uses MAX_BOT_TOKEN, MAX_CHAT_ID, MAX_API_URL from env.
 * In Beta mode (empty credentials), logs formatted messages to local/logs/max_notifications.log.
 */
final class MaxNotifier
{
    public static function send(string $text): bool
    {
        $token = Env::get('MAX_BOT_TOKEN');
        $chatId = Env::get('MAX_CHAT_ID');
        $apiUrl = rtrim(Env::get('MAX_API_URL', 'https://botapi.max.ru'), '/');

        if ($token === '' || $chatId === '') {
            self::log('BETA_SANDBOX', $text);
            return true;
        }

        try {
            $http = new HttpClient([
                'socketTimeout' => 3,
                'streamTimeout' => 3,
                'disableSslVerification' => false,
            ]);
            $http->setHeader('Content-Type', 'application/json', true);
            $http->setHeader('Authorization', $token, true);
            $url = $apiUrl . '/messages?chat_id=' . rawurlencode($chatId);
            $response = $http->post($url, Json::encode(['text' => $text]));
            $status = $http->getStatus();

            if ($status >= 200 && $status < 300) {
                self::log('SENT_' . $status, $text);
                return true;
            }
            self::log('HTTP_ERR_' . $status, $text . ' | resp=' . mb_substr((string)$response, 0, 200));
            return false;
        } catch (\Throwable $e) {
            self::log('EXCEPTION', $text . ' | err=' . $e->getMessage());
            return false;
        }
    }

    public static function notifyNewOrder(\Bitrix\Sale\Order $order): void
    {
        $props = $order->getPropertyCollection();
        $fio = $props->getPayerName() ? (string)$props->getPayerName()->getValue() : '';
        $phone = $props->getPhone() ? (string)$props->getPhone()->getValue() : '';
        $email = $props->getUserEmail() ? (string)$props->getUserEmail()->getValue() : '';
        $callTimeProp = $props->getItemByOrderPropertyCode('CALL_TIME');
        $callTime = $callTimeProp ? (string)$callTimeProp->getValue() : '';

        $lines = [
            'Новый заказ FORKEL #' . $order->getField('ACCOUNT_NUMBER'),
            'Сумма: ' . number_format((float)$order->getPrice(), 0, '.', ' ') . ' ₽',
        ];
        if ($fio !== '') {
            $lines[] = 'Клиент: ' . $fio;
        }
        if ($phone !== '') {
            $lines[] = 'Телефон: ' . $phone;
        }
        if ($email !== '') {
            $lines[] = 'Email: ' . $email;
        }
        if ($callTime !== '') {
            $lines[] = 'Удобное время звонка: ' . $callTime;
        }

        $basket = $order->getBasket();
        if ($basket && $basket->count() > 0) {
            $lines[] = 'Позиции:';
            foreach ($basket as $item) {
                $lines[] = '• ' . $item->getField('NAME') . ' × ' . (int)$item->getQuantity() . ' (' . number_format((float)$item->getFinalPrice(), 0, '.', ' ') . ' ₽)';
            }
        }

        self::send(implode("\n", $lines));
    }

    public static function notifyFeedback(string $mode, string $name, string $contact, string $text): void
    {
        $titles = [
            'feedback' => 'Обратная связь',
            'callback' => 'Заказ звонка',
            'oneclick' => 'Покупка в 1 клик',
            'lead' => 'Заявка на сотрудничество',
            'partner' => 'Анкета партнёра',
        ];
        $title = $titles[$mode] ?? 'Заявка с сайта';
        $msg = "FORKEL: {$title}\nИмя: {$name}\nКонтакт: {$contact}\n{$text}";
        self::send($msg);
    }

    private static function log(string $status, string $text): void
    {
        $docRoot = $_SERVER['DOCUMENT_ROOT'] ?: '/var/www/html';
        $logDir = $docRoot . '/local/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }
        $entry = '[' . date('Y-m-d H:i:s') . '] [' . $status . '] ' . str_replace("\n", ' | ', $text) . PHP_EOL;
        @file_put_contents($logDir . '/max_notifications.log', $entry, FILE_APPEND | LOCK_EX);
    }
}
