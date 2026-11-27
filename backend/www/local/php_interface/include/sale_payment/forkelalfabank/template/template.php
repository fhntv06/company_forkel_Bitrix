<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $params */
$url = (string)($params['URL'] ?? '');
$sum = number_format((float)($params['SUM'] ?? 0), 2, ',', ' ');
$isSbp = ($params['MODE'] ?? '') === 'sbp';
?>
<div class="fk-pay fk-pay--alfabank">
<?php if (!empty($params['PAID'])) { ?>
    <p><strong>Заказ оплачен.</strong> Спасибо!</p>
<?php } elseif (!empty($params['ERROR'])) { ?>
    <p><?= htmlspecialcharsbx($params['ERROR']) ?></p>
<?php } elseif (!empty($params['SANDBOX'])) { ?>
    <p>Сумма к оплате: <strong><?= $sum ?> ₽</strong></p>
    <p style="color:#6b7280">Тестовый режим: эквайринг Альфа-Банка ещё не подключён.</p>
    <p><a class="fk-btn fk-btn--primary" href="<?= htmlspecialcharsbx($url) ?>">Перейти к тестовой оплате</a></p>
<?php } elseif ($url !== '') { ?>
    <p>Сумма к оплате: <strong><?= $sum ?> ₽</strong></p>
    <p><?= $isSbp
        ? 'Вы перейдёте на защищённую страницу Альфа-Банка и сможете оплатить через СБП — по QR-коду или в приложении своего банка.'
        : 'Вы перейдёте на защищённую страницу Альфа-Банка для оплаты картой Мир, Visa или Mastercard.' ?></p>
    <p><a class="fk-btn fk-btn--primary" href="<?= htmlspecialcharsbx($url) ?>">Оплатить</a></p>
<?php } ?>
</div>
