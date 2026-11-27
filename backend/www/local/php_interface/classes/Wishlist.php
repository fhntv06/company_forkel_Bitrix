<?php
namespace Forkel;

use Bitrix\Main\Application;
use Bitrix\Main\Engine\CurrentUser;

/**
 * Wishlist / Favorites service (B-5 / TASK-018 Block 4.2).
 * Authorized users: persisted in table b_fk_wishlist.
 * Guests: persisted in cookie fk_wishlist (JSON array of product IDs).
 */
final class Wishlist
{
    private const COOKIE_NAME = 'fk_wishlist';
    private const TABLE_NAME = 'b_fk_wishlist';
    private static bool $tableChecked = false;

    public static function ensureTable(): void
    {
        if (self::$tableChecked) {
            return;
        }
        self::$tableChecked = true;
        $conn = Application::getConnection();
        if (!$conn->isTableExists(self::TABLE_NAME)) {
            $conn->queryExecute(
                'CREATE TABLE ' . self::TABLE_NAME . ' (
                    ID INT AUTO_INCREMENT PRIMARY KEY,
                    USER_ID INT NOT NULL,
                    PRODUCT_ID INT NOT NULL,
                    CREATED_AT DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY uq_user_product (USER_ID, PRODUCT_ID),
                    KEY ix_user (USER_ID)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            );
        }
    }

    /** @return int[] */
    public static function getIds(): array
    {
        $userId = (int)CurrentUser::get()->getId();
        if ($userId > 0) {
            self::ensureTable();
            $conn = Application::getConnection();
            $res = $conn->query('SELECT PRODUCT_ID FROM ' . self::TABLE_NAME . ' WHERE USER_ID = ' . $userId . ' ORDER BY ID DESC');
            $ids = [];
            while ($row = $res->fetch()) {
                $ids[] = (int)$row['PRODUCT_ID'];
            }
            return $ids;
        }
        return self::readCookieIds();
    }

    public static function count(): int
    {
        return count(self::getIds());
    }

    /** @return array{in: bool, count: int, ids: int[]} */
    public static function toggle(int $productId): array
    {
        if ($productId <= 0) {
            $ids = self::getIds();
            return ['in' => false, 'count' => count($ids), 'ids' => $ids];
        }

        $userId = (int)CurrentUser::get()->getId();
        if ($userId > 0) {
            self::ensureTable();
            $conn = Application::getConnection();
            $exists = $conn->queryScalar(
                'SELECT ID FROM ' . self::TABLE_NAME . ' WHERE USER_ID = ' . $userId . ' AND PRODUCT_ID = ' . $productId
            );
            if ($exists) {
                $conn->queryExecute('DELETE FROM ' . self::TABLE_NAME . ' WHERE ID = ' . (int)$exists);
                $in = false;
            } else {
                $conn->queryExecute(
                    'INSERT IGNORE INTO ' . self::TABLE_NAME . ' (USER_ID, PRODUCT_ID) VALUES (' . $userId . ', ' . $productId . ')'
                );
                $in = true;
            }
            $ids = self::getIds();
            return ['in' => $in, 'count' => count($ids), 'ids' => $ids];
        }

        $ids = self::readCookieIds();
        if (in_array($productId, $ids, true)) {
            $ids = array_values(array_filter($ids, static fn(int $id) => $id !== $productId));
            $in = false;
        } else {
            array_unshift($ids, $productId);
            $ids = array_slice(array_values(array_unique($ids)), 0, 100);
            $in = true;
        }
        self::writeCookieIds($ids);
        return ['in' => $in, 'count' => count($ids), 'ids' => $ids];
    }

    public static function syncOnLogin(int $userId): void
    {
        if ($userId <= 0) {
            return;
        }
        $cookieIds = self::readCookieIds();
        if (!$cookieIds) {
            return;
        }
        self::ensureTable();
        $conn = Application::getConnection();
        foreach ($cookieIds as $pid) {
            $conn->queryExecute(
                'INSERT IGNORE INTO ' . self::TABLE_NAME . ' (USER_ID, PRODUCT_ID) VALUES (' . $userId . ', ' . (int)$pid . ')'
            );
        }
        self::writeCookieIds([]);
    }

    /** @return int[] */
    private static function readCookieIds(): array
    {
        $raw = (string)($_COOKIE[self::COOKIE_NAME] ?? '');
        if ($raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }
        return array_values(array_unique(array_filter(array_map('intval', $decoded), static fn($v) => $v > 0)));
    }

    /** @param int[] $ids */
    private static function writeCookieIds(array $ids): void
    {
        $val = json_encode(array_values($ids));
        $_COOKIE[self::COOKIE_NAME] = $val;
        if (!headers_sent()) {
            setcookie(self::COOKIE_NAME, $val, [
                'expires' => time() + 86400 * 90,
                'path' => '/',
                'httponly' => false,
                'samesite' => 'Lax',
            ]);
        }
    }
}
