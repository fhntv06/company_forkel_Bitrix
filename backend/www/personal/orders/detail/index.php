<?php
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
/** @global CMain $APPLICATION */
$APPLICATION->SetTitle("Личный кабинет");

fk_account_open("orders", "Заказ");
$APPLICATION->IncludeComponent("bitrix:sale.personal.order.detail", "bootstrap_v4", array(
	"ID" => (int)($_REQUEST["ID"] ?? 0),
	"PATH_TO_LIST" => "/personal/orders/",
	"PATH_TO_CANCEL" => "/personal/orders/detail/?ID=#ID#",
	"PATH_TO_PAYMENT" => "/personal/order/payment/",
	"SET_TITLE" => "N",
	"CACHE_TYPE" => "N",
), false);
fk_account_close();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
