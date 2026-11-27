<?php
/**
 * TASK-018 Block 2/3/4 DB Migration via Bitrix API:
 * - Site email (s1)
 * - 4 real stores (b_catalog_store) + deactivate demo store
 * - Deliveries: Courier, Pickup (linked to 4 stores), CDEK + deactivate SPSR
 * - Order property CALL_TIME for PT 1 & 2
 * - PaySystems: YooKassa Card (Sandbox), YooKassa SBP (Sandbox), Cash, Bill (PT 2 only) + deactivate legacy demos
 * - Order statuses: N, PR, P, S, F
 * - User groups: shop_manager, 1c_exchange + user 1c_exchange
 * - 1C CommerceML options (catalog & sale)
 * - 152-FZ UserConsent Agreement ID=1
 * - Wishlist table b_fk_wishlist
 *
 * Idempotent.
 */

$_SERVER['DOCUMENT_ROOT'] = '/var/www/html';
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('BX_NO_ACCELERATOR_RESET', true);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Sale\Delivery\Services\Manager as DeliveryManager;
use Bitrix\Sale\Delivery\Services\Table as DeliveryTable;
use Bitrix\Sale\Delivery\ExtraServices\Manager as ExtraServicesManager;
use Bitrix\Sale\PaySystem\Manager as PaySystemManager;
use Bitrix\Sale\BusinessValue;
use Bitrix\Sale\Internals\ServiceRestrictionTable;

if (!Loader::includeModule('iblock') || !Loader::includeModule('catalog') || !Loader::includeModule('sale')) {
    fwrite(STDERR, "Required Bitrix modules not available.\n");
    exit(1);
}

echo "=== TASK-018 Block 3 (Shop & Integrations) Migration ===\n";

$siteEmail = \Forkel\Env::get('SITE_EMAIL', 'forkel.ru@mail.ru');

// 1. Site email & name
$siteObj = new CSite();
$siteObj->Update('s1', [
    'NAME' => 'FORKEL — интернет-магазин электроустановочных изделий',
    'SITE_NAME' => 'FORKEL',
    'EMAIL' => $siteEmail,
]);
Option::set('main', 'email_from', $siteEmail);
Option::set('sale', 'order_email', $siteEmail);
echo "[SITE] Updated s1 email -> $siteEmail\n";

// 2. Stores (4 real points from TASK-007/014, deactivate demo store)
$rsDemoStore = CCatalogStore::GetList([], ['ID' => 1]);
if ($ds = $rsDemoStore->Fetch()) {
    if (stripos($ds['ADDRESS'], 'Московский') !== false) {
        CCatalogStore::Update(1, ['ACTIVE' => 'N', 'ISSUING_CENTER' => 'N', 'SHIPPING_CENTER' => 'N']);
        echo "[STORE] Deactivated demo store #1 ({$ds['ADDRESS']})\n";
    }
}

$storesSpec = [
    [
        'XML_ID' => 'forkel_expostroy_p1',
        'TITLE' => 'Магазин — ЦДиИ «EXPOSTROY», павильон 1',
        'ADDRESS' => 'Нахимовский пр-т, 24, стр. 5, 1 этаж, место 3',
        'GPS_N' => '55.672512',
        'GPS_S' => '37.584426',
    ],
    [
        'XML_ID' => 'forkel_expostroy_p2',
        'TITLE' => 'Магазин — ЦДиИ «EXPOSTROY», павильон 2',
        'ADDRESS' => 'Нахимовский пр-т, 24, 2 этаж, блок 11B, место 251',
        'GPS_N' => '55.672100',
        'GPS_S' => '37.585100',
    ],
    [
        'XML_ID' => 'forkel_kashirsky_dvor',
        'TITLE' => 'Магазин — ТЦ «Каширский двор»',
        'ADDRESS' => 'Каширское шоссе, 19, корп. 1, этаж 4, помещение 4А7',
        'GPS_N' => '55.662614',
        'GPS_S' => '37.630502',
    ],
    [
        'XML_ID' => 'forkel_gudzon_sklad',
        'TITLE' => 'Склад — ТЦ «Гудзон»',
        'ADDRESS' => 'Каширское шоссе, д. 14, места А51, А52, А79',
        'GPS_N' => '55.664180',
        'GPS_S' => '37.624900',
    ],
];

