<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
/** @global CMain $APPLICATION */
$APPLICATION->AddChainItem("Каталог", "/catalog/");
$APPLICATION->SetTitle("Стенды");
$APPLICATION->SetPageProperty("fk_lead", "Стенды с изделиями FORKEL.");
$APPLICATION->SetPageProperty("fk_hero_art", "fk_stands_hero_art");
?>
<p class="fk-prj-intro"><?php $APPLICATION->IncludeComponent("bitrix:main.include", "", array(
	"AREA_FILE_SHOW" => "file",
	"PATH" => "/include/fk_stands_intro.php",
), false); ?></p>
<div class="fk-empty">
	<h2>Стенды пока не добавлены</h2>
	<p>Состав раздела появится после получения материалов. Чтобы узнать о стендах сейчас, свяжитесь с нами.</p>
	<div class="fk-empty__actions">
		<button class="fk-btn fk-btn--primary" type="button" data-feedback aria-haspopup="dialog">Связаться с нами</button>
		<a class="fk-btn fk-btn--secondary" href="/catalog/">В каталог</a>
	</div>
</div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
