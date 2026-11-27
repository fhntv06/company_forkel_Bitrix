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

// 4 точки на карте (3 магазина + склад, INT-002 / TASK-018 Block 4.3).
// При наличии YANDEX_MAPS_API_KEY в env или заполненного fk_map_coords.php рендерится bitrix:map.yandex.view с 4 метками;
// иначе — аккуратная заглушка V4.
$fkYandexKey = \Forkel\Env::get('YANDEX_MAPS_API_KEY');
$fkHasCustomCoords = (bool)preg_match('/^\s*(-?\d{1,2}(?:\.\d+)?)\s*[,;]\s*(-?\d{1,3}(?:\.\d+)?)\s*$/', fk_include_text("fk_map_coords"), $mCoords);
$fkShowMap = ($fkYandexKey !== '' || $fkHasCustomCoords);

$fkPlacemarks = array(
	array("LON" => 37.584426, "LAT" => 55.672512, "TEXT" => "1. Магазин — ЦДиИ «EXPOSTROY», павильон 1 (Нахимовский пр-т, 24, стр. 5, 1 эт., место 3)"),
	array("LON" => 37.585100, "LAT" => 55.672100, "TEXT" => "2. Магазин — ЦДиИ «EXPOSTROY», павильон 2 (Нахимовский пр-т, 24, 2 эт., блок 11B, место 251)"),
	array("LON" => 37.630502, "LAT" => 55.662614, "TEXT" => "3. Магазин — ТЦ «Каширский двор» (Каширское шоссе, 19, корп. 1, эт. 4, пом. 4А7)"),
	array("LON" => 37.624900, "LAT" => 55.664180, "TEXT" => "4. Склад — ТЦ «Гудзон» (Каширское шоссе, д. 14, места А51, А52, А79)"),
);
$fkCenterLat = $fkHasCustomCoords ? (float)$mCoords[1] : 55.6678;
$fkCenterLon = $fkHasCustomCoords ? (float)$mCoords[2] : 37.6075;
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
<?php if ($fkShowMap) {
	$APPLICATION->IncludeComponent("bitrix:map.yandex.view", "", array(
		"API_KEY" => $fkYandexKey,
		"INIT_MAP_TYPE" => "MAP",
		"MAP_DATA" => serialize(array(
			"yandex_lat" => $fkCenterLat,
			"yandex_lon" => $fkCenterLon,
			"yandex_scale" => 13,
			"PLACEMARKS" => $fkPlacemarks,
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
