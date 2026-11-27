<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
/** @global CMain $APPLICATION */
$APPLICATION->SetTitle("Новинки");
$APPLICATION->SetPageProperty("fk_no_hero", "Y"); // как на «Контактах»: без баннера-героя, крошки + h1
?>
<h1 class="fk-page-title fk-ct-title" id="new-title">Новинки</h1>
<?php
// Товары с отмеченным флагом «Новинка» (свойство NEW). Список, фильтр и карточки — как в каталоге.
$listParams = fk_catalog_params();
$listParams['FK_NO_CHIPS'] = true; // в V4 на странице «Новинки» нет ссылок на категории
$listResult = [
	'FOLDER' => '/catalog/',
	'URL_TEMPLATES' => ['section' => '#SECTION_CODE_PATH#/', 'element' => '#SECTION_CODE_PATH#/#ELEMENT_CODE#/'],
	'VARIABLES' => ['SECTION_ID' => 0],
];
fk_catalog_list($listParams, $listResult, null, fk_new_filter(), ['NEW']);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
