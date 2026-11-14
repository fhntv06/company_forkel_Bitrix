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
        $heroImg = trim((string)$APPLICATION->GetProperty('fk_hero_img'));
        $heroAlt = trim((string)$APPLICATION->GetProperty('fk_hero_alt'));
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
        // Картинка hero: свойство fk_hero_art = имя области включения /include/<имя>.php (разметка <img>).
        $artName = trim((string)$APPLICATION->GetProperty('fk_hero_art'));
        $artFile = $artName !== '' ? $_SERVER['DOCUMENT_ROOT'] . '/include/' . basename($artName) . '.php' : '';
        $art = $artFile !== '' && is_file($artFile) ? trim((string)file_get_contents($artFile)) : '';
        return '<section class="fk-ab-hero" aria-labelledby="fk-page-title"><div class="fk-ab-hero__text">'
            . '<p class="fk-ab-hero__kicker">FORKEL</p>'
            . '<h1 class="fk-ab-hero__title" id="fk-page-title">' . $title . '</h1>'
            . ($lead !== '' ? '<p class="fk-ab-hero__lead">' . htmlspecialcharsbx($lead) . '</p>' : '')
            . ($cta ? '<div class="fk-ab-hero__cta">' . implode('', $cta) . '</div>' : '')
            . '</div>'
            . ($art !== '' ? '<figure class="fk-ab-hero__art">' . $art . '</figure>' : '')
            . '</section>';
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

if (!function_exists('fk_catalog_list')) {
    /**
     * Панель категорий, поиск, сортировка, фильтр и сетка товаров каталога.
     * $arParams/$arResult — как у комплексного компонента bitrix:catalog (нужны IBLOCK_*, FILTER_NAME, URL_TEMPLATES, FOLDER, VARIABLES и параметры списка).
     * $base — дополнительный фильтр (например, ['=PROPERTY_COLLECTION' => 5]); $hide — коды свойств, которые не показывать в фильтре.
     */
    function fk_catalog_list(array $arParams, array $arResult, $component, array $base = [], array $hide = []): void
    {
        global $APPLICATION;
        include $_SERVER['DOCUMENT_ROOT'] . SITE_TEMPLATE_PATH . '/partials/catalog_list.php';
    }
}

if (!function_exists('fk_catalog_params')) {
    /** Параметры списка товаров каталога для страниц вне /catalog/ (коллекции). Держать в согласии с /catalog/index.php. */
    function fk_catalog_params(): array
    {
        return [
            'IBLOCK_TYPE' => 'catalog',
            'IBLOCK_ID' => fk_iblock_id('catalog'),
            'FILTER_NAME' => 'arrFilter',
            'CACHE_TYPE' => 'A',
            'CACHE_TIME' => '36000000',
            'CACHE_GROUPS' => 'Y',
            'SET_STATUS_404' => 'N',
            'BASKET_URL' => '/personal/cart/',
            'ACTION_VARIABLE' => 'action',
            'PRODUCT_ID_VARIABLE' => 'id',
            'SECTION_ID_VARIABLE' => 'SECTION_ID',
            'PRODUCT_QUANTITY_VARIABLE' => 'quantity',
            'PRODUCT_PROPS_VARIABLE' => 'prop',
            'PRICE_CODE' => ['BASE'],
            '~PRICE_CODE' => ['BASE'],
            'USE_PRICE_COUNT' => 'N',
            'SHOW_PRICE_COUNT' => '1',
            'PRICE_VAT_INCLUDE' => 'Y',
            'CONVERT_CURRENCY' => 'N',
            'HIDE_NOT_AVAILABLE' => 'N',
            'PAGE_ELEMENT_COUNT' => '12',
            'ELEMENT_SORT_FIELD' => 'sort',
            'ELEMENT_SORT_ORDER' => 'asc',
            'ELEMENT_SORT_FIELD2' => 'id',
            'ELEMENT_SORT_ORDER2' => 'desc',
            'LIST_PROPERTY_CODE' => ['ARTICLE', 'COLLECTION', 'MATERIAL', 'NEW', 'MORE_PHOTO'],
            'LIST_OFFERS_FIELD_CODE' => ['NAME', 'PREVIEW_PICTURE', 'DETAIL_PICTURE'],
            'LIST_OFFERS_PROPERTY_CODE' => ['COLOR', 'MORE_PHOTO'],
            'OFFERS_SORT_FIELD' => 'sort',
            'OFFERS_SORT_ORDER' => 'asc',
            'OFFERS_SORT_FIELD2' => 'id',
            'OFFERS_SORT_ORDER2' => 'asc',
            'PAGER_TEMPLATE' => 'round',
            'DISPLAY_BOTTOM_PAGER' => 'Y',
            'PAGER_SHOW_ALWAYS' => 'N',
            'PAGER_DESC_NUMBERING' => 'N',
            'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000000',
            'PAGER_SHOW_ALL' => 'N',
            'FK_CHIPS_CURRENT' => -1,
        ];
    }
}

