<?php
/**
 * AJAX: добавление собранного комплекта из конфигуратора (/configurator/) в реальную корзину 1С-Битрикс.
 * POST: sessid, collection (F70|F55), product_type, frame_color, mech_color, material, posts (1..5), qty (1..99).
 * Создаёт позицию в корзине с сохранением состава комплекта в свойствах позиции корзины.
 */
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Main\Web\Json;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Fuser;

header('Content-Type: application/json; charset=UTF-8');

$answer = static function (bool $ok, array $errors = [], int $count = 0): void {
    echo Json::encode(['ok' => $ok, 'errors' => $errors, 'count' => $count]);
    die();
};

if (!\Forkel\RateLimiter::check('add_kit', 30, 60)) {
    http_response_code(429);
    $answer(false, ['Слишком много запросов. Повторите через минуту.']);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !check_bitrix_sessid()) {
    $answer(false, ['Сессия устарела. Обновите страницу и попробуйте снова.']);
}
if (!Loader::includeModule('sale') || !Loader::includeModule('catalog') || !Loader::includeModule('iblock')) {
    $answer(false, ['Корзина временно недоступна.']);
}

$collections = ['F70' => 'Коллекция F70', 'F55' => 'Коллекция F55'];
$types = [
    'switch' => ['name' => 'Выключатель 1-клавишный', 'price' => 1290],
    'double' => ['name' => 'Выключатель 2-клавишный', 'price' => 1490],
    'socket' => ['name' => 'Розетка с заземлением', 'price' => 1490],
];
$colors = [
    'white_gloss' => 'Белый глянцевый',
    'white_matte' => 'Белый матовый',
    'black_matte' => 'Чёрный матовый',
    'cashmere_matte' => 'Кашемир матовый',
    'gray' => 'Серый',
    'gold' => 'Золотой',
];
$materials = [
    'glass' => ['name' => 'Стекло', 'add' => 400],
    'aluminum' => ['name' => 'Алюминий', 'add' => 300],
    'thermoplastic' => ['name' => 'Термопласт', 'add' => 0],
];

$colKey = (string)($_POST['collection'] ?? 'F70');
$typeKey = (string)($_POST['product_type'] ?? 'switch');
$frameColorKey = (string)($_POST['frame_color'] ?? 'white_matte');
$mechColorKey = (string)($_POST['mech_color'] ?? 'white_matte');
$matKey = (string)($_POST['material'] ?? 'glass');
$posts = max(1, min(5, (int)($_POST['posts'] ?? 1)));
$qty = max(1, min(99, (int)($_POST['qty'] ?? 1)));

if (!isset($collections[$colKey], $types[$typeKey], $colors[$frameColorKey], $colors[$mechColorKey], $materials[$matKey])) {
    $answer(false, ['Некорректные параметры комплекта.']);
}

$unitPrice = ($types[$typeKey]['price'] + $materials[$matKey]['add']) * $posts;
$kitName = sprintf(
    'Комплект FORKEL %s: %s (%d %s, рамка %s / %s, механизм %s)',
    $colKey,
    $types[$typeKey]['name'],
    $posts,
    $posts === 1 ? 'пост' : ($posts <= 4 ? 'поста' : 'постов'),
    mb_strtolower($colors[$frameColorKey]),
    mb_strtolower($materials[$matKey]['name']),
    mb_strtolower($colors[$mechColorKey])
);

// Уникальный виртуальный PRODUCT_ID для комбинации комплекта (с привязкой к модулю forkel_configurator)
$kitProductId = 900000 + (crc32($colKey . '|' . $typeKey . '|' . $frameColorKey . '|' . $mechColorKey . '|' . $matKey . '|' . $posts) & 0x7FFFF);

$siteId = Context::getCurrent()->getSite() ?: 's1';
$basket = Basket::loadItemsForFUser(Fuser::getId(), $siteId);

$propsData = [
    ['NAME' => 'Коллекция', 'CODE' => 'COLLECTION', 'VALUE' => $collections[$colKey], 'SORT' => 100],
    ['NAME' => 'Тип изделия', 'CODE' => 'PRODUCT_TYPE', 'VALUE' => $types[$typeKey]['name'], 'SORT' => 200],
    ['NAME' => 'Материал рамки', 'CODE' => 'MATERIAL', 'VALUE' => $materials[$matKey]['name'], 'SORT' => 300],
    ['NAME' => 'Цвет рамки', 'CODE' => 'FRAME_COLOR', 'VALUE' => $colors[$frameColorKey], 'SORT' => 400],
    ['NAME' => 'Цвет механизма', 'CODE' => 'MECH_COLOR', 'VALUE' => $colors[$mechColorKey], 'SORT' => 500],
    ['NAME' => 'Количество постов', 'CODE' => 'POSTS', 'VALUE' => (string)$posts, 'SORT' => 600],
];

$item = $basket->getExistsItem('catalog', $kitProductId, $propsData);
if ($item) {
    $item->setField('QUANTITY', $item->getQuantity() + $qty);
} else {
    $item = $basket->createItem('catalog', $kitProductId);
    $item->setFields([
        'QUANTITY' => $qty,
        'CURRENCY' => 'RUB',
        'LID' => $siteId,
        'PRICE' => $unitPrice,
        'BASE_PRICE' => $unitPrice,
        'CUSTOM_PRICE' => 'Y',
        'NAME' => $kitName,
        'DETAIL_PAGE_URL' => '/configurator/',
        'CAN_BUY' => 'Y',
    ]);
    $propCol = $item->getPropertyCollection();
    $propCol->setProperty($propsData);
}

$saveRes = $basket->save();
if (!$saveRes->isSuccess()) {
    $answer(false, $saveRes->getErrorMessages() ?: ['Не удалось добавить комплект в корзину.']);
}

$answer(true, [], (int)$basket->count());
