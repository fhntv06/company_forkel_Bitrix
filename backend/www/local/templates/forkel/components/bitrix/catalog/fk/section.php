<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Список товаров каталога (V4): категории, поиск и сортировка, фильтр, сетка карточек.
 * Один <form method="get"> на панель инструментов и фильтр: set_filter=Y всегда, параметры фильтра — arrFilter_*, поиск — q, сортировка — sort.
 * @var array $arParams
 * @var array $arResult
 * @global CMain $APPLICATION
 * @var CBitrixComponent $component
 */
$filterName = $arParams['FILTER_NAME'] ?: 'arrFilter';
$sectionId = (int)($arResult['VARIABLES']['SECTION_ID'] ?? 0);
$folder = $arResult['FOLDER'] ?? '/catalog/';

// Поиск по названию и сортировка (параметры запроса q и sort).
$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$q = trim((string)$request->get('q'));
$sortKey = (string)$request->get('sort');
$sorts = [
    'default' => ['По умолчанию', 'sort', 'asc'],
    'name' => ['По названию', 'name', 'asc'],
    'price-asc' => ['Цена: по возрастанию', 'SCALED_PRICE_1', 'asc'],
    'price-desc' => ['Цена: по убыванию', 'SCALED_PRICE_1', 'desc'],
];
if (!isset($sorts[$sortKey])) {
    $sortKey = 'default';
}
$sortParams = [
    'ELEMENT_SORT_FIELD' => $sorts[$sortKey][1],
    'ELEMENT_SORT_ORDER' => $sorts[$sortKey][2],
];
?>
<nav class="fk-cat-nav" aria-label="Категории каталога">
    <?php
    $APPLICATION->IncludeComponent('bitrix:catalog.section.list', 'fk_chips', [
        'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
        'IBLOCK_ID' => $arParams['IBLOCK_ID'],
        'SECTION_ID' => 0,
        'SECTION_CODE' => '',
        'CURRENT_SECTION_ID' => $sectionId,
        'TOP_DEPTH' => 1,
        'COUNT_ELEMENTS' => 'N',
        'ADD_SECTIONS_CHAIN' => 'N',
        'CACHE_TYPE' => $arParams['CACHE_TYPE'],
        'CACHE_TIME' => $arParams['CACHE_TIME'],
        'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
        'CATALOG_URL' => $folder,
    ], $component, ['HIDE_ICONS' => 'Y']);
    ?>
</nav>

