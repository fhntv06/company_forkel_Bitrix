<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
/** @global CMain $APPLICATION */
$APPLICATION->SetTitle('Подбор рамок и механизмов');
$APPLICATION->SetPageProperty('fk_lead', 'Соберите индивидуальный комплект FORKEL по цвету рамки, материалу и механизму и добавьте его в корзину.');

$APPLICATION->IncludeComponent('forkel:configurator', '', [], false);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
