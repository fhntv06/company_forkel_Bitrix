<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Error;
use Bitrix\Main\Request;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Json;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem;
use Forkel\AlfaBank;

/**
 * Обработчик платёжной системы «Альфа-Банк (Россия)» — карты и СБП (DEC-013 / TASK-019).
 *
 * Встроенный обработчик Битрикса «alfabank» работает только с Альфа-Банком Беларуси (BYN), поэтому свой.
 * Параметры подключения — только из окружения (см. \Forkel\AlfaBank), в админке настраивать ничего не нужно.
 * Режимы (PS_MODE): bank_card — оплата картой, sbp — оплата через СБП; оба ведут на платёжную страницу банка,
 * где СБП доступен, если банк включил его для магазина.
 *
 * Callback банка принимает /local/ajax/payment_webhook.php: checksum + обязательная перепроверка
 * статуса через getOrderStatusExtended.do; оплата ставится только при orderStatus = 2 и совпадении суммы.
 */
class ForkelAlfabankHandler extends PaySystem\ServiceHandler
{
    public static function getHandlerModeList()
    {
        return [
            'bank_card' => 'Банковская карта',
            'sbp' => 'Система быстрых платежей (СБП)',
        ];
    }

    public function getCurrencyList()
    {
        return ['RUB'];
    }

    protected function isTestMode(Payment $payment = null)
    {
        return AlfaBank::isTestMode();
    }

    public function initiatePay(Payment $payment, Request $request = null)
    {
        $result = new PaySystem\ServiceResult();
        $order = $payment->getOrder();

        $params = [
            'URL' => '',
            'MODE' => (string)$this->service->getField('PS_MODE'),
            'SUM' => (float)$payment->getSum(),
            'CURRENCY' => $payment->getCurrency(),
            'ORDER_ID' => (int)$order->getId(),
            'PAID' => $payment->isPaid(),
            'SANDBOX' => false,
            'ERROR' => '',
        ];

        if ($params['PAID']) {
            // ничего не делаем — шаблон покажет «Уже оплачено»
        } elseif (!AlfaBank::isConfigured()) {
            if (AlfaBank::isSandbox()) {
                $params['SANDBOX'] = true;
                $params['URL'] = '/personal/order/payment/?ORDER_ID=' . $params['ORDER_ID'];
            } else {
                $params['ERROR'] = 'Онлайн-оплата временно недоступна. Менеджер свяжется с вами, чтобы согласовать оплату.';
                PaySystem\Logger::addError(static::class . ': Alfa-Bank credentials are not configured (ALFABANK_*)');
            }
        } else {
            $url = $this->getActiveFormUrl($payment);
            if ($url === '') {
                $register = $this->registerOrder($payment);
                if ($register['ok'] && !empty($register['data']['formUrl']) && !empty($register['data']['orderId'])) {
                    $url = (string)$register['data']['formUrl'];
                    $result->setPsData([
                        'PS_INVOICE_ID' => (string)$register['data']['orderId'],
                        'PS_STATUS' => 'N',
                        'PS_STATUS_CODE' => 'created',
                        // ссылка на платёжную форму нужна, чтобы не создавать новый заказ в банке при каждом показе
                        'PS_STATUS_DESCRIPTION' => mb_substr($url, 0, 250),
                        'PS_RESPONSE_DATE' => new DateTime(),
                    ]);
                } else {
                    $params['ERROR'] = 'Не удалось перейти к оплате. Попробуйте ещё раз через несколько минут или свяжитесь с менеджером.';
                    PaySystem\Logger::addError(static::class . ': register.do failed: ' . $register['error']);
                    AlfaBank::log('REGISTER_ERR', 'payment #' . $payment->getId() . ': ' . $register['error']);
                }
            }
            $params['URL'] = $url;
        }

        $this->setExtraParams($params);
        $template = $this->showTemplate($payment, 'template');
        if ($template->isSuccess()) {
            $result->setTemplate($template->getTemplate());
        } else {
            $result->addErrors($template->getErrors());
        }
        if ($params['URL'] !== '') {
            $result->setPaymentUrl($params['URL']);
        }

        return $result;
    }