<form class="fk-catalog-form" method="get" action="<?= htmlspecialcharsbx($APPLICATION->GetCurPage(false)) ?>">
    <input type="hidden" name="set_filter" value="Y">
    <div class="fk-toolbar">
        <div class="fk-search">
            <label class="fk-field__label" for="cat-search">Поиск по названию и артикулу</label>
            <input class="fk-field" id="cat-search" name="q" type="search" value="<?= htmlspecialcharsbx($q) ?>" placeholder="Название или артикул" autocomplete="off">
        </div>
        <div class="fk-sort">
            <label class="fk-field__label" for="cat-sort">Сортировка</label>
            <select class="fk-field" id="cat-sort" name="sort" data-submit-on-change>
                <?php foreach ($sorts as $key => $s) { ?>
                    <option value="<?= $key ?>"<?= $key === $sortKey ? ' selected' : '' ?>><?= htmlspecialcharsbx($s[0]) ?></option>
                <?php } ?>
            </select>
        </div>
        <button class="fk-btn fk-btn--secondary fk-filters-toggle" id="cat-filters-toggle" type="button" aria-expanded="false" aria-controls="cat-filters">Фильтры</button>
    </div>

    <div class="fk-catalog">
        <?php
        $APPLICATION->IncludeComponent('bitrix:catalog.smart.filter', 'fk', [
            'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
            'IBLOCK_ID' => $arParams['IBLOCK_ID'],
            'SECTION_ID' => $sectionId,
            'FILTER_NAME' => $filterName,
            'PRICE_CODE' => $arParams['~PRICE_CODE'],
            'CACHE_TYPE' => $arParams['CACHE_TYPE'],
            'CACHE_TIME' => $arParams['CACHE_TIME'],
            'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
            'SAVE_IN_SESSION' => 'N',
            'XML_EXPORT' => 'N',
            'SECTION_TITLE' => 'NAME',
            'SECTION_DESCRIPTION' => 'DESCRIPTION',
            'HIDE_NOT_AVAILABLE' => $arParams['HIDE_NOT_AVAILABLE'],
            'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
            'SEF_MODE' => 'N', // фильтр работает обычными GET-параметрами
            'RESET_URL' => $APPLICATION->GetCurPage(false),
        ], $component, ['HIDE_ICONS' => 'Y']);

        if ($q !== '') {
            // Поиск по названию или артикулу (свойство ARTICLE).
            $GLOBALS[$filterName][] = ['LOGIC' => 'OR', ['%NAME' => $q], ['%PROPERTY_ARTICLE' => $q]];
        }
        ?>
        <div class="fk-results">
            <?php
            $APPLICATION->IncludeComponent('bitrix:catalog.section', 'fk', array_merge([
                'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
                'IBLOCK_ID' => $arParams['IBLOCK_ID'],
                'ELEMENT_SORT_FIELD2' => $arParams['ELEMENT_SORT_FIELD2'],
                'ELEMENT_SORT_ORDER2' => $arParams['ELEMENT_SORT_ORDER2'],
                'PROPERTY_CODE' => $arParams['LIST_PROPERTY_CODE'] ?? [],
                'INCLUDE_SUBSECTIONS' => 'Y',
                'SHOW_ALL_WO_SECTION' => 'Y',
                'BASKET_URL' => $arParams['BASKET_URL'],
                'ACTION_VARIABLE' => $arParams['ACTION_VARIABLE'],
                'PRODUCT_ID_VARIABLE' => $arParams['PRODUCT_ID_VARIABLE'],
                'SECTION_ID_VARIABLE' => $arParams['SECTION_ID_VARIABLE'],
                'PRODUCT_QUANTITY_VARIABLE' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
                'PRODUCT_PROPS_VARIABLE' => $arParams['PRODUCT_PROPS_VARIABLE'],
                'FILTER_NAME' => $filterName,
                'CACHE_TYPE' => $arParams['CACHE_TYPE'],
                'CACHE_TIME' => $arParams['CACHE_TIME'],
                'CACHE_FILTER' => 'N',
                'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
                'SET_TITLE' => 'N',
                'SET_STATUS_404' => $arParams['SET_STATUS_404'],
                'SHOW_404' => 'N',
                'PAGE_ELEMENT_COUNT' => $arParams['PAGE_ELEMENT_COUNT'],
                'PRICE_CODE' => $arParams['~PRICE_CODE'],
                'USE_PRICE_COUNT' => $arParams['USE_PRICE_COUNT'],
                'SHOW_PRICE_COUNT' => $arParams['SHOW_PRICE_COUNT'],
                'PRICE_VAT_INCLUDE' => $arParams['PRICE_VAT_INCLUDE'],
                'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                'HIDE_NOT_AVAILABLE' => $arParams['HIDE_NOT_AVAILABLE'],
                'DISPLAY_TOP_PAGER' => 'N',
                'DISPLAY_BOTTOM_PAGER' => $arParams['DISPLAY_BOTTOM_PAGER'],
                'PAGER_TEMPLATE' => $arParams['PAGER_TEMPLATE'],
                'PAGER_SHOW_ALWAYS' => $arParams['PAGER_SHOW_ALWAYS'],
                'PAGER_DESC_NUMBERING' => $arParams['PAGER_DESC_NUMBERING'],
                'PAGER_DESC_NUMBERING_CACHE_TIME' => $arParams['PAGER_DESC_NUMBERING_CACHE_TIME'],
                'PAGER_SHOW_ALL' => $arParams['PAGER_SHOW_ALL'],
                'OFFERS_FIELD_CODE' => $arParams['LIST_OFFERS_FIELD_CODE'] ?? [],
                'OFFERS_PROPERTY_CODE' => $arParams['LIST_OFFERS_PROPERTY_CODE'] ?? [],
                'OFFERS_SORT_FIELD' => $arParams['OFFERS_SORT_FIELD'],
                'OFFERS_SORT_ORDER' => $arParams['OFFERS_SORT_ORDER'],
                'OFFERS_SORT_FIELD2' => $arParams['OFFERS_SORT_FIELD2'],
                'OFFERS_SORT_ORDER2' => $arParams['OFFERS_SORT_ORDER2'],
                'OFFERS_LIMIT' => 0,
                'SECTION_ID' => $sectionId,
                'SECTION_CODE' => '',
                'SECTION_URL' => $folder . $arResult['URL_TEMPLATES']['section'],
                'DETAIL_URL' => $folder . $arResult['URL_TEMPLATES']['element'],
                'ADD_SECTIONS_CHAIN' => 'N',
                'COMPATIBLE_MODE' => 'N',
            ], $sortParams), $component, ['HIDE_ICONS' => 'Y']);
            ?>
        </div>
    </div>
</form>
