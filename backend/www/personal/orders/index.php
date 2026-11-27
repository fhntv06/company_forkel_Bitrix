<?php
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
/** @global CMain $APPLICATION */
$APPLICATION->SetTitle("Личный кабинет");

fk_account_open("orders", "История заказов");
$APPLICATION->IncludeComponent("bitrix:sale.personal.order.list", "bootstrap_v4", array(
	"PATH_TO_DETAIL" => "/personal/orders/detail/?ID=#ID#",
	"PATH_TO_COPY" => "/personal/cart/",
	"PATH_TO_BASKET" => "/personal/cart/",
	"PATH_TO_PAYMENT" => "/personal/order/payment/",
	"PATH_TO_CANCEL" => "/personal/orders/detail/?ID=#ID#",
	"ORDERS_PER_PAGE" => "10",
	"SAVE_IN_SESSION" => "N",
	"SET_TITLE" => "N",
	"CACHE_TYPE" => "N",
), false);
fk_account_close();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
