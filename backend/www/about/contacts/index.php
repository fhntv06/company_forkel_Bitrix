<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
/** @global CMain $APPLICATION */
$APPLICATION->SetTitle("Контакты");
$APPLICATION->SetPageProperty("fk_no_hero", "Y"); // TASK-013 / TASK-017: без баннера-героя, крошки + h1

/** Строка реквизитов: значение из области включения; если пусто или равно подписи — выводим «Уточняется». */
$fkRow = static function (string $label, string $area): string {
	$v = fk_include_text($area);
	$text = ($v !== '' && $v !== $label) ? nl2br(htmlspecialcharsbx($v)) : 'Уточняется';
	return '<div><dt>'.htmlspecialcharsbx($label).'</dt><dd>'.$text.'</dd></div>';
};

$fkInclude = static function (string $name): void {
	global $APPLICATION;
	$APPLICATION->IncludeComponent("bitrix:main.include", "", array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => "/include/" . $name . ".php",
	), false);
};

// Координаты карты: область fk_map_coords.php, формат «широта, долгота» (например 55.75, 37.62). Пусто — показываем заглушку V4 с 4 маркерами.
$fkCoords = array();
if (preg_match('/^\s*(-?\d{1,2}(?:\.\d+)?)\s*[,;]\s*(-?\d{1,3}(?:\.\d+)?)\s*$/', fk_include_text("fk_map_coords"), $m)) {
	$fkCoords = array("lat" => (float)$m[1], "lon" => (float)$m[2]);
}
?>
<h1 class="fk-page-title fk-ct-title" id="ct-title">Контакты</h1>

<section class="fk-ct-connection" aria-labelledby="ct-title">
	<div class="fk-ct-channels">
		<article class="fk-ct-channel">
			<span class="fk-ct-channel__icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 3h4l2 5-3 2a16 16 0 006 6l2-3 5 2v4c-9 3-21-9-16-16Z"/></svg></span>
			<h2>Телефон</h2>
			<p><?php $fkInclude("fk_phone"); ?></p>
		</article>
		<article class="fk-ct-channel">
			<span class="fk-ct-channel__icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5h18v14H3ZM3 5l9 7 9-7"/></svg></span>
			<h2>Email</h2>
			<p><?php $fkInclude("fk_email"); ?></p>
		</article>
		<article class="fk-ct-channel fk-ct-channel--wide">
			<span class="fk-ct-channel__icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0ZM12 7v5l3 2"/></svg></span>
			<h2>График работы</h2>
			<p><?php $fkInclude("fk_schedule"); ?></p>
		</article>
	</div>
	<p class="fk-ct-actions">
		<button class="fk-btn fk-btn--primary" type="button" data-feedback aria-haspopup="dialog">Написать нам</button>
		<button class="fk-btn fk-btn--secondary" type="button" data-callback aria-haspopup="dialog">Заказать звонок</button>
	</p>
</section>

<section class="fk-ct-places" id="ct-places" aria-labelledby="ct-places-title">
	<h2 class="fk-ct-places__title" id="ct-places-title">Магазины и склад</h2>
	<div class="fk-ct-places__body">
		<ol class="fk-ct-places__list">
			<?php $fkInclude("fk_contacts_places"); ?>
		</ol>
		<div class="fk-ct-map">
			<h3 class="fk-vh">Карта</h3>
<?php if ($fkCoords) {
	$APPLICATION->IncludeComponent("bitrix:map.yandex.view", "", array(
		"INIT_MAP_TYPE" => "MAP",
		"MAP_DATA" => serialize(array(
			"yandex_lat" => $fkCoords["lat"],
			"yandex_lon" => $fkCoords["lon"],
			"yandex_scale" => 16,
			"PLACEMARKS" => array(array("LON" => $fkCoords["lon"], "LAT" => $fkCoords["lat"], "TEXT" => "FORKEL")),
		)),
		"MAP_WIDTH" => "100%",
		"MAP_HEIGHT" => "400",
		"CONTROLS" => array("ZOOM", "SMALLZOOM", "TYPECONTROL"),
		"OPTIONS" => array("ENABLE_DBLCLICK_ZOOM", "ENABLE_DRAGGING"),
		"MAP_ID" => "fk_contacts_map",
	), false);
} else { ?>
			<div class="fk-ct-map__stub" role="img" aria-label="Карта: 4 точки — магазины и склад">
				<svg viewBox="0 0 400 260" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false"><path d="M0 70h400M0 150h400M0 215h400M90 0v260M200 0v260M310 0v260" /><path d="M20 240L380 20" /></svg>
				<span class="fk-ct-pin fk-ct-pin--1">1</span>
				<span class="fk-ct-pin fk-ct-pin--2">2</span>
				<span class="fk-ct-pin fk-ct-pin--3">3</span>
				<span class="fk-ct-pin fk-ct-pin--4">4</span>
			</div>
<?php } ?>
		</div>
	</div>
</section>

<section class="fk-ct-card" aria-labelledby="ct-legal">
	<h2 id="ct-legal">Реквизиты</h2>
	<dl class="fk-ct-list">
		<?= $fkRow("Юридическое наименование", "fk_req_name") ?>
		<?= $fkRow("ИНН", "fk_req_inn") ?>
		<?= $fkRow("ОГРН / ОГРНИП", "fk_req_ogrn") ?>
		<?= $fkRow("Юридический адрес", "fk_req_address") ?>
	</dl>
	<p class="fk-ct-links"><a class="fk-link fk-link--tap" href="/about/privacy/">Политика обработки персональных данных</a></p>
</section>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
