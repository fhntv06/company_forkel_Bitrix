<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */
/** @var array $arParams */
/** @global CMain $APPLICATION */
/**
 * Страница товара (V4: .fk-pdp). Цвета — торговые предложения (COLOR); смена цвета, количество и «В корзину» — js/pdp.js.
 * Данные предложений отдаются в data-атрибутах кнопок цвета.
 */
$e = static function ($v): string {
    return htmlspecialcharsbx((string)$v);
};
$flat = static function ($v): string {
    return trim(strip_tags(is_array($v) ? implode(', ', $v) : (string)$v));
};
$fkColorHex = [
    'Белый' => '#FBFBFA', 'Чёрный' => '#2A2C2F', 'Серый' => '#9AA0A6',
    'Светло-серый' => '#D3D6D9', 'Кашемир' => '#CDBFAA', 'Золотой' => '#C3A464',
];
$fileSrc = static function ($id, int $size = 900): string {
    $id = (int)$id;
    if ($id <= 0) {
        return '';
    }
    $r = \CFile::ResizeImageGet($id, ['width' => $size, 'height' => $size], BX_RESIZE_IMAGE_PROPORTIONAL);
    return (string)($r['src'] ?? '');
};
$photo = static function (array $el) use ($fileSrc): string {
    foreach (['DETAIL_PICTURE', 'PREVIEW_PICTURE'] as $f) {
        if (!empty($el[$f]['SRC'])) {
            return $el[$f]['SRC'];
        }
    }
    $v = $el['PROPERTIES']['MORE_PHOTO']['VALUE'] ?? null;
    if (is_array($v)) {
        $v = reset($v);
    }
    return $fileSrc($v);
};
// Галерея под основным фото: основное фото (детальная картинка, иначе картинка анонса), картинка анонса, затем MORE_PHOTO.
// Одинаковые файлы не повторяются. Миниатюры 240×240 (для 120×120 с запасом на ретину), крупное — до 1200 px.
$gallery = static function (array $el): array {
    $ids = [];
    $add = static function ($v) use (&$ids): void {
        $id = is_array($v) ? (int)($v['ID'] ?? 0) : (int)$v;
        if ($id > 0 && !in_array($id, $ids, true)) {
            $ids[] = $id;
        }
    };
    $add($el['DETAIL_PICTURE'] ?? 0);
    $add($el['PREVIEW_PICTURE'] ?? 0);
    foreach ((array)($el['PROPERTIES']['MORE_PHOTO']['VALUE'] ?? []) as $id) {
        $add($id);
    }
    $list = [];
    foreach ($ids as $id) {
        $t = \CFile::ResizeImageGet($id, ['width' => 240, 'height' => 240], BX_RESIZE_IMAGE_EXACT);
        $f = \CFile::ResizeImageGet($id, ['width' => 1200, 'height' => 1200], BX_RESIZE_IMAGE_PROPORTIONAL);
        if (!empty($t['src']) && !empty($f['src'])) {
            $list[] = ['t' => $t['src'], 'f' => $f['src']];
        }
    }
    return $list;
};
$prop = static function (array $el, string $code) use ($flat): string {
    return $flat($el['PROPERTIES'][$code]['VALUE'] ?? '');
};

// Остаток для показа: только если включён количественный учёт, иначе просто «В наличии».
$qtyOf = static function (array $el): int {
    $p = $el['PRODUCT'] ?? [];
    $trace = (string)($p['QUANTITY_TRACE'] ?? 'N');
    if ($trace === 'D') { // «по настройке магазина»
        $trace = (string)\Bitrix\Main\Config\Option::get('catalog', 'default_quantity_trace', 'N');
    }
    return ($trace === 'Y' || $trace === '1') ? max(0, (int)($p['QUANTITY'] ?? 0)) : -1;
};
$stockText = static function (bool $can, int $qty): string {
    if (!$can) {
        return 'Нет в наличии';
    }
    return $qty > 0 ? 'В наличии: ' . number_format($qty, 0, '', ' ') . ' шт.' : 'В наличии';
};

