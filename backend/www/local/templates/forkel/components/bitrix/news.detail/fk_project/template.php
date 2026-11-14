<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */
/**
 * Cover, «О проекте», «Использовано» и галерея проекта (V4 project.html). Картинка cover — детальная (иначе анонса).
 * Галерея — множественное файловое свойство GALLERY. «Товары проекта», CTA и «Другие проекты» выводит detail.php.
 */
$tones = ['tone-graphite', 'tone-light', 'tone-sand', 'tone-stone', 'tone-gold'];
$tone = $tones[((int)$arResult['ID']) % count($tones)];
$text = static function ($v): string {
    return trim(strip_tags(is_array($v) ? implode(', ', $v) : (string)$v));
};
$cover = $arResult['DETAIL_PICTURE']['SRC'] ?? $arResult['PREVIEW_PICTURE']['SRC'] ?? '';
$lead = trim(strip_tags((string)$arResult['PREVIEW_TEXT']));
$body = trim((string)$arResult['DETAIL_TEXT']);
$room = $text($arResult['DISPLAY_PROPERTIES']['ROOM']['DISPLAY_VALUE'] ?? '');
$coll = $text($arResult['DISPLAY_PROPERTIES']['COLLECTION']['DISPLAY_VALUE'] ?? '');
$gallery = [];
$g = $arResult['PROPERTIES']['GALLERY']['VALUE'] ?? [];
foreach ((array)$g as $fileId) {
    $src = (int)$fileId > 0 ? CFile::GetPath((int)$fileId) : '';
    if ($src) {
        $gallery[] = $src;
    }
}
$name = htmlspecialcharsbx($arResult['NAME']);
?>
<section class="fk-prj-cover <?= $tone ?>" aria-labelledby="prj-title">
    <div class="fk-prj-cover__media">
        <?php if ($cover !== '') { ?><img src="<?= htmlspecialcharsbx($cover) ?>" alt="" decoding="async"><?php } ?>
    </div>
    <div class="fk-prj-cover__text">
        <p class="fk-prj-cover__label">Проект</p>
        <h1 class="fk-page-title" id="prj-title"><?= $name ?></h1>
        <?php if ($lead !== '') { ?><p class="fk-prj-cover__lead"><?= htmlspecialcharsbx($lead) ?></p><?php } ?>
    </div>
</section>
<?php if ($body !== '' || $room !== '' || $coll !== '') { ?>
<div class="fk-prj-body">
    <?php if ($body !== '') { ?>
    <section class="fk-prj-text" aria-labelledby="prj-about">
        <h2 id="prj-about">О проекте</h2>
        <?= $body ?>
    </section>
    <?php } ?>
    <?php if ($room !== '' || $coll !== '') { ?>
    <aside class="fk-prj-facts" aria-labelledby="prj-facts">
        <h2 id="prj-facts">Использовано</h2>
        <dl class="fk-spec">
            <?php if ($room !== '') { ?><div><dt>Помещение</dt><dd><?= htmlspecialcharsbx($room) ?></dd></div><?php } ?>
            <?php if ($coll !== '') { ?><div><dt>Коллекция</dt><dd><?= htmlspecialcharsbx($coll) ?></dd></div><?php } ?>
        </dl>
    </aside>
    <?php } ?>
</div>
<?php } ?>
<?php if ($gallery) { ?>
<section class="fk-prj-gallery" aria-labelledby="prj-gal">
    <h2 id="prj-gal">Галерея</h2>
    <ul class="fk-prj-tiles">
        <?php foreach ($gallery as $src) { ?>
        <li class="fk-prj-tile"><img src="<?= htmlspecialcharsbx($src) ?>" alt="" loading="lazy" decoding="async"></li>
        <?php } ?>
    </ul>
</section>
<?php } ?>
