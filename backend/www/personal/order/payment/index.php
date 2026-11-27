<?php
/**
 * Страница оплаты и результата оплаты заказа (FR-008 / INT-006).
 * Поддерживает: ?result=success|fail|cancel, ?ORDER_ID=..., а также Beta Sandbox подтверждение оплаты.
 */
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
/** @global CMain $APPLICATION */
$APPLICATION->SetTitle('Оплата заказа');
$APPLICATION->SetPageProperty('fk_lead', 'Статус и подтверждение оплаты заказа FORKEL.');

use Bitrix\Main\Loader;
use Bitrix\Sale\Order;

$resultParam = (string)($_GET['result'] ?? '');
$orderId = (int)($_GET['ORDER_ID'] ?? $_GET['order_id'] ?? 0);
$sandbox = \Forkel\Env::get('PAYMENT_SANDBOX', '1') === '1';
$hasLiveKeys = \Forkel\Env::get('YOOKASSA_SHOP_ID') !== '' && \Forkel\Env::get('YOOKASSA_SECRET_KEY') !== '';
?>
<section class="fk-sec" style="max-width:720px;margin:0 auto">
<?php if ($resultParam === 'success') { ?>
    <div class="fk-card" style="padding:32px">
        <h2 style="margin-top:0">Оплата прошла успешно</h2>
        <p>Спасибо! Платёж подтверждён<?= $orderId > 0 ? ' по заказу №' . $orderId : '' ?>. Менеджер приступил к обработке заказа.</p>
        <p><a class="fk-btn fk-btn--primary" href="/personal/orders/">Перейти к моим заказам</a> <a class="fk-btn fk-btn--secondary" href="/catalog/">В каталог</a></p>
    </div>
<?php } elseif ($resultParam === 'fail' || $resultParam === 'cancel') { ?>
    <div class="fk-card" style="padding:32px">
        <h2 style="margin-top:0"><?= $resultParam === 'cancel' ? 'Оплата отменена' : 'Ошибка при оплате' ?></h2>
        <p>Платёж не был завершён. Вы можете повторить попытку оплаты или выбрать другой способ в личном кабинете.</p>
        <p>
            <?php if ($orderId > 0) { ?>
                <a class="fk-btn fk-btn--primary" href="/personal/order/payment/?ORDER_ID=<?= $orderId ?>">Повторить оплату</a>
            <?php } ?>
            <a class="fk-btn fk-btn--secondary" href="/personal/orders/">Мои заказы</a>
        </p>
    </div>
<?php } elseif ($orderId > 0 && $sandbox && !$hasLiveKeys && Loader::includeModule('sale')) {
    $order = Order::load($orderId);
    ?>
    <div class="fk-card" style="padding:32px">
        <h2 style="margin-top:0">Beta Sandbox — оплата заказа №<?= $orderId ?></h2>
        <?php if ($order) { ?>
            <p>Сумма к оплате: <strong><?= number_format((float)$order->getPrice(), 0, '.', ' ') ?> ₽</strong></p>
            <p>Статус оплаты: <strong><?= $order->isPaid() ? 'Оплачен' : 'Ожидает оплаты' ?></strong></p>
            <p style="color:#6b7280;font-size:.9375rem">Боевые ключи эквайринга (<code>YOOKASSA_SHOP_ID</code> / <code>YOOKASSA_SECRET_KEY</code>) не заданы — активен тестовый режим Beta Sandbox.</p>
            <?php if (!$order->isPaid()) { ?>
                <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:20px">
                    <button type="button" class="fk-btn fk-btn--primary" id="fk-sandbox-pay">Подтвердить тестовую оплату</button>
                    <a class="fk-btn fk-btn--secondary" href="/personal/order/payment/?result=cancel&ORDER_ID=<?= $orderId ?>">Отменить оплату</a>
                </div>
                <script>
                document.getElementById('fk-sandbox-pay')?.addEventListener('click', async function () {
                    this.disabled = true;
                    const res = await fetch('/local/ajax/payment_webhook.php?sessid=<?= bitrix_sessid() ?>', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({event: 'payment.succeeded', object: {id: 'sandbox_' + Date.now(), metadata: {order_id: <?= $orderId ?>}}})
                    });
                    const data = await res.json();
                    if (data.ok) {
                        location.href = '/personal/order/payment/?result=success&ORDER_ID=<?= $orderId ?>';
                    } else {
                        location.href = '/personal/order/payment/?result=fail&ORDER_ID=<?= $orderId ?>';
                    }
                });
                </script>
            <?php } else { ?>
                <p><a class="fk-btn fk-btn--primary" href="/personal/orders/">Перейти к заказам</a></p>
            <?php } ?>
        <?php } else { ?>
            <p>Заказ №<?= $orderId ?> не найден.</p>
        <?php } ?>
    </div>
<?php } elseif ($orderId > 0) {
    $APPLICATION->IncludeComponent('bitrix:sale.order.payment', '', []);
} else { ?>
    <div class="fk-card" style="padding:32px">
        <h2 style="margin-top:0">Оплата заказа</h2>
        <p>Выберите заказ для оплаты в личном кабинете или оформите новый заказ в корзине.</p>
        <p><a class="fk-btn fk-btn--primary" href="/personal/orders/">Мои заказы</a> <a class="fk-btn fk-btn--secondary" href="/personal/cart/">Корзина</a></p>
    </div>
<?php } ?>
</section>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
