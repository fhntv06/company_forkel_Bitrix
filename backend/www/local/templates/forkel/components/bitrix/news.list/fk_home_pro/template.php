<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */
/**
 * Пункты блока «Профессионалам» на главной (V4: .pro > ul > li > h3 + p). Выводит <li>; обёртку секции и кнопку задаёт index.php.
 * Заголовок — название элемента, текст — анонс.
 */
foreach ($arResult['ITEMS'] as $item) {
    $text = trim(strip_tags((string)$item['PREVIEW_TEXT']));
    ?>
<li>
    <h3><?= htmlspecialcharsbx($item['NAME']) ?></h3>
    <?php if ($text !== '') { ?><p><?= htmlspecialcharsbx($text) ?></p><?php } ?>
</li>
<?php
}
