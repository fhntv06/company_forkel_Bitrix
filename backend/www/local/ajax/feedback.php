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

$mode = in_array($_POST['mode'] ?? '', ['callback', 'oneclick', 'lead', 'partner'], true) ? $_POST['mode'] : 'feedback';
$cut = static function ($v, int $len): string {
    return mb_substr(trim(strip_tags((string)$v)), 0, $len);
};
$name = $cut($_POST['name'] ?? '', 100);
$contact = $cut($_POST['contact'] ?? '', 100);
$msg = $cut($_POST['msg'] ?? '', $mode === 'oneclick' ? 600 : 3000); // для oneclick описание товара собирает страница товара

// Дополнительные поля заявок «lead» и «partner»: только из белого списка, подпись → значение.
$extraLabels = [
    'lead' => ['role' => 'Кто вы'],
    'partner' => [
        'direction' => 'Направление', 'company' => 'Компания / специализация', 'city' => 'Город', 'format' => 'Формат работы',
        'volume' => 'Примерный объём', 'time' => 'Удобное время', 'email' => 'Email', 'task' => 'Задача',
    ],
];
$extra = [];
foreach ($extraLabels[$mode] ?? [] as $key => $label) {
    $v = $cut($_POST[$key] ?? '', $key === 'task' ? 3000 : 200);
    if ($v !== '') {
        $extra[$label] = $v;
    }
}

$digits = preg_replace('/\D/', '', $contact);
$isEmail = (bool)filter_var($contact, FILTER_VALIDATE_EMAIL);
$isPhone = !$isEmail && preg_match('/^[+\d][\d\s()\-+]*$/', $contact) && strlen($digits) >= 10 && strlen($digits) <= 15;
$phoneOnly = in_array($mode, ['callback', 'oneclick'], true); // в этих режимах контакт — только телефон

$errors = [];
if (mb_strlen($name) < 2) {
    $errors[] = 'Укажите имя.';
}
if ($phoneOnly ? !$isPhone : !($isEmail || $isPhone)) {
    $errors[] = $phoneOnly ? 'Укажите телефон (10–15 цифр).' : 'Укажите телефон (10–15 цифр) или email.';
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

$tail = '';
foreach ($extra as $label => $v) {
    $tail .= "\n" . $label . ': ' . $v;
}
if ($mode === 'callback') {
    $text = 'Заказ обратного звонка. Телефон: ' . $contact;
} elseif ($mode === 'oneclick') {
    $text = 'Покупка в 1 клик. Телефон: ' . $contact . ($msg !== '' ? "\n" . $msg : '');
} elseif ($mode === 'lead') {
    $text = 'Заявка на сотрудничество (страница «О компании»). Контакт: ' . $contact . $tail . ($msg !== '' ? "\nСообщение: " . $msg : '');
} elseif ($mode === 'partner') {
    $text = 'Анкета партнёра. Имя: ' . $name . '. Контакт: ' . $contact . $tail;
} else {
    $text = ($isEmail ? '' : 'Телефон: ' . $contact . "\n\n") . $msg;
}

// Подставляем данные в формат, который ожидает main.feedback.
$_POST['user_name'] = $name;
$replyEmail = $isEmail ? $contact : '';
if ($replyEmail === '' && $mode === 'partner' && filter_var((string)($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL)) {
    $replyEmail = (string)$_POST['email'];
}
$_POST['user_email'] = $replyEmail;
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