$realStoreIds = [];
foreach ($storesSpec as $idx => $st) {
    $ex = CCatalogStore::GetList([], ['XML_ID' => $st['XML_ID']])->Fetch();
    $fields = [
        'TITLE' => $st['TITLE'],
        'ACTIVE' => 'Y',
        'ADDRESS' => $st['ADDRESS'],
        'PHONE' => '+7 (903) 154-16-16',
        'SCHEDULE' => '10:00 - 20:00',
        'GPS_N' => $st['GPS_N'],
        'GPS_S' => $st['GPS_S'],
        'XML_ID' => $st['XML_ID'],
        'ISSUING_CENTER' => 'Y',
        'SHIPPING_CENTER' => 'Y',
        'SORT' => ($idx + 1) * 100,
        'SITE_ID' => 's1',
    ];
    if ($ex) {
        CCatalogStore::Update((int)$ex['ID'], $fields);
        $sid = (int)$ex['ID'];
        echo "[STORE] Updated #{$sid}: {$st['TITLE']}\n";
    } else {
        $sid = (int)CCatalogStore::Add($fields);
        echo "[STORE] Created #{$sid}: {$st['TITLE']}\n";
    }
    if ($sid > 0) {
        $realStoreIds[] = $sid;
    }
}

// 3. Deliveries: Courier (ID 1), Pickup (ID 2), CDEK (create), deactivate SPSR (ID 3)
$spsr = DeliveryTable::getList(['filter' => ['=CLASS_NAME' => '\\Sale\\Handlers\\Delivery\\SpsrHandler']])->fetch();
if ($spsr) {
    DeliveryManager::update((int)$spsr['ID'], ['ACTIVE' => 'N']);
    echo "[DELIVERY] Deactivated SPSR (#{$spsr['ID']})\n";
}

if (DeliveryTable::getById(1)->fetch()) {
    DeliveryManager::update(1, [
        'NAME' => 'Курьерская доставка',
        'DESCRIPTION' => 'Доставка курьером по Москве и МО. Бесплатно при заказе от 10 000 ₽ (Beta-тариф).',
        'ACTIVE' => 'Y',
        'SORT' => 100,
        'CONFIG' => [
            'MAIN' => [
                'PRICE' => 500,
                'CURRENCY' => 'RUB',
                'PERIOD' => ['FROM' => 1, 'TO' => 2, 'TYPE' => 'D'],
            ],
        ],
    ]);
    echo "[DELIVERY] Updated Courier (#1) -> 500 RUB\n";
}

if (DeliveryTable::getById(2)->fetch()) {
    DeliveryManager::update(2, [
        'NAME' => 'Самовывоз из магазина или склада',
        'DESCRIPTION' => 'Бесплатный самовывоз из 3 магазинов и склада FORKEL в Москве (10:00 – 20:00).',
        'ACTIVE' => 'Y',
        'SORT' => 200,
        'CONFIG' => [
            'MAIN' => [
                'PRICE' => 0,
                'CURRENCY' => 'RUB',
                'PERIOD' => ['FROM' => 0, 'TO' => 1, 'TYPE' => 'D'],
            ],
        ],
    ]);
    if ($realStoreIds) {
        ExtraServicesManager::saveStores(2, $realStoreIds);
        echo "[DELIVERY] Updated Pickup (#2) + linked " . count($realStoreIds) . " stores\n";
    }
}

