<?php
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
/** @global CMain $APPLICATION */
$APPLICATION->SetTitle("Личный кабинет");

fk_account_open("profile", "Профиль");
$APPLICATION->IncludeComponent("bitrix:main.profile", ".default", array(
	"SET_TITLE" => "N",
	"SEND_INFO" => "N",
	"CHECK_RIGHTS" => "N",
	"USER_PROPERTY_NAME" => "",
	"AJAX_MODE" => "N",
), false);
fk_account_close();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
