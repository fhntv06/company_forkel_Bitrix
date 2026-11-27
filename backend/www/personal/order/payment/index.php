<?php
/**
 * Страница оплаты и возврата из Альфа-Банка (FR-008 / INT-006, DEC-013 / TASK-019).
 *   ?ORDER_ID=N                 — переход к оплате (или тестовая оплата в песочнице);
 *   ?result=success&ORDER_ID=N  — возврат из банка: статус сверяется с банком (оплачено / ожидание / отказ);
 *   ?result=fail|cancel&ORDER_ID=N — оплата не прошла, можно повторить.
 * Заказ показывается только его владельцу (или администратору).
 */
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
/** @global CMain $APPLICATION */
/** @global CUser $USER */
$APPLICATION->SetTitle('Оплата заказа');
$APPLICATION->SetPageProperty('fk_lead', 'Статус и подтверждение оплаты заказа FORKEL.');

use Bitrix\Main\Loader;
use Bitrix\Sale\Order;
use Bitrix\Sale\PaySystem;
use Forkel\AlfaBank;

$resultParam = (string)($_GET['result'] ?? '');
$orderId = (int)($_GET['ORDER_ID'] ?? $_GET['order_id'] ?? 0);

$order = null;
$onlinePayment = null;
if ($orderId > 0 && Loader::includeModule('sale')) {
    $loaded = Order::load($orderId);
    $sessionOrders = array_map('intval', (array)($_SESSION['SALE_ORDER_ID'] ?? []));
    if ($loaded && (
        ($USER->IsAuthorized() && (int)$loaded->getUserId() === (int)$USER->GetID())
        || in_array($orderId, $sessionOrders, true)
        || $USER->IsAdmin()
    )) {
        $order = $loaded;
        foreach ($order->getPaymentCollection() as $payment) {
            $service = $payment->getPaySystem();
            if ($service && $service->getField('ACTION_FILE') === AlfaBank::HANDLER) {
                $onlinePayment = $payment;
                if (!$payment->isPaid()) {
                    break;
                }
            }
        }
    }
}
$orderNo = $order ? htmlspecialcharsbx((string)$order->getField('ACCOUNT_NUMBER')) : '';
$retryUrl = '/personal/order/payment/?ORDER_ID=' . $orderId;

