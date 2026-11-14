<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */
/**
 * Слайды «Готовые решения» (V4: .fk-sol__slide). Выводит <li>; каркас карусели и стрелки задаёт index.php, логику — js/solutions.js.
 * Слайд — элемент инфоблока solutions: фото (детальная, иначе анонс), alt — текст анонса, иначе название.
 * У слайда одна точка: COORDS_POINT — строка «x;y» (проценты от левого верхнего угла фото), HOTSPOTS — товары этой точки
 * (множественная привязка к товарам каталога). Нет координат или товаров — слайд выводится без точки.
 */
$items = array_values($arResult['ITEMS']);
$total = count($items);
$pin = '<svg viewBox="0 0 16 16" aria-hidden="true"><path d="M8 2v12M2 8h12"/></svg>';
foreach ($items as $i => $item) {
    // Картинка слайда: детальная, иначе анонс (поле картинки анонса обязательное).
    $picData = !empty($item['DETAIL_PICTURE']['SRC']) ? $item['DETAIL_PICTURE'] : ($item['PREVIEW_PICTURE'] ?? []);
    $pic = (string)($picData['SRC'] ?? '');
    if ($pic === '') {
        continue; // слайд без картинки не выводим
    }
    $alt = trim(strip_tags((string)$item['PREVIEW_TEXT'])) ?: $item['NAME'];
    $ratio = !empty($picData['WIDTH']) && !empty($picData['HEIGHT']) ? round($picData['WIDTH'] / $picData['HEIGHT'], 4) : 0;

    // Координаты точки читаем один раз.
    $coords = $item['PROPERTIES']['COORDS_POINT']['VALUE'] ?? '';
    $coords = trim((string)(is_array($coords) ? reset($coords) : $coords));
    $x = $y = null;
    if (preg_match('/(\d+(?:[.,]\d+)?)\s*[;:\s]\s*(\d+(?:[.,]\d+)?)/', $coords, $m)) {
        $x = min(100.0, (float)str_replace(',', '.', $m[1]));
        $y = min(100.0, (float)str_replace(',', '.', $m[2]));
    }

    // Товары точки.
    $ids = array_values(array_unique(array_filter(array_map('intval', (array)($item['PROPERTIES']['HOTSPOTS']['VALUE'] ?? [])))));

    $button = $pop = '';
    if ($x !== null && $ids) {
        $products = fk_product_html('sol-' . $item['ID'], ['ID' => $ids], 0, count($ids), true, 'fk_mini');
        if ($products !== '') {
            $popId = 'hs-pop-' . (int)$item['ID']; // ID слайда уникален, дублей не будет
            $button = '<button class="fk-hs" type="button" style="left:' . $x . '%;top:' . $y . '%" aria-expanded="false" aria-controls="' . $popId . '" aria-label="Показать товары на фото"><span class="fk-hs__dot">' . $pin . '</span></button>';
            $pop = '<ul class="fk-hs-pop" id="' . $popId . '" hidden aria-label="Товары из этого элемента">' . $products . '</ul>';
        }
    }
    $cx = $x !== null ? round($x / 100, 3) : 0.5;
    ?>
<li class="fk-sol__slide" style="--img:url('<?= htmlspecialcharsbx($pic) ?>');--cx:<?= $cx ?>" role="group" aria-roledescription="слайд" aria-label="Слайд <?= $i + 1 ?> из <?= $total ?>">
    <div class="fk-sol__art"<?= $ratio ? ' style="--ar:' . $ratio . '"' : '' ?>>
        <img src="<?= htmlspecialcharsbx($pic) ?>" alt="<?= htmlspecialcharsbx($alt) ?>"<?= $i > 0 ? ' loading="lazy"' : '' ?> decoding="async">
        <?= $button ?>
    </div>
    <?= $pop ?>
</li>
<?php
}
