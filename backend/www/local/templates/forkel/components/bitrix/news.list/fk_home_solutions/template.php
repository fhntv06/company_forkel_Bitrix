<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */
/**
 * Слайды «Готовые решения» (V4: .fk-sol__slide). Выводит <li>; каркас карусели и стрелки задаёт index.php, логику — js/solutions.js.
 * Слайд — элемент инфоблока solutions: фото (детальная, иначе анонс), alt — текст анонса, иначе название.
 * Точки — свойство HOTSPOTS (множественная привязка к товарам каталога); в описании значения координаты «x;y» в процентах
 * от левого верхнего угла фото. Товары с одинаковыми координатами собираются в одну точку.
 */
$items = array_values($arResult['ITEMS']);
$total = count($items);
$pin = '<svg viewBox="0 0 16 16" aria-hidden="true"><path d="M8 2v12M2 8h12"/></svg>';
$n = 0; // сквозной номер точки для id всплывающих блоков
foreach ($items as $i => $item) {
    $pic = $item['DETAIL_PICTURE'] ?? $item['PREVIEW_PICTURE'] ?? null;
    if (empty($pic['SRC'])) {
        continue;
    }
    $alt = trim(strip_tags((string)$item['PREVIEW_TEXT'])) ?: $item['NAME'];
    $ratio = !empty($pic['WIDTH']) && !empty($pic['HEIGHT']) ? round($pic['WIDTH'] / $pic['HEIGHT'], 4) : 0;

    // группировка товаров по координатам
    $groups = [];
    $hs = $item['PROPERTIES']['HOTSPOTS'] ?? [];
    $ids = (array)($hs['VALUE'] ?? []);
    $desc = (array)($hs['DESCRIPTION'] ?? []);
    foreach ($ids as $k => $productId) {
        $productId = (int)$productId;
        if ($productId <= 0 || !preg_match('/(\d+(?:[.,]\d+)?)\s*[;:\s]\s*(\d+(?:[.,]\d+)?)/', (string)($desc[$k] ?? ''), $m)) {
            continue;
        }
        $x = (float)str_replace(',', '.', $m[1]);
        $y = (float)str_replace(',', '.', $m[2]);
        $key = $x . ';' . $y;
        $groups[$key]['x'] = $x;
        $groups[$key]['y'] = $y;
        $groups[$key]['ids'][] = $productId;
    }
    $cx = $groups ? round(reset($groups)['x'] / 100, 3) : 0.5;
    ?>
<li class="fk-sol__slide" style="--img:url('<?= htmlspecialcharsbx($pic['SRC']) ?>');--cx:<?= $cx ?>" role="group" aria-roledescription="слайд" aria-label="Слайд <?= $i + 1 ?> из <?= $total ?>">
    <div class="fk-sol__art"<?= $ratio ? ' style="--ar:' . $ratio . '"' : '' ?>>
        <img src="<?= htmlspecialcharsbx($pic['SRC']) ?>" alt="<?= htmlspecialcharsbx($alt) ?>"<?= $i > 0 ? ' loading="lazy"' : '' ?> decoding="async">
        <?php
        $pops = '';
        foreach ($groups as $g) {
            $n++;
            $products = fk_product_html('sol-' . md5(implode(',', $g['ids'])), ['ID' => $g['ids']], 0, count($g['ids']), true, 'fk_mini');
            if ($products === '') {
                continue;
            }
            $popId = 'hs-pop-' . $n;
            ?>
        <button class="fk-hs" type="button" style="left:<?= $g['x'] ?>%;top:<?= $g['y'] ?>%" aria-expanded="false" aria-controls="<?= $popId ?>" aria-label="Показать товары на фото"><span class="fk-hs__dot"><?= $pin ?></span></button>
            <?php
            $pops .= '<ul class="fk-hs-pop" id="' . $popId . '" hidden aria-label="Товары из этого элемента">' . $products . '</ul>';
        }
        ?>
    </div>
    <?= $pops ?>
</li>
<?php
}
