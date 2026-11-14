<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */
/**
 * Карточки преимуществ на главной (V4: .feat > li.feat__c.feat__c--photo). Выводит <li>; обёртку секции задаёт index.php.
 * Заголовок — название элемента, текст — анонс, фото — картинка анонса (иначе детальная).
 */
foreach ($arResult['ITEMS'] as $item) {
    $pic = $item['PREVIEW_PICTURE']['SRC'] ?? $item['DETAIL_PICTURE']['SRC'] ?? '';
    $text = trim(strip_tags((string)$item['PREVIEW_TEXT']));
    ?>
<li class="feat__c feat__c--photo">
    <h3><?= htmlspecialcharsbx($item['NAME']) ?></h3>
    <?php if ($text !== '') { ?><p><?= htmlspecialcharsbx($text) ?></p><?php } ?>
    <?php if ($pic !== '') { ?><img class="ph" src="<?= htmlspecialcharsbx($pic) ?>" alt="" width="1264" height="848" loading="lazy" decoding="async"><?php } ?>
    <span class="go"><svg class="ar" aria-hidden="true"><use href="#arrow"/></svg></span>
</li>
<?php
}