    /** Ссылка на уже созданную в банке и ещё не оплаченную форму на ту же сумму. */
    private function getActiveFormUrl(Payment $payment): string
    {
        $mdOrder = (string)$payment->getField('PS_INVOICE_ID');
        $url = (string)$payment->getField('PS_STATUS_DESCRIPTION');
        if ($mdOrder === '' || $payment->getField('PS_STATUS_CODE') !== 'created' || !str_starts_with($url, 'https://')) {
            return '';
        }
        $status = AlfaBank::call('getOrderStatusExtended.do', ['orderId' => $mdOrder]);
        if (!$status['ok']
            || (int)($status['data']['orderStatus'] ?? -1) !== AlfaBank::STATUS_REGISTERED
            || !AlfaBank::isAmountCorrect($payment, $status['data'])
        ) {
            return '';
        }
        return $url;
    }

    private function registerOrder(Payment $payment): array
    {
        $order = $payment->getOrder();
        $orderId = (int)$order->getId();
        $site = AlfaBank::siteUrl();

        $params = [
            'orderNumber' => AlfaBank::makeOrderNumber($payment),
            'amount' => AlfaBank::amountInKopecks($payment),
            'currency' => '643',
            'language' => 'ru',
            'returnUrl' => $site . '/personal/order/payment/?result=success&ORDER_ID=' . $orderId,
            'failUrl' => $site . '/personal/order/payment/?result=fail&ORDER_ID=' . $orderId,
            'description' => 'Оплата заказа №' . $order->getField('ACCOUNT_NUMBER') . ' в интернет-магазине FORKEL',
            'jsonParams' => Json::encode([
                'bx_paysystem_id' => (string)$this->service->getField('ID'),
                'bx_order_id' => (string)$orderId,
                'bx_payment_id' => (string)$payment->getId(),
            ]),
        ];
        // Банк доставляет callback только на публичный HTTPS-адрес; локально статус сверяет страница возврата.
        if (str_starts_with($site, 'https://')) {
            $params['dynamicCallbackUrl'] = $site . '/local/ajax/payment_webhook.php';
        }
        $email = $order->getPropertyCollection()->getUserEmail();
        if ($email && filter_var((string)$email->getValue(), FILTER_VALIDATE_EMAIL)) {
            $params['email'] = (string)$email->getValue();
        }

        return AlfaBank::call('register.do', $params);
    }

    /** Callback банка (вызывается из /local/ajax/payment_webhook.php через PaySystem\Service::processRequest). */
    public function processRequest(Payment $payment, Request $request)
    {
        $result = new PaySystem\ServiceResult();

        if (!AlfaBank::verifyCallback($request->toArray())) {
            $result->addError(new Error('Alfa-Bank callback: invalid checksum'));
            return $result;
        }
        $mdOrder = (string)$request->get('mdOrder');
        if ($mdOrder === '') {
            $result->addError(new Error('Alfa-Bank callback: mdOrder is empty'));
            return $result;
        }

        // Данным из уведомления не доверяем: статус и сумма берутся из API банка.
        $status = AlfaBank::call('getOrderStatusExtended.do', ['orderId' => $mdOrder]);
        if (!$status['ok']) {
            $result->addError(new Error('Alfa-Bank status check failed: ' . $status['error']));
            return $result;
        }
        $data = $status['data'];
        [$paymentId, $paySystemId] = AlfaBank::parseOrderNumber((string)($data['orderNumber'] ?? ''));
        if ($paymentId !== (int)$payment->getId() || $paySystemId !== (int)$this->service->getField('ID')) {
            $result->addError(new Error('Alfa-Bank callback: bank order does not belong to this payment'));
            return $result;
        }

        $fields = AlfaBank::psFields($mdOrder, $data);
        if ((int)($data['orderStatus'] ?? -1) === AlfaBank::STATUS_DEPOSITED) {
            if (AlfaBank::isAmountCorrect($payment, $data)) {
                if (!$payment->isPaid()) {
                    $result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
                }
            } else {
                $fields['PS_STATUS'] = 'N';
                $fields['PS_STATUS_DESCRIPTION'] = mb_substr($fields['PS_STATUS_DESCRIPTION'] . '. Сумма не совпадает с заказом', 0, 250);
                $result->addError(new Error('Alfa-Bank callback: amount mismatch'));
            }
        }
        $result->setPsData($fields);

        return $result;
    }

    public function getPaymentIdFromRequest(Request $request)
    {
        [$paymentId] = AlfaBank::parseOrderNumber((string)$request->get('orderNumber'));
        return $paymentId > 0 ? $paymentId : null;
    }

    public static function getIndicativeFields()
    {
        return ['mdOrder', 'orderNumber', 'operation'];
    }

    protected static function isMyResponseExtended(Request $request, $paySystemId)
    {
        [, $psId] = AlfaBank::parseOrderNumber((string)$request->get('orderNumber'));
        return $psId > 0 && $psId === (int)$paySystemId;
    }
}
