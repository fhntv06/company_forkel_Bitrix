"""Проверка паритета FORKEL (Playwright, Chromium). Запуск: python3 -I prototype/tools/parity/footer_social.py [ширины]. FK_BASE — адрес версии, FK_OUT — папка для скриншотов."""
import os
BASE = os.environ.get("FK_BASE", "http://localhost:8081/homepage-v3/")
OUT = os.environ.get("FK_OUT", "/tmp/")
from playwright.sync_api import sync_playwright
B=BASE + ''
with sync_playwright() as p:
    br=p.chromium.launch()
    for w in (360,768,1024,1440):
        pg=br.new_page(viewport={'width':w,'height':900}); errs=[]
        pg.on('pageerror',lambda e:errs.append(str(e)))
        pg.goto(B+'catalog.html')
        sw=pg.evaluate('document.documentElement.scrollWidth>innerWidth')
        r=pg.locator('.fk-footer__social').bounding_box(); a=[pg.locator('.fk-social-link').nth(i).bounding_box() for i in range(3)]
        print(w,'hscroll',sw,'box',[round(r['x']),round(r['width'])],'icons x/y',[(round(b['x']),round(b['y'])) for b in a],errs)
        pg.click('.fk-soc__btn'); pg.wait_for_timeout(400)
        if w in (360,1440):
            pg.locator('.fk-footer__social').scroll_into_view_if_needed()
            pg.screenshot(path=OUT + f'soc{w}.png')
