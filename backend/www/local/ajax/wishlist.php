<?php
/**
 * AJAX: Избранное (В-5 / TASK-018 Block 4.2).
 * GET: ?action=list -> {ok, count, ids}
 * POST: sessid, action=toggle, id=<PRODUCT_ID> -> {ok, in, count, ids}
 */
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

header('Content-Type: application/json; charset=UTF-8');

$send = static function (array $payload, int $code = 200): void {
    http_response_code($code);
    echo \Bitrix\Main\Web\Json::encode($payload);
    die();
};

if (!\Forkel\RateLimiter::check('wishlist', 60, 60)) {
    $send(['ok' => false, 'errors' => ['Слишком много запросов.']], 429);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $ids = \Forkel\Wishlist::getIds();
    $send(['ok' => true, 'count' => count($ids), 'ids' => $ids]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !check_bitrix_sessid()) {
    $send(['ok' => false, 'errors' => ['Сессия устарела. Обновите страницу.']], 403);
}

$productId = (int)($_POST['id'] ?? 0);
if ($productId <= 0) {
    $send(['ok' => false, 'errors' => ['Не указан товар.']], 400);
}

$res = \Forkel\Wishlist::toggle($productId);
$send(['ok' => true] + $res);
