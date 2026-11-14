# QA TASK-007 — Этап 3: главная страница по секциям, шаги 3.1–3.13 (статическая приёмка)

- Статус: **FAIL** (один P2: изменён замороженный прототип; прочее без блокеров). Рендер в Битриксе не проверялся.
- Ref: ветка `role/bitrix-developer/TASK-007-stage3-home`, HEAD `24c5720` (рабочее дерево чистое).
- Среда: Windows, Git Bash, `php -l`, `node --check`, grep, чтение кода. Сервер и браузер не запускались, **рендер страницы не проверялся**, данные инфоблоков не проверялись. Шаг 3.14 (SEO) вне объёма.

## Сводка по критериям
1. Синтаксис: **выполнено**. `php -l` по `backend/www/index.php`, `backend/www/local/**/*.php`, `backend/www/include/*.php`: ошибок нет. `node --check` по `local/**/*.js` (catalog, feedback, pdp, site, social, solutions): ошибок нет.
2. Разметка и классы V4: **выполнено с замечаниями**. Совпадают: hero, dirs, cols, feat, mini, `fk-grid--products`, `fk-grid--projects`, `fk-sol`/`fk-hs`/`fk-sol-dots`, `fk-grid--interiors`, show, nums, pro, del, fin. Расхождения (все осознанные, описаны в комментариях index.php/шаблонов, кроме п. «г»):
   - а) нет секции «Подбор» (по заданию);
   - б) кнопки hero/CTA/шоурума: «Записаться на визит», «Связаться с нами» — `button[data-feedback]`, hero-кнопки ведут на `/catalog/`, `/catalog/new/` (заменено осознанно);
   - в) карточки интерьеров — `div.fk-interior` вместо `a href="#"` (решение владельца, в комментарии);
   - г) в прототипе слайды/точки используют `data-ids`, здесь связь через `aria-controls` + `ul.fk-hs-pop[hidden]` (осознанно, в комментарии solutions.js); у `.fk-sol__slide` добавлены inline `--img/--cx`; у секций проектов/слайдера/интерьеров inline `style="margin-top:24px"` (повторяет прототип по виду, но это inline-стиль вместо класса — P3, QA-003);
   - д) `fk-grid--catalog` на главной убран через `FK_PLAIN_GRID=Y` (осознанно, в helper и шаблоне);
   - е) breadcrumb `fk_crumbs`: крошки скрыты при цепочке < 2 пунктов (нужно главной; на остальных страницах без изменений, пока в цепочке ≥ 2 пунктов).
3. Данные: **выполнено с замечаниями**.
   - Все шаблоны news.list используют `$arResult['ITEMS']`, `NAME`, `PREVIEW_TEXT`, `DETAIL_PAGE_URL`; картинки читаются через `$item['PREVIEW_PICTURE']['SRC'] ?? $item['DETAIL_PICTURE']['SRC'] ?? ''` — безопасно для пустого поля (не массив).
   - `fk_home_dirs`: `$section['PICTURE'] ?? null` + `empty($pic['SRC'])` — безопасно; `SECTION_FIELDS=PICTURE` передан.
   - `fk_home_projects`: `PROPERTY_CODE` = ROOM, COLLECTION, читается `DISPLAY_PROPERTIES[...]['DISPLAY_VALUE']`, массив/HTML обрабатывается (`implode`, `strip_tags`). Верно.
   - `fk_home_solutions`: `PROPERTY_CODE` = HOTSPOTS, COORDS_POINT; `COORDS_POINT` читается один раз, парсится regexp, одна точка на слайд; при отсутствии координат или товаров `$button=$pop=''`, цикл не прерывается (`continue`/`break` нет), слайд выводится без точки. Верно. Замечание: QA-001.
   - `fk_mini`: `ITEM_START_PRICE` с запасным `ITEM_PRICES[ITEM_PRICE_SELECTED]`, `PRINT_RATIO_PRICE`/`PRINT_PRICE`; изображение из `OFFERS` при отсутствии своего. Серия берётся из `DISPLAY_PROPERTIES.COLLECTION`, вывод только если свойство входит в `LIST_PROPERTY_CODE` каталога (не проверялось на данных).
   - `fk_new_filter`: `CIBlockPropertyEnum::GetList([], ['IBLOCK_ID'=>fk_iblock_id('catalog'),'CODE'=>'NEW'])`, поиск по XML_ID `y/yes` или тексту `y/да`, фильтр `=PROPERTY_NEW` по ID значения, при отсутствии `[-1]` (пусто, а не весь каталог). Инфоблок/модуль iblock загружаются. Корректно; XML_ID/значение флажка в реальном каталоге не проверялись.
