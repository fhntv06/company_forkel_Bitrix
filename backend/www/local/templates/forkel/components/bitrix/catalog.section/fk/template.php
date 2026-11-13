<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */
/** @var array $arParams */
/** @global CMain $APPLICATION */
/** Сетка карточек товаров каталога (V4: .fk-product-card). Цвета — из торговых предложений (свойство COLOR). */
$fkColorClass = [
    'Белый' => 'white', 'Чёрный' => 'black', 'Серый' => 'gray',
    'Светло-серый' => 'lightgray', 'Кашемир' => 'cashmere', 'Золотой' => 'gold',
];
$items = $arResult['ITEMS'] ?? [];
// Адрес картинки: основное фото, иначе первое из MORE_PHOTO (файл по ID).
$fileSrc = static function ($id): string {
    $id = (int)$id;
    if ($id <= 0) {
        return '';
    }
    $r = \CFile::ResizeImageGet($id, ['width' => 600, 'height' => 600], BX_RESIZE_IMAGE_PROPORTIONAL);
    return (string)($r['src'] ?? '');
};
$firstPhoto = static function (array $el) use ($fileSrc): string {
    if (!empty($el['PREVIEW_PICTURE']['SRC'])) {
        return $el['PREVIEW_PICTURE']['SRC'];
    }
    if (!empty($el['DETAIL_PICTURE']['SRC'])) {
        return $el['DETAIL_PICTURE']['SRC'];
    }
    $v = $el['PROPERTIES']['MORE_PHOTO']['VALUE'] ?? null;
    if (is_array($v)) {
        $v = reset($v);
    }
    return $fileSrc($v);
};
$flat = static function ($v): string {
    return trim(strip_tags(is_array($v) ? implode(', ', $v) : (string)$v));
};
?>
<p class="fk-results__count" role="status" aria-live="polite"><?= $items ? 'Найдено: ' . (int)($arResult['NAV_RESULT']->NavRecordCount ?? count($items)) : '' ?></p>
<?php if (!$items) { ?>
<div class="fk-empty">
    <h2>Ничего не найдено</h2>
    <p>Измените запрос или сбросьте фильтры.</p>
    <a class="fk-btn fk-btn--primary" href="<?= htmlspecialcharsbx($APPLICATION->GetCurPage(false)) ?>">Сбросить фильтры</a>
</div>
<?php return; } ?>
<ul class="fk-grid fk-grid--products fk-grid--catalog">
<?php foreach ($items as $item) {
    $name = htmlspecialcharsbx($item['NAME']);
    $url = htmlspecialcharsbx($item['DETAIL_PAGE_URL']);
    $pic = $firstPhoto($item);
    if ($pic === '') {
        foreach ($item['OFFERS'] ?? [] as $offer) {
            $pic = $firstPhoto($offer);
            if ($pic !== '') {
                break;
            }
        }
    }
    $price = $item['ITEM_START_PRICE'] ?? ($item['ITEM_PRICES'][$item['ITEM_PRICE_SELECTED'] ?? 0] ?? null);
    $priceText = $price ? ($price['PRINT_RATIO_PRICE'] ?? $price['PRINT_PRICE'] ?? '') : '';
    $colors = [];
    foreach ($item['OFFERS'] ?? [] as $offer) {
        $c = trim((string)($offer['PROPERTIES']['COLOR']['VALUE'] ?? ''));
        if ($c !== '') {
            $colors[$c] = $fkColorClass[$c] ?? '';
        }
    }
    $sku = $flat($item['PROPERTIES']['ARTICLE']['VALUE'] ?? '');
    $inStock = false;
    foreach ($item['OFFERS'] ?? [] as $offer) {
        if (!empty($offer['CAN_BUY'])) {
            $inStock = true;
            break;
        }
    }
    if (empty($item['OFFERS'])) {
        $inStock = !empty($item['CAN_BUY']);
    }
    $firstColor = $colors ? array_key_first($colors) : '';
    $coll = $flat($item['DISPLAY_PROPERTIES']['COLLECTION']['DISPLAY_VALUE'] ?? '');
    $mat = $flat($item['DISPLAY_PROPERTIES']['MATERIAL']['DISPLAY_VALUE'] ?? '');
    $meta = $coll . ($coll !== '' && $mat !== '' ? ' · ' : '') . $mat;
    ?>
    <li class="fk-product-card">
        <a class="fk-product-card__media tone-light" href="<?= $url ?>" tabindex="-1" aria-hidden="true">
            <?php if ($pic) { ?><img src="<?= htmlspecialcharsbx($pic) ?>" alt="" loading="lazy" style="width:100%;height:100%;object-fit:contain"><?php } ?>
        </a>
        <h3 class="fk-product-card__name"><a href="<?= $url ?>"><?= $name ?></a></h3>
        <?php if ($sku !== '') { ?><p class="fk-product-card__sku">Арт. <?= htmlspecialcharsbx($sku) ?></p><?php } ?>
        <?php if ($firstColor !== '') { ?><p class="fk-product-card__sku">Цвет: <?= htmlspecialcharsbx($firstColor) ?></p><?php } ?>
        <?php if ($meta !== '') { ?><p class="fk-product-card__sku"><?= htmlspecialcharsbx($meta) ?></p><?php } ?>
        <?php if ($colors) { ?>
        <div class="fk-variants">
            <ul class="fk-dots" aria-label="Цветовые варианты">
                <?php foreach ($colors as $label => $cls) { ?>
                <li<?= $cls ? ' class="fin-' . $cls . '"' : '' ?> title="<?= htmlspecialcharsbx($label) ?>"><span class="fk-vh"><?= htmlspecialcharsbx($label) ?></span></li>
                <?php } ?>
            </ul>
        </div>
        <?php } ?>
        <?php if ($priceText !== '') { ?><p class="fk-product-card__price"><?= $priceText ?></p><?php } ?>
        <p class="fk-product-card__stock"><?= $inStock ? 'В наличии' : 'Нет в наличии' ?></p>
        <div class="fk-product-card__actions">
            <a class="fk-btn fk-btn--secondary fk-btn--card" href="<?= $url ?>">Подробнее<span class="fk-vh"> — <?= $name ?></span></a>
        </div>
    </li>
<?php } ?>
</ul>
<?= $arResult['NAV_STRING'] ?? '' ?>
