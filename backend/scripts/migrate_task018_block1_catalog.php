<?php
/**
 * TASK-018 Block 1: Bitrix API migration for permissions, URLs, catalog structure,
 * properties, smart filter, Beta products/offers, SEO templates, and search reindex.
 *
 * Idempotent. Run inside PHP container:
 *   docker exec forkel-php php /var/www/html/../scripts/migrate_task018_block1_catalog.php
 */

$_SERVER['DOCUMENT_ROOT'] = '/var/www/html';
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('BX_NO_ACCELERATOR_RESET', true);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;
use Bitrix\Iblock\InheritedProperty\IblockTemplates;

if (!Loader::includeModule('iblock') || !Loader::includeModule('catalog') || !Loader::includeModule('search')) {
    fwrite(STDERR, "Required Bitrix modules (iblock, catalog, search) not available.\n");
    exit(1);
}

echo "=== TASK-018 Block 1 Migration ===\n";

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
    fwrite(STDERR, "Catalog or offers iblock not found.\n");
    exit(1);
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

// Load enum maps
$enumMap = static function (int $propId): array {
    $out = [];
    $rs = CIBlockPropertyEnum::GetList([], ['PROPERTY_ID' => $propId]);
    while ($e = $rs->Fetch()) {
        $out[$e['XML_ID']] = (int)$e['ID'];
    }
    return $out;
};
$matEnums = $enumMap((int)$propMap['MATERIAL']['ID']);
$newEnums = $enumMap((int)$propMap['NEW']['ID']);
$colorEnums = $enumMap((int)$offerPropMap['COLOR']['ID']);

$colIds = [];
if ($collectionsId) {
    $rsCol = CIBlockElement::GetList([], ['IBLOCK_ID' => $collectionsId, 'CHECK_PERMISSIONS' => 'N'], false, false, ['ID', 'CODE']);
    while ($c = $rsCol->Fetch()) {
        $colIds[strtolower($c['CODE'])] = (int)$c['ID'];
    }
}

