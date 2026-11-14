<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
/** @global CMain $APPLICATION */
$APPLICATION->SetTitle("FORKEL — розетки и выключатели");
$APPLICATION->SetPageProperty("fk_no_hero", "Y"); // у главной свои секции (V4)

// Подключение одной области включения: /include/<имя>.php (правится в админке «Изменить включаемую область»).
$fkInclude = static function (string $name): void {
	global $APPLICATION;
	$APPLICATION->IncludeComponent("bitrix:main.include", "", array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => "/include/" . $name . ".php",
	), false);
};

// Захват вывода компонента: секция показывается, только если в ней есть данные.
$fkCapture = static function (callable $fn): string {
	ob_start();
	$fn();
	return trim((string)ob_get_clean());
};
?>
<!-- 1. Первый экран -->
<section class="hero wrap" aria-labelledby="h1">
	<div class="hero__text">
		<h1 id="h1"><?php $fkInclude("fk_home_hero_title"); ?></h1>
		<p class="lead"><?php $fkInclude("fk_home_hero_lead"); ?></p>
		<div class="actions">
			<a class="btn btn--dark" href="/catalog/">Смотреть каталог
				<svg class="ar" aria-hidden="true"><use href="#arrow"/></svg>
			</a>
			<a class="btn btn--ghost" href="/catalog/new/">Новинки</a>
		</div>
	</div>
	<div class="hero__stage">
		<?php $fkInclude("fk_home_hero_photo"); ?>
		<a class="float float--a" href="#features"><?php $fkInclude("fk_home_hero_float_a"); ?>
			<svg class="ar" aria-hidden="true"><use href="#arrow"/></svg>
		</a>
		<a class="float float--b" href="#features"><?php $fkInclude("fk_home_hero_float_b"); ?>
			<svg class="ar" aria-hidden="true"><use href="#arrow"/></svg>
		</a>
	</div>
</section>

