<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @global CMain $APPLICATION */
?><!DOCTYPE html>
<html lang="<?= LANGUAGE_ID ?>" class="no-js">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>document.documentElement.className = 'js';</script>
    <title><?php $APPLICATION->ShowTitle(); ?></title>
    <?php
    // Выводит мета-теги, подключённые стили (в том числе template_styles.css этого шаблона) и скрипты.
    $APPLICATION->ShowHead();
    ?>
</head>
<body>
<?php $APPLICATION->ShowPanel(); ?>

<a class="fk-skip" href="#main">К содержимому</a>

<!-- Шапка. Разметка из prototype/homepage-v3; ссылки заменены на адреса сайта. Меню статическое: компонент меню подключается отдельной задачей. -->
<header class="fk-header" id="top">
    <div class="fk-container fk-header__row">
        <a class="fk-logo" href="/" aria-label="FORKEL, на главную">FORKEL</a>

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
                                        <li><a href="/#collections">Коллекция A</a></li>
                                        <li><a href="/#collections">Коллекция B</a></li>
                                        <li><a href="/#collections">Коллекция C</a></li>
                                        <li><a href="/#collections">Коллекция D</a></li>
                                    </ul>
                                </div>
                                <div>
                                    <p class="fk-catalog-title">Категории</p>
                                    <ul class="fk-catalog-list">
                                        <li><a href="/catalog/">Розетки</a></li>
                                        <li><a href="/catalog/">Выключатели</a></li>
                                        <li><a href="/catalog/">Рамки</a></li>
                                    </ul>
                                    <p class="fk-catalog-all"><a class="fk-link" href="/catalog/">Весь каталог</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <!-- Запасные ссылки, если JavaScript недоступен -->
                <li class="fk-nav-fallback"><a class="fk-nav__link" href="/#collections">Коллекции</a></li>
                <li class="fk-nav-fallback"><a class="fk-nav__link" href="/#categories">Категории</a></li>
                <li><a class="fk-nav__link" href="/#new">Новинки</a></li>
                <li><a class="fk-nav__link" href="/#projects">Проекты</a></li>
                <li><a class="fk-nav__link" href="/#pro">Сотрудничество</a></li>
                <li class="fk-nav__mobile-only"><a class="fk-nav__link" href="/about/delivery/">Доставка и оплата</a></li>
                <li><a class="fk-nav__link" href="/about/contacts/">Контакты</a></li>
                <li class="fk-nav__phone"><span class="fk-phone">Телефон (заглушка)</span></li>
            </ul>
        </nav>

        <div class="fk-actions">
            <a class="fk-icon-btn" href="/search/">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="M16 16l4.5 4.5"/></svg>
                <span class="fk-vh">Поиск</span>
            </a>
            <a class="fk-icon-btn fk-icon-btn--account" href="/personal/">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8.5" r="3.75"/><path d="M4.5 20c.9-3.6 3.8-5.5 7.5-5.5s6.6 1.9 7.5 5.5"/></svg>
                <span class="fk-vh">Личный кабинет</span>
            </a>
            <a class="fk-icon-btn fk-cart-link" href="/personal/cart/">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 8h14l-1 12H6L5 8z"/><path d="M9 8V6.5a3 3 0 016 0V8"/></svg>
                <span class="fk-vh">Корзина</span>
            </a>
            <span class="fk-phone fk-phone--header">Телефон (заглушка)</span>
            <button class="fk-menu-btn" type="button" aria-expanded="false" aria-controls="site-nav">Меню</button>
        </div>
    </div>
</header>

<main id="main">