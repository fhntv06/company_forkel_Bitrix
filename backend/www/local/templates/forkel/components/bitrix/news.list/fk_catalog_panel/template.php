<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */
/** Коллекции в панели «Каталог». Адрес берётся из настроек инфоблока (URL детальной страницы). */
foreach ($arResult['ITEMS'] as $item) {
    ?><li><a href="<?= htmlspecialcharsbx($item['DETAIL_PAGE_URL']) ?>"><?= htmlspecialcharsbx($item['NAME']) ?></a></li>
<?php
}
