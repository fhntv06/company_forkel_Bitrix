<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @global CMain $APPLICATION */

$fkSocial = [
    'ВКонтакте' => ['fk_social_vk', 'vk-By-Pixel-perfect.webp'],
    'MAX' => ['fk_social_max', 'max_v5.webp'],
    'Telegram' => ['fk_social_tg', 'telegram-By-Freepik.webp'],
];
?>
<?php if (fk_page_has_wrapper()) { ?></div><?php } ?>
</main>

<footer class="foot fk-footer" id="contacts">
    <div class="fk-container">
        <div class="fk-footer__grid">
            <div class="fk-footer__row">
                <div class="fk-footer__brand">
                    <p class="fk-footer__logo"><svg viewBox="0 0 40 40" aria-hidden="true"><path d="M6 24A16 16 0 0 1 20 4" fill="none" stroke="var(--yellow)" stroke-width="2.4" stroke-linecap="round"/></svg>FORKEL</p>
                </div>
                <nav class="fk-footer__social" aria-label="Соцсети">
                    <h2>Мы в соцсетях</h2>
                    <ul class="fk-social-list">
                        <?php foreach ($fkSocial as $name => [$area, $icon]) {
                            $url = fk_include_text($area);
                            if ($url === '') {
                                continue; // адрес не заполнен — иконку не показываем
                            }
                            ?><li><a class="fk-social-link" href="<?= htmlspecialcharsbx($url) ?>" target="_blank" rel="noopener" aria-label="<?= htmlspecialcharsbx($name) ?> (откроется в новой вкладке)"><img src="<?= SITE_TEMPLATE_PATH ?>/assets/images/icons/<?= $icon ?>" alt="" width="24" height="24"></a></li>
                        <?php } ?>
                    </ul>
                </nav>
            </div>
            <div class="fk-footer__row">
                <nav class="fk-footer__nav" aria-label="Разделы">
                    <h2>Разделы</h2>
                    <ul>
                        <?php $APPLICATION->IncludeComponent('bitrix:menu', 'fk_footer', ['ROOT_MENU_TYPE' => 'footer_sections', 'MENU_CACHE_TYPE' => 'A', 'MENU_CACHE_TIME' => 3600, 'MENU_CACHE_USE_GROUPS' => 'N', 'MENU_CACHE_GET_VARS' => [], 'MAX_LEVEL' => 1, 'CHILD_MENU_TYPE' => '', 'USE_EXT' => 'N', 'DELAY' => 'N', 'ALLOW_MULTI_SELECT' => 'N'], false); ?>
                    </ul>
                </nav>
                <nav class="fk-footer__info" aria-label="Информация">
                    <h2>Информация</h2>
                    <ul>
                        <?php $APPLICATION->IncludeComponent('bitrix:menu', 'fk_footer', ['ROOT_MENU_TYPE' => 'footer_info', 'MENU_CACHE_TYPE' => 'A', 'MENU_CACHE_TIME' => 3600, 'MENU_CACHE_USE_GROUPS' => 'N', 'MENU_CACHE_GET_VARS' => [], 'MAX_LEVEL' => 1, 'CHILD_MENU_TYPE' => '', 'USE_EXT' => 'N', 'DELAY' => 'N', 'ALLOW_MULTI_SELECT' => 'N'], false); ?>
                    </ul>
                </nav>
                <div class="fk-footer__contacts">
                    <h2>Контакты</h2>
                    <?php $APPLICATION->IncludeComponent('bitrix:main.include', '', ['AREA_FILE_SHOW' => 'file', 'PATH' => '/include/fk_phone.php'], false); ?>
                    <?php $APPLICATION->IncludeComponent('bitrix:main.include', '', ['AREA_FILE_SHOW' => 'file', 'PATH' => '/include/fk_email.php'], false); ?>
                    <?php $APPLICATION->IncludeComponent('bitrix:main.include', '', ['AREA_FILE_SHOW' => 'file', 'PATH' => '/include/fk_address.php'], false); ?>
                    <?php $APPLICATION->IncludeComponent('bitrix:main.include', '', ['AREA_FILE_SHOW' => 'file', 'PATH' => '/include/fk_schedule.php'], false); ?>
                </div>
            </div>
        </div>
        <div class="fk-footer__legal">
            <?php $APPLICATION->IncludeComponent('bitrix:main.include', '', ['AREA_FILE_SHOW' => 'file', 'PATH' => '/include/fk_legal_link.php'], false); ?>
            <?php $APPLICATION->IncludeComponent('bitrix:main.include', '', ['AREA_FILE_SHOW' => 'file', 'PATH' => '/include/fk_requisites.php'], false); ?>
        </div>
    </div>
</footer>

<div class="toast" id="toast" role="status" aria-live="polite"></div>

<?php include __DIR__ . "/partials/feedback_dialog.php"; ?>
<script src="<?= SITE_TEMPLATE_PATH ?>/js/site.js"></script>
<script src="<?= SITE_TEMPLATE_PATH ?>/js/feedback.js"></script>
<script src="<?= SITE_TEMPLATE_PATH ?>/js/catalog.js"></script>
<script src="<?= SITE_TEMPLATE_PATH ?>/js/pdp.js"></script>
<script src="<?= SITE_TEMPLATE_PATH ?>/js/solutions.js"></script>
<script src="<?= SITE_TEMPLATE_PATH ?>/js/forms.js"></script>
<script src="<?= SITE_TEMPLATE_PATH ?>/js/social.js"></script>
</body>
</html>
