<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */
/**
 * Мини-карточки товаров для всплывающего блока точки на слайдере «Готовые решения» (V4: li.fk-mini).
 * Выводит только <li>; <ul class="fk-hs-pop"> задаёт шаблон слайдера.
 */
$flat = static function ($v): string {
    return trim(strip_tags(is_array($v) ? implode(', ', $v) : (string)$v));
};
foreach ($arResult['ITEMS'] ?? [] as $item) {
    $name = htmlspecialcharsbx($item['NAME']);
    $url = htmlspecialcharsbx($item['DETAIL_PAGE_URL']);
    $pic = $item['PREVIEW_PICTURE']['SRC'] ?? $item['DETAIL_PICTURE']['SRC'] ?? '';
    if ($pic === '') {
        foreach ($item['OFFERS'] ?? [] as $o) {
            $pic = $o['PREVIEW_PICTURE']['SRC'] ?? $o['DETAIL_PICTURE']['SRC'] ?? '';
            if ($pic !== '') {
                break;
            }
        }
    }
    $price = $item['ITEM_START_PRICE'] ?? ($item['ITEM_PRICES'][$item['ITEM_PRICE_SELECTED'] ?? 0] ?? null);
    $priceText = $price ? ($price['PRINT_RATIO_PRICE'] ?? $price['PRINT_PRICE'] ?? '') : '';
    $series = $flat($item['DISPLAY_PROPERTIES']['COLLECTION']['DISPLAY_VALUE'] ?? '');
    ?>
<li class="fk-mini">
    <a class="fk-mini__media tone-light" href="<?= $url ?>" tabindex="-1" aria-hidden="true">
        <?php if ($pic !== '') { ?><img src="<?= htmlspecialcharsbx($pic) ?>" alt="" loading="lazy" style="width:100%;height:100%;object-fit:contain"><?php } ?>
    </a>
    <?php if ($series !== '') { ?><p class="fk-mini__series">Серия <?= htmlspecialcharsbx($series) ?></p><?php } ?>
    <h3 class="fk-mini__name"><a href="<?= $url ?>" title="<?= $name ?>"><?= $name ?></a></h3>
    <?php if ($priceText !== '') { ?><p class="fk-mini__price"><?= $priceText ?></p><?php } ?>
    <a class="fk-btn fk-btn--secondary" href="<?= $url ?>">К товару<span class="fk-vh"> — <?= $name ?></span></a>
</li>
<?php
}
