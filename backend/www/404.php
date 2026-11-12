<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/urlrewrite.php');

CHTTP::SetStatus("404 Not Found");
@define("ERROR_404","Y");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

/** @global CMain $APPLICATION */

$APPLICATION->SetTitle("Страница не найдена");
$APPLICATION->SetPageProperty("fk_lead", "Неправильно набран адрес, или такой страницы на сайте больше нет.");
?>
<div class="fk-empty">
	<h2>Попробуйте начать с главной или каталога</h2>
	<p class="fk-ab-hero__cta">
		<a class="fk-btn fk-btn--primary" href="<?= SITE_DIR ?>">На главную</a>
		<a class="fk-btn fk-btn--secondary" href="<?= SITE_DIR ?>catalog/">Открыть каталог</a>
	</p>
</div>
<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
