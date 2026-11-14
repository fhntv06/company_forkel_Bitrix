<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */
/**
 * Карточки интерьеров на главной (V4: .fk-grid--interiors > li > .fk-interior). Выводит <li>; обёртку секции задаёт index.php.
 * Название — название элемента, описание — анонс, фото — картинка анонса (иначе детальная). По решению владельца карточки
 * не ссылки: в прототипе стоял <a href="#">, здесь <div> с теми же классами (стили и hover не меняются, прыжка к началу страницы нет).
 */
foreach ($arResult['ITEMS'] as $item) {
    $pic = $item['PREVIEW_PICTURE']['SRC'] ?? $item['DETAIL_PICTURE']['SRC'] ?? '';
    $text = trim(strip_tags((string)$item['PREVIEW_TEXT']));
    ?>
<li>
    <div class="fk-interior">
        <span class="fk-interior__media"><?php if ($pic !== '') { ?><img src="<?= htmlspecialcharsbx($pic) ?>" alt="" loading="lazy" decoding="async"><?php } ?></span>
        <span class="fk-interior__body">
            <h3 class="fk-interior__title"><?= htmlspecialcharsbx($item['NAME']) ?></h3>
            <?php if ($text !== '') { ?><span class="fk-interior__text"><?= htmlspecialcharsbx($text) ?></span><?php } ?>
        </span>
        <span class="fk-interior__arrow" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 17L17 7M9 7h8v8"/></svg></span>
    </div>
</li>
<?php
}
