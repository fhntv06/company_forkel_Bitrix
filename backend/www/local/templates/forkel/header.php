<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @global CMain $APPLICATION */

use Bitrix\Main\Page\Asset;

$asset = Asset::getInstance();
$curPagePath = (string)$APPLICATION->GetCurPage(false);
$isCustomShopTemplate = strpos($curPagePath, '/personal/cart/') === 0 || strpos($curPagePath, '/personal/order/make/') === 0;
// Порядок как в прототипе V4; v4-skin.css всегда последний.
if (fk_is_shop_page() && !$isCustomShopTemplate) {
    // штатные шаблоны кабинета/авторизации рассчитаны на Bootstrap; корзина и оформление — собственные без Bootstrap
    $asset->addCss('/bitrix/css/main/bootstrap.min.css');
}
$asset->addCss(SITE_TEMPLATE_PATH . '/styles.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/shared.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/header.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/footer.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/search.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/feedback.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/products.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/favorites.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/projects.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/solutions.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/interiors.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/configurator.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/page.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/cooperation.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/about.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/account.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/cart.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/collections.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/categories.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/catalog.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/checkout.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/contacts.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/product-page.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/download-file.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/accessories.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/search-page.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/v4-skin.css');
    $asset->addCss(SITE_TEMPLATE_PATH . '/css/shop-skin.css');
$asset->addString(
    '<script>document.documentElement.className = "js"; window.FK = ' . \Bitrix\Main\Web\Json::encode([
        'tpl' => SITE_TEMPLATE_PATH,
        'social' => [
            'vk' => fk_include_text('fk_social_vk'),
            'max' => fk_include_text('fk_social_max'),
            'tg' => fk_include_text('fk_social_tg'),
        ],
    ]) . ';</script>'
);

// Canonical URL (FR-015 / ТЗ §3.6): пагинация PAGEN_*, сортировки и UTM сводятся к базовому чистому URL
$fkProto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$fkHost = (string)($_SERVER['HTTP_HOST'] ?? 'forkel.ru');
$fkCanonicalUrl = $fkProto . '://' . $fkHost . $curPagePath;

$collectionsId = fk_iblock_id('collections');
$catalogId = fk_iblock_id('catalog');
$fkWishlistCount = class_exists('\Forkel\Wishlist') ? \Forkel\Wishlist::count() : 0;
?><!DOCTYPE html>
<html lang="<?= LANGUAGE_ID ?>" class="no-js">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php $APPLICATION->ShowTitle(); ?></title>
    <?php if (!defined('ERROR_404') || ERROR_404 !== 'Y'): ?>
    <link rel="canonical" href="<?= htmlspecialcharsbx($fkCanonicalUrl) ?>">
    <?php endif; ?>
    <?php $APPLICATION->ShowHead(); ?>
</head>
<body>
<?php $APPLICATION->ShowPanel(); ?>
<?php include __DIR__ . '/partials/sprite.php'; ?>

<a class="skip" href="#main">К содержимому</a>

