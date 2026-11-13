<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */
/** Пункты основной навигации шапки (разметка V4). Оборачивающий <ul> — в header.php. */
foreach ($arResult as $item) {
    if ((int)$item['DEPTH_LEVEL'] > 1) {
        continue;
    }
    ?><li><a class="fk-nav__link" href="<?= htmlspecialcharsbx($item['LINK']) ?>"<?= $item['SELECTED'] ? ' aria-current="page"' : '' ?>><?= htmlspecialcharsbx($item['TEXT']) ?></a></li>
<?php
}
