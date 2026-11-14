<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
/** @global CMain $APPLICATION */
$APPLICATION->SetTitle("Контакты");
$APPLICATION->SetPageProperty("fk_lead", "Способы связи с FORKEL.");
$APPLICATION->SetPageProperty("fk_hero_art", "fk_contacts_hero_art");

/** Строка списка: значение — из области включения; пока там остался текст-заглушка (равен подписи), значение не выводим. */
$fkRow = static function (string $label, string $area): string {
	$v = fk_include_text($area);
	return '<div><dt>'.htmlspecialcharsbx($label).'</dt>'
		.($v !== '' && $v !== $label ? '<dd>'.nl2br(htmlspecialcharsbx($v)).'</dd>' : '').'</div>';
};
// Координаты карты: область fk_map_coords.php, формат «широта, долгота» (например 55.75, 37.62). Пусто — показываем заглушку V4.
$fkCoords = array();
if (preg_match('/^\s*(-?\d{1,2}(?:\.\d+)?)\s*[,;]\s*(-?\d{1,3}(?:\.\d+)?)\s*$/', fk_include_text("fk_map_coords"), $m)) {
	$fkCoords = array("lat" => (float)$m[1], "lon" => (float)$m[2]);
}
?>
<div class="fk-ct">
	<section class="fk-ct-card" aria-labelledby="ct-conn">
		<h2 id="ct-conn">Связь</h2>
		<dl class="fk-ct-list">
			<?= $fkRow("Телефон", "fk_phone") ?>
			<?= $fkRow("Email", "fk_email") ?>
			<?= $fkRow("Адрес", "fk_address") ?>
			<?= $fkRow("График работы", "fk_schedule") ?>
		</dl>
		<p class="fk-ct-actions"><button class="fk-btn fk-btn--primary" type="button" data-feedback aria-haspopup="dialog">Написать нам</button> <button class="fk-btn fk-btn--secondary" type="button" data-callback aria-haspopup="dialog">Заказать звонок</button></p>
	</section>
	<section class="fk-ct-map" aria-labelledby="ct-map">
		<h2 id="ct-map" class="fk-vh">Карта</h2>
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
		<div class="fk-ct-map__stub" role="img" aria-label="Карта">
			<svg viewBox="0 0 400 260" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false"><path d="M0 70h400M0 150h400M0 215h400M90 0v260M200 0v260M310 0v260" /><path d="M20 240L380 20" /><circle cx="200" cy="150" r="14" /></svg>
		</div>
<?php } ?>
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
</div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
