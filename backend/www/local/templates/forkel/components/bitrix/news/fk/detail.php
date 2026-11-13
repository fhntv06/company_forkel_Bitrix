<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Страница коллекции (V4): hero коллекции и список товаров с привязкой COLLECTION к этой коллекции.
 * @var array $arParams
 * @var array $arResult
 * @global CMain $APPLICATION
 * @var CBitrixComponent $component
 */
$APPLICATION->SetPageProperty('fk_no_hero', 'Y'); // у коллекции свой hero

$elementId = (int)$APPLICATION->IncludeComponent('bitrix:news.detail', 'fk_collection_hero', [
    'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
    'IBLOCK_ID' => $arParams['IBLOCK_ID'],
    'ELEMENT_ID' => $arResult['VARIABLES']['ELEMENT_ID'] ?? 0,
    'ELEMENT_CODE' => $arResult['VARIABLES']['ELEMENT_CODE'] ?? '',
    'CHECK_DATES' => 'N',
    'FIELD_CODE' => ['NAME', 'PREVIEW_TEXT', 'DETAIL_TEXT', 'PREVIEW_PICTURE', 'DETAIL_PICTURE'],
    'PROPERTY_CODE' => [],
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

if ($elementId > 0) {
    // Параметры списка — как у каталога (/catalog/index.php), чтобы карточки, фильтр и ссылки совпадали.
    $listParams = fk_catalog_params();
    $listResult = [
        'FOLDER' => '/catalog/',
        'URL_TEMPLATES' => ['section' => '#SECTION_CODE_PATH#/', 'element' => '#SECTION_CODE_PATH#/#ELEMENT_CODE#/'],
        'VARIABLES' => ['SECTION_ID' => 0],
    ];
    fk_catalog_list($listParams, $listResult, $component, ['=PROPERTY_COLLECTION' => $elementId], ['COLLECTION']);
}
