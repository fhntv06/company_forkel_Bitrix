<?php
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
/** @global CMain $APPLICATION */
$APPLICATION->SetTitle("Личный кабинет");

fk_account_open("addresses", "Адрес доставки");
$APPLICATION->IncludeComponent("bitrix:sale.personal.profile.detail", "bootstrap_v4", array(
	"ID" => (int)($_REQUEST["ID"] ?? 0),
	"PATH_TO_LIST" => "/personal/addresses/",
	"SET_TITLE" => "N",
	"USE_AJAX_LOCATIONS" => "N",
), false);
fk_account_close();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
