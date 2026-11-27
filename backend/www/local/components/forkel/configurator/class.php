<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * FORKEL Visual Configurator Component (TASK-018 Block 4.1).
 * Data & layer mapping are configurable via component params so brand layers can replace Beta layers without logic changes.
 */
class ForkelConfiguratorComponent extends CBitrixComponent
{
    public function executeComponent(): void
    {
        $layerBase = SITE_TEMPLATE_PATH . '/assets/images/configurator/';

        $this->arResult = [
            'COLLECTIONS' => [
                'F70' => ['name' => 'Коллекция F70', 'desc' => 'Архитектурная серия 70×70 мм'],
                'F55' => ['name' => 'Коллекция F55', 'desc' => 'Классический стандарт 55×55 мм'],
            ],
            'TYPES' => [
                'switch' => ['name' => 'Выключатель 1-клавишный', 'price' => 1290],
                'double' => ['name' => 'Выключатель 2-клавишный', 'price' => 1490],
                'socket' => ['name' => 'Розетка с заземлением', 'price' => 1490],
            ],
            'MATERIALS' => [
                'glass' => ['name' => 'Стекло', 'add' => 400],
                'aluminum' => ['name' => 'Алюминий', 'add' => 300],
                'thermoplastic' => ['name' => 'Термопласт', 'add' => 0],
            ],
            'COLORS' => [
                'white_gloss' => [
                    'name' => 'Белый глянцевый',
                    'hex' => '#F8F8F8',
                    'frame' => $layerBase . 'tcvet_ramki_belyy_glyantsevyy.png',
                    'mech' => $layerBase . 'tcvet_mehanizma_belyy_glyantsevyy.png',
                ],
                'white_matte' => [
                    'name' => 'Белый матовый',
                    'hex' => '#EFEFEF',
                    'frame' => $layerBase . 'tcvet_ramki_belyy_matovyy.png',
                    'mech' => $layerBase . 'tcvet_mehanizma_belyy_matovyy.png',
                ],
                'black_matte' => [
                    'name' => 'Чёрный матовый',
                    'hex' => '#1C1E22',
                    'frame' => $layerBase . 'tcvet_ramki_chernyy_matovyy.png',
                    'mech' => $layerBase . 'tcvet_mehanizma_chyernyy_matovyy.png',
                ],
                'cashmere_matte' => [
                    'name' => 'Кашемир матовый',
                    'hex' => '#D8CFC2',
                    'frame' => $layerBase . 'tcvet_ramki_kashemir-matovyy.png',
                    'mech' => $layerBase . 'tcvet_mehanizma_kashemir-matovyy.png',
                ],
                'gray' => [
                    'name' => 'Серый',
                    'hex' => '#8A8F98',
                    'frame' => $layerBase . 'tcvet_ramki_seryy.png',
                    'mech' => $layerBase . 'tcvet_mehanizma_seryy.png',
                ],
                'gold' => [
                    'name' => 'Золотой',
                    'hex' => '#C9A86A',
                    'frame' => $layerBase . 'tcvet_ramki_zolotoy.png',
                    'mech' => $layerBase . 'tcvet_mehanizma_zolotoy.png',
                ],
            ],
            'ADD_KIT_URL' => '/local/ajax/add_kit.php',
            'SESSID_URL' => '/local/ajax/sessid.php',
            'CART_URL' => '/personal/cart/',
        ];

        $this->includeComponentTemplate();
    }
}