$cdek = DeliveryTable::getList(['filter' => ['=CODE' => 'cdek']])->fetch();
$cdekFields = [
    'CODE' => 'cdek',
    'PARENT_ID' => 0,
    'NAME' => 'СДЭК (до пункта выдачи или курьером)',
    'ACTIVE' => 'Y',
    'DESCRIPTION' => 'Доставка СДЭК по России. На этапе Beta действует фиксированный расчёт; API-расчёт ПВЗ подключается через CDEK_CLIENT_ID / CDEK_CLIENT_SECRET.',
    'SORT' => 300,
    'CLASS_NAME' => '\\Bitrix\\Sale\\Delivery\\Services\\Configurable',
    'CURRENCY' => 'RUB',
    'ALLOW_EDIT_SHIPMENT' => 'Y',
    'CONFIG' => [
        'MAIN' => [
            'PRICE' => 450,
            'CURRENCY' => 'RUB',
            'PERIOD' => ['FROM' => 2, 'TO' => 5, 'TYPE' => 'D'],
        ],
    ],
];
if ($cdek) {
    DeliveryManager::update((int)$cdek['ID'], $cdekFields);
    echo "[DELIVERY] Updated CDEK (#{$cdek['ID']})\n";
} else {
    $res = DeliveryManager::add($cdekFields);
    if ($res->isSuccess()) {
        echo "[DELIVERY] Created CDEK (#" . $res->getId() . ")\n";
    } else {
        echo "[ERR] CDEK: " . implode('; ', $res->getErrorMessages()) . "\n";
    }
}

// 4. Order Property CALL_TIME ("Удобное время для звонка") for PT 1 & 2
foreach ([1 => 'Личные данные', 2 => 'Контактная информация'] as $ptId => $groupHint) {
    $exProp = CSaleOrderProps::GetList([], ['PERSON_TYPE_ID' => $ptId, 'CODE' => 'CALL_TIME'])->Fetch();
    if (!$exProp) {
        $pg = CSaleOrderPropsGroup::GetList(['SORT' => 'ASC'], ['PERSON_TYPE_ID' => $ptId])->Fetch();
        $pgId = $pg ? (int)$pg['ID'] : 1;
        $newPid = CSaleOrderProps::Add([
            'PERSON_TYPE_ID' => $ptId,
            'NAME' => 'Удобное время для звонка',
            'TYPE' => 'TEXT',
            'REQUIED' => 'N',
            'DEFAULT_VALUE' => '',
            'SORT' => 160,
            'USER_PROPS' => 'Y',
            'IS_LOCATION' => 'N',
            'PROPS_GROUP_ID' => $pgId,
            'SIZE1' => 40,
            'DESCRIPTION' => 'Например: будни с 10:00 до 18:00',
            'IS_EMAIL' => 'N',
            'IS_PROFILE_NAME' => 'N',
            'IS_PAYER' => 'N',
            'IS_LOCATION4TAX' => 'N',
            'CODE' => 'CALL_TIME',
            'ACTIVE' => 'Y',
            'UTIL' => 'N',
        ]);
        echo "[ORDER_PROP] Created CALL_TIME for PT #$ptId (#$newPid)\n";
    } else {
        echo "[ORDER_PROP] Exists CALL_TIME for PT #$ptId (#{$exProp['ID']})\n";
    }
}

// 5. Pay Systems
$deactivateCodes = ['cashondeliverycalc', 'yandex', 'webmoney', 'sberbank'];
$rsPs = PaySystemManager::getList(['order' => ['ID' => 'ASC']]);
while ($ps = $rsPs->fetch()) {
    if (in_array($ps['ACTION_FILE'], $deactivateCodes, true) && $ps['ACTIVE'] === 'Y') {
        PaySystemManager::update((int)$ps['ID'], ['ACTIVE' => 'N']);
        echo "[PAY] Deactivated legacy #{$ps['ID']} ({$ps['ACTION_FILE']} - {$ps['NAME']})\n";
    }
}

