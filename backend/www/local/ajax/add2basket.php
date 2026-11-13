<?php
/**
 * AJAX: добавление торгового предложения (или простого товара) в корзину со страницы товара.
 * POST: sessid, id (ID предложения/товара), qty. Ответ JSON {ok, count, errors[]}.
 * Корзину ведёт штатный API Bitrix\Catalog\Product\Basket::addProduct, проверку возможности покупки делает он.
 */
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

header('Content-Type: application/json; charset=UTF-8');

$answer = static function (bool $ok, array $errors = [], int $count = 0): void {
    echo \Bitrix\Main\Web\Json::encode(['ok' => $ok, 'errors' => $errors, 'count' => $count]);
    die();
};

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !check_bitrix_sessid()) {
    $answer(false, ['Сессия устарела. Обновите страницу и попробуйте снова.']);
}
if (!\Bitrix\Main\Loader::includeModule('catalog') || !\Bitrix\Main\Loader::includeModule('sale')) {
    $answer(false, ['Корзина недоступна.']);
}

$id = (int)($_POST['id'] ?? 0);
$qty = (int)($_POST['qty'] ?? 1);
$qty = max(1, min(99, $qty));
if ($id <= 0) {
    $answer(false, ['Не выбран товар.']);
}

$result = \Bitrix\Catalog\Product\Basket::addProduct(['PRODUCT_ID' => $id, 'QUANTITY' => $qty]);
if (!$result->isSuccess()) {
    $answer(false, $result->getErrorMessages() ?: ['Не удалось добавить товар в корзину.']);
}

$basket = \Bitrix\Sale\Basket::loadItemsForFUser(\Bitrix\Sale\Fuser::getId(), \Bitrix\Main\Context::getCurrent()->getSite());
$answer(true, [], (int)$basket->count());
