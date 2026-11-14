<?php
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
/** @global CMain $APPLICATION */
$APPLICATION->SetTitle("Личный кабинет");

fk_account_open("addresses", "Адреса доставки");
$APPLICATION->IncludeComponent("bitrix:sale.personal.profile.list", "bootstrap_v4", array(
	"PATH_TO_DETAIL" => "/personal/addresses/edit/?ID=#ID#",
	"PATH_TO_DELETE" => "/personal/addresses/?del_id=#ID#",
	"PER_PAGE" => "20",
	"SET_TITLE" => "N",
	"USE_AJAX_LOCATIONS" => "N",
), false);
fk_account_close();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
