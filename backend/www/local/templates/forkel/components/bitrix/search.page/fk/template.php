<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */
/** @var array $arParams */
/**
 * Результаты поиска (V4: .fk-sr-form, .fk-sr-bar, сетка карточек каталога). В MVP — только товары (DEC-DEV-03):
 * из результатов берутся ID товаров каталога, карточки рисует тот же шаблон, что в каталоге (fk_product_html).
 * Блок проектов скрыт до этапа с поиском по проектам.
 */
$q = trim((string)($arResult['REQUEST']['~QUERY'] ?? ''));
$found = $arResult['SEARCH'] ?? [];
$ids = [];
foreach ($found as $it) {
    if (($it['MODULE_ID'] ?? '') === 'iblock' && ctype_digit((string)($it['ITEM_ID'] ?? ''))) {
        $ids[] = (int)$it['ITEM_ID'];
    }
}
$ids = array_values(array_unique($ids));
$total = (int)($arResult['NAV_RESULT']->NavRecordCount ?? count($ids));
?>
<form class="fk-sr-form" id="sr-form" role="search" action="<?= htmlspecialcharsbx($APPLICATION->GetCurPage(false)) ?>" method="get">
    <label class="fk-field__label" for="sr-input">Поиск по товарам</label>
    <div class="fk-sr-form__row">
        <input class="fk-field" id="sr-input" name="q" type="search" value="<?= htmlspecialcharsbx($q) ?>" autocomplete="off" placeholder="Название, артикул, цвет, коллекция">
        <button class="fk-btn fk-btn--primary" type="submit">Найти</button>
    </div>
</form>
<?php if ($q === '') { return; } ?>

<?php
$html = $ids ? fk_product_html('search-' . md5($q . '|' . implode(',', $ids)), ['ID' => $ids], 0, count($ids), false) : '';
if ($html === '') { ?>
    <div class="fk-empty">
        <h2>Ничего не найдено</h2>
        <p>По запросу «<?= htmlspecialcharsbx($q) ?>» товаров нет. Проверьте написание или попробуйте другое название.</p>
        <a class="fk-btn fk-btn--primary" href="/catalog/">Открыть каталог</a>
    </div>
<?php return; } ?>
<div class="fk-sr-bar">
    <p class="fk-sr-count" role="status">Найдено товаров: <?= $total ?></p>
</div>
<section aria-labelledby="sr-products-title">
    <h2 class="fk-sr-h" id="sr-products-title">Товары</h2>
    <?= $html ?>
</section>
<?php if (!empty($arResult['NAV_STRING'])) { ?><?= $arResult['NAV_STRING'] ?><?php } ?>
