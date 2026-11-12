<?php
/**
 * FORKEL: небольшие помощники шаблона. Подключаются из local/php_interface/init.php.
 */
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (!function_exists('fk_iblock_id')) {
    /**
     * ID инфоблока по символьному коду. 0, если модуль iblock не установлен или инфоблока нет.
     * Нужен, чтобы в коде не держать числовые ID: они у разных установок разные.
     */
    function fk_iblock_id(string $code): int
    {
        static $cache = [];
        if (isset($cache[$code])) {
            return $cache[$code];
        }
        $id = 0;
        if (\Bitrix\Main\Loader::includeModule('iblock')) {
            $row = \Bitrix\Iblock\IblockTable::getList([
                'select' => ['ID'],
                'filter' => ['=CODE' => $code],
                'limit' => 1,
            ])->fetch();
            $id = $row ? (int)$row['ID'] : 0;
        }
        return $cache[$code] = $id;
    }
}

if (!function_exists('fk_include_text')) {
    /**
     * Текст области включения /include/<name>.php без разметки. Пусто, если файла нет.
     * Для значений вроде ссылок на соцсети.
     */
    function fk_include_text(string $name): string
    {
        $file = $_SERVER['DOCUMENT_ROOT'] . '/include/' . basename($name) . '.php';
        if (!is_file($file)) {
            return '';
        }
        return trim(html_entity_decode(strip_tags((string)file_get_contents($file)), ENT_QUOTES, 'UTF-8'));
    }
}