<?php
// 2. Направления: разделы каталога с картинкой раздела
$fkDirs = $fkCapture(static function () {
	global $APPLICATION;
	$APPLICATION->IncludeComponent("bitrix:catalog.section.list", "fk_home_dirs", array(
		"IBLOCK_TYPE" => "",
		"IBLOCK_ID" => fk_iblock_id("catalog"),
		"SECTION_ID" => 0,
		"SECTION_CODE" => "",
		"TOP_DEPTH" => 1,
		"COUNT_ELEMENTS" => "N",
		"SECTION_FIELDS" => array("PICTURE"),
		"SECTION_URL" => "/catalog/#SECTION_CODE_PATH#/",
		"ADD_SECTIONS_CHAIN" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
	), false);
});
if ($fkDirs !== "") { ?>
<section class="sec wrap" aria-labelledby="dir-t">
	<div class="split">
		<div><h2 id="dir-t"><?php $fkInclude("fk_home_dirs_title"); ?></h2></div>
		<ul class="dirs"><?= $fkDirs ?></ul>
	</div>
</section>
<?php } ?>
<?php
// 3. Коллекции: элементы инфоблока collections
$fkCols = $fkCapture(static function () {
	global $APPLICATION;
	$APPLICATION->IncludeComponent("bitrix:news.list", "fk_home_collections", array(
		"IBLOCK_TYPE" => "",
		"IBLOCK_ID" => fk_iblock_id("collections"),
		"NEWS_COUNT" => "6",
		"SORT_BY1" => "SORT",
		"SORT_ORDER1" => "ASC",
		"SORT_BY2" => "NAME",
		"SORT_ORDER2" => "ASC",
		"FIELD_CODE" => array("NAME", "PREVIEW_TEXT", "PREVIEW_PICTURE", "DETAIL_PICTURE"),
		"PROPERTY_CODE" => array(),
		"DETAIL_URL" => "/collections/#ELEMENT_CODE#/",
		"CHECK_DATES" => "N",
		"SET_TITLE" => "N",
		"SET_BROWSER_TITLE" => "N",
		"SET_META_KEYWORDS" => "N",
		"SET_META_DESCRIPTION" => "N",
		"SET_LAST_MODIFIED" => "N",
		"ADD_SECTIONS_CHAIN" => "N",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
		"SET_STATUS_404" => "N",
		"SHOW_404" => "N",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
	), false);
});
if ($fkCols !== "") { ?>
<section class="sec wrap" id="collections" aria-labelledby="col-t">
	<div class="head2"><h2 id="col-t"><?php $fkInclude("fk_home_collections_title"); ?></h2><a class="btn btn--ghost btn--sm" href="/catalog/">Весь каталог</a></div>
	<ul class="cols"><?= $fkCols ?></ul>
</section>
<?php } ?>
<?php
// 4. Преимущества: элементы инфоблока features + четыре пункта из области
$fkFeat = $fkCapture(static function () {
	global $APPLICATION;
	$APPLICATION->IncludeComponent("bitrix:news.list", "fk_home_features", array(
		"IBLOCK_TYPE" => "",
		"IBLOCK_ID" => fk_iblock_id("features"),
		"NEWS_COUNT" => "3",
		"SORT_BY1" => "SORT",
		"SORT_ORDER1" => "ASC",
		"SORT_BY2" => "ID",
		"SORT_ORDER2" => "ASC",
		"FIELD_CODE" => array("NAME", "PREVIEW_TEXT", "PREVIEW_PICTURE", "DETAIL_PICTURE"),
		"PROPERTY_CODE" => array(),
		"CHECK_DATES" => "N",
		"SET_TITLE" => "N",
		"SET_BROWSER_TITLE" => "N",
		"SET_META_KEYWORDS" => "N",
		"SET_META_DESCRIPTION" => "N",
		"SET_LAST_MODIFIED" => "N",
		"ADD_SECTIONS_CHAIN" => "N",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
		"SET_STATUS_404" => "N",
		"SHOW_404" => "N",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
	), false);
});
if ($fkFeat !== "") { ?>
<section class="sec wrap" id="features" aria-labelledby="f-t">
	<h2 class="big" id="f-t"><?php $fkInclude("fk_home_features_title"); ?></h2>
	<ul class="feat"><?= $fkFeat ?></ul>
	<ul class="mini"><?php $fkInclude("fk_home_features_mini"); ?></ul>
</section>
<?php } ?>
<?php
// 5. Новинки: 8 товаров с флагом NEW (как в V4 по дизайну)
$fkNew = fk_product_html("home-new", fk_new_filter(), 0, 8, true);
if ($fkNew !== "") { ?>
<section class="sec wrap" id="new" aria-labelledby="new-t">
	<div class="head2"><h2 id="new-t"><?php $fkInclude("fk_home_new_title"); ?></h2><a class="btn btn--ghost btn--sm" href="/catalog/new/">Все новинки</a></div>
	<?= $fkNew ?>
</section>
<?php } ?>
<?php
// 6. Проекты: элементы инфоблока projects (обложка, помещение, коллекция)
$fkProj = $fkCapture(static function () {
	global $APPLICATION;
	$APPLICATION->IncludeComponent("bitrix:news.list", "fk_home_projects", array(
		"IBLOCK_TYPE" => "",
		"IBLOCK_ID" => fk_iblock_id("projects"),
		"NEWS_COUNT" => "3",
		"SORT_BY1" => "SORT",
		"SORT_ORDER1" => "ASC",
		"SORT_BY2" => "ID",
		"SORT_ORDER2" => "DESC",
		"FIELD_CODE" => array("NAME", "PREVIEW_PICTURE", "DETAIL_PICTURE"),
		"PROPERTY_CODE" => array("ROOM", "COLLECTION"),
		"DETAIL_URL" => "/projects/#ELEMENT_CODE#/",
		"CHECK_DATES" => "N",
		"SET_TITLE" => "N",
		"SET_BROWSER_TITLE" => "N",
		"SET_META_KEYWORDS" => "N",
		"SET_META_DESCRIPTION" => "N",
		"SET_LAST_MODIFIED" => "N",
		"ADD_SECTIONS_CHAIN" => "N",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
		"SET_STATUS_404" => "N",
		"SHOW_404" => "N",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
	), false);
});
if ($fkProj !== "") { ?>
<section class="sec wrap" id="projects" aria-labelledby="projects-title">
	<div class="head2"><h2 id="projects-title"><?php $fkInclude("fk_home_projects_title"); ?></h2><a class="btn btn--ghost btn--sm" href="/projects/">Все проекты</a></div>
	<div style="margin-top:24px">
		<ul class="fk-grid fk-grid--projects"><?= $fkProj ?></ul>
	</div>
</section>
<?php } ?>
<?php
// 7. Готовые решения: слайды из инфоблока solutions, точки — свойство HOTSPOTS (товар + координаты)
$fkSol = $fkCapture(static function () {
	global $APPLICATION;
	$APPLICATION->IncludeComponent("bitrix:news.list", "fk_home_solutions", array(
		"IBLOCK_TYPE" => "",
		"IBLOCK_ID" => fk_iblock_id("solutions"),
		"NEWS_COUNT" => "8",
		"SORT_BY1" => "SORT",
		"SORT_ORDER1" => "ASC",
		"SORT_BY2" => "ID",
		"SORT_ORDER2" => "ASC",
		"FIELD_CODE" => array("NAME", "PREVIEW_TEXT", "PREVIEW_PICTURE", "DETAIL_PICTURE"),
		"PROPERTY_CODE" => array("HOTSPOTS", "COORDS_POINT"),
		"CHECK_DATES" => "N",
		"SET_TITLE" => "N",
		"SET_BROWSER_TITLE" => "N",
		"SET_META_KEYWORDS" => "N",
		"SET_META_DESCRIPTION" => "N",
		"SET_LAST_MODIFIED" => "N",
		"ADD_SECTIONS_CHAIN" => "N",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
		"SET_STATUS_404" => "N",
		"SHOW_404" => "N",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
	), false);
});
if ($fkSol !== "") { ?>
<section class="sec wrap fk-solutions" id="solutions" aria-labelledby="solutions-title">
	<div class="head2"><h2 id="solutions-title"><?php $fkInclude("fk_home_solutions_title"); ?></h2></div>
	<div style="margin-top:24px">
		<div class="fk-sol" id="solutions-slider" role="region" aria-roledescription="карусель" aria-label="Готовые решения">
			<button class="fk-sol-btn fk-sol-btn--prev" type="button" data-sol-prev aria-label="Предыдущий слайд">
				<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>
			</button>
			<button class="fk-sol-btn fk-sol-btn--next" type="button" data-sol-next aria-label="Следующий слайд">
				<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>
			</button>
			<ul class="fk-sol__track" tabindex="0" aria-label="Слайды: прокрутка стрелками влево и вправо"><?= $fkSol ?></ul>
		</div>
		<div class="fk-sol-dots" role="group" aria-label="Выбор слайда"></div>
	</div>
</section>
<?php } ?>
<?php
// 8. Интерьеры: элементы инфоблока interiors (карточки не ссылки)
$fkInt = $fkCapture(static function () {
	global $APPLICATION;
	$APPLICATION->IncludeComponent("bitrix:news.list", "fk_home_interiors", array(
		"IBLOCK_TYPE" => "",
		"IBLOCK_ID" => fk_iblock_id("interiors"),
		"NEWS_COUNT" => "8",
		"SORT_BY1" => "SORT",
		"SORT_ORDER1" => "ASC",
		"SORT_BY2" => "ID",
		"SORT_ORDER2" => "ASC",
		"FIELD_CODE" => array("NAME", "PREVIEW_TEXT", "PREVIEW_PICTURE", "DETAIL_PICTURE"),
		"PROPERTY_CODE" => array(),
		"CHECK_DATES" => "N",
		"SET_TITLE" => "N",
		"SET_BROWSER_TITLE" => "N",
		"SET_META_KEYWORDS" => "N",
		"SET_META_DESCRIPTION" => "N",
		"SET_LAST_MODIFIED" => "N",
		"ADD_SECTIONS_CHAIN" => "N",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
		"SET_STATUS_404" => "N",
		"SHOW_404" => "N",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
	), false);
});
if ($fkInt !== "") { ?>
<section class="sec wrap" id="interiors" aria-labelledby="interiors-title">
	<div class="head2"><h2 id="interiors-title"><?php $fkInclude("fk_home_interiors_title"); ?></h2></div>
	<div style="margin-top:24px">
		<ul class="fk-grid fk-grid--interiors"><?= $fkInt ?></ul>
	</div>
</section>
<?php } ?>
<!-- 9. Шоурум: тексты и фото из областей включения; кнопка открывает окно обратной связи -->
<section class="sec wrap" id="showroom" aria-labelledby="sr-t">
	<div class="show">
		<div class="show__t">
			<h2 id="sr-t"><?php $fkInclude("fk_home_showroom_title"); ?></h2>
			<p><?php $fkInclude("fk_home_showroom_text"); ?></p>
			<button class="btn btn--light" type="button" data-feedback aria-haspopup="dialog">Записаться на визит
				<svg class="ar" aria-hidden="true"><use href="#arrow"/></svg>
			</button>
		</div>
		<div class="show__img"><?php $fkInclude("fk_home_showroom_photo"); ?></div>
	</div>
