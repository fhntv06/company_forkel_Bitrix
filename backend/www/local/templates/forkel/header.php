<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @global CMain $APPLICATION */

use Bitrix\Main\Page\Asset;

$asset = Asset::getInstance();
// Порядок как в прототипе V4; v4-skin.css всегда последний.
    if (fk_is_shop_page()) {
    $asset->addCss('/bitrix/css/main/bootstrap.min.css'); // штатные шаблоны корзины, заказа и кабинета рассчитаны на Bootstrap
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

$collectionsId = fk_iblock_id('collections');
$catalogId = fk_iblock_id('catalog');
?><!DOCTYPE html>
<html lang="<?= LANGUAGE_ID ?>" class="no-js">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php $APPLICATION->ShowTitle(); ?></title>
    <?php $APPLICATION->ShowHead(); ?>
</head>
<body>
<?php $APPLICATION->ShowPanel(); ?>
<?php include __DIR__ . '/partials/sprite.php'; ?>

<a class="skip" href="#main">К содержимому</a>

<header class="fk-header fk-container" id="top">
    <div class="fk-container fk-header__row">
        <a class="fk-logo" href="/" aria-label="FORKEL, на главную">
            <svg viewBox="0 0 40 40" aria-hidden="true"><path d="M6 24A16 16 0 0 1 20 4" fill="none" stroke="var(--yellow)" stroke-width="2.4" stroke-linecap="round"/></svg>
            FORKEL
        </a>

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
            <a class="fk-icon-btn fk-icon-btn--account" href="/personal/" data-account>
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
