# QA TASK-008 — Этап 4: шаги 4.4–4.7 (статическая приёмка)

- Статус: **FAIL** (один P2: картинка hero не выводится). Шаги 4.1–4.3 вне объёма.
- Ref: ветка `role/bitrix-developer/TASK-008-stage4-pages`, HEAD `0be797e`, рабочее дерево чистое.
- Среда: Windows, Git Bash, `php -l`, `node --check`, grep, чтение кода и ядра `backend/www/bitrix`. Сервер и браузер не запускались. **Рендер страниц не проверялся**, письма не отправлялись, индексация поиска и данные инфоблоков не проверялись.
- Коммиты автора KArtem (включая любые правки в `prototype/`) дефектом не считаются: «правка владельца, принято».

## Сводка по критериям
1. Синтаксис: **PASS**. `php -l` по всем `*.php` в `about`, `cooperation`, `search`, `local`, `include`: ошибок нет. `node --check` по js шаблона: ошибок нет.
2. Разметка и классы V4: **PASS с замечаниями**. Скриптом сравнены классы `fk-ab-*`, `fk-partner*`, `fk-sr-*`, `fk-coop-*`, `fk-pro__*` из `about.html`, `cooperation.html`, `search.html` с реализацией (страницы, шаблон поиска, include-файлы). Отсутствуют только:
   - `fk-ab-hero*` — генерируется хелпером `fk_page_hero` (forkel_helpers.php), не страницей (не дефект);
   - `fk-ab` (корневой класс) — не используется, обёртка `fk-container fk-page` (осознанно);
   - `fk-sr-tabs` — блок проектов в поиске убран (осознанно, по заданию);
   - «Собрать в подборе» нет (осознанно); карточка с фото в cooperation необязательна (`fk_coop_card_image.php`, пусто = не выводится, осознанно); форма отправляется письмом без демо-заглушек (осознанно).
   - Случайные/неоговорённые: у `/cooperation/` вводный блок остался с inline-стилями (`cooperation/index.php:26`), как в прототипе, P3 (QA-004); `style="position:absolute;left:-9999px"` у honeypot — inline (P3, QA-004).
3. `feedback.php`: **PASS с замечаниями**. Проверено чтением:
   - режимы `feedback/callback/oneclick/lead/partner` — белый список `in_array(..., true)`, остальное = `feedback` (стр. 36);
   - `check_bitrix_sessid` (27), honeypot `website` (31, тихий «успех»), согласие `consent === 'Y'` (75);
   - контакт: телефон 10–15 цифр с разрешёнными символами или email; для `callback/oneclick` только телефон (60–63, 69);
   - длины: `mb_substr(trim(strip_tags()))` — имя/контакт 100, сообщение 600/3000, допполя 200 (`task` 3000) (37–58);
   - допполя только из белого списка `$extraLabels`, подпись фиксирована в коде, произвольные ключи в письмо попасть не могут (45–58); письмо собирается в `$text` и передаётся в `main.feedback` как `MESSAGE`;
   - `user_email` для partner: email из контакта, иначе валидный `$_POST['email']`, иначе пусто (100–104);
   - ветка GET `?success` (19–23): отдаёт `ok:true` любому GET с непустым `success` без проверки. Статус: известный открытый P3 QA-003 прошлых задач, не новый, не регрессия. Эффекта (письма) нет.
   - Замечание QA-005 (P3): значения допполей могут содержать переводы строк (strip_tags их не убирает) — в тексте письма можно «нарисовать» ложные строки вида «Контакт: …». Письмо идёт менеджеру, угрозы нет, но стоит заменить `\s+` на пробел для однострочных полей. Если `$_POST['name']` придёт массивом, `(string)` даст notice «Array to string» — P3, на логику не влияет.
4. `forms.js`: **PASS**.
   - Правила совпадают с серверными: имя ≥2, телефон/email 10–15 цифр с тем же regexp символов, `phone` = только телефон (partner-страница шлёт `data-v="phone"` на `pf-phone`; на сервере для partner допускается и email — сервер мягче клиента, допустимо); `text` ≥5 (клиент) — сервер для lead/partner длину сообщения не требует (мягче). Согласие проверяется на обеих сторонах.
   - `innerHTML` не используется: ошибки через `textContent`/`createElement`. Серверные ошибки выводятся через `String(m)` + `textContent`.
   - id согласованы: `ab-name/contact/consent-err`, `pf-name/phone/task/consent-err` есть в разметке; `partner-direction`, `pf-count`, `pf-counter` существуют; `.fk-co-errors`, `[data-form-done]` есть в обеих формах. Для поля без `-err` (`ab-role`, `ab-msg`) `setErr` безопасен (`er` проверяется). `ab-msg` не имеет `data-v`, не проверяется.
   - При отсутствии элементов: `querySelectorAll` по формам/вкладкам/счётчикам даёт пустой список, `dir`/`out` проверяются на null — без ошибок на других страницах.
   - Замечание QA-006 (P3): после успешной отправки `form.reset()` не сбрасывает счётчик символов `#pf-count`, и не возвращает вкладку направления/`#partner-direction` (value остаётся выбранным, вкладки не сбрасываются) — это согласовано между собой, но счётчик показывает старое число.
