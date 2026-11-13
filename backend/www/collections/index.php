<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
/** @global CMain $APPLICATION */
$APPLICATION->SetTitle("Коллекции");
$APPLICATION->IncludeComponent("bitrix:news", "fk", array(
	"IBLOCK_TYPE" => "",
	"IBLOCK_ID" => fk_iblock_id("collections"),
	"NEWS_COUNT" => "20",
	"USE_SEARCH" => "N",
	"USE_RSS" => "N",
	"USE_RATING" => "N",
	"USE_CATEGORIES" => "N",
	"USE_REVIEW" => "N",
	"USE_FILTER" => "N",
	"SORT_BY1" => "SORT",
	"SORT_ORDER1" => "ASC",
	"SORT_BY2" => "NAME",
	"SORT_ORDER2" => "ASC",
	"CHECK_DATES" => "N",
	"SEF_MODE" => "Y",
	"SEF_FOLDER" => "/collections/",
	"SEF_URL_TEMPLATES" => array(
		"news" => "",
		"section" => "",
		"detail" => "#ELEMENT_CODE#/",
	),
	"SET_TITLE" => "Y",
	"ADD_SECTIONS_CHAIN" => "N",
	"ADD_ELEMENT_CHAIN" => "Y",
	"SET_STATUS_404" => "Y",
	"SHOW_404" => "Y",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "3600",
	"CACHE_GROUPS" => "Y",
	"DISPLAY_TOP_PAGER" => "N",
	"DISPLAY_BOTTOM_PAGER" => "N",
), false);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