$setPsPersonRestriction = static function (int $psId, array $personTypeIds): void {
    $className = '\\Bitrix\\Sale\\Services\\PaySystem\\Restrictions\\PersonType';
    $ex = ServiceRestrictionTable::getList([
        'filter' => [
            '=SERVICE_ID' => $psId,
            '=SERVICE_TYPE' => \Bitrix\Sale\Services\PaySystem\Restrictions\Manager::SERVICE_TYPE_PAYMENT,
            '=CLASS_NAME' => $className,
        ],
    ])->fetch();
    $fields = [
        'SERVICE_ID' => $psId,
        'SERVICE_TYPE' => \Bitrix\Sale\Services\PaySystem\Restrictions\Manager::SERVICE_TYPE_PAYMENT,
        'CLASS_NAME' => $className,
        'SORT' => 100,
        'PARAMS' => ['PERSON_TYPE_ID' => $personTypeIds],
    ];
    if ($ex) {
        ServiceRestrictionTable::update((int)$ex['ID'], ['PARAMS' => ['PERSON_TYPE_ID' => $personTypeIds]]);
    } else {
        ServiceRestrictionTable::add($fields);
    }
};

PaySystemManager::update(1, [
    'NAME' => 'Оплата при получении (наличные / карта курьеру)',
    'PSA_NAME' => 'Оплата при получении',
    'DESCRIPTION' => 'Оплата наличными или банковской картой при получении заказа у курьера или в магазине.',
    'ACTIVE' => 'Y',
    'SORT' => 300,
]);
$setPsPersonRestriction(1, [1, 2]);
echo "[PAY] Updated Cash (#1)\n";

PaySystemManager::update(8, [
    'NAME' => 'Безналичный счёт для юридических лиц',
    'PSA_NAME' => 'Счёт на оплату',
    'DESCRIPTION' => 'Выставление счёта на оплату по реквизитам организации.',
    'ACTIVE' => 'Y',
    'SORT' => 400,
]);
$setPsPersonRestriction(8, [2]);
echo "[PAY] Updated Bill (#8) -> PT 2 only\n";

$ykShopId = \Forkel\Env::get('YOOKASSA_SHOP_ID', 'sandbox_shop_id');
$ykSecret = \Forkel\Env::get('YOOKASSA_SECRET_KEY', 'sandbox_secret_key');

$ykSpecs = [
    [
        'CODE' => 'forkel_yookassa_card',
        'PS_MODE' => 'bank_card',
        'NAME' => 'Онлайн-оплата банковской картой (Мир, Visa, Mastercard)',
        'DESCRIPTION' => 'Безопасная онлайн-оплата картой через шлюз ЮKassa / Альфа-Банк с чеком 54-ФЗ (Beta Sandbox).',
        'SORT' => 100,
    ],
    [
        'CODE' => 'forkel_yookassa_sbp',
        'PS_MODE' => 'sbp',
        'NAME' => 'Оплата через СБП (Система быстрых платежей)',
        'DESCRIPTION' => 'Мгновенная оплата по QR-коду через СБП с чеком 54-ФЗ (Beta Sandbox).',
        'SORT' => 200,
    ],
];

