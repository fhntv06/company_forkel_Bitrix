<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arResult */
/** Шаблон без разметки: отдаёт результат main.feedback как JSON для /local/ajax/feedback.php. */
$errors = [];
if (!empty($arResult['ERROR_MESSAGE'])) {
    foreach ((array)$arResult['ERROR_MESSAGE'] as $e) {
        $errors[] = strip_tags((string)$e);
    }
}
$ok = empty($errors) && !empty($arResult['OK_MESSAGE']);
if (!$ok && !$errors) {
    $errors[] = 'Не удалось отправить сообщение. Попробуйте позже.';
}
echo \Bitrix\Main\Web\Json::encode(['ok' => $ok, 'errors' => $errors]);
