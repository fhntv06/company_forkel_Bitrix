# FORKEL — прототип V4

Главная V4 (`index.html`) и переход страниц и функций V3 в дизайн V4. План: `docs/design/v3-to-v4-migration.md`; задача PRT-003.

**Запуск:** `python3 -m http.server 8081 --directory prototype`, затем `http://localhost:8081/homepage-v4/index.html`. Без сборки и зависимостей.

## Структура

| Путь | Назначение |
|---|---|
| `styles.css` | токены и компоненты V4 (источник дизайна; шрифт Montserrat) |
| `css/v4-skin.css` | слой оформления: токены V3 `--fk-*` → токены V4. Подключается после `styles.css` и компонентов V3 |
| `data.js` | данные V3 без изменений (`FKData`); пути картинок — `assets/images/` |
| `assets/images/` | изображения V3 (слои подбора — только референс; `solutions/`, `designs/` — материалы заказчика) |
| `assets/fonts/` | Montserrat (OFL) |
| `page-template.html` | шаблон страницы; шапка и подвал в нём временные (M2, M3) |
| `app.js` | поведение главной V4 (на страницах без подбора и «Новинок» не падает) |

## Выполнено

- M0 — эталон главной (`prototype/tools/parity/baseline/`).
- M1 — каркас: папки, `data.js`, изображения, `css/v4-skin.css`, шаблон страницы.
