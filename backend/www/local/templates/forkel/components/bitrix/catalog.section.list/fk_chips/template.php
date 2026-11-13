<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */
/** @var array $arParams */
/** Ссылки на категории каталога (V4, .fk-cat-nav): «Все» и разделы первого уровня. Текущий — aria-current. */
$current = (int)($arParams['CURRENT_SECTION_ID'] ?? 0);
$root = $arParams['CATALOG_URL'] ?? '/catalog/';
?>
<a class="fk-chip" href="<?= htmlspecialcharsbx($root) ?>"<?= $current === 0 ? ' aria-current="page"' : '' ?>>Все</a>
<?php foreach ($arResult['SECTIONS'] as $section) { ?>
<a class="fk-chip" href="<?= htmlspecialcharsbx(rtrim($section['SECTION_PAGE_URL'], '/') . '/') ?>"<?= (int)$section['ID'] === $current ? ' aria-current="page"' : '' ?>><?= htmlspecialcharsbx($section['NAME']) ?></a>
<?php } ?>
