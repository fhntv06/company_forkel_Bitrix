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
                    <a class="fk-footer__logo" href="/" aria-label="FORKEL, на главную"><svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xodm="http://www.corel.com/coreldraw/odm/2003" xml:space="preserve" width="150" height="44" version="1.1" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd" viewBox="0 0 29700 21000">
                        <defs>
                            <linearGradient id="fk-footer-gradient-0" gradientUnits="userSpaceOnUse" x1="9604.9" y1="6271.54" x2="22207.98" y2="6271.54">
                                <stop offset="0" style="stop-opacity:1; stop-color:#FFCC00"/>
                                <stop offset="1" style="stop-opacity:1; stop-color:white"/>
                            </linearGradient>
                            <linearGradient id="fk-footer-gradient-1" gradientUnits="userSpaceOnUse" xlink:href="#fk-footer-gradient-0" x1="9604.9" y1="14742.68" x2="22207.98" y2="14742.68"></linearGradient>
                        </defs>
                        <g id="fk-footer-layer">
                            <metadata id="fk-footer-metadata"/>
                            <path fill="currentColor" d="M17636.83 10044.03l1910.79 -1415.45 -912.89 0 -2407.59 1787.05 0 -1787.05 -636.27 0 0 3574.04 636.27 0 0 -1118.19 894.27 -659.63 1746.25 1777.82 890.8 0 -2121.63 -2158.59zm-14901.75 49.93l0 638.24 2433.11 0 0 -638.24 -2433.11 0zm-249.34 -827.09l2977.72 0 0 -638.29 -3613.99 0 0 3574.04 636.27 0 0 -2935.75zm5929.88 -679.1l-819.52 0c-992.5,0 -1801.86,811.8 -1801.86,1807.42 0,995.67 809.36,1807.43 1801.86,1807.43l819.52 0c992.61,0 1801.91,-811.76 1801.91,-1807.43 0,-995.62 -809.3,-1807.42 -1801.91,-1807.42zm0 2986.86l-819.52 0c-646.43,0 -1175.8,-531.01 -1175.8,-1179.44 0,-648.42 529.37,-1179.44 1175.8,-1179.44l819.52 0c646.43,0 1175.81,531.02 1175.81,1179.44 0,648.43 -529.38,1179.44 -1175.81,1179.44zm5659.41 -441.71c532,-68.55 882.94,-703.35 889.3,-1201.32 8.35,-658.67 -534.51,-1303.02 -1206.33,-1303.02l-2955.1 0 0 3574.04 636.21 0 0 -2935.75 2323.97 0c325.75,0 564.99,368.6 564.99,664.73 0,301.18 -249.51,664.68 -564.99,664.68l-2084.36 0 0 638.23 1605.72 0 930.91 968.11 895.87 0 -1036.19 -1064.66 0 -5.04zm6872.65 -1038.96l0 638.24 2442.95 0 0 -638.24 -2442.95 0zm-239.55 -827.09l2977.72 0 0 -638.29 -3613.99 0 0 3574.04 3613.99 0 0 -638.18 -2977.72 0 0 -2297.57zm4296.03 -638.29l-636.27 0 0 3574.04 3481.59 0 0 -638.18 -2845.32 0 0 -2935.86z"/>
                            <path fill="url(#fk-footer-gradient-0)" d="M14654.89 4678.29c-2195.47,0 -4183.9,1276.95 -5049.99,3186.55 1058.81,-1648.77 2924.62,-2743.08 5049.99,-2743.08 2125.37,0 3991.18,1094.31 5049.99,2743.08 -866.09,-1909.6 -2854.52,-3186.55 -5049.99,-3186.55z"/>
                            <path fill="url(#fk-footer-gradient-1)" d="M14654.89 16335.98c2195.47,0 4183.9,-1276.95 5049.99,-3186.55 -1058.81,1648.77 -2924.62,2743.08 -5049.99,2743.08 -2125.37,0 -3991.18,-1094.31 -5049.99,-2743.08 866.09,1909.6 2854.52,3186.55 5049.99,3186.55z"/>
                        </g>
                    </svg></a>
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
                    <p><?php $APPLICATION->IncludeComponent('bitrix:main.include', '', ['AREA_FILE_SHOW' => 'file', 'PATH' => '/include/fk_phone.php'], false); ?></p>
                    <p><?php $APPLICATION->IncludeComponent('bitrix:main.include', '', ['AREA_FILE_SHOW' => 'file', 'PATH' => '/include/fk_email.php'], false); ?></p>
                    <p class="fk-footer__hours">График работы: <span class="fk-footer__time"><?php $APPLICATION->IncludeComponent('bitrix:main.include', '', ['AREA_FILE_SHOW' => 'file', 'PATH' => '/include/fk_schedule.php'], false); ?></span></p>
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
