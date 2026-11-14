<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arParams */
/** @var CMain $APPLICATION */
?>
<?php $APPLICATION->IncludeComponent("bitrix:news.list", "fk_projects_page", array(
    "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
    "IBLOCK_ID" => $arParams["IBLOCK_ID"],
    "NEWS_COUNT" => $arParams["NEWS_COUNT"],
    "SORT_BY1" => $arParams["SORT_BY1"],
    "SORT_ORDER1" => $arParams["SORT_ORDER1"],
    "SORT_BY2" => $arParams["SORT_BY2"],
    "SORT_ORDER2" => $arParams["SORT_ORDER2"],
    "CHECK_DATES" => $arParams["CHECK_DATES"],
    "FIELD_CODE" => array("PREVIEW_PICTURE", "DETAIL_PICTURE"),
    "PROPERTY_CODE" => $arParams["LIST_PROPERTY_CODE"],
    "DETAIL_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["detail"],
    "SET_TITLE" => "N",
    "SET_STATUS_404" => $arParams["SET_STATUS_404"],
    "SHOW_404" => $arParams["SHOW_404"],
    "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
    "ADD_SECTIONS_CHAIN" => "N",
    "CACHE_TYPE" => $arParams["CACHE_TYPE"],
    "CACHE_TIME" => $arParams["CACHE_TIME"],
    "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
    "DISPLAY_TOP_PAGER" => "N",
    "DISPLAY_BOTTOM_PAGER" => $arParams["DISPLAY_BOTTOM_PAGER"],
    "PAGER_TEMPLATE" => $arParams["PAGER_TEMPLATE"],
    "PAGER_SHOW_ALWAYS" => $arParams["PAGER_SHOW_ALWAYS"],
    "PAGER_DESC_NUMBERING" => $arParams["PAGER_DESC_NUMBERING"],
    "PAGER_SHOW_ALL" => $arParams["PAGER_SHOW_ALL"],
    "PARENT_SECTION" => "",
    "STRICT_SECTION_CHECK" => "N",
), $component);
