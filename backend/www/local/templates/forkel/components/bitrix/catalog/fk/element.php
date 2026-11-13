<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Страница товара (V4). У товара свой заголовок в шаблоне, общий hero страницы скрываем.
 * @var array $arParams
 * @var array $arResult
 * @global CMain $APPLICATION
 * @var CBitrixComponent $component
 */
$APPLICATION->SetPageProperty('fk_no_hero', 'Y');
$folder = $arResult['FOLDER'] ?? '/catalog/';

$APPLICATION->IncludeComponent('bitrix:catalog.element', 'fk', [
    'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
    'IBLOCK_ID' => $arParams['IBLOCK_ID'],
    'ELEMENT_ID' => $arResult['VARIABLES']['ELEMENT_ID'] ?? 0,
    'ELEMENT_CODE' => $arResult['VARIABLES']['ELEMENT_CODE'] ?? '',
    'SECTION_ID' => $arResult['VARIABLES']['SECTION_ID'] ?? 0,
    'SECTION_CODE' => $arResult['VARIABLES']['SECTION_CODE'] ?? '',
    'CHECK_SECTION_ID_VARIABLE' => 'N',
    'STRICT_SECTION_CHECK' => 'N',
    'PROPERTY_CODE' => $arParams['DETAIL_PROPERTY_CODE'] ?? [],
    'OFFERS_FIELD_CODE' => $arParams['DETAIL_OFFERS_FIELD_CODE'] ?? [],
    'OFFERS_PROPERTY_CODE' => $arParams['DETAIL_OFFERS_PROPERTY_CODE'] ?? [],
    'OFFERS_LIMIT' => 0,
    'OFFERS_SORT_FIELD' => $arParams['OFFERS_SORT_FIELD'],
    'OFFERS_SORT_ORDER' => $arParams['OFFERS_SORT_ORDER'],
    'OFFERS_SORT_FIELD2' => $arParams['OFFERS_SORT_FIELD2'],
    'OFFERS_SORT_ORDER2' => $arParams['OFFERS_SORT_ORDER2'],
    'PRICE_CODE' => $arParams['~PRICE_CODE'],
    'USE_PRICE_COUNT' => $arParams['USE_PRICE_COUNT'],
    'SHOW_PRICE_COUNT' => $arParams['SHOW_PRICE_COUNT'],
    'PRICE_VAT_INCLUDE' => $arParams['PRICE_VAT_INCLUDE'],
    'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
    'HIDE_NOT_AVAILABLE' => $arParams['HIDE_NOT_AVAILABLE'],
    'BASKET_URL' => $arParams['BASKET_URL'],
    'ACTION_VARIABLE' => $arParams['ACTION_VARIABLE'],
    'PRODUCT_ID_VARIABLE' => $arParams['PRODUCT_ID_VARIABLE'],
    'SECTION_ID_VARIABLE' => $arParams['SECTION_ID_VARIABLE'],
    'SECTION_URL' => $folder . $arResult['URL_TEMPLATES']['section'],
    'DETAIL_URL' => $folder . $arResult['URL_TEMPLATES']['element'],
    'SET_TITLE' => 'Y',
    'SET_BROWSER_TITLE' => 'Y',
    'SET_META_KEYWORDS' => 'Y',
    'SET_META_DESCRIPTION' => 'Y',
    'SET_LAST_MODIFIED' => 'N',
    'ADD_SECTIONS_CHAIN' => 'Y',
    'ADD_ELEMENT_CHAIN' => 'Y',
    'SET_STATUS_404' => 'Y',
    'SHOW_404' => 'Y',
    'USE_MAIN_ELEMENT_SECTION' => 'N',
    'CACHE_TYPE' => $arParams['CACHE_TYPE'],
    'CACHE_TIME' => $arParams['CACHE_TIME'],
    'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
    'COMPATIBLE_MODE' => 'N',
], $component);
