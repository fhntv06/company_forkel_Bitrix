<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */
/** Категории (разделы каталога) в панели «Каталог». Адрес берётся из настроек инфоблока (URL раздела). */
foreach ($arResult['SECTIONS'] as $section) {
    ?><li><a href="<?= htmlspecialcharsbx($section['SECTION_PAGE_URL']) ?>"><?= htmlspecialcharsbx($section['NAME']) ?></a></li>
<?php
}
