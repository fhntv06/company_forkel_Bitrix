<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */
/**
 * Карточки проектов на главной (V4: .fk-grid--projects > li > a.fk-project-card). Выводит <li>; обёртку секции задаёт index.php.
 * Первая карточка крупная (is-p1). Мета — «Помещение · Коллекция» из свойств ROOM и COLLECTION; обложка — картинка анонса.
 */
$tones = ['tone-graphite', 'tone-light', 'tone-sand'];
$text = static function ($v): string {
    return trim(strip_tags(is_array($v) ? implode(', ', $v) : (string)$v));
};
foreach (array_values($arResult['ITEMS']) as $i => $item) {
    $pic = $item['PREVIEW_PICTURE']['SRC'] ?? $item['DETAIL_PICTURE']['SRC'] ?? '';
    $room = $text($item['DISPLAY_PROPERTIES']['ROOM']['DISPLAY_VALUE'] ?? '');
    $coll = $text($item['DISPLAY_PROPERTIES']['COLLECTION']['DISPLAY_VALUE'] ?? '');
    $meta = $room . ($room !== '' && $coll !== '' ? ' · ' : '') . $coll;
    ?>
<li<?= $i === 0 ? ' class="is-p1"' : '' ?>>
    <a class="fk-project-card <?= $tones[$i % count($tones)] ?>" href="<?= htmlspecialcharsbx($item['DETAIL_PAGE_URL']) ?>">
        <span class="fk-project-card__media">
            <?php if ($pic !== '') { ?><img src="<?= htmlspecialcharsbx($pic) ?>" alt="" loading="lazy" decoding="async"><?php } ?>
        </span>
        <span class="fk-project-card__cap">
            <h3 class="fk-project-card__name"><?= htmlspecialcharsbx($item['NAME']) ?></h3>
            <?php if ($meta !== '') { ?><span class="fk-project-card__meta"><?= htmlspecialcharsbx($meta) ?></span><?php } ?>
        </span>
    </a>
</li>
<?php
}
