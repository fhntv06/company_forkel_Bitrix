<?php
/**
 * Локальный тест обмена CommerceML 1С (FR-011 / INT-005 / TASK-018 Block 3.4).
 * Проверяет загрузку import.xml + offers.xml в инфоблок catalog по XML_ID и повторный прогон без создания дубликатов.
 */
$_SERVER['DOCUMENT_ROOT'] = '/var/www/html';
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;

if (!Loader::includeModule('iblock') || !Loader::includeModule('catalog')) {
    fwrite(STDERR, "Modules iblock/catalog unavailable.\n");
    exit(1);
}

$catalogId = fk_iblock_id('catalog');
$importFile = $argv[1] ?? '/tmp/1c-import.xml';
$offersFile = $argv[2] ?? '/tmp/1c-offers.xml';

if (!is_file($importFile) || !is_file($offersFile)) {
    fwrite(STDERR, "Test XML files not found: $importFile, $offersFile\n");
    exit(1);
}

$runImport = static function (string $importXmlPath, string $offersXmlPath, int $iblockId): array {
    $importXml = simplexml_load_file($importXmlPath);
    $offersXml = simplexml_load_file($offersXmlPath);

    $offersByXmlId = [];
    foreach ($offersXml->ПакетПредложений->Предложения->Предложение as $off) {
        $xmlId = (string)$off->Ид;
        $offersByXmlId[$xmlId] = [
            'PRICE' => (float)$off->Цены->Цена->ЦенаЗаЕдиницу,
            'CURRENCY' => (string)$off->Цены->Цена->Валюта ?: 'RUB',
            'QTY' => (int)$off->Количество,
        ];
    }

    $secRow = CIBlockSection::GetList([], ['IBLOCK_ID' => $iblockId, '=CODE' => 'rozetki', 'CHECK_PERMISSIONS' => 'N'], false, ['ID'])->Fetch();
    $secId = $secRow ? (int)$secRow['ID'] : false;

    $el = new CIBlockElement();
    $stats = ['created' => 0, 'updated' => 0];

    foreach ($importXml->Каталог->Товары->Товар as $item) {
        $xmlId = (string)$item->Ид;
        $name = (string)$item->Наименование;
        $art = (string)$item->Артикул;
        $desc = (string)$item->Описание;
        $code = CUtil::translit($name, 'ru', ['replace_space' => '-', 'replace_other' => '-', 'change_case' => 'L']);
        $code = trim(preg_replace('/-+/', '-', $code), '-');

        $ex = CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => $iblockId, '=XML_ID' => $xmlId, 'CHECK_PERMISSIONS' => 'N'],
            false,
            false,
            ['ID']
        )->Fetch();

        if ($ex) {
            $id = (int)$ex['ID'];
            $el->Update($id, ['NAME' => $name, 'PREVIEW_TEXT' => $desc, 'ACTIVE' => 'Y']);
            CIBlockElement::SetPropertyValuesEx($id, $iblockId, ['ARTICLE' => $art]);
            $stats['updated']++;
        } else {
            $id = (int)$el->Add([
                'IBLOCK_ID' => $iblockId,
                'IBLOCK_SECTION_ID' => $secId,
                'ACTIVE' => 'Y',
                'XML_ID' => $xmlId,
                'CODE' => $code,
                'NAME' => $name,
                'PREVIEW_TEXT' => $desc,
                'PROPERTY_VALUES' => ['ARTICLE' => $art],
            ]);
            if ($id <= 0) {
                fwrite(STDERR, "Add error: {$el->LAST_ERROR}\n");
                continue;
            }
            $stats['created']++;
        }

        if ($id > 0 && isset($offersByXmlId[$xmlId])) {
            $o = $offersByXmlId[$xmlId];
            CCatalogProduct::Add(['ID' => $id, 'QUANTITY' => $o['QTY'], 'QUANTITY_TRACE' => 'Y', 'AVAILABLE' => $o['QTY'] > 0 ? 'Y' : 'N']);
            CCatalogProduct::Update($id, ['QUANTITY' => $o['QTY'], 'QUANTITY_TRACE' => 'Y', 'AVAILABLE' => $o['QTY'] > 0 ? 'Y' : 'N']);
            CPrice::SetBasePrice($id, $o['PRICE'], $o['CURRENCY']);
        }
    }
    return $stats;
};

echo "=== 1C CommerceML Import Test ===\n";
$run1 = $runImport($importFile, $offersFile, $catalogId);
echo "Pass 1: created={$run1['created']}, updated={$run1['updated']}\n";
$run2 = $runImport($importFile, $offersFile, $catalogId);
echo "Pass 2 (idempotency): created={$run2['created']}, updated={$run2['updated']}\n";

$cnt = (int)CIBlockElement::GetList(
    [],
    ['IBLOCK_ID' => $catalogId, '=XML_ID' => '1c-fk-f70-test-socket-001', 'CHECK_PERMISSIONS' => 'N'],
    []
);
echo "Total elements with XML_ID=1c-fk-f70-test-socket-001: $cnt (expected 1)\n";
if ($cnt !== 1 || $run2['created'] !== 0) {
    fwrite(STDERR, "FAIL: duplicate or import failure detected!\n");
    exit(1);
}
echo "=== 1C CommerceML Test PASS ===\n";
