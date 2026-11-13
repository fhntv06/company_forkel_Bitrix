<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */
/** Пункты списков подвала (разметка V4). Оборачивающий <ul> — в footer.php. */
foreach ($arResult as $item) {
    if ((int)$item['DEPTH_LEVEL'] > 1) {
        continue;
    }
    ?><li><a href="<?= htmlspecialcharsbx($item['LINK']) ?>"><?= htmlspecialcharsbx($item['TEXT']) ?></a></li>
<?php
}
