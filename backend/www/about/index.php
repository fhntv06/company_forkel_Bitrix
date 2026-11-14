<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
/** @global CMain $APPLICATION */
$APPLICATION->SetTitle("О компании");
$APPLICATION->SetPageProperty("fk_lead", "FORKEL — современный бренд электроустановочных изделий, в котором стиль, качество и удобство объединены в одном решении.");
$APPLICATION->SetPageProperty("fk_cta1_text", "Стать партнёром");
$APPLICATION->SetPageProperty("fk_cta1_link", "#feedback");
$APPLICATION->SetPageProperty("fk_cta2_text", "Смотреть каталог");
$APPLICATION->SetPageProperty("fk_cta2_link", "/catalog/");
$APPLICATION->SetPageProperty("fk_hero_img", "/local/templates/forkel/assets/images/about/hero.jpg");
$APPLICATION->SetPageProperty("fk_hero_alt", "Полка с растением и выключателем-розеткой FORKEL на стене");

// Область включения /include/<имя>.php (правится в админке).
$fkInclude = static function (string $name): void {
	global $APPLICATION;
	$APPLICATION->IncludeComponent("bitrix:main.include", "", array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => "/include/" . $name . ".php",
	), false);
};

// Коллекции для кнопок блока «Две серии»
$fkCollections = array();
if (CModule::IncludeModule("iblock") && fk_iblock_id("collections") > 0) {
	$rs = CIBlockElement::GetList(array("SORT" => "ASC", "NAME" => "ASC"), array("IBLOCK_ID" => fk_iblock_id("collections"), "ACTIVE" => "Y"), false, false, array("ID", "NAME", "DETAIL_PAGE_URL"));
	while ($row = $rs->GetNext()) {
		$fkCollections[] = $row;
	}
}
?>
<div class="fk-ab-flow">
	<section class="fk-ab-block fk-ab-block--full" aria-labelledby="ab-about">
		<div class="fk-ab-block__text"><?php $fkInclude("fk_about_1_text"); ?></div>
	</section>

	<section class="fk-ab-block fk-ab-block--text-left" aria-labelledby="ab-approach">
		<div class="fk-ab-block__text"><?php $fkInclude("fk_about_2_text"); ?></div>
		<figure class="fk-ab-block__media"><?php $fkInclude("fk_about_2_photo"); ?></figure>
	</section>

	<section class="fk-ab-block fk-ab-block--photo-left" aria-labelledby="ab-aud">
		<div class="fk-ab-block__text"><?php $fkInclude("fk_about_3_text"); ?></div>
		<figure class="fk-ab-block__media"><?php $fkInclude("fk_about_3_photo"); ?></figure>
	</section>

	<section class="fk-ab-block fk-ab-block--text-left" aria-labelledby="ab-adv">
		<div class="fk-ab-block__text"><?php $fkInclude("fk_about_4_text"); ?></div>
		<figure class="fk-ab-block__media"><?php $fkInclude("fk_about_4_photo"); ?></figure>
	</section>

	<section class="fk-ab-block fk-ab-block--full fk-ab-block--mission" aria-labelledby="ab-mission">
		<div class="fk-ab-block__text"><?php $fkInclude("fk_about_5_text"); ?></div>
	</section>
</div>

<section class="fk-ab-range" aria-labelledby="ab-range">
	<div>
		<h2 id="ab-range"><?php $fkInclude("fk_about_range_title"); ?></h2>
		<p><?php $fkInclude("fk_about_range_text"); ?></p>
	</div>
	<div class="fk-ab-range__links">
		<?php foreach ($fkCollections as $c) { ?>
		<a class="fk-btn fk-btn--inverse" href="<?= htmlspecialcharsbx($c["DETAIL_PAGE_URL"]) ?>">Серия <?= htmlspecialcharsbx($c["NAME"]) ?></a>
		<?php } ?>
		<a class="fk-btn fk-btn--ghost" href="/catalog/">Смотреть каталог</a>
	</div>
</section>

<section class="fk-ab-lead" aria-labelledby="ab-form-title">
	<div class="fk-ab-lead__intro">
		<h2 id="ab-form-title"><?php $fkInclude("fk_about_lead_title"); ?></h2>
		<p><?php $fkInclude("fk_about_lead_text"); ?></p>
		<div class="fk-ab-lead__alt"><button class="fk-btn fk-btn--ghost" type="button" data-callback aria-haspopup="dialog">Заказать звонок</button></div>
	</div>
	<form class="fk-ab-form" id="ab-form" action="/local/ajax/feedback.php" method="post" novalidate data-fk-form>
		<input type="hidden" name="sessid" value="<?= bitrix_sessid() ?>">
		<input type="hidden" name="mode" value="lead">
		<div style="position:absolute;left:-9999px" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
		<div class="fk-co-errors" role="alert" hidden></div>
		<div class="fk-co-field"><label class="fk-field__label" for="ab-name">Имя</label><input class="fk-field" id="ab-name" name="name" type="text" autocomplete="name" required data-v="name" aria-describedby="ab-name-err"><p class="fk-co-error" id="ab-name-err" hidden></p></div>
		<div class="fk-co-field"><label class="fk-field__label" for="ab-contact">Телефон или email</label><input class="fk-field" id="ab-contact" name="contact" type="text" autocomplete="off" required data-v="contact" aria-describedby="ab-contact-err"><p class="fk-co-error" id="ab-contact-err" hidden></p></div>
		<div class="fk-co-field"><label class="fk-field__label" for="ab-role">Вы</label><select class="fk-field" id="ab-role" name="role"><option>Дизайнер или архитектор</option><option>Монтажная организация</option><option>Дилер</option><option>Другое</option></select></div>
		<div class="fk-co-field"><label class="fk-field__label" for="ab-msg">Сообщение (необязательно)</label><textarea class="fk-field fk-co-textarea" id="ab-msg" name="msg" rows="3"></textarea></div>
		<div class="fk-co-field"><label class="fk-check"><input type="checkbox" id="ab-consent" name="consent" value="Y" required data-v="consent" aria-describedby="ab-consent-err"><span>Согласен с обработкой персональных данных (<a href="/about/privacy/" target="_blank" rel="noopener">политика</a>)</span></label><p class="fk-co-error" id="ab-consent-err" hidden></p></div>
		<button class="fk-btn fk-btn--inverse fk-ab-form__submit" type="submit">Отправить</button>
		<div class="fk-ab-done" data-form-done role="status" hidden tabindex="-1"><strong>Спасибо!</strong> Заявка отправлена, мы свяжемся с вами.</div>
	</form>
</section>
<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
