<?php

namespace Sprint\Migration;

use Bitrix\Main\Loader;
use Bitrix\Iblock\InheritedProperty\IblockTemplates;
use CCatalogProduct;
use CCatalogStore;
use CFile;
use CGroup;
use CIBlock;
use CIBlockElement;
use CIBlockProperty;
use CIBlockPropertyEnum;
use CIBlockSection;
use CIBlockSectionPropertyLink;
use CPrice;
use CSaleOrderProps;
use CSaleOrderPropsGroup;
use CSaleStatus;
use CSearch;
use CSite;
use CUser;

/**
 * TASK-018 блок 1: права инфоблоков, URL, разделы каталога, свойства, умный фильтр, SEO-шаблоны, переиндексация поиска
 * Перенесено из backend/scripts/ (TASK-018) без изменения логики. Идемпотентно: повторный запуск безопасен.
 */
class Version20261127100000 extends Version
{
    protected $author = 'FORKEL';

    protected $description = 'TASK-018 блок 1: права инфоблоков, URL, разделы каталога, свойства, умный фильтр, SEO-шаблоны, переиндексация поиска';

    protected $moduleVersion = '5.16.1';

    public function up()
    {
global $APPLICATION, $USER, $DB;

if (!Loader::includeModule('iblock') || !Loader::includeModule('catalog') || !Loader::includeModule('search')) {
    throw new \RuntimeException('Required Bitrix modules (iblock, catalog, search) not available.');
}

// 1. Permissions on all 8 iblocks (GROUP_ID=2 -> R, GROUP_ID=1 -> X)
$iblockMap = [];
$rsIb = CIBlock::GetList(['ID' => 'ASC'], ['CHECK_PERMISSIONS' => 'N']);
while ($ib = $rsIb->Fetch()) {
    $iblockMap[$ib['CODE']] = (int)$ib['ID'];
    $perms = CIBlock::GetGroupPermissions((int)$ib['ID']);
    $perms[1] = 'X';
    $perms[2] = 'R';
    CIBlock::SetPermission((int)$ib['ID'], $perms);
    echo "[PERM] Iblock #{$ib['ID']} ({$ib['CODE']}): GROUP 2 = R\n";
}

$catalogId = $iblockMap['catalog'] ?? 0;
$offersId = $iblockMap['catalog_offers'] ?? 0;
$collectionsId = $iblockMap['collections'] ?? 0;
$projectsId = $iblockMap['projects'] ?? 0;

if (!$catalogId || !$offersId) {
    throw new \RuntimeException("Catalog or offers iblock not found.");
}

// 2. Fix URL templates & enable SECTION_PROPERTY / INDEX_ELEMENT
$ibObj = new CIBlock();
$ibObj->Update($catalogId, [
    'LIST_PAGE_URL' => '/catalog/',
    'SECTION_PAGE_URL' => '/catalog/#SECTION_CODE_PATH#/',
    'DETAIL_PAGE_URL' => '/catalog/#SECTION_CODE_PATH#/#ELEMENT_CODE#/',
    'INDEX_ELEMENT' => 'Y',
    'INDEX_SECTION' => 'Y',
    'SECTION_PROPERTY' => 'Y',
]);
echo "[URL] Updated catalog (#$catalogId)\n";

$ibObj->Update($offersId, [
    'INDEX_ELEMENT' => 'Y',
    'SECTION_PROPERTY' => 'Y',
]);
echo "[URL] Updated catalog_offers (#$offersId)\n";

if ($projectsId) {
    $ibObj->Update($projectsId, [
        'LIST_PAGE_URL' => '/projects/',
        'DETAIL_PAGE_URL' => '/projects/#ELEMENT_CODE#/',
        'INDEX_ELEMENT' => 'Y',
    ]);
    echo "[URL] Updated projects (#$projectsId)\n";
}

// 3. Ensure all 6 catalog sections exist
$sectionsSpec = [
    'rozetki' => ['NAME' => 'Розетки', 'SORT' => 100],
    'vyklyuchateli' => ['NAME' => 'Выключатели', 'SORT' => 200],
    'ramki' => ['NAME' => 'Рамки', 'SORT' => 300],
    'upravlenie-zhalyuzi' => ['NAME' => 'Управление жалюзи', 'SORT' => 400],
    'termoregulyatory' => ['NAME' => 'Терморегуляторы', 'SORT' => 500],
    'dimmery' => ['NAME' => 'Диммеры', 'SORT' => 600],
];
$sectionIds = [];
$rsSec = CIBlockSection::GetList([], ['IBLOCK_ID' => $catalogId, 'CHECK_PERMISSIONS' => 'N'], false, ['ID', 'CODE', 'NAME']);
while ($s = $rsSec->Fetch()) {
    $sectionIds[$s['CODE']] = (int)$s['ID'];
}
$secObj = new CIBlockSection();
foreach ($sectionsSpec as $code => $spec) {
    if (!isset($sectionIds[$code])) {
        $newId = $secObj->Add([
            'IBLOCK_ID' => $catalogId,
            'ACTIVE' => 'Y',
            'CODE' => $code,
            'NAME' => $spec['NAME'],
            'SORT' => $spec['SORT'],
        ]);
        if ($newId) {
            $sectionIds[$code] = (int)$newId;
            echo "[SECTION] Created '$code' (#$newId)\n";
        } else {
            echo "[ERR] Section '$code': {$secObj->LAST_ERROR}\n";
        }
    } else {
        $secObj->Update($sectionIds[$code], ['ACTIVE' => 'Y', 'NAME' => $spec['NAME'], 'SORT' => $spec['SORT']]);
        echo "[SECTION] Exists '$code' (#{$sectionIds[$code]})\n";
    }
}

// 4. Fix & add catalog properties
$propMap = [];
$rsProp = CIBlockProperty::GetList([], ['IBLOCK_ID' => $catalogId]);
while ($p = $rsProp->Fetch()) {
    $propMap[$p['CODE']] = $p;
}
$propObj = new CIBlockProperty();

// Fix ARTICLE typo & enable search/filter
if (isset($propMap['ARTICLE'])) {
    $propObj->Update((int)$propMap['ARTICLE']['ID'], [
        'NAME' => 'Артикул',
        'SEARCHABLE' => 'Y',
        'FILTRABLE' => 'Y',
    ]);
    echo "[PROP] Updated ARTICLE -> 'Артикул' (SEARCHABLE=Y)\n";
}

// Fix MANUAL type S -> F
if (isset($propMap['MANUAL']) && $propMap['MANUAL']['PROPERTY_TYPE'] !== 'F') {
    $propObj->Update((int)$propMap['MANUAL']['ID'], [
        'PROPERTY_TYPE' => 'F',
        'FILE_TYPE' => 'pdf,doc,docx,jpg,png',
    ]);
    echo "[PROP] Updated MANUAL -> type F\n";
}

// Ensure COLLECTION, MATERIAL, NEW are filtrable & searchable
foreach (['COLLECTION', 'MATERIAL', 'NEW'] as $code) {
    if (isset($propMap[$code])) {
        $propObj->Update((int)$propMap[$code]['ID'], [
            'FILTRABLE' => 'Y',
            'SEARCHABLE' => 'Y',
        ]);
    }
}

// Add MECHANISM_TYPE if missing
if (!isset($propMap['MECHANISM_TYPE'])) {
    $pid = $propObj->Add([
        'IBLOCK_ID' => $catalogId,
        'NAME' => 'Тип механизма',
        'ACTIVE' => 'Y',
        'SORT' => 150,
        'CODE' => 'MECHANISM_TYPE',
        'PROPERTY_TYPE' => 'S',
        'FILTRABLE' => 'Y',
        'SEARCHABLE' => 'Y',
    ]);
    if ($pid) {
        $propMap['MECHANISM_TYPE'] = ['ID' => $pid];
        echo "[PROP] Created MECHANISM_TYPE (#$pid)\n";
    } else {
        echo "[ERR] MECHANISM_TYPE: {$propObj->LAST_ERROR}\n";
    }
}

// Add ACCESSORIES if missing
if (!isset($propMap['ACCESSORIES'])) {
    $pid = $propObj->Add([
        'IBLOCK_ID' => $catalogId,
        'NAME' => 'С этим товаром покупают',
        'ACTIVE' => 'Y',
        'SORT' => 600,
        'CODE' => 'ACCESSORIES',
        'PROPERTY_TYPE' => 'E',
        'MULTIPLE' => 'Y',
        'LINK_IBLOCK_ID' => $catalogId,
    ]);
    if ($pid) {
        $propMap['ACCESSORIES'] = ['ID' => $pid];
        echo "[PROP] Created ACCESSORIES (#$pid)\n";
    } else {
        echo "[ERR] ACCESSORIES: {$propObj->LAST_ERROR}\n";
    }
}

// Offers properties
$offerPropMap = [];
$rsOProp = CIBlockProperty::GetList([], ['IBLOCK_ID' => $offersId]);
while ($p = $rsOProp->Fetch()) {
    $offerPropMap[$p['CODE']] = $p;
}
if (isset($offerPropMap['COLOR'])) {
    $propObj->Update((int)$offerPropMap['COLOR']['ID'], [
        'FILTRABLE' => 'Y',
        'SEARCHABLE' => 'Y',
    ]);
}

// 5. Enable SMART_FILTER on COLLECTION, MATERIAL, NEW (catalog) and COLOR (offers)
$smartProps = [
    [$catalogId, (int)($propMap['COLLECTION']['ID'] ?? 0), 'F'],
    [$catalogId, (int)($propMap['MATERIAL']['ID'] ?? 0), 'F'],
    [$catalogId, (int)($propMap['NEW']['ID'] ?? 0), 'F'],
    [$offersId, (int)($offerPropMap['COLOR']['ID'] ?? 0), 'F'],
];
foreach ($smartProps as [$ibId, $propId, $displayType]) {
    if ($propId > 0) {
        CIBlockSectionPropertyLink::Set(0, $propId, [
            'IBLOCK_ID' => $ibId,
            'SMART_FILTER' => 'Y',
            'DISPLAY_TYPE' => $displayType,
            'DISPLAY_EXPANDED' => 'Y',
        ]);
        echo "[SMART_FILTER] Enabled on IB #$ibId prop #$propId\n";
    }
}


// 7. SEO Templates for catalog, collections, projects
$seoSpecs = [
    $catalogId => [
        'SECTION_META_TITLE' => '{=this.Name} FORKEL — купить в интернет-магазине',
        'SECTION_META_DESCRIPTION' => '{=this.Name} FORKEL: коллекции F70 и F55, стекло, алюминий, термопласт. Доставка по России и самовывоз в Москве.',
        'ELEMENT_META_TITLE' => '{=this.Name} — купить по цене от производителя | FORKEL',
        'ELEMENT_META_DESCRIPTION' => '{=this.Name}. Характеристики, цвета рамок и механизмов, наличие и доставка. Интернет-магазин FORKEL.',
    ],
];
if ($collectionsId) {
    $seoSpecs[$collectionsId] = [
        'ELEMENT_META_TITLE' => 'Коллекция {=this.Name} — электроустановочные изделия FORKEL',
        'ELEMENT_META_DESCRIPTION' => 'Розетки, выключатели и рамки коллекции {=this.Name} FORKEL. Материалы: стекло, алюминий, термопласт.',
    ];
}
if ($projectsId) {
    $seoSpecs[$projectsId] = [
        'ELEMENT_META_TITLE' => 'Проект {=this.Name} — портфолио FORKEL',
        'ELEMENT_META_DESCRIPTION' => 'Реализованный проект с электроустановочными изделиями FORKEL: {=this.Name}.',
    ];
}
foreach ($seoSpecs as $ibId => $tpls) {
    $iprop = new IblockTemplates($ibId);
    $iprop->set($tpls);
    echo "[SEO] Updated IblockTemplates for IB #$ibId\n";
}

// 8. Reindex search
$count = CSearch::ReIndexAll(true);
echo "[SEARCH] ReIndexed search items: $count\n";

// Clear managed cache
\Bitrix\Main\Application::getInstance()->getManagedCache()->cleanAll();
echo "[CACHE] Managed cache cleared.\n";
echo "=== Block 1 DONE ===\n";
    }

    public function down()
    {
        throw new \RuntimeException('Откат не поддерживается: изменения данных/настроек необратимы. Восстановление - из резервной копии БД.');
    }
}
