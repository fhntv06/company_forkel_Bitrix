<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Sale\PaySystem;

/**
 * Описание обработчика для админки. Параметров в админке нет: доступы задаются в backend/.env (ALFABANK_*).
 */
$data = [
    'NAME' => 'FORKEL: Альфа-Банк (интернет-эквайринг: карты и СБП)',
    'DESCRIPTION' => 'Оплата через платёжную страницу Альфа-Банка (REST API RBS). '
        . 'Доступы задаются переменными окружения ALFABANK_* (см. docs/development/deployment.md).',
    'SORT' => 100,
    'IS_AVAILABLE' => PaySystem\Manager::HANDLER_AVAILABLE_TRUE,
    'CODES' => [],
];
