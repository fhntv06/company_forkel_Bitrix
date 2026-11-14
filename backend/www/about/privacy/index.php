<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
/** @global CMain $APPLICATION */
$APPLICATION->SetTitle("Политика обработки персональных данных");
$APPLICATION->SetPageProperty("fk_lead", "Как FORKEL собирает, хранит и использует персональные данные.");
?>
<section class="fk-ab-block fk-ab-block--full" aria-label="Текст политики">
	<div class="fk-ab-block__text">
		<?php $APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => "/include/fk_privacy_text.php"), false); ?>
	</div>
</section>
<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
