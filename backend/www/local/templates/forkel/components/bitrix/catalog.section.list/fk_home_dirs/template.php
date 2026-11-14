<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */
/**
 * Плитки направлений на главной (V4: .dirs > li > a.dir). Только разделы с картинкой раздела.
 * Выводит <li>; обёртку секции и <ul> задаёт index.php и скрывает секцию, если плиток нет.
 */
foreach ($arResult['SECTIONS'] as $section) {
    $pic = $section['PICTURE'] ?? null;
    if (empty($pic['SRC'])) {
        continue;
    }
    $url = rtrim($section['SECTION_PAGE_URL'], '/') . '/';
    ?>
<li>
    <a class="dir" href="<?= htmlspecialcharsbx($url) ?>">
        <span class="dir__img"><img class="ph" src="<?= htmlspecialcharsbx($pic['SRC']) ?>" alt="" width="<?= (int)($pic['WIDTH'] ?? 1024) ?>" height="<?= (int)($pic['HEIGHT'] ?? 1024) ?>" loading="lazy" decoding="async"></span>
        <b><?= htmlspecialcharsbx($section['NAME']) ?></b>
        <span class="dir__go"><svg class="ar" aria-hidden="true"><use href="#arrow"/></svg></span>
    </a>
</li>
<?php
}