<header class="fk-header fk-container" id="top">
    <div class="fk-container fk-header__row">
        <a class="fk-logo" href="/" aria-label="FORKEL, на главную"><svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xodm="http://www.corel.com/coreldraw/odm/2003" xml:space="preserve" width="150" height="44" version="1.1" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd" viewBox="0 0 29700 21000">
            <defs>
                <linearGradient id="fk-header-gradient-0" gradientUnits="userSpaceOnUse" x1="9604.9" y1="6271.54" x2="22207.98" y2="6271.54">
                    <stop offset="0" style="stop-opacity:1; stop-color:#FFCC00"/>
                    <stop offset="1" style="stop-opacity:1; stop-color:white"/>
                </linearGradient>
                <linearGradient id="fk-header-gradient-1" gradientUnits="userSpaceOnUse" xlink:href="#fk-header-gradient-0" x1="9604.9" y1="14742.68" x2="22207.98" y2="14742.68"></linearGradient>
            </defs>
            <g id="fk-header-layer">
                <metadata id="fk-header-metadata"/>
                <path fill="currentColor" d="M17636.83 10044.03l1910.79 -1415.45 -912.89 0 -2407.59 1787.05 0 -1787.05 -636.27 0 0 3574.04 636.27 0 0 -1118.19 894.27 -659.63 1746.25 1777.82 890.8 0 -2121.63 -2158.59zm-14901.75 49.93l0 638.24 2433.11 0 0 -638.24 -2433.11 0zm-249.34 -827.09l2977.72 0 0 -638.29 -3613.99 0 0 3574.04 636.27 0 0 -2935.75zm5929.88 -679.1l-819.52 0c-992.5,0 -1801.86,811.8 -1801.86,1807.42 0,995.67 809.36,1807.43 1801.86,1807.43l819.52 0c992.61,0 1801.91,-811.76 1801.91,-1807.43 0,-995.62 -809.3,-1807.42 -1801.91,-1807.42zm0 2986.86l-819.52 0c-646.43,0 -1175.8,-531.01 -1175.8,-1179.44 0,-648.42 529.37,-1179.44 1175.8,-1179.44l819.52 0c646.43,0 1175.81,531.02 1175.81,1179.44 0,648.43 -529.38,1179.44 -1175.81,1179.44zm5659.41 -441.71c532,-68.55 882.94,-703.35 889.3,-1201.32 8.35,-658.67 -534.51,-1303.02 -1206.33,-1303.02l-2955.1 0 0 3574.04 636.21 0 0 -2935.75 2323.97 0c325.75,0 564.99,368.6 564.99,664.73 0,301.18 -249.51,664.68 -564.99,664.68l-2084.36 0 0 638.23 1605.72 0 930.91 968.11 895.87 0 -1036.19 -1064.66 0 -5.04zm6872.65 -1038.96l0 638.24 2442.95 0 0 -638.24 -2442.95 0zm-239.55 -827.09l2977.72 0 0 -638.29 -3613.99 0 0 3574.04 3613.99 0 0 -638.18 -2977.72 0 0 -2297.57zm4296.03 -638.29l-636.27 0 0 3574.04 3481.59 0 0 -638.18 -2845.32 0 0 -2935.86z"/>
                <path fill="url(#fk-header-gradient-0)" d="M14654.89 4678.29c-2195.47,0 -4183.9,1276.95 -5049.99,3186.55 1058.81,-1648.77 2924.62,-2743.08 5049.99,-2743.08 2125.37,0 3991.18,1094.31 5049.99,2743.08 -866.09,-1909.6 -2854.52,-3186.55 -5049.99,-3186.55z"/>
                <path fill="url(#fk-header-gradient-1)" d="M14654.89 16335.98c2195.47,0 4183.9,-1276.95 5049.99,-3186.55 -1058.81,1648.77 -2924.62,2743.08 -5049.99,2743.08 -2125.37,0 -3991.18,-1094.31 -5049.99,-2743.08 866.09,1909.6 2854.52,3186.55 5049.99,3186.55z"/>
            </g>
        </svg></a>

        <nav class="fk-nav" id="site-nav" aria-label="Основная навигация">
            <ul class="fk-nav__list">
                <li>
                    <button class="fk-catalog-btn" type="button" aria-expanded="false" aria-controls="catalog-panel">
                        Каталог
                        <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M3 6l5 5 5-5"/></svg>
                    </button>
                    <div class="fk-catalog-panel" id="catalog-panel">
                        <div class="fk-container">
                            <div class="fk-catalog-cols">
                                <div>
                                    <p class="fk-catalog-title">Коллекции</p>
                                    <ul class="fk-catalog-list">
                                        <?php if ($collectionsId > 0) {
                                            $APPLICATION->IncludeComponent('bitrix:news.list', 'fk_catalog_panel', [
                                                'IBLOCK_TYPE' => '',
                                                'IBLOCK_ID' => $collectionsId,
                                                'NEWS_COUNT' => 20,
                                                'SORT_BY1' => 'SORT',
                                                'SORT_ORDER1' => 'ASC',
                                                'SORT_BY2' => 'NAME',
                                                'SORT_ORDER2' => 'ASC',
                                                'FIELD_CODE' => ['NAME'],
                                                'PROPERTY_CODE' => [],
                                                'CHECK_DATES' => 'Y',
                                                'SET_TITLE' => 'N',
                                                'SET_BROWSER_TITLE' => 'N',
                                                'SET_META_KEYWORDS' => 'N',
                                                'SET_META_DESCRIPTION' => 'N',
                                                'SET_LAST_MODIFIED' => 'N',
                                                'ADD_SECTIONS_CHAIN' => 'N',
                                                'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
                                                'SET_STATUS_404' => 'N',
                                                'SHOW_404' => 'N',
                                                'DISPLAY_TOP_PAGER' => 'N',
                                                'DISPLAY_BOTTOM_PAGER' => 'N',
                                                'CACHE_TYPE' => 'A',
                                                'CACHE_TIME' => 3600,
                                            ], false);
                                        } ?>
                                    </ul>
                                </div>
                                <div>
                                    <p class="fk-catalog-title">Категории</p>
                                    <ul class="fk-catalog-list">
                                        <?php if ($catalogId > 0) {
                                            $APPLICATION->IncludeComponent('bitrix:catalog.section.list', 'fk_catalog_panel', [
                                                'IBLOCK_TYPE' => '',
                                                'IBLOCK_ID' => $catalogId,
                                                'SECTION_ID' => 0,
                                                'SECTION_CODE' => '',
                                                'TOP_DEPTH' => 1,
                                                'COUNT_ELEMENTS' => 'N',
                                                'ADD_SECTIONS_CHAIN' => 'N',
                                                'CACHE_TYPE' => 'A',
                                                'CACHE_TIME' => 3600,
                                            ], false);
                                        } ?>
                                        <li><a href="/stands/">Стенды</a></li>
                                    </ul>
                                    <p class="fk-catalog-all"><a class="fk-link" href="/catalog/">Весь каталог</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <?php
                // Остальные пункты — меню типа «top» (Контент → Структура сайта). «Каталог» и «Коллекции» в нём не нужны: их заменяет панель выше.
                $APPLICATION->IncludeComponent('bitrix:menu', 'fk_top', [
                    'ROOT_MENU_TYPE' => 'top',
                    'MENU_CACHE_TYPE' => 'A',
                    'MENU_CACHE_TIME' => 3600,
                    'MENU_CACHE_USE_GROUPS' => 'N',
                    'MENU_CACHE_GET_VARS' => [],
                    'MAX_LEVEL' => 1,
                    'CHILD_MENU_TYPE' => '',
                    'USE_EXT' => 'N',
                    'DELAY' => 'N',
                    'ALLOW_MULTI_SELECT' => 'N',
                ], false);
                ?>
                <li class="fk-nav__phone"><span class="fk-phone"><?php $APPLICATION->IncludeComponent('bitrix:main.include', '', ['AREA_FILE_SHOW' => 'file', 'PATH' => '/include/fk_phone.php'], false); ?></span></li>
            </ul>
        </nav>

        <div class="fk-actions">
            <a class="fk-icon-btn fk-search-btn" href="/search/" aria-label="Поиск по товарам">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="M16 16l4.5 4.5"/></svg>
                <span class="fk-search-btn__text" aria-hidden="true">Поиск по товарам</span>
            </a>
            <a class="fk-icon-btn fk-icon-btn--fav" href="/favorites/" aria-label="Избранное" data-wishlist-link>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20.5l-1.45-1.32C5.4 14.56 2 11.48 2 7.75 2 4.72 4.42 2.5 7.45 2.5c1.74 0 3.41.81 4.55 2.09C13.14 3.31 14.81 2.5 16.55 2.5 19.58 2.5 22 4.72 22 7.75c0 3.73-3.4 6.81-8.55 11.44L12 20.5z"/></svg>
                <span class="fk-badge" data-wishlist-badge<?= $fkWishlistCount > 0 ? '' : ' hidden' ?>><?= $fkWishlistCount ?></span>
                <span class="fk-vh">Избранное</span>
            </a>
            <a class="fk-icon-btn fk-icon-btn--account" href="<?= \Bitrix\Main\Engine\CurrentUser::get()->getId() ? '/personal/' : '/auth/' ?>" data-account>
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8.5" r="3.75"/><path d="M4.5 20c.9-3.6 3.8-5.5 7.5-5.5s6.6 1.9 7.5 5.5"/></svg>
                <span class="fk-vh">Личный кабинет</span>
            </a>
            <?php
            if (\Bitrix\Main\Loader::includeModule('sale')) {
                $APPLICATION->IncludeComponent('bitrix:sale.basket.basket.line', 'fk_badge', [
                    'PATH_TO_BASKET' => '/personal/cart/',
                    'SHOW_NUM_PRODUCTS' => 'Y',
                    'SHOW_TOTAL_PRICE' => 'N',
                    'SHOW_PERSONAL_LINK' => 'N',
                    'SHOW_AUTHOR' => 'N',
                    'SHOW_PRODUCTS' => 'N',
                    'SHOW_EMPTY_VALUES' => 'Y',
                    'HIDE_ON_BASKET_PAGES' => 'N',
                    'POSITION_FIXED' => 'N',
                ], false);
            } else { ?>
            <a class="fk-icon-btn fk-cart-link" href="/personal/cart/">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 8h14l-1 12H6L5 8z"/><path d="M9 8V6.5a3 3 0 016 0V8"/></svg>
                <span class="fk-vh">Корзина</span>
            </a>
            <?php } ?>
            <span class="fk-phone fk-phone--header"><?php $APPLICATION->IncludeComponent('bitrix:main.include', '', ['AREA_FILE_SHOW' => 'file', 'PATH' => '/include/fk_phone.php'], false); ?></span>
            <button class="fk-menu-btn" type="button" aria-expanded="false" aria-controls="site-nav">Меню</button>
        </div>
    </div>
</header>

<main id="main">
<?php
// Внутренние страницы: обёртка, крошки и hero (V4). Hero отложенный: свойства страницы задаются после header.php.
$APPLICATION->AddBufferContent('fk_render_page_open');
$APPLICATION->IncludeComponent('bitrix:breadcrumb', 'fk_crumbs', ['START_FROM' => 0, 'PATH' => '', 'SITE_ID' => SITE_ID], false);
$APPLICATION->AddBufferContent('fk_render_page_hero');
?>
