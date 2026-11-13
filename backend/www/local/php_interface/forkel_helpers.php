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

if (!function_exists('fk_render_page_hero')) {
    /**
     * Hero внутренней страницы (V4). Вызывается отложенно через AddBufferContent,
     * когда свойства страницы уже заданы. fk_no_hero=Y скрывает блок.
     * Кнопки: fk_cta1_text/fk_cta1_link, fk_cta2_text/fk_cta2_link; ссылка «#feedback» открывает окно обратной связи.
     */
    function fk_render_page_hero(): string
    {
        global $APPLICATION;
        if (!fk_page_has_wrapper() || $APPLICATION->GetProperty('fk_no_hero') === 'Y') {
            return '';
        }
        $title = htmlspecialcharsbx((string)$APPLICATION->GetTitle());
        $lead = trim((string)$APPLICATION->GetProperty('fk_lead'));
        $cta = [];
        foreach ([1 => 'fk-btn--inverse', 2 => 'fk-btn--ghost'] as $n => $mod) {
            $t = trim((string)$APPLICATION->GetProperty('fk_cta' . $n . '_text'));
            $l = trim((string)$APPLICATION->GetProperty('fk_cta' . $n . '_link'));
            if ($t === '' || $l === '') {
                continue;
            }
            $cta[] = $l === '#feedback'
                ? '<button class="fk-btn ' . $mod . '" type="button" data-feedback aria-haspopup="dialog">' . htmlspecialcharsbx($t) . '</button>'
                : '<a class="fk-btn ' . $mod . '" href="' . htmlspecialcharsbx($l) . '">' . htmlspecialcharsbx($t) . '</a>';
        }
        return '<section class="fk-ab-hero" aria-labelledby="fk-page-title"><div class="fk-ab-hero__text">'
            . '<p class="fk-ab-hero__kicker">FORKEL</p>'
            . '<h1 class="fk-ab-hero__title" id="fk-page-title">' . $title . '</h1>'
            . ($lead !== '' ? '<p class="fk-ab-hero__lead">' . htmlspecialcharsbx($lead) . '</p>' : '')
            . ($cta ? '<div class="fk-ab-hero__cta">' . implode('', $cta) . '</div>' : '')
            . '</div></section>';
    }
}

if (!function_exists('fk_page_has_wrapper')) {
    /** Внутренние страницы (не главная) оборачиваются в fk-container fk-page: крошки + hero + содержимое. Закрывает обёртку footer.php. */
    function fk_page_has_wrapper(): bool
    {
        global $APPLICATION;
        return $APPLICATION->GetCurPage(false) !== '/';
    }
}

if (!function_exists('fk_render_page_open')) {
    function fk_render_page_open(): string
    {
        return fk_page_has_wrapper() ? '<div class="fk-container fk-page">' : '';
    }
}
