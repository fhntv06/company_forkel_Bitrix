<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
/** @global CMain $APPLICATION */
$APPLICATION->SetTitle("Результаты поиска");
$APPLICATION->SetPageProperty("fk_lead", "Найдите изделия по названию, артикулу или цвету.");
$APPLICATION->SetPageProperty("fk_hero_art", "fk_search_hero_art");

// Поиск только по товарам каталога (проекты — позже). Индексацию каталога включает админка: «Настройки → Поиск → Переиндексация».
$APPLICATION->IncludeComponent("bitrix:search.page", "fk", array(
	"RESTART" => "N",
	"CHECK_DATES" => "N",
	"USE_TITLE_RANK" => "N",
	"DEFAULT_SORT" => "rank",
	"arrFILTER" => array("iblock_catalog"),
	"arrFILTER_iblock_catalog" => array(fk_iblock_id("catalog")),
	"SHOW_WHERE" => "N",
	"SHOW_WHEN" => "N",
	"PAGE_RESULT_COUNT" => "12",
	"DISPLAY_TOP_PAGER" => "N",
	"DISPLAY_BOTTOM_PAGER" => "N",
	"PAGER_TITLE" => "Результаты поиска",
	"PAGER_SHOW_ALWAYS" => "N",
	"PAGER_TEMPLATE" => "round",
	"NO_WORD_LOGIC" => "N",
	"USE_LANGUAGE_GUESS" => "N",
	"SHOW_RATING" => "N",
	"AJAX_MODE" => "N",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "3600",
), false);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
