<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
/** @global CMain $APPLICATION */
$APPLICATION->SetTitle("Станьте партнером FORKEL");
$APPLICATION->SetPageProperty("fk_lead", "FORKEL — это не просто розетки и выключатели. Это современная электрика, которая помогает создавать красивые и продуманные интерьеры.");
$APPLICATION->SetPageProperty("fk_cta1_text", "Стать партнёром");
$APPLICATION->SetPageProperty("fk_cta1_link", "#partner-form");
$APPLICATION->SetPageProperty("fk_hero_img", "/local/templates/forkel/assets/images/about/hero.jpg");

// Область включения /include/<имя>.php (правится в админке).
$fkInclude = static function (string $name): void {
	global $APPLICATION;
	$APPLICATION->IncludeComponent("bitrix:main.include", "", array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => "/include/" . $name . ".php",
	), false);
};
$fkCardImage = trim((string)@file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/include/fk_coop_card_image.php"));
?>
<section style="display:flex;align-items:center;justify-content:center;margin:64px auto;padding:32px;background-color:white;border-radius:var(--fk-r-lg)">
	<p style="max-width:900px;font-size:32px"><?php $fkInclude("fk_coop_intro"); ?></p>
</section>

<section class="fk-section" aria-labelledby="coop-adv-title">
	<div class="fk-coop-split">
		<div class="fk-coop-adv">
			<div class="fk-section-head"><h2 id="coop-adv-title"><?php $fkInclude("fk_coop_adv_title"); ?></h2></div>
			<ul class="fk-pro__list"><?php $fkInclude("fk_coop_adv"); ?></ul>
		</div>

		<section class="fk-partner" aria-labelledby="partner-title">
			<div class="fk-partner__head">
				<h2 id="partner-title">Анкета партнёра</h2>
				<a class="fk-partner__close" href="/" aria-label="Закрыть анкету, перейти на главную">&times;</a>
			</div>
			<div class="fk-partner__tabs" role="tablist" aria-label="Направление сотрудничества">
				<button class="fk-partner__tab" type="button" role="tab" id="ptab-designer" aria-selected="false" tabindex="-1" data-role="Дизайнер">Дизайнер</button>
				<button class="fk-partner__tab" type="button" role="tab" id="ptab-wholesale" aria-selected="true" data-role="Оптовик">Оптовик</button>
				<button class="fk-partner__tab" type="button" role="tab" id="ptab-electrician" aria-selected="false" tabindex="-1" data-role="Электрик">Электрик</button>
			</div>
			<form class="fk-partner__form" id="partner-form" action="/local/ajax/feedback.php" method="post" novalidate data-fk-form aria-busy="false" aria-labelledby="partner-title">
				<input type="hidden" name="sessid" value="<?= bitrix_sessid() ?>">
				<input type="hidden" name="mode" value="partner">
				<input type="hidden" name="direction" id="partner-direction" value="Оптовик">
				<div style="position:absolute;left:-9999px" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
				<div class="fk-co-errors" role="alert" hidden></div>
				<div class="fk-partner__row">
					<div class="fk-co-field"><label class="fk-field__label" for="pf-name">Имя</label><input class="fk-field" id="pf-name" name="name" type="text" autocomplete="name" required data-v="name" placeholder="Как к вам обращаться?" aria-describedby="pf-name-err"><p class="fk-co-error" id="pf-name-err" hidden></p></div>
					<div class="fk-co-field"><label class="fk-field__label" for="pf-phone">Телефон</label><input class="fk-field" id="pf-phone" name="contact" type="tel" inputmode="tel" autocomplete="tel" required data-v="phone" placeholder="+7 (___) ___-__-__" aria-describedby="pf-phone-err"><p class="fk-co-error" id="pf-phone-err" hidden></p></div>
				</div>
				<div class="fk-partner__row">
					<div class="fk-co-field"><label class="fk-field__label" for="pf-company">Компания / студия</label><input class="fk-field" id="pf-company" name="company" type="text" autocomplete="organization"></div>
					<div class="fk-co-field"><label class="fk-field__label" for="pf-city">Город</label><input class="fk-field" id="pf-city" name="city" type="text" autocomplete="address-level2"></div>
				</div>
				<div class="fk-co-field">
					<label class="fk-field__label" for="pf-task">Расскажите о задаче</label>
					<textarea class="fk-field fk-co-textarea" id="pf-task" name="task" rows="4" required data-v="text" maxlength="500" placeholder="Объект, стиль, серии и цвета" aria-describedby="pf-counter pf-task-err" data-counter="pf-count"></textarea>
					<p class="fk-co-error" id="pf-task-err" hidden></p>
					<p class="fk-partner__counter" id="pf-counter" aria-live="off"><span id="pf-count">0</span>/500</p>
				</div>
				<details class="fk-partner__more">
					<summary>Дополнительные сведения</summary>
					<div class="fk-partner__row">
						<div class="fk-co-field"><label class="fk-field__label" for="pf-email">E-mail</label><input class="fk-field" id="pf-email" name="email" type="email" autocomplete="email"></div>
						<div class="fk-co-field"><label class="fk-field__label" for="pf-format">Формат работы</label>
							<select class="fk-field" id="pf-format" name="format">
								<option value="">Выберите вариант</option>
								<option>Частный специалист</option>
								<option>Студия-компания</option>
								<option>Магазин-поставщик</option>
								<option>Монтажная бригада</option>
								<option>Объект-застройщик</option>
							</select></div>
					</div>
					<div class="fk-partner__row">
						<div class="fk-co-field"><label class="fk-field__label" for="pf-volume">Примерный объём закупки в месяц</label><input class="fk-field" id="pf-volume" name="volume" type="text"></div>
						<div class="fk-co-field"><label class="fk-field__label" for="pf-time">Удобное время связи</label><input class="fk-field" id="pf-time" name="time" type="text"></div>
					</div>
				</details>
				<label class="fk-check fk-partner__consent"><input type="checkbox" id="pf-consent" name="consent" value="Y" required data-v="consent" aria-describedby="pf-consent-err"><span>Согласен на обработку персональных данных (<a href="/about/privacy/" target="_blank" rel="noopener">Политика обработки данных</a>)</span></label>
				<p class="fk-co-error" id="pf-consent-err" hidden></p>
				<button class="fk-btn fk-btn--primary fk-partner__submit" type="submit">Отправить анкету</button>
				<p class="fk-note fk-partner__note" data-form-done role="status" hidden tabindex="-1">Спасибо! Менеджер свяжется с вами.</p>
			</form>
		</section>
	</div>
</section>

<section class="fk-section" aria-labelledby="coop-terms">
	<div class="fk-grid fk-grid--coop">
		<div class="fk-coop-card">
			<h3 id="coop-terms"><?php $fkInclude("fk_coop_card_title"); ?></h3>
			<p><?php $fkInclude("fk_coop_card_text"); ?></p>
		</div>
		<?php if ($fkCardImage !== "") { ?>
		<div class="fk-coop-card fk-coop-card--with-image" style="background-image:url('<?= htmlspecialcharsbx($fkCardImage) ?>')"></div>
		<?php } ?>
	</div>
</section>

<section class="fk-section" aria-labelledby="coop-who-title">
	<div class="fk-section-head"><h2 id="coop-who-title"><?php $fkInclude("fk_coop_who_title"); ?></h2></div>
	<div class="fk-grid fk-grid--projects">
		<div class="fk-grid--projects__card"><p><?php $fkInclude("fk_coop_who_1"); ?></p></div>
		<div class="fk-grid--projects__card"><p><?php $fkInclude("fk_coop_who_2"); ?></p></div>
	</div>
</section>

<section class="fk-coop-cta" aria-labelledby="coop-cta">
	<div>
		<h2 id="coop-cta"><?php $fkInclude("fk_coop_cta_title"); ?></h2>
		<p><?php $fkInclude("fk_coop_cta_text"); ?></p>
	</div>
	<div class="fk-coop-cta__actions">
		<button class="fk-btn fk-btn--inverse" type="button" data-feedback aria-haspopup="dialog">Написать нам</button>
		<button class="fk-btn fk-btn--ghost" type="button" data-callback aria-haspopup="dialog">Заказать звонок</button>
	</div>
</section>
<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