// 6. Seed Beta products & offers
$imgBase = $_SERVER['DOCUMENT_ROOT'] . '/local/templates/forkel/assets/images/designs/';
$betaProducts = [
    [
        'CODE' => 'f70-rozetka-s-zazemleniem',
        'NAME' => 'Розетка с заземлением FORKEL F70',
        'SECTION' => 'rozetki',
        'COLLECTION' => 'f70',
        'MATERIAL' => 'glass',
        'NEW' => 'Y',
        'ARTICLE' => 'FK-F70-SK01',
        'MECHANISM_TYPE' => 'Розетка силовая 16А с заземлением',
        'PREVIEW_TEXT' => 'Силовая розетка 16А 250В с заземляющим контактом и защитными шторками. Серия F70, рамка закалённое стекло.',
        'IMAGE' => '1G Cashmere.jpg',
        'OFFERS' => [
            ['COLOR' => 'white', 'PRICE' => 1490, 'QTY' => 42, 'CAN_BUY_ZERO' => 'N', 'CODE' => 'f70-sk01-white'],
            ['COLOR' => 'black', 'PRICE' => 1590, 'QTY' => 35, 'CAN_BUY_ZERO' => 'N', 'CODE' => 'f70-sk01-black'],
            ['COLOR' => 'cashmere', 'PRICE' => 1690, 'QTY' => 18, 'CAN_BUY_ZERO' => 'N', 'CODE' => 'f70-sk01-cashmere'],
            ['COLOR' => 'gold', 'PRICE' => 1890, 'QTY' => 12, 'CAN_BUY_ZERO' => 'N', 'CODE' => 'f70-sk01-gold'],
        ],
    ],
    [
        'CODE' => 'f70-vyklyuchatel-odnoklavishnyy',
        'NAME' => 'Выключатель одноклавишный FORKEL F70',
        'SECTION' => 'vyklyuchateli',
        'COLLECTION' => 'f70',
        'MATERIAL' => 'aluminum',
        'NEW' => 'Y',
        'ARTICLE' => 'FK-F70-SW01',
        'MECHANISM_TYPE' => 'Выключатель 1-клавишный 10А',
        'PREVIEW_TEXT' => 'Одноклавишный выключатель 10А с мягким ходом клавиши. Серия F70, анодированный алюминий.',
        'IMAGE' => '09a7a6aa_img_4K.jpg',
        'OFFERS' => [
            ['COLOR' => 'white', 'PRICE' => 1290, 'QTY' => 50, 'CAN_BUY_ZERO' => 'N', 'CODE' => 'f70-sw01-white'],
            ['COLOR' => 'black', 'PRICE' => 1390, 'QTY' => 30, 'CAN_BUY_ZERO' => 'N', 'CODE' => 'f70-sw01-black'],
            ['COLOR' => 'gray', 'PRICE' => 1390, 'QTY' => 24, 'CAN_BUY_ZERO' => 'N', 'CODE' => 'f70-sw01-gray'],
        ],
    ],
    [
        'CODE' => 'f55-vyklyuchatel-dvukhklavishnyy',
        'NAME' => 'Выключатель двухклавишный FORKEL F55',
        'SECTION' => 'vyklyuchateli',
        'COLLECTION' => 'f55',
        'MATERIAL' => 'thermoplastic',
        'NEW' => 'N',
        'ARTICLE' => 'FK-F55-SW02',
        'MECHANISM_TYPE' => 'Выключатель 2-клавишный 10А',
        'PREVIEW_TEXT' => 'Двухклавишный выключатель серии F55 в корпусе из износостойкого термопласта.',
        'IMAGE' => '215850e6cd344fd9f815580cfbd74d4c_da1667d2-ec38-4e74-a2c0-c17b37f25be6.jpg',
        'OFFERS' => [
            ['COLOR' => 'white', 'PRICE' => 990, 'QTY' => 40, 'CAN_BUY_ZERO' => 'N', 'CODE' => 'f55-sw02-white'],
            ['COLOR' => 'black', 'PRICE' => 1090, 'QTY' => 22, 'CAN_BUY_ZERO' => 'N', 'CODE' => 'f55-sw02-black'],
            ['COLOR' => 'gray', 'PRICE' => 1090, 'QTY' => 15, 'CAN_BUY_ZERO' => 'N', 'CODE' => 'f55-sw02-gray'],
        ],
    ],
    [
        'CODE' => 'f70-ramka-2-posta-steklo',
        'NAME' => 'Рамка 2-постовая FORKEL F70 Стекло',
        'SECTION' => 'ramki',
        'COLLECTION' => 'f70',
        'MATERIAL' => 'glass',
        'NEW' => 'Y',
        'ARTICLE' => 'FK-F70-FR02',
        'MECHANISM_TYPE' => 'Рамка установочная 2 поста',
        'PREVIEW_TEXT' => 'Двухпостовая установочная рамка из закалённого стекла для горизонтального и вертикального монтажа.',
        'IMAGE' => '1G Cashmere.jpg',
        'OFFERS' => [
            ['COLOR' => 'white', 'PRICE' => 1150, 'QTY' => 38, 'CAN_BUY_ZERO' => 'N', 'CODE' => 'f70-fr02-white'],
            ['COLOR' => 'black', 'PRICE' => 1150, 'QTY' => 27, 'CAN_BUY_ZERO' => 'N', 'CODE' => 'f70-fr02-black'],
            ['COLOR' => 'lightgray', 'PRICE' => 1250, 'QTY' => 16, 'CAN_BUY_ZERO' => 'N', 'CODE' => 'f70-fr02-lightgray'],
        ],
    ],
    [
        'CODE' => 'f55-ramka-1-post-alyuminiy',
        'NAME' => 'Рамка 1-постовая FORKEL F55 Алюминий',
        'SECTION' => 'ramki',
        'COLLECTION' => 'f55',
        'MATERIAL' => 'aluminum',
        'NEW' => 'N',
        'ARTICLE' => 'FK-F55-FR01',
        'MECHANISM_TYPE' => 'Рамка установочная 1 пост',
        'PREVIEW_TEXT' => 'Однопостовая рамка серии F55 из шлифованного алюминия.',
        'IMAGE' => '2e5a6406cab54b2416d63e19fc1e6c57_ca3ebba6-7e95-47d9-b108-03cc3c814268.jpg',
        'OFFERS' => [
            ['COLOR' => 'white', 'PRICE' => 690, 'QTY' => 60, 'CAN_BUY_ZERO' => 'N', 'CODE' => 'f55-fr01-white'],
            ['COLOR' => 'gold', 'PRICE' => 890, 'QTY' => 20, 'CAN_BUY_ZERO' => 'N', 'CODE' => 'f55-fr01-gold'],
        ],
    ],
    [
        'CODE' => 'f70-upravlenie-zhalyuzi',
        'NAME' => 'Выключатель управления жалюзи FORKEL F70',
        'SECTION' => 'upravlenie-zhalyuzi',
        'COLLECTION' => 'f70',
        'MATERIAL' => 'aluminum',
        'NEW' => 'Y',
        'ARTICLE' => 'FK-F70-BL01',
        'MECHANISM_TYPE' => 'Механизм управления жалюзи и шторами',
        'PREVIEW_TEXT' => 'Двухклавишный механизм с механической блокировкой одновременного нажатия для электроприводов штор и жалюзи.',
        'IMAGE' => 'a51201760d633fddf1e5fb9c2ee1ce92_1a6c08ec-f420-4d27-b8c9-562e8ca2fa67.jpg',
        'OFFERS' => [
            ['COLOR' => 'black', 'PRICE' => 2190, 'QTY' => 14, 'CAN_BUY_ZERO' => 'N', 'CODE' => 'f70-bl01-black'],
            ['COLOR' => 'white', 'PRICE' => 2190, 'QTY' => 19, 'CAN_BUY_ZERO' => 'N', 'CODE' => 'f70-bl01-white'],
        ],
    ],
    [
        'CODE' => 'f70-termoregulyator-teplogo-pola',
        'NAME' => 'Терморегулятор тёплого пола FORKEL F70',
        'SECTION' => 'termoregulyatory',
        'COLLECTION' => 'f70',
        'MATERIAL' => 'glass',
        'NEW' => 'Y',
        'ARTICLE' => 'FK-F70-TH01',
        'MECHANISM_TYPE' => 'Терморегулятор цифровой 16А с датчиком пола',
        'PREVIEW_TEXT' => 'Программируемый терморегулятор с выносным датчиком температуры и сенсорным экраном. Поставляется под заказ.',
        'IMAGE' => 'Thermostat.jpg',
        // Под заказ: QUANTITY=0, CAN_BUY_ZERO=Y -> AVAILABLE=Y
        'OFFERS' => [
            ['COLOR' => 'black', 'PRICE' => 5490, 'QTY' => 0, 'CAN_BUY_ZERO' => 'Y', 'CODE' => 'f70-th01-black'],
            ['COLOR' => 'white', 'PRICE' => 5490, 'QTY' => 0, 'CAN_BUY_ZERO' => 'Y', 'CODE' => 'f70-th01-white'],
        ],
    ],
    [
        'CODE' => 'f70-dimmer-povorotnyy-led',
        'NAME' => 'Диммер поворотный LED FORKEL F70',
        'SECTION' => 'dimmery',
        'COLLECTION' => 'f70',
        'MATERIAL' => 'aluminum',
        'NEW' => 'N',
        'ARTICLE' => 'FK-F70-DM01',
        'MECHANISM_TYPE' => 'Светорегулятор поворотно-нажимной LED 5-300Вт',
        'PREVIEW_TEXT' => 'Плавная регулировка яркости светодиодных и галогенных ламп с защитой от перегрузки.',
        'IMAGE' => 'ed7610b49bb17b5ec2b457cf2966fc26_a36abffd-3740-4427-add8-19dd7800aa36.jpg',
        'OFFERS' => [
            ['COLOR' => 'black', 'PRICE' => 3890, 'QTY' => 11, 'CAN_BUY_ZERO' => 'N', 'CODE' => 'f70-dm01-black'],
            ['COLOR' => 'gold', 'PRICE' => 4190, 'QTY' => 8, 'CAN_BUY_ZERO' => 'N', 'CODE' => 'f70-dm01-gold'],
        ],
    ],
    [
        'CODE' => 'f55-rozetka-usb-c-pd',
        'NAME' => 'Розетка с USB Type-C PD FORKEL F55',
        'SECTION' => 'rozetki',
        'COLLECTION' => 'f55',
        'MATERIAL' => 'thermoplastic',
        'NEW' => 'Y',
        'ARTICLE' => 'FK-F55-SK02',
        'MECHANISM_TYPE' => 'Розетка 16А + USB Type-C 20Вт',
        'PREVIEW_TEXT' => 'Комбинированная силовая розетка со встроенным модулем быстрой зарядки USB-C. Временно нет в наличии.',
        'IMAGE' => '215850e6cd344fd9f815580cfbd74d4c_da1667d2-ec38-4e74-a2c0-c17b37f25be6.jpg',
        // Нет в наличии: QUANTITY=0, CAN_BUY_ZERO=N -> AVAILABLE=N
        'OFFERS' => [
            ['COLOR' => 'white', 'PRICE' => 2790, 'QTY' => 0, 'CAN_BUY_ZERO' => 'N', 'CODE' => 'f55-sk02-white'],
            ['COLOR' => 'black', 'PRICE' => 2890, 'QTY' => 0, 'CAN_BUY_ZERO' => 'N', 'CODE' => 'f55-sk02-black'],
        ],
    ],
];

