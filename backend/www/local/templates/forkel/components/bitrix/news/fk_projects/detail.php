<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Страница проекта (V4 project.html): cover, «О проекте», галерея, «Товары проекта», CTA, «Другие проекты».
 * @var array $arParams
 * @var array $arResult
 * @global CMain $APPLICATION
 * @var CBitrixComponent $component
 */
$APPLICATION->SetPageProperty('fk_no_hero', 'Y'); // у проекта свой cover

$elementId = (int)$APPLICATION->IncludeComponent('bitrix:news.detail', 'fk_project', [
    'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
    'IBLOCK_ID' => $arParams['IBLOCK_ID'],
    'ELEMENT_ID' => $arResult['VARIABLES']['ELEMENT_ID'] ?? 0,
    'ELEMENT_CODE' => $arResult['VARIABLES']['ELEMENT_CODE'] ?? '',
    'CHECK_DATES' => 'Y',
    'FIELD_CODE' => ['NAME', 'PREVIEW_TEXT', 'DETAIL_TEXT', 'PREVIEW_PICTURE', 'DETAIL_PICTURE'],
    'PROPERTY_CODE' => ['ROOM', 'COLLECTION', 'GALLERY'],
    'SET_TITLE' => 'Y',
    'SET_BROWSER_TITLE' => 'Y',
    'SET_META_KEYWORDS' => 'N',
    'SET_META_DESCRIPTION' => 'Y',
    'SET_LAST_MODIFIED' => 'N',
    'ADD_SECTIONS_CHAIN' => 'N',
    'ADD_ELEMENT_CHAIN' => 'Y',
    'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
    'SET_STATUS_404' => 'Y',
    'SHOW_404' => 'Y',
    'CACHE_TYPE' => $arParams['CACHE_TYPE'],
    'CACHE_TIME' => $arParams['CACHE_TIME'],
    'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
], $component);

if ($elementId <= 0) {
    return;
}

// Товары проекта: свойство PRODUCTS (привязка к элементам каталога, множественное).
$productIds = [];
$rs = CIBlockElement::GetProperty((int)$arParams['IBLOCK_ID'], $elementId, ['sort' => 'asc'], ['CODE' => 'PRODUCTS']);
while ($p = $rs->Fetch()) {
    if ((int)$p['VALUE'] > 0) {
        $productIds[] = (int)$p['VALUE'];
    }
}
if ($productIds) {
    fk_product_block('Товары проекта', ['ID' => $productIds], 0, 12);
}
?>
<section class="fk-prj-cta" aria-labelledby="prj-cta">
    <div>
        <h2 id="prj-cta"><?php $APPLICATION->IncludeComponent('bitrix:main.include', '', ['AREA_FILE_SHOW' => 'file', 'PATH' => '/include/fk_project_cta_title.php'], false); ?></h2>
        <p><?php $APPLICATION->IncludeComponent('bitrix:main.include', '', ['AREA_FILE_SHOW' => 'file', 'PATH' => '/include/fk_project_cta_text.php'], false); ?></p>
    </div>
    <button class="fk-btn fk-btn--primary" type="button" data-feedback aria-haspopup="dialog">Связаться с нами</button>
</section>
<?php
$GLOBALS['arrFkOtherProjects'] = ['!ID' => $elementId];
?>
<section class="fk-prj-more" aria-labelledby="prj-more">
    <div class="fk-section-head fk-section-head__row">
        <h2 id="prj-more">Другие проекты</h2>
        <a class="fk-link fk-link--tap" href="<?= htmlspecialcharsbx($arResult['FOLDER']) ?>">Все проекты</a>
    </div>
<?php
$APPLICATION->IncludeComponent('bitrix:news.list', 'fk_projects_page', [
    'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
    'IBLOCK_ID' => $arParams['IBLOCK_ID'],
    'NEWS_COUNT' => '3',
    'SORT_BY1' => $arParams['SORT_BY1'],
    'SORT_ORDER1' => $arParams['SORT_ORDER1'],
    'SORT_BY2' => $arParams['SORT_BY2'],
    'SORT_ORDER2' => $arParams['SORT_ORDER2'],
    'FILTER_NAME' => 'arrFkOtherProjects',
    'CHECK_DATES' => 'Y',
    'FIELD_CODE' => ['PREVIEW_PICTURE', 'DETAIL_PICTURE'],
    'PROPERTY_CODE' => ['ROOM', 'COLLECTION'],
    'DETAIL_URL' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['detail'],
    'SET_TITLE' => 'N',
    'SET_STATUS_404' => 'N',
    'SHOW_404' => 'N',
    'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
    'ADD_SECTIONS_CHAIN' => 'N',
    'CACHE_TYPE' => $arParams['CACHE_TYPE'],
    'CACHE_TIME' => $arParams['CACHE_TIME'],
    'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
    'DISPLAY_TOP_PAGER' => 'N',
    'DISPLAY_BOTTOM_PAGER' => 'N',
    'PARENT_SECTION' => '',
    'STRICT_SECTION_CHECK' => 'N',
    'FK_GRID_MOD' => 'fk-grid--projects-more',
], $component);
?>
</section>
