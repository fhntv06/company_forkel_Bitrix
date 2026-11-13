<?php
/**
 * AJAX-обработчик окон «Написать нам» и «Заказать звонок». Письмо отправляет штатный bitrix:main.feedback
 * (почтовое событие FEEDBACK_FORM), своего хранилища нет. Ответ — JSON {ok, errors[]}.
 */
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
/** @global CMain $APPLICATION */

header('Content-Type: application/json; charset=UTF-8');

$fail = static function (array $errors): void {
    echo \Bitrix\Main\Web\Json::encode(['ok' => false, 'errors' => $errors]);
    die();
};

// После успешной отправки main.feedback сам делает редирект на «?success=<хэш>»; fetch идёт по нему обычным GET — это и есть успех.
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['success'])) {
    echo \Bitrix\Main\Web\Json::encode(['ok' => true, 'errors' => []]);
    die();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $fail(['Неверный запрос.']);
}
if (!check_bitrix_sessid()) {
    $fail(['Сессия устарела. Обновите страницу и попробуйте снова.']);
}
// Ловушка для ботов: поле скрыто от людей. Отвечаем «успехом», письмо не отправляем.
if (trim((string)($_POST['website'] ?? '')) !== '') {
    echo \Bitrix\Main\Web\Json::encode(['ok' => true, 'errors' => []]);
    die();
}

$mode = ($_POST['mode'] ?? '') === 'callback' ? 'callback' : 'feedback';
$name = trim((string)($_POST['name'] ?? ''));
$contact = trim((string)($_POST['contact'] ?? ''));
$msg = trim((string)($_POST['msg'] ?? ''));

$errors = [];
if (mb_strlen($name) < 2) {
    $errors[] = 'Укажите имя.';
}
if ($contact === '') {
    $errors[] = $mode === 'callback' ? 'Укажите телефон.' : 'Укажите телефон или email.';
}
if ($mode === 'feedback' && mb_strlen($msg) < 5) {
    $errors[] = 'Напишите сообщение.';
}
if (($_POST['consent'] ?? '') !== 'Y') {
    $errors[] = 'Нужно согласие на обработку персональных данных.';
}
if ($errors) {
    $fail($errors);
}

$isEmail = strpos($contact, '@') !== false;
if ($mode === 'callback') {
    $text = 'Заказ обратного звонка. Телефон: ' . $contact;
} else {
    $text = ($isEmail ? '' : 'Телефон: ' . $contact . "\n\n") . $msg;
}

// Подставляем данные в формат, который ожидает main.feedback.
$_POST['user_name'] = $name;
$_POST['user_email'] = $isEmail ? $contact : '';
$_POST['MESSAGE'] = $text;
$_POST['submit'] = 'Y';

$APPLICATION->IncludeComponent('bitrix:main.feedback', 'fk_ajax', [
    'USE_CAPTCHA' => 'N',
    'OK_TEXT' => 'Спасибо, сообщение отправлено.',
    'EMAIL_TO' => '',               // пусто = «E-mail по умолчанию» из настроек сайта
    'REQUIRED_FIELDS' => ['NAME', 'MESSAGE'],
    'EVENT_MESSAGE_ID' => [],       // пусто = штатный шаблон события FEEDBACK_FORM
], false);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';