$elObj = new CIBlockElement();
$createdProductIds = [];

foreach ($betaProducts as $p) {
    $existing = CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => $catalogId, '=CODE' => $p['CODE'], 'CHECK_PERMISSIONS' => 'N'],
        false,
        false,
        ['ID']
    )->Fetch();

    $props = [
        'COLLECTION' => $colIds[$p['COLLECTION']] ?? false,
        'MATERIAL' => $matEnums[$p['MATERIAL']] ?? false,
        'NEW' => $newEnums[$p['NEW']] ?? false,
        'ARTICLE' => $p['ARTICLE'],
        'MECHANISM_TYPE' => $p['MECHANISM_TYPE'],
    ];

    $fields = [
        'IBLOCK_ID' => $catalogId,
        'IBLOCK_SECTION_ID' => $sectionIds[$p['SECTION']] ?? false,
        'ACTIVE' => 'Y',
        'CODE' => $p['CODE'],
        'NAME' => $p['NAME'],
        'PREVIEW_TEXT' => $p['PREVIEW_TEXT'],
        'DETAIL_TEXT' => $p['PREVIEW_TEXT'],
        'PROPERTY_VALUES' => $props,
    ];

    $imgPath = $imgBase . $p['IMAGE'];
    if (is_file($imgPath) && !$existing) {
        $fields['PREVIEW_PICTURE'] = CFile::MakeFileArray($imgPath);
        $fields['DETAIL_PICTURE'] = CFile::MakeFileArray($imgPath);
    }

    if ($existing) {
        $prodId = (int)$existing['ID'];
        $elObj->Update($prodId, [
            'IBLOCK_SECTION_ID' => $fields['IBLOCK_SECTION_ID'],
            'ACTIVE' => 'Y',
            'NAME' => $fields['NAME'],
            'PREVIEW_TEXT' => $fields['PREVIEW_TEXT'],
            'DETAIL_TEXT' => $fields['DETAIL_TEXT'],
        ]);
        CIBlockElement::SetPropertyValuesEx($prodId, $catalogId, $props);
        echo "[PRODUCT] Updated '{$p['CODE']}' (#$prodId)\n";
    } else {
        $prodId = (int)$elObj->Add($fields);
        if (!$prodId) {
            echo "[ERR] Product '{$p['CODE']}': {$elObj->LAST_ERROR}\n";
            continue;
        }
        echo "[PRODUCT] Created '{$p['CODE']}' (#$prodId)\n";
    }
    $createdProductIds[$p['CODE']] = $prodId;

    // Ensure product catalog record (type SKU = 3)
    CCatalogProduct::Add([
        'ID' => $prodId,
        'TYPE' => \Bitrix\Catalog\ProductTable::TYPE_SKU,
    ]);

    // Offers
    foreach ($p['OFFERS'] as $off) {
        $exOff = CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => $offersId, '=CODE' => $off['CODE'], 'CHECK_PERMISSIONS' => 'N'],
            false,
            false,
            ['ID']
        )->Fetch();

        $offProps = [
            'CML2_LINK' => $prodId,
            'COLOR' => $colorEnums[$off['COLOR']] ?? false,
        ];
        $offName = $p['NAME'] . ' (' . $off['COLOR'] . ')';
        if ($exOff) {
            $offId = (int)$exOff['ID'];
            $elObj->Update($offId, ['ACTIVE' => 'Y', 'NAME' => $offName]);
            CIBlockElement::SetPropertyValuesEx($offId, $offersId, $offProps);
        } else {
            $offFields = [
                'IBLOCK_ID' => $offersId,
                'ACTIVE' => 'Y',
                'CODE' => $off['CODE'],
                'NAME' => $offName,
                'PROPERTY_VALUES' => $offProps,
            ];
            if (is_file($imgPath)) {
                $offFields['PREVIEW_PICTURE'] = CFile::MakeFileArray($imgPath);
                $offFields['DETAIL_PICTURE'] = CFile::MakeFileArray($imgPath);
            }
            $offId = (int)$elObj->Add($offFields);
            if (!$offId) {
                echo "[ERR] Offer '{$off['CODE']}': {$elObj->LAST_ERROR}\n";
                continue;
            }
        }

        $avail = ($off['QTY'] > 0 || $off['CAN_BUY_ZERO'] === 'Y') ? 'Y' : 'N';
        CCatalogProduct::Add([
            'ID' => $offId,
            'QUANTITY' => $off['QTY'],
            'QUANTITY_TRACE' => 'Y',
            'CAN_BUY_ZERO' => $off['CAN_BUY_ZERO'],
            'AVAILABLE' => $avail,
            'TYPE' => \Bitrix\Catalog\ProductTable::TYPE_OFFER,
        ]);
        CCatalogProduct::Update($offId, [
            'QUANTITY' => $off['QTY'],
            'QUANTITY_TRACE' => 'Y',
            'CAN_BUY_ZERO' => $off['CAN_BUY_ZERO'],
            'AVAILABLE' => $avail,
        ]);
        CPrice::SetBasePrice($offId, (float)$off['PRICE'], 'RUB');
    }
}