$name = $arResult['NAME'];
$article = $prop($arResult, 'ARTICLE');
$offers = $arResult['OFFERS'] ?? [];
$parentGallery = $gallery($arResult);
$selectedId = (int)($arResult['OFFER_ID_SELECTED'] ?? 0);
$variants = [];
if ($offers) {
    foreach ($offers as $o) {
        $price = $o['ITEM_PRICES'][$o['ITEM_PRICE_SELECTED'] ?? 0] ?? null;
        $color = $flat($o['PROPERTIES']['COLOR']['VALUE'] ?? '');
        $variants[] = [
            'id' => (int)$o['ID'],
            'color' => $color,
            'sku' => $prop($o, 'ARTICLE') ?: $article,
            'price' => $price ? ($price['PRINT_RATIO_PRICE'] ?? $price['PRINT_PRICE'] ?? '') : '',
            'can' => !empty($o['CAN_BUY']),
            'qty' => $qtyOf($o),
            'code' => $prop($o, 'PRODUCT_CODE') ?: $prop($arResult, 'PRODUCT_CODE'),
            'img' => $photo($o) ?: $photo($arResult),
            'gallery' => $gallery($o) ?: $parentGallery,
        ];
    }
} else {
    $price = $arResult['ITEM_PRICES'][$arResult['ITEM_PRICE_SELECTED'] ?? 0] ?? null;
    $variants[] = [
        'id' => (int)$arResult['ID'], 'color' => '', 'sku' => $article,
        'price' => $price ? ($price['PRINT_RATIO_PRICE'] ?? $price['PRINT_PRICE'] ?? '') : '',
        'can' => !empty($arResult['CAN_BUY']), 'qty' => $qtyOf($arResult),
        'code' => $prop($arResult, 'PRODUCT_CODE'), 'img' => $photo($arResult),
        'gallery' => $parentGallery,
    ];
}
$cur = $variants[0];
foreach ($variants as $v) {
    if ($v['id'] === $selectedId) {
        $cur = $v;
        break;
    }
}
$fileUrl = static function (array $el, string $code) use ($flat): string {
    $v = $el['PROPERTIES'][$code]['VALUE'] ?? '';
    if (is_array($v)) {
        $v = reset($v);
    }
    if (($el['PROPERTIES'][$code]['PROPERTY_TYPE'] ?? '') === 'F') {
        return (int)$v > 0 ? (string)\CFile::GetPath((int)$v) : '';
    }
    return $flat($v);
};
$links = [
    'model' => $fileUrl($arResult, 'MODEL_3D'),
    'video' => $fileUrl($arResult, 'VIDEO'),
    'manual' => $fileUrl($arResult, 'MANUAL'),
];
$skip = ['MORE_PHOTO', 'NEW', 'MODEL_3D', 'VIDEO', 'MANUAL', 'ARTICLE', 'COLLECTION', 'MATERIAL'];
$extra = [];
foreach ($arResult['DISPLAY_PROPERTIES'] ?? [] as $code => $p) {
    if (!in_array($code, $skip, true) && $flat($p['DISPLAY_VALUE'] ?? '') !== '') {
        $extra[$p['NAME']] = $flat($p['DISPLAY_VALUE']);
    }
}
$rows = ['Бренд' => 'FORKEL'];
if ($flat($arResult['DISPLAY_PROPERTIES']['COLLECTION']['DISPLAY_VALUE'] ?? '') !== '') {
    $rows['Серия'] = $flat($arResult['DISPLAY_PROPERTIES']['COLLECTION']['DISPLAY_VALUE']);
}
if ($article !== '') {
    $rows['Артикул'] = $article;
}
if ($flat($arResult['DISPLAY_PROPERTIES']['MATERIAL']['DISPLAY_VALUE'] ?? '') !== '') {
    $rows['Материал'] = $flat($arResult['DISPLAY_PROPERTIES']['MATERIAL']['DISPLAY_VALUE']);
}
$rows = array_merge($rows, $extra);
?>
<div class="fk-pdp" id="pdp" data-pdp data-sessid-url="/local/ajax/sessid.php" data-add-url="/local/ajax/add2basket.php" data-cart-url="<?= $e($arParams['BASKET_URL'] ?? '/personal/cart/') ?>">
    <div class="fk-pdp__gallery">
        <div class="fk-pdp__media tone-light">
            <img src="<?= $e($cur['img']) ?>" alt="<?= $e($name) ?>" style="width:100%;height:100%;object-fit:contain" data-pdp-img>
            <a class="fk-dl" href="<?= $e($cur['img']) ?>" download data-pdp-dl aria-label="Скачать фото"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4v11M7 11l5 5 5-5M5 20h14"/></svg></a>
        </div>
        <div class="fk-pdp__thumbs" data-pdp-thumbs role="group" aria-label="Фотографии товара"<?= count($cur['gallery']) > 1 ? '' : ' hidden' ?>>
            <button type="button" class="fk-pdp__arrow" data-thumbs-prev aria-label="Предыдущие фото" disabled><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg></button>
            <div class="fk-pdp__thumbs-track" data-thumbs-track>
                <?php foreach ($cur['gallery'] as $i => $g) { ?>
                <button type="button" class="fk-thumb" data-thumb="<?= $i ?>" data-full="<?= $e($g['f']) ?>" aria-label="Фото <?= $i + 1 ?> из <?= count($cur['gallery']) ?>"<?= $i === 0 ? ' aria-current="true"' : '' ?>><img src="<?= $e($g['t']) ?>" alt="" width="120" height="120" loading="lazy" decoding="async"></button>
                <?php } ?>
            </div>
            <button type="button" class="fk-pdp__arrow" data-thumbs-next aria-label="Следующие фото"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg></button>
        </div>
    </div>
    <div class="fk-pdp__info">
        <h1 class="fk-pdp__title"><?= $e($name) ?></h1>
        <p class="fk-pdp__code" data-pdp-code-row<?= $cur['code'] === '' ? ' hidden' : '' ?>>Код: <span data-code data-pdp-code><?= $e($cur['code']) ?></span>
            <button type="button" class="fk-copy-btn" data-copy-code aria-label="Скопировать код"><span aria-hidden="true"></span></button></p>
        <p class="fk-pdp__sku" data-pdp-sku-row<?= $cur['sku'] === '' ? ' hidden' : '' ?>>Артикул: <span data-pdp-sku><?= $e($cur['sku']) ?></span></p>
        <p class="fk-pdp__price" data-pdp-price><?= $cur['price'] ?></p>
        <p class="fk-pdp__stock" data-pdp-stock><?= $e($stockText($cur['can'], $cur['qty'])) ?></p>
        <?php if ($links['model'] || $links['video'] || $links['manual']) { ?>
        <div class="fk-res" role="group" aria-label="Материалы к товару">
            <?php if ($links['model']) { ?><a class="fk-chip fk-btn--secondary" href="<?= $e($links['model']) ?>" target="_blank" rel="noopener noreferrer">3D-модель<span class="fk-vh"> (откроется в новой вкладке)</span></a><?php } ?>
            <?php if ($links['video']) { ?><button class="fk-chip fk-btn--secondary" type="button" data-video="<?= $e($links['video']) ?>" aria-haspopup="dialog">Видео</button><?php } ?>
            <?php if ($links['manual']) { ?><a class="fk-chip fk-btn--secondary" href="<?= $e($links['manual']) ?>" target="_blank" rel="noopener noreferrer">Инструкция<span class="fk-vh"> (откроется в новой вкладке)</span></a><?php } ?>
        </div>
        <?php } ?>
        <?php if (count($variants) > 1 || $cur['color'] !== '') { ?>
        <div>
            <p class="fk-step__label">Цвет: <span data-pdp-color><?= $e($cur['color']) ?></span></p>
            <div class="fk-swatches">
                <?php foreach ($variants as $v) { ?>
                <button type="button" class="fk-swatch" data-offer="<?= $e(\Bitrix\Main\Web\Json::encode($v)) ?>" style="--finish:<?= $e($fkColorHex[$v['color']] ?? '#ccc') ?>" aria-label="<?= $e($v['color'] ?: 'Вариант') ?>"<?= $v['id'] === $cur['id'] ? ' aria-current="true"' : '' ?>></button>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
        <div class="fk-pdp__buy">
            <div class="fk-qty" role="group" aria-label="Количество">
                <button type="button" class="fk-qty__btn" data-qty="-1" aria-label="Уменьшить количество">−</button>
                <input class="fk-qty__input" id="pdp-qty" type="number" inputmode="numeric" min="1" max="99" value="1" aria-label="Количество">
                <button type="button" class="fk-qty__btn" data-qty="1" aria-label="Увеличить количество">+</button>
            </div>
            <button class="fk-btn fk-btn--primary fk-pdp__cart" type="button" id="pdp-add"<?= $cur['can'] ? '' : ' disabled' ?>>В корзину</button>
        </div>
        <button class="fk-btn fk-btn--secondary fk-pdp__oneclick" type="button" data-oneclick data-fb-context aria-haspopup="dialog">Купить в 1 клик</button>
        <button class="fk-btn fk-btn--secondary fk-pdp__oneclick" type="button" data-feedback data-fb-context aria-haspopup="dialog">Задать вопрос</button>
        <p class="fk-pdp__more fk-pdp__cartlink" id="pdp-cartlink" hidden><a class="fk-link fk-link--tap" href="<?= $e($arParams['BASKET_URL'] ?? '/personal/cart/') ?>">Перейти в корзину</a></p>
        <p class="fk-pdp__more" id="pdp-msg" role="status" aria-live="polite" hidden></p>
    </div>
    <section class="fk-pdp__specs" aria-labelledby="specs-title">
        <h2 id="specs-title">Характеристики</h2>
        <div class="fk-chars-groups">
            <div class="fk-chars"><h3>Основные</h3>
                <dl>
                    <?php foreach ($rows as $label => $val) { ?>
                        <div class="fk-char" data-copyable tabindex="0" role="button" aria-label="Скопировать: <?= $e($label) ?> — <?= $e($val) ?>"><dt><?= $e($label) ?></dt><dd><?= $e($val) ?></dd></div>
                    <?php } ?>
                </dl>
            </div>
        </div>
    </section>

    <?php if (trim((string)$arResult['DETAIL_TEXT']) !== '') { ?>
        <section class="fk-pdp__desc" aria-labelledby="desc-title">
            <h2 id="desc-title">Описание</h2>
            <?= $arResult['DETAIL_TEXT'] ?>
        </section>
    <?php } ?>
</div>

<?php
// Сопутствующие товары: свойство ACCESSORIES (привязка к товарам каталога, множественное). Похожие — из того же раздела.
$accIds = array_values(array_filter(array_map('intval', (array)($arResult['PROPERTIES']['ACCESSORIES']['VALUE'] ?? []))));
if ($accIds) {
    fk_product_block('Сопутствующие товары', ['ID' => $accIds], 0, 8);
}
fk_product_block('Похожие товары', ['!ID' => (int)$arResult['ID']], (int)($arResult['IBLOCK_SECTION_ID'] ?? 0), 4);