4. Секции без данных и области включения: **выполнено**. Секции dirs, collections, features, new, projects, solutions, interiors, pro обёрнуты в `fkCapture` + `if ($x !== "")`. Секции hero, showroom, trust, delivery, final — всегда (контент из областей). Все 23 уникальных имени `$fkInclude("...")` из index.php имеют файл `backend/www/include/<имя>.php` (скриптом проверено, отсутствующих нет); файлы отслеживаются Git. Вызов — `bitrix:main.include`, `AREA_FILE_SHOW=file`, `PATH=/include/<имя>.php`. Замечание QA-002.
5. Безопасность: **выполнено**. Во всех шаблонах значения инфоблоков идут через `htmlspecialcharsbx`. Неэкранированы только `$priceText` в `fk_mini` (форматированная цена Битрикса, штатно, как в `fk`), `$products`/`$pop` (HTML собственных шаблонов), `$x`/`$y` (float-приведение) и `$cx`/`$ratio` (числа). Идентификаторы `$popId` — `(int)`. `var_dump/print_r/fk_debug` в index.php и шаблонах `components/` не найдены (grep).
6. `js/solutions.js`: **выполнено**. `innerHTML` не используется; точки связаны через `aria-controls` (`getElementById`, при отсутствии блока `return`); при пустом слайдере ранний выход (`!root`, `!slides.length`). Замечание QA-004 (P3).
7. `prototype/` не изменён: **НЕ выполнено**. `git diff --stat main -- prototype` непуст: `prototype/homepage-v4/styles.css | 4 ++--` (коммит `24c5720` «sync styles front and back»: `.mini` переведён с grid на flex, убран `@media` с `grid-template-columns: repeat(4,1fr)`). Если V4 не заявлена как разрешённая к правке в задаче — нарушение правила «prior versions frozen» (QA-005).

## Дефекты и замечания

| ID | Приоритет | Где | Описание |
|---|---|---|---|
| QA-005 | P2 | `prototype/homepage-v4/styles.css:155,158` (коммит `24c5720`) | Изменён замороженный прототип. Нужно либо подтвердить владельцем, что V4 в этой задаче правится (и зафиксировать в задаче/decisions), либо откатить файл и держать правку только в `local/templates/forkel/styles.css` (там она уже есть, 4 строки). |
| QA-001 | P3 | `components/bitrix/news.list/fk_home_solutions/template.php:17-18` | `$picData = DETAIL_PICTURE ?: PREVIEW_PICTURE; $pic = $picData['SRC'];` без проверки. Если у слайда нет обеих картинок (поле PREVIEW обязательно по комментарию, но это настройка инфоблока), обращение `['SRC']` к не-массиву даст предупреждение (null) или TypeError (пустая строка в PHP 8). Желательно `$pic = $picData['SRC'] ?? ''` и пропуск слайда без фото. Также `preg_match(..., $coords)` на строке 25 упадёт, если `COORDS_POINT` вдруг множественное (значение-массив): привести `(string)` / взять первый элемент. Диапазон координат 0–100 не ограничивается (P3). |
| QA-002 | P3 | `index.php:26-27,143,296,344` и `include/fk_home_*.php` | Заголовки/тексты/пункты секций hero, showroom, trust, delivery — «ХХ», «Условия доставки.» — плейсхолдеры, которые должен заменить владелец; до этого главная содержит заглушки. Не дефект кода, но блокирует публикацию. |
| QA-003 | P3 | `index.php:189,227,274` | Inline `style="margin-top:24px"` вместо класса. |
| QA-004 | P3 | `js/solutions.js:10-11` | `prev/next/dotsBox` берутся через `document.querySelector` (глобально, не от `root`) и не проверяются на null: если разметка index.php изменится, будет TypeError. Сейчас элементы существуют. Кроме того, у слайдов без точки нет перехвата, всё работает штатно. |

P0/P1 нет.

## Не проверено
Рендер главной в Битриксе (кэш компонентов, вложенный `catalog.section` внутри кэшируемого `news.list`), реальные данные инфоблоков (collections, features, projects, solutions, interiors, pro; свойства ROOM, COLLECTION, HOTSPOTS, COORDS_POINT, NEW), фильтр новинок на живой БД, позиционирование поповера и адаптив, доступность в браузере, работа `data-feedback` на главной, наличие SVG-спрайта `#arrow`, CSS-стили секций (сравнение правил), `include/*.php` как редактируемые в админке области.

## Рекомендация
Вернуть Bitrix Developer: решить QA-005 (откат правки прототипа или явное подтверждение владельца), по возможности QA-001/QA-004. Затем короткий ретест: `git diff --stat main -- prototype`, `php -l`, `node --check`. Рендер главной на живом Битриксе с реальными данными обязателен до PASS.


## Решение владельца (2026-11-14)

QA-005: коммит `24c5720` («sync styles front and back»), изменивший `prototype/homepage-v4/styles.css`, сделан владельцем и принят как намеренный. Замечание снято, правки владельца дефектом не считаются. Итог по задаче: DONE, шаг 3.14 (SEO) не выполнялся по решению владельца.
