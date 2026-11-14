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
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
