<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */
/**
 * Карточки коллекций на главной (V4: .cols > li > a.col.col--photo). Выводит <li>; обёртку секции задаёт index.php.
 * Название — «Коллекция <NAME>» (если в названии нет слова «Коллекция»). Чипы — строки текста анонса (каждая строка = один чип).
 * Фото — картинка анонса, иначе детальная.
 */
foreach ($arResult['ITEMS'] as $item) {
    $pic = $item['PREVIEW_PICTURE']['SRC'] ?? $item['DETAIL_PICTURE']['SRC'] ?? '';
    $name = trim((string)$item['NAME']);
    $title = mb_stripos($name, 'коллекц') === false ? 'Коллекция ' . $name : $name;
    $chips = array_filter(array_map('trim', preg_split('/\R/u', strip_tags((string)$item['PREVIEW_TEXT']))));
    ?>
<li>
    <a class="col col--photo" href="<?= htmlspecialcharsbx($item['DETAIL_PAGE_URL']) ?>">
        <h3><?= htmlspecialcharsbx($title) ?></h3>
        <?php if ($chips) { ?>
        <span class="chips">
            <?php foreach ($chips as $chip) { ?><i><?= htmlspecialcharsbx($chip) ?></i><?php } ?>
        </span>
        <?php } ?>
        <?php if ($pic !== '') { ?><img class="ph" src="<?= htmlspecialcharsbx($pic) ?>" alt="" width="1200" height="896" loading="lazy" decoding="async"><?php } ?>
        <span class="btn btn--glass btn--sm">Подробнее <svg class="ar" aria-hidden="true"><use href="#arrow"/></svg></span>
    </a>
</li>
<?php
}