// Link ACCESSORIES between products
$accLinks = [
    'f70-rozetka-s-zazemleniem' => ['f70-ramka-2-posta-steklo', 'f70-vyklyuchatel-odnoklavishnyy', 'f70-dimmer-povorotnyy-led'],
    'f70-vyklyuchatel-odnoklavishnyy' => ['f70-ramka-2-posta-steklo', 'f70-rozetka-s-zazemleniem'],
    'f55-vyklyuchatel-dvukhklavishnyy' => ['f55-ramka-1-post-alyuminiy', 'f55-rozetka-usb-c-pd'],
];
foreach ($accLinks as $srcCode => $targetCodes) {
    if (!isset($createdProductIds[$srcCode])) {
        continue;
    }
    $ids = [];
    foreach ($targetCodes as $tc) {
        if (isset($createdProductIds[$tc])) {
            $ids[] = $createdProductIds[$tc];
        }
    }
    if ($ids) {
        CIBlockElement::SetPropertyValuesEx($createdProductIds[$srcCode], $catalogId, ['ACCESSORIES' => $ids]);
        echo "[ACCESSORIES] Linked " . count($ids) . " items to '$srcCode'\n";
    }
}

// Also fix existing test products/offers so none are broken with QUANTITY=0 unless intended
$rsTestOff = CIBlockElement::GetList([], ['IBLOCK_ID' => $offersId, 'CODE' => 'tp-test-%', 'CHECK_PERMISSIONS' => 'N'], false, false, ['ID', 'CODE']);
while ($to = $rsTestOff->Fetch()) {
    CCatalogProduct::Update((int)$to['ID'], ['QUANTITY' => 10, 'QUANTITY_TRACE' => 'Y', 'CAN_BUY_ZERO' => 'N', 'AVAILABLE' => 'Y']);
    CPrice::SetBasePrice((int)$to['ID'], 1200.0, 'RUB');
    echo "[FIX] Updated existing test offer {$to['CODE']} (#{$to['ID']}) -> QTY=10, 1200 RUB\n";
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
