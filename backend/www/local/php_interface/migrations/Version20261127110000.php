<?php

namespace Sprint\Migration;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Sale\Delivery\Services\Manager as DeliveryManager;
use Bitrix\Sale\Delivery\Services\Table as DeliveryTable;
use Bitrix\Sale\Internals\ServiceRestrictionTable;
use Bitrix\Sale\PaySystem\Manager as PaySystemManager;

/**
 * TASK-019 (DEC-010, DEC-013): «Отправка ТК» вместо «СДЭК» и оплата картой / СБП через Альфа-Банк.
 *
 * - Служба доставки XML_ID=forkel_cdek переименована в «Отправка транспортной компанией (ТК)» (CODE/XML_ID и тариф не меняются).
 * - Платёжные системы «Карта» и «СБП» переведены с yandexcheckout на обработчик forkelalfabank
 *   (local/php_interface/include/sale_payment/forkelalfabank), коды forkel_alfabank_card / forkel_alfabank_sbp.
 *   Доступы банка в БД не пишутся — только окружение (ALFABANK_*).
 * - Профили yandexcheckout деактивированы, их плейсхолдеры ключей в бизнес-смыслах удалены.
 * - Статус заказа при полной оплате — «Оплачен» (P).
 * Идемпотентно: записи ищутся по XML_ID / CODE, повторный запуск ничего не дублирует.
 */
class Version20261127110000 extends Version
{
    protected $author = 'FORKEL';

    protected $description = 'TASK-019: доставка «Отправка ТК», оплата картой и СБП через Альфа-Банк (вместо yandexcheckout)';

    protected $moduleVersion = '5.16.1';

    private const DELIVERY_XML_ID = 'forkel_cdek';

    private const PAY_SYSTEMS = [
        [
            'CODE' => 'forkel_alfabank_card',
            'OLD_CODE' => 'forkel_yookassa_card',
            'PS_MODE' => 'bank_card',
            'NAME' => 'Онлайн-оплата банковской картой (Мир, Visa, Mastercard)',
            'DESCRIPTION' => 'Безопасная оплата картой на платёжной странице Альфа-Банка.',
            'SORT' => 100,
            'OLD_NAME' => 'Онлайн-оплата банковской картой (Мир, Visa, Mastercard)',
            'OLD_DESCRIPTION' => 'Безопасная онлайн-оплата картой через шлюз ЮKassa / Альфа-Банк с чеком 54-ФЗ (Beta Sandbox).',
        ],
        [
            'CODE' => 'forkel_alfabank_sbp',
            'OLD_CODE' => 'forkel_yookassa_sbp',
            'PS_MODE' => 'sbp',
            'NAME' => 'Оплата через СБП (Система быстрых платежей)',
            'DESCRIPTION' => 'Оплата по QR-коду или в приложении своего банка через платёжную страницу Альфа-Банка.',
            'SORT' => 200,
            'OLD_NAME' => 'Оплата через СБП (Система быстрых платежей)',
            'OLD_DESCRIPTION' => 'Мгновенная оплата по QR-коду через СБП с чеком 54-ФЗ (Beta Sandbox).',
        ],
    ];

