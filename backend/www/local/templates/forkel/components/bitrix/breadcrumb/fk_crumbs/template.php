<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */
/** Хлебные крошки в разметке V4. Последняя — текущая страница без ссылки. */
if (empty($arResult)) {
    return '';
}
$last = count($arResult) - 1;
$html = '<nav class="fk-crumbs" aria-label="Хлебные крошки"><ol>';
foreach ($arResult as $i => $item) {
    $title = htmlspecialcharsbx($item['TITLE']);
    if ($i === $last || $item['LINK'] === '') {
        $html .= '<li aria-current="page">' . $title . '</li>';
    } else {
        $html .= '<li><a href="' . htmlspecialcharsbx($item['LINK']) . '">' . $title . '</a></li>';
    }
}
return $html . '</ol></nav>';
