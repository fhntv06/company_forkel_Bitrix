<?php
/**
 * Генератор backend/www/sitemap.xml через Bitrix API (FR-015 / TASK-018 Block 4.4).
 * Собирает статические страницы, разделы и товары каталога, коллекции и проекты.
 */
$_SERVER['DOCUMENT_ROOT'] = '/var/www/html';
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;

$baseUrl = rtrim(\Forkel\Env::get('SITE_URL', 'https://forkel.ru'), '/');
$today = date('Y-m-d');

$urls = [
    ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
    ['loc' => '/catalog/', 'priority' => '0.9', 'changefreq' => 'daily'],
    ['loc' => '/catalog/new/', 'priority' => '0.8', 'changefreq' => 'weekly'],
    ['loc' => '/configurator/', 'priority' => '0.8', 'changefreq' => 'weekly'],
    ['loc' => '/projects/', 'priority' => '0.7', 'changefreq' => 'weekly'],
    ['loc' => '/about/', 'priority' => '0.7', 'changefreq' => 'monthly'],
    ['loc' => '/about/contacts/', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => '/cooperation/', 'priority' => '0.7', 'changefreq' => 'monthly'],
    ['loc' => '/stands/', 'priority' => '0.7', 'changefreq' => 'monthly'],
];

if (Loader::includeModule('iblock')) {
    $catalogId = fk_iblock_id('catalog');
    if ($catalogId > 0) {
        $rsSec = CIBlockSection::GetList(
            ['SORT' => 'ASC'],
            ['IBLOCK_ID' => $catalogId, 'ACTIVE' => 'Y', 'CHECK_PERMISSIONS' => 'N'],
            false,
            ['ID', 'SECTION_PAGE_URL', 'TIMESTAMP_X']
        );
        while ($s = $rsSec->GetNext()) {
            if (!empty($s['SECTION_PAGE_URL'])) {
                $urls[] = ['loc' => $s['SECTION_PAGE_URL'], 'priority' => '0.8', 'changefreq' => 'weekly'];
            }
        }
        $rsEl = CIBlockElement::GetList(
            ['ID' => 'ASC'],
            ['IBLOCK_ID' => $catalogId, 'ACTIVE' => 'Y', 'CHECK_PERMISSIONS' => 'N'],
            false,
            false,
            ['ID', 'DETAIL_PAGE_URL']
        );
        while ($e = $rsEl->GetNext()) {
            if (!empty($e['DETAIL_PAGE_URL'])) {
                $urls[] = ['loc' => $e['DETAIL_PAGE_URL'], 'priority' => '0.7', 'changefreq' => 'weekly'];
            }
        }
    }

    foreach (['collections' => '0.8', 'projects' => '0.6'] as $code => $prio) {
        $ibId = fk_iblock_id($code);
        if ($ibId > 0) {
            $rs = CIBlockElement::GetList(
                ['ID' => 'ASC'],
                ['IBLOCK_ID' => $ibId, 'ACTIVE' => 'Y', 'CHECK_PERMISSIONS' => 'N'],
                false,
                false,
                ['ID', 'DETAIL_PAGE_URL']
            );
            while ($e = $rs->GetNext()) {
                if (!empty($e['DETAIL_PAGE_URL'])) {
                    $urls[] = ['loc' => $e['DETAIL_PAGE_URL'], 'priority' => $prio, 'changefreq' => 'monthly'];
                }
            }
        }
    }
}

$seen = [];
$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
foreach ($urls as $u) {
    $loc = $u['loc'];
    if (isset($seen[$loc])) {
        continue;
    }
    $seen[$loc] = true;
    $full = htmlspecialchars($baseUrl . $loc, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    $xml .= "  <url>\n";
    $xml .= "    <loc>{$full}</loc>\n";
    $xml .= "    <lastmod>{$today}</lastmod>\n";
    $xml .= "    <changefreq>{$u['changefreq']}</changefreq>\n";
    $xml .= "    <priority>{$u['priority']}</priority>\n";
    $xml .= "  </url>\n";
}
$xml .= "</urlset>\n";

$outPath = $_SERVER['DOCUMENT_ROOT'] . '/sitemap.xml';
file_put_contents($outPath, $xml);
echo "[SITEMAP] Generated " . count($seen) . " URLs -> $outPath\n";