// Статус после возврата из банка: не верим параметру result, сверяемся с банком.
$state = '';
if ($order && in_array($resultParam, ['success', 'fail', 'cancel'], true)) {
    if ($order->isPaid()) {
        $state = 'paid';
    } elseif ($onlinePayment) {
        $state = AlfaBank::syncPayment($onlinePayment);
        if ($state === 'paid') {
            $order = Order::load($orderId);
        }
    }
}
?>
<section class="fk-sec" style="max-width:720px;margin:0 auto">
<?php if ($resultParam !== '' && $orderId > 0 && !$order) { ?>
    <div class="fk-card" style="padding:32px">
        <h2 style="margin-top:0">Заказ не найден</h2>
        <p>Войдите в личный кабинет, чтобы посмотреть статус оплаты.</p>
        <p><a class="fk-btn fk-btn--primary" href="/personal/orders/">Мои заказы</a></p>
    </div>
<?php } elseif ($state === 'paid') { ?>
    <div class="fk-card" style="padding:32px">
        <h2 style="margin-top:0">Оплата прошла успешно</h2>
        <p>Спасибо! Платёж по заказу №<?= $orderNo ?> подтверждён. Менеджер приступил к обработке заказа.</p>
        <p><a class="fk-btn fk-btn--primary" href="/personal/orders/">Перейти к моим заказам</a> <a class="fk-btn fk-btn--secondary" href="/catalog/">В каталог</a></p>
    </div>
<?php } elseif ($resultParam === 'fail' || $resultParam === 'cancel' || $state === 'declined') { ?>
    <div class="fk-card" style="padding:32px">
        <h2 style="margin-top:0"><?= $resultParam === 'cancel' ? 'Оплата отменена' : 'Оплата не прошла' ?></h2>
        <p>Платёж не был завершён, деньги не списаны. Вы можете повторить оплату или связаться с менеджером.</p>
        <p>
            <?php if ($order) { ?>
                <a class="fk-btn fk-btn--primary" href="<?= $retryUrl ?>">Повторить оплату</a>
            <?php } ?>
            <a class="fk-btn fk-btn--secondary" href="/personal/orders/">Мои заказы</a>
        </p>
    </div>
<?php } elseif ($resultParam === 'success') { ?>
    <div class="fk-card" style="padding:32px">
        <h2 style="margin-top:0">Ожидаем подтверждение банка</h2>
        <p>Платёж по заказу<?= $orderNo !== '' ? ' №' . $orderNo : '' ?> обрабатывается. Обычно это занимает не больше минуты.</p>
        <p>
            <a class="fk-btn fk-btn--primary" href="/personal/order/payment/?result=success&amp;ORDER_ID=<?= $orderId ?>">Проверить снова</a>
            <a class="fk-btn fk-btn--secondary" href="/personal/orders/">Мои заказы</a>
        </p>
    </div>
<?php } elseif ($orderId > 0 && !$order) { ?>
    <div class="fk-card" style="padding:32px">
        <h2 style="margin-top:0">Заказ не найден</h2>
        <p>Войдите в личный кабинет, чтобы оплатить заказ.</p>
        <p><a class="fk-btn fk-btn--primary" href="/personal/orders/">Мои заказы</a></p>
    </div>
<?php } elseif ($order && $order->isPaid()) { ?>
    <div class="fk-card" style="padding:32px">
        <h2 style="margin-top:0">Заказ №<?= $orderNo ?> оплачен</h2>
        <p><a class="fk-btn fk-btn--primary" href="/personal/orders/">Перейти к заказам</a></p>
    </div>
<?php } elseif ($order && $onlinePayment && AlfaBank::isSandbox()) { ?>
    <div class="fk-card" style="padding:32px">
        <h2 style="margin-top:0">Beta Sandbox — оплата заказа №<?= $orderNo ?></h2>
        <p>Сумма к оплате: <strong><?= number_format((float)$onlinePayment->getSum(), 0, '.', ' ') ?> ₽</strong></p>
        <p style="color:#6b7280;font-size:.9375rem">Доступы эквайринга Альфа-Банка (<code>ALFABANK_*</code>) не заданы — активен тестовый режим сайта, деньги не списываются.</p>
        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:20px">
            <button type="button" class="fk-btn fk-btn--primary" id="fk-sandbox-pay">Подтвердить тестовую оплату</button>
            <a class="fk-btn fk-btn--secondary" href="/personal/order/payment/?result=cancel&amp;ORDER_ID=<?= $orderId ?>">Отменить оплату</a>
        </div>
        <script>
        document.getElementById('fk-sandbox-pay')?.addEventListener('click', async function () {
            this.disabled = true;
            let ok = false;
            try {
                const res = await fetch('/local/ajax/payment_webhook.php?sessid=<?= bitrix_sessid() ?>', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({order_id: <?= $orderId ?>})
                });
                ok = (await res.json()).ok === true;
            } catch (e) {}
            location.href = '/personal/order/payment/?result=' + (ok ? 'success' : 'fail') + '&ORDER_ID=<?= $orderId ?>';
        });
        </script>
    </div>
<?php } elseif ($order && $onlinePayment) {
    $service = $onlinePayment->getPaySystem();
    $init = $service->initiatePay($onlinePayment, null, PaySystem\BaseServiceHandler::STRING);
    ?>
    <div class="fk-card" style="padding:32px">
        <h2 style="margin-top:0">Оплата заказа №<?= $orderNo ?></h2>
        <?php if ($init->isSuccess() && $init->getTemplate() !== '') {
            echo $init->getTemplate();
        } else { ?>
            <p>Не удалось перейти к оплате. Попробуйте позже или свяжитесь с менеджером.</p>
        <?php } ?>
    </div>
<?php } elseif ($order) { ?>
    <div class="fk-card" style="padding:32px">
        <h2 style="margin-top:0">Заказ №<?= $orderNo ?></h2>
        <p>Для этого заказа выбран способ оплаты без онлайн-платежа (при получении или по счёту). Менеджер свяжется с вами.</p>
        <p><a class="fk-btn fk-btn--primary" href="/personal/orders/">Мои заказы</a></p>
    </div>
<?php } else { ?>
    <div class="fk-card" style="padding:32px">
        <h2 style="margin-top:0">Оплата заказа</h2>
        <p>Выберите заказ для оплаты в личном кабинете или оформите новый заказ в корзине.</p>
        <p><a class="fk-btn fk-btn--primary" href="/personal/orders/">Мои заказы</a> <a class="fk-btn fk-btn--secondary" href="/personal/cart/">Корзина</a></p>
    </div>
<?php } ?>
</section>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
