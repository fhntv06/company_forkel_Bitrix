<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */
/** @var array $arParams */
/** Hero страницы коллекции (V4: .fk-colhero): название, описание, переключатель коллекций и изображение. */
$e = static function ($v): string {
    return htmlspecialcharsbx((string)$v);
};
$pic = $arResult['DETAIL_PICTURE']['SRC'] ?? $arResult['PREVIEW_PICTURE']['SRC'] ?? '';
$text = trim((string)($arResult['PREVIEW_TEXT'] ?: strip_tags((string)$arResult['DETAIL_TEXT'])));

$others = [];
if (\Bitrix\Main\Loader::includeModule('iblock')) {
    $rs = \CIBlockElement::GetList(['SORT' => 'ASC', 'NAME' => 'ASC'], ['IBLOCK_ID' => $arResult['IBLOCK_ID'], 'ACTIVE' => 'Y'], false, false, ['ID', 'NAME', 'DETAIL_PAGE_URL']);
    while ($row = $rs->GetNext()) {
        $others[] = $row;
    }
}
?>
<section class="fk-colhero tone-graphite" aria-labelledby="col-title">
    <div class="fk-colhero__text">
        <p class="fk-colhero__label">Коллекция</p>
        <h1 class="fk-page-title" id="col-title"><?= $e($arResult['NAME']) ?></h1>
        <?php if ($text !== '') { ?><p class="fk-colhero__lead"><?= $e($text) ?></p><?php } ?>
        <?php if (count($others) > 1) { ?>
        <nav class="fk-colhero__switch" aria-label="Другие коллекции">
            <?php foreach ($others as $o) { ?>
            <a class="fk-chip" href="<?= $e($o['DETAIL_PAGE_URL']) ?>"<?= (int)$o['ID'] === (int)$arResult['ID'] ? ' aria-current="page"' : '' ?>><?= $e($o['NAME']) ?></a>
            <?php } ?>
        </nav>
        <?php } ?>
    </div>
    <?php if ($pic !== '') { ?>
    <div class="fk-colhero__media"><img src="<?= $e($pic) ?>" alt="" style="width:100%;height:100%;object-fit:cover"></div>
    <?php } ?>
</section>
