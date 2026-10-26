<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @global CMain $APPLICATION */

// Версия в адресе скрипта: браузер подхватывает правки без ручного сброса кеша.
$fkAppJs = SITE_TEMPLATE_PATH . '/js/app.js';
$fkAppJsFile = $_SERVER['DOCUMENT_ROOT'] . $fkAppJs;
$fkAppJsVersion = is_file($fkAppJsFile) ? filemtime($fkAppJsFile) : 0;
?>
</main>

<!-- Подвал. Разметка из prototype/homepage-v3. Контакты и реквизиты предоставит заказчик (DEC-DEV-11: демонстрационные материалы временные). -->
<footer class="fk-footer" id="contacts">
    <div class="fk-container">
        <div class="fk-footer__grid">
            <div class="fk-footer__brand">
                <p class="fk-footer__logo">FORKEL</p>
            </div>
            <nav class="fk-footer__nav" aria-label="Разделы">
                <h2>Разделы</h2>
                <ul>
                    <li><a href="/catalog/">Каталог</a></li>
                    <li><a href="/#collections">Коллекции</a></li>
                    <li><a href="/#new">Новинки</a></li>
                    <li><a href="/#projects">Проекты</a></li>
                    <li><a href="/#pro">Сотрудничество</a></li>
                    <li><a href="/about/delivery/">Доставка и оплата</a></li>
                    <li><a href="/about/contacts/">Контакты</a></li>
                </ul>
            </nav>
            <div class="fk-footer__contacts">
                <h2>Контакты</h2>
                <p>Телефон, email и адрес добавит владелец.</p>
            </div>
        </div>
        <div class="fk-footer__legal">
            <span>Политика обработки персональных данных — текст предоставит владелец.</span>
            <p>Юридические данные добавит владелец.</p>
        </div>
    </div>
</footer>

<div class="fk-toast" id="proto-note" role="status" aria-live="polite"></div>

<script src="<?= htmlspecialchars($fkAppJs . '?v=' . $fkAppJsVersion, ENT_QUOTES) ?>"></script>
</body>
</html>