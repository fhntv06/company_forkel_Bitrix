# QA TASK-005 — Этап 2: каталог, коллекция, товар, новинки (статическая приёмка)

- Статус: **FAIL** (один P1 по коду; рендер в Битриксе недоступен, поведение не проверено)
- Ref: ветка `role/bitrix-developer/TASK-005-stage2-catalog`, HEAD `cf550e1` плюс незакоммиченные правки рабочего дерева (проверено именно оно).
- Среда: Windows, Git Bash, `php -l`, `node --check`, grep, чтение ядра `backend/www/bitrix` (только чтение). Сервер и браузер не запускались, рендер страниц не проверялся.

## Критерии
1. Синтаксис: **выполнено**. `php -l` для всех `*.php` в `local`, `catalog`, `collections`, а также `urlrewrite.php`, `.top.menu.php`, `.footer_*.menu.php`, `about/index.php` — ошибок нет. `node --check` для всех `local/templates/forkel/js/*.js` — ошибок нет.
2. `urlrewrite.php`: **выполнено**. Ключи загружены через php: 0, 2, 5, 8, 6, 4, 7, 1, 3 (девять, дублей нет). Присутствуют `#^/catalog/#` -> `/catalog/index.php` (ключ 4) и `#^/collections/#` -> `/collections/index.php` (ключ 8). Порядок: `/personal/order/` стоит раньше `/personal/`, это верно.
3. Корректность вызовов: **выполнено с замечаниями**.
   - Ключи ядра подтверждены чтением `modules/iblock/lib/component/base.php`, `element.php`, `elementlist.php`: `ITEM_START_PRICE` (ставится только при наличии предложений, шаблон имеет запасной вариант `ITEM_PRICES`), `OFFER_ID_SELECTED`, `PRODUCT` (`QUANTITY`, `QUANTITY_TRACE`), `NAV_STRING`, `ITEM_PRICES`, `ITEM_PRICE_SELECTED`, `CAN_BUY`. `QUANTITY_TRACE` в ядре бывает `Y/N/D`; шаблон трактует как учёт только `Y`/`1` (значение `D` = «по умолчанию» считается без учёта), см. QA-005.
   - Параметры `bitrix:catalog`, `catalog.section`, `catalog.element`, `catalog.smart.filter`, `news`, `news.detail` соответствуют штатным; лишние (`CATALOG_URL` у `catalog.section.list`) компонентом игнорируются. `main.feedback` читает `$_POST` напрямую, подмена `$_POST` в feedback.php работает (проверено по `component.php`); шаблон `fk_ajax` существует в `templates/.default`.
   - Баланс `<div>`: catalog.element 11/11, catalog.section 3/3, smart.filter 3/3, news.detail 2/2, catalog_list 5/5, feedback_dialog 10/10; `<ul>` 2/2. Совпадения `<ul>` 1/0 в `menu/fk_top` и `fk_footer` — ложные (подсчёт по строке `<ul` в комментариях; изменённые файлы не затронуты, обёртка `<ul>` в header.php/footer.php). Блоки `if/foreach/return` в шаблонах закрыты (подтверждено `php -l`).
4. Безопасность: **выполнено с замечаниями**.
   - `add2basket.php`: POST + `check_bitrix_sessid()`, `qty` 1..99, `id` int, добавление штатным `Basket::addProduct`. Замечание: QA-001 (устаревший sessid из кэша шаблона).
   - `feedback.php`: sessid, honeypot `website`, режимы `feedback/callback/oneclick` (белый список), для oneclick `strip_tags` + `mb_substr(…, 0, 600)`, обязательное согласие, проверка имени/контакта/сообщения. Замечания QA-003 и QA-004.
   - Вывод: везде `htmlspecialcharsbx`. Неэкранированные места: `$cur['price']` и `$priceText` (отформатированная цена Битрикса, штатное поведение) и `DETAIL_TEXT` (HTML описания из админки, штатно).
   - JS: `pdp.js:32` `price.innerHTML = o.price` (данные из серверно-отформатированной цены, не пользовательский ввод); `pdp.js:113` `innerHTML` со статической строкой. Пользовательские данные идут через `textContent`. XSS не найдено; замечание QA-002 (схема URL видео).
5. Разметка V4: **выполнено**. Классы `fk-product-card*`, `fk-dots`, `fk-variants`, `fk-cat-nav`, `fk-toolbar`, `fk-filters/fk-filter/fk-check/fk-check__chip`, `fk-colhero*`, `fk-results__count`, `fk-grid--catalog`, `fk-empty` есть в `catalog.html/collection.html/new.html/index.html`. Классы PDP (`fk-pdp__*`, `fk-swatches`, `fk-qty`, `fk-chars-groups`, `fk-dl`, `fk-copy-btn`, `fk-modal__box`) в V4 генерирует `shop.js` и описывают `css/product-page.css`, `v4-skin.css`, `download-file.css`; в шаблоне они совпадают, а CSS подключены в header.php. `git diff --stat main -- prototype` пуст, `git status --short prototype` пуст.
6. nginx: **выполнено по чтению конфига**. Правило `location ~ ^/(?:catalog|collections)/[^.]*[^/.]$`: редирект только GET/HEAD, пути с точкой в последнем сегменте (файлы, `.php`, `/local/ajax/*.php`) не затрагиваются; `/catalog/new` -> 301 на `/catalog/new/` -> физическая папка с index.php; `/catalog` без слэша правилом не ловится, его перенаправляет nginx сам (каталог существует). Запрос не проверялся (nginx не запускался). Замечание QA-006.