</section>
<!-- 10. Цифры доверия: заголовок и четыре числа из областей включения (реальные значения заполняет владелец) -->
<section class="sec wrap" aria-labelledby="tr-t">
	<div class="split">
		<h2 id="tr-t"><?php $fkInclude("fk_home_trust_title"); ?></h2>
		<ul class="nums"><?php $fkInclude("fk_home_trust_nums"); ?></ul>
	</div>
</section>
<?php
// 11. Профессионалам: элементы инфоблока pro
$fkPro = $fkCapture(static function () {
	global $APPLICATION;
	$APPLICATION->IncludeComponent("bitrix:news.list", "fk_home_pro", array(
		"IBLOCK_TYPE" => "",
		"IBLOCK_ID" => fk_iblock_id("pro"),
		"NEWS_COUNT" => "6",
		"SORT_BY1" => "SORT",
		"SORT_ORDER1" => "ASC",
		"SORT_BY2" => "ID",
		"SORT_ORDER2" => "ASC",
		"FIELD_CODE" => array("NAME", "PREVIEW_TEXT"),
		"PROPERTY_CODE" => array(),
		"CHECK_DATES" => "N",
		"SET_TITLE" => "N",
		"SET_BROWSER_TITLE" => "N",
		"SET_META_KEYWORDS" => "N",
		"SET_META_DESCRIPTION" => "N",
		"SET_LAST_MODIFIED" => "N",
		"ADD_SECTIONS_CHAIN" => "N",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
		"SET_STATUS_404" => "N",
		"SHOW_404" => "N",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
	), false);
});
if ($fkPro !== "") { ?>
<section class="sec wrap" id="pro" aria-labelledby="pro-t">
	<div class="pro">
		<h2 id="pro-t"><?php $fkInclude("fk_home_pro_title"); ?></h2>
		<ul><?= $fkPro ?></ul>
		<a class="btn btn--light" href="/cooperation/">Обсудить сотрудничество
			<svg class="ar" aria-hidden="true"><use href="#arrow"/></svg>
		</a>
	</div>
</section>
<?php } ?>
<!-- 12. Доставка и оплата: заголовок и пункты из областей включения (условия заполняет владелец) -->
<section class="sec wrap" id="delivery" aria-labelledby="d-t">
	<div class="split">
		<div><h2 id="d-t"><?php $fkInclude("fk_home_delivery_title"); ?></h2></div>
		<ul class="del"><?php $fkInclude("fk_home_delivery"); ?></ul>
	</div>
</section>
<!-- 13. Финальный призыв: текст из областей; «Связаться с нами» открывает окно обратной связи -->
<section class="sec wrap" aria-labelledby="fin-t">
	<div class="fin">
		<h2 id="fin-t"><?php $fkInclude("fk_home_cta_title"); ?></h2>
		<p><?php $fkInclude("fk_home_cta_text"); ?></p>
		<div class="actions">
			<a class="btn btn--light" href="/catalog/">Смотреть каталог</a>
			<button class="btn btn--glass" type="button" data-feedback aria-haspopup="dialog">Связаться с нами</button>
		</div>
	</div>
</section>
<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
