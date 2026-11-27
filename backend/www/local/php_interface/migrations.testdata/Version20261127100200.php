<?php

namespace Sprint\Migration;

use Bitrix\Main\Loader;
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
 * TASK-018 блок 1, шаг 6: бета-товары и торговые предложения (ТЕСТОВЫЕ ДАННЫЕ, только локально)
 * Перенесено из backend/scripts/ (TASK-018) без изменения логики. Идемпотентно: повторный запуск безопасен.
 */
class Version20261127100200 extends Version
{
    protected $author = 'FORKEL';

    protected $description = 'TASK-018 блок 1, шаг 6: бета-товары и торговые предложения (ТЕСТОВЫЕ ДАННЫЕ, только локально)';

    protected $moduleVersion = '5.16.1';

    public function up()
    {
global $APPLICATION, $USER, $DB;

if (!Loader::includeModule('iblock') || !Loader::includeModule('catalog')) {
    throw new \RuntimeException('Required Bitrix modules (iblock, catalog) not available.');
}

// Контекст, который раньше строился шагами 1-5 блока 1 (структура уже должна быть применена основными миграциями).
$iblockMap = [];
$rsIb = CIBlock::GetList(['ID' => 'ASC'], ['CHECK_PERMISSIONS' => 'N']);
while ($ib = $rsIb->Fetch()) {
    $iblockMap[$ib['CODE']] = (int)$ib['ID'];
}
$catalogId = $iblockMap['catalog'] ?? 0;
$offersId = $iblockMap['catalog_offers'] ?? 0;
$collectionsId = $iblockMap['collections'] ?? 0;
if (!$catalogId || !$offersId) {
    throw new \RuntimeException('Catalog or offers iblock not found.');
}
$sectionIds = [];
$rsSec = CIBlockSection::GetList([], ['IBLOCK_ID' => $catalogId, 'CHECK_PERMISSIONS' => 'N'], false, ['ID', 'CODE']);
while ($s = $rsSec->Fetch()) {
    $sectionIds[$s['CODE']] = (int)$s['ID'];
}
$propMap = [];
$rsProp = CIBlockProperty::GetList([], ['IBLOCK_ID' => $catalogId]);
while ($p = $rsProp->Fetch()) {
    $propMap[$p['CODE']] = $p;
}
$offerPropMap = [];
$rsOProp = CIBlockProperty::GetList([], ['IBLOCK_ID' => $offersId]);
while ($p = $rsOProp->Fetch()) {
    $offerPropMap[$p['CODE']] = $p;
}
foreach (['MATERIAL', 'NEW', 'COLLECTION', 'ARTICLE', 'MECHANISM_TYPE', 'ACCESSORIES'] as $code) {
    if (!isset($propMap[$code])) {
        throw new \RuntimeException("Свойство $code не найдено: сначала примените основные миграции (cfg).");
    }
}
if (!isset($offerPropMap['COLOR'])) {
    throw new \RuntimeException('Свойство COLOR торговых предложений не найдено.');
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

\Bitrix\Main\Application::getInstance()->getManagedCache()->cleanAll();
echo "=== Beta test data DONE ===\n";
    }

    public function down()
    {
        throw new \RuntimeException('Откат не поддерживается: изменения данных/настроек необратимы. Восстановление - из резервной копии БД.');
    }
}
