<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */
/** @var array $arParams */
/** Ссылка на корзину со счётчиком позиций (разметка V4). Обновляется при загрузке страницы. */
$num = (int)($arResult['NUM_PRODUCTS'] ?? 0);
$path = !empty($arParams['PATH_TO_BASKET']) ? $arParams['PATH_TO_BASKET'] : '/personal/cart/';
?>
<a class="fk-icon-btn fk-cart-link" href="<?= htmlspecialcharsbx($path) ?>">
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 8h14l-1 12H6L5 8z"/><path d="M9 8V6.5a3 3 0 016 0V8"/></svg>
    <span class="fk-vh">Корзина<?= $num > 0 ? ', товаров: ' . $num : '' ?></span>
    <span class="fk-badge" data-cart-count<?= $num > 0 ? '' : ' hidden' ?> aria-hidden="true"><?= $num ?></span>
</a>
