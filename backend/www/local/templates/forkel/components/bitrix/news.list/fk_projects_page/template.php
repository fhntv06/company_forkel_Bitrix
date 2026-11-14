<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */
/**
 * Страница «Проекты» (V4 projects.html): ul.fk-grid.fk-grid--projects-page > li > a.fk-project-card.
 * Заголовок и lead выводит шаблон сайта (fk_render_page_hero). Мета — «Помещение · Коллекция» из ROOM и COLLECTION.
 */
$tones = ['tone-graphite', 'tone-light', 'tone-sand', 'tone-stone', 'tone-gold'];
$text = static function ($v): string {
    return trim(strip_tags(is_array($v) ? implode(', ', $v) : (string)$v));
};
if (empty($arResult['ITEMS'])) { ?>
<div class="fk-empty">
    <h2>Проекты пока не добавлены</h2>
    <p>Загляните позже или посмотрите каталог.</p>
    <a class="fk-btn fk-btn--primary" href="/catalog/">В каталог</a>
</div>
<?php
    return;
} ?>
<ul class="fk-grid fk-grid--projects-page">
<?php foreach (array_values($arResult['ITEMS']) as $i => $item) {
    $pic = $item['PREVIEW_PICTURE']['SRC'] ?? $item['DETAIL_PICTURE']['SRC'] ?? '';
    $room = $text($item['DISPLAY_PROPERTIES']['ROOM']['DISPLAY_VALUE'] ?? '');
    $coll = $text($item['DISPLAY_PROPERTIES']['COLLECTION']['DISPLAY_VALUE'] ?? '');
    $meta = $room . ($room !== '' && $coll !== '' ? ' · ' : '') . $coll;
    ?>
    <li>
        <a class="fk-project-card <?= $tones[$i % count($tones)] ?>" href="<?= htmlspecialcharsbx($item['DETAIL_PAGE_URL']) ?>">
            <span class="fk-project-card__media">
                <?php if ($pic !== '') { ?><img src="<?= htmlspecialcharsbx($pic) ?>" alt="" loading="lazy" decoding="async"><?php } ?>
            </span>
            <span class="fk-project-card__cap">
                <h2 class="fk-project-card__name"><?= htmlspecialcharsbx($item['NAME']) ?></h2>
                <?php if ($meta !== '') { ?><span class="fk-project-card__meta"><?= htmlspecialcharsbx($meta) ?></span><?php } ?>
            </span>
        </a>
    </li>
<?php } ?>
</ul>
<?= $arResult['NAV_STRING'] ?? '' ?>
