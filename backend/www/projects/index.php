<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
/** @global CMain $APPLICATION */
$APPLICATION->SetTitle("Проекты");
$APPLICATION->SetPageProperty("fk_lead", "Реализованные проекты с изделиями FORKEL.");
$APPLICATION->SetPageProperty("fk_hero_art", "fk_projects_hero_art");
$fkIntro = fk_include_text("fk_projects_intro");
if ($fkIntro !== "" && $APPLICATION->GetCurPage(false) === "/projects/") {
	echo '<p class="fk-prj-intro">'.htmlspecialcharsbx($fkIntro).'</p>';
}
$APPLICATION->IncludeComponent("bitrix:news", "fk_projects", array(
	"IBLOCK_TYPE" => "",
	"IBLOCK_ID" => fk_iblock_id("projects"),
	"NEWS_COUNT" => "12",
	"USE_SEARCH" => "N",
	"USE_RSS" => "N",
	"USE_RATING" => "N",
	"USE_CATEGORIES" => "N",
	"USE_REVIEW" => "N",
	"USE_FILTER" => "N",
	"SORT_BY1" => "SORT",
	"SORT_ORDER1" => "ASC",
	"SORT_BY2" => "ACTIVE_FROM",
	"SORT_ORDER2" => "DESC",
	"CHECK_DATES" => "Y",
	"SEF_MODE" => "Y",
	"SEF_FOLDER" => "/projects/",
	"SEF_URL_TEMPLATES" => array(
		"news" => "",
		"section" => "",
		"detail" => "#ELEMENT_CODE#/",
	),
	"LIST_PROPERTY_CODE" => array("ROOM", "COLLECTION"),
	"SET_TITLE" => "N",
	"ADD_SECTIONS_CHAIN" => "N",
	"ADD_ELEMENT_CHAIN" => "Y",
	"SET_STATUS_404" => "Y",
	"SHOW_404" => "Y",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "3600",
	"CACHE_GROUPS" => "Y",
	"DISPLAY_TOP_PAGER" => "N",
	"DISPLAY_BOTTOM_PAGER" => "Y",
	"PAGER_TEMPLATE" => "round",
	"PAGER_SHOW_ALWAYS" => "N",
	"PAGER_DESC_NUMBERING" => "N",
	"PAGER_SHOW_ALL" => "N",
), false);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
