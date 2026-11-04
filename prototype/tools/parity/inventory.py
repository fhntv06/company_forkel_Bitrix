"""Инвентаризация паритета V3 -> V4: какие страницы и ключевые id уже есть в V4.
Без браузера, только чтение файлов. Запуск: python3 -I inventory.py
Печатает таблицу и итог; код выхода 0 всегда (это отчёт, не вердикт)."""
import os, re
ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..'))
V3 = os.path.join(ROOT, 'homepage-v3'); V4 = os.path.join(ROOT, 'homepage-v4')
SCRIPTS = ['data.js', 'cart.js', 'favorites.js', 'auth.js', 'shop.js', 'search.js',
           'feedback.js', 'callback.js', 'solutions.js', 'app.js']

def ids(path):
    try: return set(re.findall(r'\sid="([^"]+)"', open(path, encoding='utf-8').read()))
    except FileNotFoundError: return None

pages = sorted(f for f in os.listdir(V3) if f.endswith('.html'))
have = 0
print('Страницы V3 -> V4')
for p in pages:
    a, b = ids(os.path.join(V3, p)), ids(os.path.join(V4, p))
    if b is None: print(f'  [нет ] {p}  (id в V3: {len(a)})'); continue
    have += 1
    miss = sorted(a - b)
    print(f'  [есть] {p}  id V3: {len(a)}, не найдено в V4: {len(miss)}' + (f' {miss[:8]}' if miss else ''))
print(f'Итого страниц: {have}/{len(pages)}')
print('\nСкрипты V3 -> V4')
got = 0
for s in SCRIPTS:
    ok = os.path.exists(os.path.join(V4, s)); got += ok
    print(f'  [{"есть" if ok else "нет "}] {s}')
print(f'Итого скриптов: {got}/{len(SCRIPTS)}')