5. Поиск: **PASS с замечаниями**. В ядре `search.page/component.php` подтверждены: `$arResult['REQUEST']['~QUERY']` (стр. 268–291, `false` при отсутствии запроса — шаблон приводит к `''`), `SEARCH[]` с `ITEM_ID`/`MODULE_ID` (строки 415–439), `NAV_RESULT` (446), `NAV_STRING` (444). `NavRecordCount` — свойство CDBResult. Параметры: `arrFILTER = ["iblock_catalog"]` → `CSearchParameters::_iblock` берёт `arrFILTER_iblock_catalog` (массив ID инфоблоков) и строит `MODULE_ID=iblock`, `PARAM1=catalog` (подстрока после «iblock_»), `PARAM2=[ID]` — корректно, если тип инфоблока каталога = `catalog` (в `fk_catalog_params` `IBLOCK_TYPE='catalog'`, совпадает). Запрос выводится только через `htmlspecialcharsbx` от `~QUERY` — двойного экранирования нет. Нечисловые `ITEM_ID` (разделы с префиксом `S`) отфильтрованы `ctype_digit`.
   - Замечание QA-007 (P3): порядок релевантности не сохраняется — карточки выводятся `catalog.section` с сортировкой sort/id (`fk_product_html`), т.к. передаётся только фильтр `ID`.
   - Замечание QA-008 (P3): пагинация: `PAGER_TEMPLATE="round"`, шаблона `round` в ядре не найдено (поиск по `find`); при отсутствии шаблона Битрикс использует стандартный. Выбор шаблона пагинации и вывод NAV_STRING в рендере не проверялись. Также в папке шаблона нет `chain_template.php`, который компонент передаёт в `GetNavChain` (стр. 416): в ядре есть проверка `file_exists` (`main.php`), ошибки быть не должно, но не проверялось в рендере.
6. Области включения: **PASS**. Скриптом проверены все уникальные `$fkInclude("…")` в `about/index.php` и `cooperation/index.php`: отсутствующих файлов в `backend/www/include` нет. Дополнительно есть `fk_coop_card_image.php` (читается `file_get_contents`) и `fk_privacy_text.php` (privacy); 24 файла `fk_about/fk_coop/fk_privacy` отслеживаются Git. Отладочного вывода (`var_dump/print_r/console.log`) в страницах, шаблоне поиска и forms.js нет.
7. `prototype/`: **PASS**. `git diff --stat main -- prototype` пуст.

## Дефекты

| ID | Приоритет | Где | Описание |
|---|---|---|---|
| QA-001 | P2 | `backend/www/local/php_interface/forkel_helpers.php:62-63,79-86` | Свойства страницы `fk_hero_img` и `fk_hero_alt` читаются в `$heroImg`/`$heroAlt`, но нигде не используются в возвращаемой разметке: hero выводит картинку только через `fk_hero_art` (область включения). Страницы `/about/`, `/cooperation/`, `/search/` задают `fk_hero_img` (`about/index.php:10`, `cooperation/index.php:9`, `search/index.php:6`) и `fk_hero_alt` (`about/index.php:11`), поэтому картинка hero (`assets/images/about/hero.jpg`) на них **не отобразится**. Воспроизведение: чтение кода `fk_page_hero` (там нет `<img>` с `$heroImg`). Исправление: добавить в `<figure class="fk-ab-hero__art">` `<img src=htmlspecialcharsbx($heroImg) alt=htmlspecialcharsbx($heroAlt)>` при непустом `$heroImg` (приоритет `fk_hero_art` — на усмотрение). Подтверждено статически; рендер не проверялся. Заявлено в задаче как сделанное («hero с картинкой»). |
| QA-004 | P3 | `cooperation/index.php:26`, `about/index.php:68`, `cooperation/index.php:60` | Inline-стили (вводный блок, honeypot) вместо классов. |
| QA-005 | P3 | `local/ajax/feedback.php:54-58` | Допполя могут содержать переводы строк (подделка строк письма); `name` как массив даёт notice. |
| QA-006 | P3 | `local/templates/forkel/js/forms.js:98-99` | `form.reset()` не обновляет счётчик `#pf-count`. |
| QA-007 | P3 | `search/.../fk/template.php:~40` | Потеря порядка по релевантности. |
| QA-008 | P3 | `search/index.php:23` | Шаблон пагинации `round` в ядре не найден; рендер не проверялся. |
| QA-003 (прошлых задач) | P3, открыт | `local/ajax/feedback.php:19-23` | Ветка GET `?success` — не регрессия, статус прежний. |

P0/P1 нет.

## Не проверено
Рендер всех страниц, фактическая отправка писем и шаблон события FEEDBACK_FORM, индексация каталога и реальные результаты поиска, поведение пагинации, вёрстка/адаптив/доступность в браузере, соответствие содержимого include-файлов макету, `/about/privacy/` кроме подключения области (`fk_privacy_text.php` по содержанию не вычитывался), работа вкладок и счётчика в браузере.

## Рекомендация по ретесту
Исправить QA-001 (P2), затем повторная приёмка QA-001 + короткая регрессия (php -l, hero на `/about/`, `/cooperation/`, `/search/`); по возможности — проверка рендера на стенде Docker.


## Исправления после приёмки (2026-11-14)

- QA-001 (P2): страницы `/about/`, `/cooperation/`, `/search/` переведены на общий механизм `fk_hero_art` (области `fk_about_hero_art`, `fk_coop_hero_art`, `fk_search_hero_art`); неиспользуемый код `fk_hero_img`/`fk_hero_alt` удалён из `forkel_helpers.php`. Повторная проверка не проводилась.
- QA-005 (P3): `feedback.php` — значения полей скалярные, переводы строк вне полей «задача» и «сообщение» заменяются пробелом.
- QA-006 (P3): `forms.js` обнуляет счётчик символов после отправки.
- Остаются открытыми: QA-003 (прошлых задач), QA-004, QA-007, QA-008.
