# QA TASK-002 — h1 прототипа V4 ×1,3

- Статус: **PASS**
- Ref: ветка `role/implementation/TASK-002-v4-h1-font-size-130`, HEAD `8cef418`; база — `role/implementation/TASK-001-v4-h1-font-size` (`3a5cf7d`).
- Среда: Chromium 153.0.8010.12 (Playwright python), headless, высота окна 900; внешние (не localhost) запросы заблокированы (прокси их отклоняет, иначе `networkidle` не наступает).

## Критерии
1. h1 = 1,3 × TASK-001 (±0,5 px), 4 ширины, 18 страниц: **выполнено** (72 из 72 комбинаций, проверены все h1 на странице).
2. Диф только 4 правила: **выполнено**. `git diff role/implementation/TASK-001-v4-h1-font-size...HEAD -- prototype` затрагивает 4 файла по 1 строке: `styles.css` (h1), `css/about.css` (.fk-ab-hero__title), `css/projects.css` (.fk-prj-cover__text .fk-page-title), `css/product-page.css` (.fk-pdp__title). Значения совпадают с таблицей TASK-002 §3. Документация в проверку дифа по `prototype` не входила.
3. h2/h3 не изменились (computed font-size всех h2/h3 идентичны базе на всех 72 комбинациях); горизонтальной прокрутки нет (scrollWidth <= clientWidth) ни на HEAD, ни на базе, на всех ширинах, включая 360.

## Измерения (px, база -> HEAD; на странице один h1)

| Страница | 360 | 768 | 1280 | 1920 |
|---|---|---|---|---|
| index (h1 по умолчанию) | 42,7 -> 55,5 | 42,7 -> 55,5 | 43,5 -> 56,6 | 58,9 -> 76,5 |
| collection?collection=F70 (.fk-page-title) | 42,7 -> 55,5 | 42,7 -> 55,5 | 43,5 -> 56,6 | 58,9 -> 76,5 |
| project?id=99 (не найден, .fk-page-title) | 42,7 -> 55,5 | 42,7 -> 55,5 | 43,5 -> 56,6 | 58,9 -> 76,5 |
| project?id=1 (.fk-prj-cover) | 18,7 -> 24,3 | 20,9 -> 27,2 | 27,7 -> 36,1 | 32,0 -> 41,6 |
| product?id=1 и ?id=999999 (.fk-pdp__title) | 17,1 -> 22,2 | 20,9 -> 27,2 | 27,1 -> 35,2 | 27,7 -> 36,0 |
| catalog, new, cart, checkout, about, projects, contacts, account, favorites, search, configurator, cooperation (.fk-ab-hero__title) | 20,0 -> 26,0 | 26,1 -> 33,9 | 35,0 -> 45,5 | 37,3 -> 48,5 |

Все 12 страниц последней строки дали одинаковые значения. Отношение HEAD/база = 1,300 в пределах округления.

## Команды
```
git diff role/implementation/TASK-001-v4-h1-font-size...HEAD -- prototype
git worktree add <scratchpad>/base role/implementation/TASK-001-v4-h1-font-size
python3 -m http.server 8101   # prototype/homepage-v4 (HEAD)
python3 -m http.server 8102   # worktree TASK-001
python3 <scratchpad>/m.py     # playwright: getComputedStyle h1/h2/h3, scrollWidth vs clientWidth
git worktree remove --force <scratchpad>/base; pkill -f "http.server 810"
```
Скрипт и результаты: `/tmp/claude-0/-home-claude/9599c2f9-0734-5668-b80f-4905e66c22f8/scratchpad/m.py`, `.../r.json`.

## Дефекты
Не обнаружено.

## Не проверено
- Визуальные скриншоты, переносы/обрезка заголовков (проверялись только числа и отсутствие горизонтальной прокрутки).
- Ширины между контрольными (напр. 400-700, 1000), другие браузеры (Firefox/WebKit), высота окна кроме 900.
- Состояния, зависящие от JS/данных после взаимодействия (авторизация, корзина с товарами); h1, появляющиеся только после действий.
- Внешние изображения/шрифты не загружались (заблокированы).
- Сравнение с `main` (допущение 1 в задаче) не выполнялось.

## Рекомендация
Повторная проверка не требуется; можно передавать на ревью владельцу.
