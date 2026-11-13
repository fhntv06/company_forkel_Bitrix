<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */
/** @var array $arParams */
/** Умный фильтр (V4: .fk-filters). Только поля: <form> и set_filter=Y — в section.php. Цена — диапазон, остальное — чекбоксы. */
$fkColorClass = [
    'Белый' => 'white', 'Чёрный' => 'black', 'Серый' => 'gray',
    'Светло-серый' => 'lightgray', 'Кашемир' => 'cashmere', 'Золотой' => 'gold',
];
$e = static function ($v): string {
    return htmlspecialcharsbx((string)$v);
};
?>
<div class="fk-filters" id="cat-filters" aria-label="Фильтры">
<?php foreach ($arResult['ITEMS'] as $item) {
    if (empty($item['VALUES']) || in_array($item['CODE'] ?? '', (array)($arParams['HIDE_CODES'] ?? []), true)) {
        continue;
    }
    ?>
    <fieldset class="fk-filter">
        <legend><?= $e($item['NAME']) ?></legend>
        <?php if (!empty($item['PRICE'])) {
            $min = $item['VALUES']['MIN'] ?? [];
            $max = $item['VALUES']['MAX'] ?? [];
            ?>
            <div class="fk-co-field">
                <label class="fk-field__label" for="<?= $e($min['CONTROL_ID'] ?? '') ?>">От</label>
                <input class="fk-field" type="number" inputmode="numeric" min="0" id="<?= $e($min['CONTROL_ID'] ?? '') ?>" name="<?= $e($min['CONTROL_NAME'] ?? '') ?>" value="<?= $e($min['HTML_VALUE'] ?? '') ?>" placeholder="<?= $e($min['VALUE'] ?? '') ?>">
            </div>
            <div class="fk-co-field">
                <label class="fk-field__label" for="<?= $e($max['CONTROL_ID'] ?? '') ?>">До</label>
                <input class="fk-field" type="number" inputmode="numeric" min="0" id="<?= $e($max['CONTROL_ID'] ?? '') ?>" name="<?= $e($max['CONTROL_NAME'] ?? '') ?>" value="<?= $e($max['HTML_VALUE'] ?? '') ?>" placeholder="<?= $e($max['VALUE'] ?? '') ?>">
            </div>
        <?php } else {
            foreach ($item['VALUES'] as $val) {
                if (!isset($val['CONTROL_NAME'])) {
                    continue;
                }
                $cls = $fkColorClass[$val['VALUE'] ?? ''] ?? '';
                ?>
                <label class="fk-check"><input type="checkbox" id="<?= $e($val['CONTROL_ID']) ?>" name="<?= $e($val['CONTROL_NAME']) ?>" value="<?= $e($val['HTML_VALUE']) ?>"<?= !empty($val['CHECKED']) ? ' checked' : '' ?><?= !empty($val['DISABLED']) ? ' disabled' : '' ?>><?= $cls ? '<span class="fk-check__chip fin-' . $cls . '"></span>' : '' ?><?= $e($val['VALUE']) ?></label>
            <?php }
        } ?>
    </fieldset>
<?php } ?>
    <button class="fk-btn fk-btn--primary" type="submit">Показать</button>
    <a class="fk-btn fk-btn--secondary" href="<?= $e($arParams['RESET_URL'] ?? '') ?>">Сбросить фильтры</a>
</div>
