<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
/** @global CMain $APPLICATION */
$APPLICATION->SetTitle("Новинки");
$APPLICATION->SetPageProperty("fk_lead", "Свежие позиции каталога FORKEL.");

// Товары с отмеченным флагом «Новинка» (свойство NEW). Список, фильтр и карточки — как в каталоге.
$listParams = fk_catalog_params();
$listParams['FK_NO_CHIPS'] = true; // в V4 на странице «Новинки» нет ссылок на категории
$listResult = [
	'FOLDER' => '/catalog/',
	'URL_TEMPLATES' => ['section' => '#SECTION_CODE_PATH#/', 'element' => '#SECTION_CODE_PATH#/#ELEMENT_CODE#/'],
	'VARIABLES' => ['SECTION_ID' => 0],
];
fk_catalog_list($listParams, $listResult, null, ['!PROPERTY_NEW' => false], ['NEW']);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
