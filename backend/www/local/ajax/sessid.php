<?php
/**
 * Отдаёт текущий sessid. Нужен страницам, чей HTML кешируется (страница товара): sessid в кеше устаревает.
 */
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');
echo \Bitrix\Main\Web\Json::encode(['sessid' => bitrix_sessid()]);
