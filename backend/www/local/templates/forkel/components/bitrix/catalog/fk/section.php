<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Список товаров каталога (V4). Вся разметка — partials/catalog_list.php через fk_catalog_list().
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 */
fk_catalog_list($arParams, $arResult, $component);
