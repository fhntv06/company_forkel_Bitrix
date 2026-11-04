"""Эталон главной V4 (этап M0 плана перехода): снимки полной страницы на 7 ширинах.
  python3 -I prototype/tools/parity/baseline_v4.py --save     # записать эталон в baseline/
  python3 -I prototype/tools/parity/baseline_v4.py            # сравнить текущую V4 с эталоном
FK_BASE — адрес V4 (по умолчанию http://localhost:8081/homepage-v4/). Анимации отключены (prefers-reduced-motion), шрифт ждём.
Сравнение: доля отличающихся пикселей по ширинам; допуск 0.2%. Главная V4 меняется только на этапах I1–I4 (обновлять эталон осознанно)."""
import os, sys, io
from playwright.sync_api import sync_playwright
from PIL import Image, ImageChops

BASE = os.environ.get('FK_BASE', 'http://localhost:8081/homepage-v4/')
DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'baseline')
WIDTHS = (360, 390, 768, 1024, 1280, 1440, 1920)
TOL = 0.002
save = '--save' in sys.argv
bad = []

with sync_playwright() as p:
    br = p.chromium.launch()
    for w in WIDTHS:
        ctx = br.new_context(viewport={'width': w, 'height': 900}, reduced_motion='reduce')
        pg = ctx.new_page()
        pg.goto(BASE + 'index.html')
        pg.evaluate('document.fonts.ready')
        pg.wait_for_timeout(600)
        png = pg.screenshot(full_page=True)
        img = Image.open(io.BytesIO(png)).convert('RGB')
        path = os.path.join(DIR, 'v4-index-%d.jpg' % w)
        if save:
            os.makedirs(DIR, exist_ok=True)
            img.save(path, 'JPEG', quality=80, optimize=True)
            print('saved', w, img.size)
        else:
            if not os.path.exists(path):
                bad.append((w, 'нет эталона')); continue
            ref = Image.open(path).convert('RGB')
            if ref.size != img.size:
                bad.append((w, 'размер страницы изменился', ref.size, img.size)); continue
            d = ImageChops.difference(ref, img).convert('L').point(lambda v: 255 if v > 40 else 0)
            share = sum(1 for v in d.getdata() if v) / (img.size[0] * img.size[1])
            print(w, 'отличий %.3f%%' % (share * 100))
            if share > TOL:
                bad.append((w, 'отличий %.2f%%' % (share * 100)))
        ctx.close()
if not save:
    print(bad or 'OK')