## Дефекты и замечания

| ID | Приоритет | Где | Описание |
|---|---|---|---|
| QA-001 | P1 | `components/bitrix/catalog.element/fk/template.php:130` (`data-sessid`), `catalog/index.php` (`CACHE_TYPE=A`, `CACHE_TIME=36000000`); читает `js/pdp.js:60` | `bitrix_sessid()` вставлен в HTML шаблона, а вывод компонента кэшируется на 10 часов. Второй и последующие посетители получат sessid первого, `add2basket.php` ответит «Сессия устарела», кнопка «В корзину» не будет работать. Исправление: брать sessid в JS (`BX.bitrix_sessid()`) или из некэшируемого места (footer.php/`<meta>`/inline в header), либо не класть его в кэшируемый шаблон. Проверено по коду, в Битриксе не воспроизводилось. |
| QA-002 | P3 | `js/pdp.js:93-99,121` | `embed()` для нераспознанного URL возвращает его как есть и присваивает `iframe.src`; схема не проверяется (`javascript:`). Источник — свойство VIDEO из админки, риск низкий. Желательно допускать только `https:`/`/`. |
| QA-003 | P3 | `local/ajax/feedback.php` (ветка `GET ?success`, длины полей) | Любой GET с непустым `success` возвращает `ok:true` без проверки хэша; нет верхнего предела длины `name`, `contact`, `msg` в режимах `feedback/callback` и нет ограничения частоты. Режим `oneclick` ограничен 600 символами. |
| QA-004 | P3 | `feedback.php` | Контакт в режиме `callback/oneclick` проверяется на сервере только на непустоту (формат телефона проверяет лишь клиент). |
| QA-005 | P3 | `catalog.element/fk/template.php:55-58` | Остаток показывается только при `QUANTITY_TRACE` = `Y`/`1`; значение `D` (по умолчанию из настроек магазина) трактуется как «без учёта». Нужно сверить с настройкой магазина. |
| QA-006 | P3 | `nginx/default.conf:50` | Элемент каталога с точкой в символьном коде (например `a.b`) редирект на слэш не получит и может дать 404 без слэша; кириллические коды отдаются в `Location` декодированными (`$uri`). Нужна проверка на живом nginx. |
| QA-007 | P3 | `catalog.section.list/fk_catalog_panel/template.php:9` против `fk_chips/template.php:13` | Панель добавляет `/` к `SECTION_PAGE_URL`, чипсы категорий — нет. Если URL раздела в инфоблоке оканчивается на `/`, получится `//`; если нет — у чипсов лишний 301. Привести к одному виду после проверки настроек инфоблока. |

P0 нет. P1: QA-001.

## Не проверено
Рендер всех страниц (каталог, раздел, фильтр, товар, коллекция, новинки, 404), работа smart.filter в режиме `SEF_MODE=N` и сортировки по `SCALED_PRICE_1`, фактическая отправка почты `main.feedback`, добавление в корзину, nginx в запущенном виде, адаптив и доступность в браузере, данные инфоблоков (свойства `COLOR`, `ACCESSORIES`, `NEW`, `COLLECTION` и т. д.).

## Рекомендация
Вернуть Bitrix Developer: исправить QA-001, по возможности QA-002/QA-007; затем повторная приёмка по QA-001 и короткий регресс (php -l, urlrewrite, js check). Рендер в Битриксе обязателен до признания PASS.

---

## Раунд 2 (повторная приёмка, статическая)

Статус раунда 2: **PARTIAL** (исправления подтверждены по коду, открытых P0-P2 нет; рендер в Битриксе не проверялся, поэтому PASS не ставится).

| ID | Результат | Основание |
|---|---|---|
| QA-001 (P1) | fixed (по коду) | `catalog.element/fk/template.php:132` отдаёт `data-sessid-url`, sessid в кэшируемый HTML не попадает. `local/ajax/sessid.php` возвращает JSON `{sessid}` с `Cache-Control: no-store`. `pdp.js:62-68` перед POST запрашивает свежий sessid с `cache: 'no-store'`. Работа в браузере и Битриксе не проверялась. |
| QA-002 (P3) | fixed | `pdp.js:104` `embed()` возвращает URL только для `http(s)://`, иначе пустую строку. Пустую строку при открытии окна `iframe.src` получит как пустой адрес; окно может открыться пустым (мелочь, в браузере не проверялось). |
| QA-005 (P3) | fixed | Опция `catalog / default_quantity_trace` существует в ядре (`modules/catalog/lib/config/catalogsettings.php:36`, `baseform.php:1585`). Шаблон `template.php:49-53` для `D` читает её, учёт остатка при `Y`/`1`. |
| QA-007 (P3) | fixed | `fk_catalog_panel/template.php:8` и `fk_chips/template.php:13` используют `rtrim(url,'/') . '/'`. |
| QA-003, QA-004, QA-006 (P3) | open (не менялись) | см. таблицу раунда 1. |

Регресс: `php -l` по `local`, `catalog`, `collections`, `urlrewrite.php` без ошибок; `node --check` по `js/*.js` без ошибок; ключи `urlrewrite.php` 0,2,5,8,6,4,7,1,3 без дублей, правила `/catalog/` и `/collections/` на месте. Остальное из раздела «Не проверено» остаётся непроверенным.

Рекомендация: PASS возможен после проверки в живом Битриксе (добавление в корзину у второго посетителя при включённом кэше, рендер страниц).
