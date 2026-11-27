<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

require_once __DIR__ . '/classes/Env.php';
require_once __DIR__ . '/classes/RateLimiter.php';
require_once __DIR__ . '/classes/MaxNotifier.php';
require_once __DIR__ . '/classes/Wishlist.php';
require_once __DIR__ . '/classes/AlfaBank.php';
require_once __DIR__ . '/forkel_helpers.php';

use Bitrix\Main\Event;
use Bitrix\Main\EventManager;

$eventManager = EventManager::getInstance();

// Уведомление менеджерам в MAX при создании нового заказа (FR-014 / INT-008)
$eventManager->addEventHandler('sale', 'OnSaleOrderSaved', static function (Event $event): void {
    $isNew = (bool)$event->getParameter('IS_NEW');
    if (!$isNew) {
        return;
    }
    $order = $event->getParameter('ENTITY');
    if ($order instanceof \Bitrix\Sale\Order) {
        \Forkel\MaxNotifier::notifyNewOrder($order);
    }
});

// Синхронизация гостевого избранного в БД при входе (В-5)
$eventManager->addEventHandler('main', 'OnAfterUserLogin', static function (&$arFields): void {
    $uid = (int)($arFields['USER_ID'] ?? 0);
    if ($uid > 0) {
        \Forkel\Wishlist::syncOnLogin($uid);
    }
});