    public function up()
    {
        $this->requireModules();

        // 1. Доставка: «Отправка ТК» (DEC-010)
        $delivery = $this->findDelivery();
        if (!$delivery) {
            throw new \RuntimeException('Служба доставки XML_ID=' . self::DELIVERY_XML_ID . ' не найдена: сначала примените Version20261127100100.');
        }
        $res = DeliveryManager::update((int)$delivery['ID'], [
            'NAME' => 'Отправка транспортной компанией (ТК)',
            'DESCRIPTION' => 'Отправка транспортными компаниями по России (СДЭК, Деловые Линии, ПЭК и др.). '
                . 'Транспортную компанию и детали отправки менеджер согласует с вами после оформления заказа.',
        ]);
        $this->assertSuccess($res, 'Delivery #' . $delivery['ID']);
        echo "[DELIVERY] #{$delivery['ID']} -> «Отправка транспортной компанией (ТК)»\n";

        // 2. Карта и СБП -> Альфа-Банк (DEC-013)
        $alfaIds = [];
        foreach (self::PAY_SYSTEMS as $spec) {
            $found = $this->findPaySystem([$spec['CODE'], $spec['OLD_CODE']]);
            $fields = [
                'NAME' => $spec['NAME'],
                'PSA_NAME' => $spec['NAME'],
                'CODE' => $spec['CODE'],
                'ACTION_FILE' => 'forkelalfabank',
                'PS_MODE' => $spec['PS_MODE'],
                'ACTIVE' => 'Y',
                'DESCRIPTION' => $spec['DESCRIPTION'],
                'NEW_WINDOW' => 'N',
                'HAVE_PAYMENT' => 'Y',
                'IS_CASH' => 'N',
            ];
            if ($found) {
                $psId = (int)$found['ID'];
                $this->assertSuccess(PaySystemManager::update($psId, $fields), 'PaySystem #' . $psId);
                echo "[PAY] #$psId {$spec['CODE']} -> forkelalfabank ({$spec['PS_MODE']})\n";
            } else {
                $res = PaySystemManager::add($fields + [
                    'SORT' => $spec['SORT'],
                    'ENTITY_REGISTRY_TYPE' => \Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER,
                ]);
                $this->assertSuccess($res, 'PaySystem ' . $spec['CODE']);
                $psId = (int)$res->getId();
                $this->setPersonTypeRestriction($psId, [1, 2]);
                echo "[PAY] Created #$psId {$spec['CODE']}\n";
            }
            $alfaIds[] = $psId;
        }

        // 3. Оставшиеся профили yandexcheckout — выключить
        $rs = PaySystemManager::getList(['filter' => ['=ACTION_FILE' => 'yandexcheckout', '=ACTIVE' => 'Y']]);
        while ($ps = $rs->fetch()) {
            PaySystemManager::update((int)$ps['ID'], ['ACTIVE' => 'N']);
            echo "[PAY] Deactivated yandexcheckout #{$ps['ID']}\n";
        }

        // 4. Плейсхолдеры ключей ЮKassa в бизнес-смыслах этих платёжных систем больше не нужны
        $conn = Application::getConnection();
        foreach ($alfaIds as $psId) {
            $conn->queryExecute(
                "DELETE FROM b_sale_bizval WHERE CONSUMER_KEY = 'PAYSYSTEM_" . (int)$psId . "' AND CODE_KEY LIKE 'YANDEX\\_CHECKOUT\\_%'"
            );
        }
        echo "[PAY] Removed YANDEX_CHECKOUT_* business values for #" . implode(', #', $alfaIds) . "\n";

        // 5. После полной оплаты заказ автоматически переходит в статус «Оплачен»
        Option::set('sale', 'status_on_paid', 'P');
        echo "[SALE] status_on_paid = P\n";

        Application::getInstance()->getManagedCache()->cleanAll();
        echo "=== TASK-019 DONE ===\n";
    }

    public function down()
    {
        $this->requireModules();

        $delivery = $this->findDelivery();
        if ($delivery) {
            DeliveryManager::update((int)$delivery['ID'], [
                'NAME' => 'СДЭК (до пункта выдачи или курьером)',
                'DESCRIPTION' => 'Доставка СДЭК по России. На этапе Beta действует фиксированный расчёт; API-расчёт ПВЗ подключается через CDEK_CLIENT_ID / CDEK_CLIENT_SECRET.',
            ]);
            echo "[DELIVERY] #{$delivery['ID']} -> «СДЭК»\n";
        }

        foreach (self::PAY_SYSTEMS as $spec) {
            $found = $this->findPaySystem([$spec['CODE']]);
            if (!$found) {
                continue;
            }
            PaySystemManager::update((int)$found['ID'], [
                'NAME' => $spec['OLD_NAME'],
                'PSA_NAME' => $spec['OLD_NAME'],
                'CODE' => $spec['OLD_CODE'],
                'ACTION_FILE' => 'yandexcheckout',
                'DESCRIPTION' => $spec['OLD_DESCRIPTION'],
            ]);
            echo "[PAY] #{$found['ID']} -> yandexcheckout (ключи ЮKassa нужно задать в админке)\n";
        }

        Application::getInstance()->getManagedCache()->cleanAll();
    }

    private function requireModules(): void
    {
        if (!Loader::includeModule('sale')) {
            throw new \RuntimeException('Модуль sale недоступен.');
        }
    }

    private function findDelivery(): ?array
    {
        $row = DeliveryTable::getList([
            'filter' => ['=XML_ID' => self::DELIVERY_XML_ID],
            'order' => ['ID' => 'ASC'],
        ])->fetch();
        return $row ?: null;
    }

    private function findPaySystem(array $codes): ?array
    {
        foreach ($codes as $code) {
            $row = PaySystemManager::getList(['filter' => ['=CODE' => $code], 'order' => ['ID' => 'ASC']])->fetch();
            if ($row) {
                return $row;
            }
        }
        return null;
    }

    private function setPersonTypeRestriction(int $psId, array $personTypeIds): void
    {
        ServiceRestrictionTable::add([
            'SERVICE_ID' => $psId,
            'SERVICE_TYPE' => \Bitrix\Sale\Services\PaySystem\Restrictions\Manager::SERVICE_TYPE_PAYMENT,
            'CLASS_NAME' => '\\Bitrix\\Sale\\Services\\PaySystem\\Restrictions\\PersonType',
            'SORT' => 100,
            'PARAMS' => ['PERSON_TYPE_ID' => $personTypeIds],
        ]);
    }

    private function assertSuccess($result, string $what): void
    {
        if (!$result->isSuccess()) {
            throw new \RuntimeException($what . ': ' . implode('; ', $result->getErrorMessages()));
        }
    }
}