foreach ($ykSpecs as $spec) {
    $found = PaySystemManager::getList(['filter' => ['=CODE' => $spec['CODE']]])->fetch();
    $psFields = [
        'NAME' => $spec['NAME'],
        'PSA_NAME' => $spec['NAME'],
        'CODE' => $spec['CODE'],
        'ACTION_FILE' => 'yandexcheckout',
        'PS_MODE' => $spec['PS_MODE'],
        'ACTIVE' => 'Y',
        'SORT' => $spec['SORT'],
        'DESCRIPTION' => $spec['DESCRIPTION'],
        'NEW_WINDOW' => 'N',
        'HAVE_PAYMENT' => 'Y',
        'IS_CASH' => 'N',
        'CAN_PRINT_CHECK' => 'Y',
        'ENTITY_REGISTRY_TYPE' => \Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER,
    ];
    if ($found) {
        $psId = (int)$found['ID'];
        PaySystemManager::update($psId, $psFields);
        echo "[PAY] Updated {$spec['CODE']} (#$psId)\n";
    } else {
        $res = PaySystemManager::add($psFields);
        if (!$res->isSuccess()) {
            echo "[ERR] PaySystem {$spec['CODE']}: " . implode('; ', $res->getErrorMessages()) . "\n";
            continue;
        }
        $psId = (int)$res->getId();
        echo "[PAY] Created {$spec['CODE']} (#$psId)\n";
    }

    $consumer = 'PAYSYSTEM_' . $psId;
    BusinessValue::setMapping('YANDEX_CHECKOUT_SHOP_ID', $consumer, null, ['PROVIDER_KEY' => 'VALUE', 'PROVIDER_VALUE' => $ykShopId]);
    BusinessValue::setMapping('YANDEX_CHECKOUT_SECRET_KEY', $consumer, null, ['PROVIDER_KEY' => 'VALUE', 'PROVIDER_VALUE' => $ykSecret]);
    BusinessValue::setMapping('YANDEX_CHECKOUT_RETURN_URL', $consumer, null, ['PROVIDER_KEY' => 'VALUE', 'PROVIDER_VALUE' => '/personal/order/payment/?result=success']);
    BusinessValue::setMapping('YANDEX_CHECKOUT_CHANGE_STATUS_PAY', $consumer, null, ['PROVIDER_KEY' => 'VALUE', 'PROVIDER_VALUE' => 'Y']);
    $setPsPersonRestriction($psId, [1, 2]);
}

// 6. Order Statuses (N, PR, P, S, F)
$statusesSpec = [
    'N' => ['SORT' => 100, 'NAME' => 'Новый', 'DESCRIPTION' => 'Заказ принят, ожидает обработки или оплаты'],
    'PR' => ['SORT' => 150, 'NAME' => 'В обработке', 'DESCRIPTION' => 'Заказ проверяется и комплектуется менеджером'],
    'P' => ['SORT' => 200, 'NAME' => 'Оплачен', 'DESCRIPTION' => 'Оплата подтверждена, заказ готовится к отправке'],
    'S' => ['SORT' => 250, 'NAME' => 'Отправлен', 'DESCRIPTION' => 'Заказ передан в службу доставки или готов к самовывозу'],
    'F' => ['SORT' => 300, 'NAME' => 'Выполнен', 'DESCRIPTION' => 'Заказ доставлен и получен покупателем'],
];
foreach ($statusesSpec as $stId => $st) {
    $exists = CSaleStatus::GetByID($stId);
    $fields = [
        'ID' => $stId,
        'SORT' => $st['SORT'],
        'NOTIFY' => 'Y',
        'LANG' => [
            ['LID' => 'ru', 'NAME' => $st['NAME'], 'DESCRIPTION' => $st['DESCRIPTION']],
            ['LID' => 'en', 'NAME' => $st['NAME'], 'DESCRIPTION' => $st['DESCRIPTION']],
        ],
    ];
    if ($exists) {
        CSaleStatus::Update($stId, $fields);
        echo "[STATUS] Updated '$stId' ({$st['NAME']})\n";
    } else {
        CSaleStatus::Add($fields);
        echo "[STATUS] Created '$stId' ({$st['NAME']})\n";
    }
}

// 7. Groups: shop_manager & 1c_exchange + user 1c_exchange
$ensureGroup = static function (string $code, string $name, string $desc): int {
    $rs = CGroup::GetList('id', 'asc', ['STRING_ID' => $code]);
    if ($g = $rs->Fetch()) {
        return (int)$g['ID'];
    }
    $grp = new CGroup();
    $gid = (int)$grp->Add([
        'ACTIVE' => 'Y',
        'C_SORT' => 200,
        'NAME' => $name,
        'DESCRIPTION' => $desc,
        'STRING_ID' => $code,
    ]);
    echo "[GROUP] Created '$code' (#$gid)\n";
    return $gid;
};