if (!function_exists('fk_product_html')) {
    /**
     * HTML сетки карточек товаров (без обёртки секции). Пусто, если товаров нет.
     * $key — уникальный ключ для имени фильтра; $filter — дополнительный фильтр; $sectionId — раздел (0 = весь каталог);
     * $plain — сетка как в V4 на главной (без «каталожных» колонок); $template — шаблон catalog.section (fk, fk_mini).
     */
    function fk_product_html(string $key, array $filter, int $sectionId = 0, int $count = 4, bool $plain = false, string $template = 'fk'): string
    {
        global $APPLICATION;
        $p = fk_catalog_params();
        $filterName = 'arrFkBlock' . substr(md5($key), 0, 6);
        $GLOBALS[$filterName] = $filter;
        ob_start();
        $APPLICATION->IncludeComponent('bitrix:catalog.section', $template, [
            'IBLOCK_TYPE' => $p['IBLOCK_TYPE'],
            'IBLOCK_ID' => $p['IBLOCK_ID'],
            'ELEMENT_SORT_FIELD' => 'sort',
            'ELEMENT_SORT_ORDER' => 'asc',
            'ELEMENT_SORT_FIELD2' => 'id',
            'ELEMENT_SORT_ORDER2' => 'desc',
            'PROPERTY_CODE' => $p['LIST_PROPERTY_CODE'],
            'OFFERS_FIELD_CODE' => $p['LIST_OFFERS_FIELD_CODE'],
            'OFFERS_PROPERTY_CODE' => $p['LIST_OFFERS_PROPERTY_CODE'],
            'OFFERS_SORT_FIELD' => 'sort',
            'OFFERS_SORT_ORDER' => 'asc',
            'OFFERS_LIMIT' => 0,
            'PRICE_CODE' => $p['~PRICE_CODE'],
            'USE_PRICE_COUNT' => 'N',
            'SHOW_PRICE_COUNT' => '1',
            'PRICE_VAT_INCLUDE' => 'Y',
            'CONVERT_CURRENCY' => 'N',
            'SECTION_ID' => $sectionId,
            'SECTION_CODE' => '',
            'INCLUDE_SUBSECTIONS' => 'Y',
            'SHOW_ALL_WO_SECTION' => 'Y',
            'FILTER_NAME' => $filterName,
            'PAGE_ELEMENT_COUNT' => $count,
            'SECTION_URL' => '/catalog/#SECTION_CODE_PATH#/',
            'DETAIL_URL' => '/catalog/#SECTION_CODE_PATH#/#ELEMENT_CODE#/',
            'BASKET_URL' => '/personal/cart/',
            'ACTION_VARIABLE' => 'fkblock_action',
            'DISPLAY_TOP_PAGER' => 'N',
            'DISPLAY_BOTTOM_PAGER' => 'N',
            'SET_TITLE' => 'N',
            'SET_STATUS_404' => 'N',
            'SHOW_404' => 'N',
            'ADD_SECTIONS_CHAIN' => 'N',
            'CACHE_TYPE' => 'A',
            'CACHE_TIME' => '3600',
            'CACHE_FILTER' => 'Y',
            'CACHE_GROUPS' => 'Y',
            'COMPATIBLE_MODE' => 'N',
            'FK_COMPACT' => 'Y',
            'FK_PLAIN_GRID' => $plain ? 'Y' : 'N',
        ], false);
        return trim((string)ob_get_clean());
    }
}

if (!function_exists('fk_product_block')) {
    /**
     * Блок карточек под товаром («Сопутствующие товары», «Похожие товары»). Пустой результат — ничего не выводит.
     * $filter — дополнительный фильтр (['ID' => [...]] или ['!ID' => 5]); $sectionId — раздел для выбора (0 = весь каталог).
     */
    function fk_product_block(string $title, array $filter, int $sectionId = 0, int $count = 4): void
    {
        $html = fk_product_html($title, $filter, $sectionId, $count);
        if ($html === '') {
            return;
        }
        $id = 'fk-block-' . substr(md5($title), 0, 6);
        echo '<section class="fk-section" aria-labelledby="' . $id . '"><div class="fk-section-head"><h2 id="' . $id . '">'
            . htmlspecialcharsbx($title) . '</h2></div>' . $html . '</section>';
    }
}

if (!function_exists('fk_new_filter')) {
    /**
     * Фильтр «Новинка»: у товара свойство NEW = «Y». NEW — свойство-список (флажок): в фильтр уходит ID значения,
     * найденного по XML_ID или тексту «Y»/«Да». Если такого значения нет, не показываем ничего (а не весь каталог).
     */
    function fk_new_filter(): array
    {
        static $filter = null;
        if ($filter !== null) {
            return $filter;
        }
        $ids = [];
        if (\Bitrix\Main\Loader::includeModule('iblock')) {
            $rs = \CIBlockPropertyEnum::GetList([], ['IBLOCK_ID' => fk_iblock_id('catalog'), 'CODE' => 'NEW']);
            while ($e = $rs->Fetch()) {
                $xml = mb_strtolower(trim((string)$e['XML_ID']));
                $val = mb_strtolower(trim((string)$e['VALUE']));
                if (in_array($xml, ['y', 'yes'], true) || in_array($val, ['y', 'да'], true)) {
                    $ids[] = (int)$e['ID'];
                }
            }
        }
        return $filter = ['=PROPERTY_NEW' => $ids ?: [-1]];
    }
}