$mgrGroupId = $ensureGroup('shop_manager', 'Менеджер магазина', 'Управление заказами, каталогом и контентом FORKEL без доступа к настройкам ядра');
$c1GroupId = $ensureGroup('1c_exchange', 'Обмен с 1С', 'Техническая группа для обмена CommerceML с 1С');

global $APPLICATION;
if ($mgrGroupId > 0) {
    $APPLICATION->SetGroupRight('sale', $mgrGroupId, 'U');
    $APPLICATION->SetGroupRight('catalog', $mgrGroupId, 'W');
    $APPLICATION->SetGroupRight('iblock', $mgrGroupId, 'W');
    echo "[GROUP] Set rights for shop_manager (#$mgrGroupId)\n";
}
if ($c1GroupId > 0) {
    $APPLICATION->SetGroupRight('sale', $c1GroupId, 'W');
    $APPLICATION->SetGroupRight('catalog', $c1GroupId, 'W');
    $APPLICATION->SetGroupRight('iblock', $c1GroupId, 'W');
    echo "[GROUP] Set rights for 1c_exchange (#$c1GroupId)\n";
}

$rsU = CUser::GetByLogin('1c_exchange');
if (!$rsU->Fetch()) {
    $u = new CUser();
    $pass = 'Fk1cExch!' . substr(md5(__DIR__ . 'forkel'), 0, 8);
    $uid = (int)$u->Add([
        'NAME' => 'Обмен',
        'LAST_NAME' => '1С',
        'EMAIL' => '1c@forkel.ru',
        'LOGIN' => '1c_exchange',
        'LID' => 's1',
        'ACTIVE' => 'Y',
        'GROUP_ID' => [$c1GroupId],
        'PASSWORD' => $pass,
        'CONFIRM_PASSWORD' => $pass,
    ]);
    echo "[USER] Created technical user '1c_exchange' (#$uid)\n";
} else {
    echo "[USER] Exists '1c_exchange'\n";
}

// 8. 1C CommerceML options
Option::set('catalog', '1C_IBLOCK_TYPE', 'catalog');
Option::set('catalog', '1C_SITE_LIST', 's1');
Option::set('catalog', '1C_GROUP_PERMISSIONS', '1,' . $c1GroupId);
Option::set('catalog', '1C_USE_OFFERS', 'Y');
Option::set('catalog', '1C_ELEMENT_ACTION', 'N');
Option::set('catalog', '1C_SECTION_ACTION', 'N');
Option::set('catalog', '1C_USE_ZIP', 'Y');
Option::set('catalog', '1C_USE_CRC', 'Y');
Option::set('catalog', '1C_TRANSLIT_ON_ADD', 'Y');
Option::set('catalog', '1C_TRANSLIT_ON_UPDATE', 'N');
Option::set('catalog', '1C_INTERVAL', '30');
Option::set('sale', '1C_SALE_GROUP_PERMISSIONS', '1,' . $c1GroupId);
Option::set('sale', '1C_SITE_LIST', 's1');
echo "[1C] Configured catalog & sale 1C_* CommerceML options\n";

// 9. UserConsent Agreement ID=1 (152-FZ)
$conn = \Bitrix\Main\Application::getConnection();
if ($conn->isTableExists('b_consent_agreement')) {
    $ag = $conn->query("SELECT ID, ACTIVE, NAME FROM b_consent_agreement WHERE ID = 1")->fetch();
    if ($ag) {
        $conn->queryExecute(
            "UPDATE b_consent_agreement SET ACTIVE = 'Y', NAME = 'Согласие на обработку персональных данных FORKEL' WHERE ID = 1"
        );
        echo "[152-FZ] Verified & updated b_consent_agreement #1\n";
    }
}

// 10. Wishlist table
\Forkel\Wishlist::ensureTable();
echo "[WISHLIST] Verified table b_fk_wishlist\n";

\Bitrix\Main\Application::getInstance()->getManagedCache()->cleanAll();
echo "[CACHE] Managed cache cleared.\n";
echo "=== Block 3 DONE ===\n";
