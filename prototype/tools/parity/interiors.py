"""Проверка паритета FORKEL (Playwright, Chromium). Запуск: python3 -I prototype/tools/parity/interiors.py [ширины]. FK_BASE — адрес версии, FK_OUT — папка для скриншотов."""
import os
BASE = os.environ.get("FK_BASE", "http://localhost:8081/homepage-v3/")
OUT = os.environ.get("FK_OUT", "/tmp/")
from playwright.sync_api import sync_playwright
bad=[]
with sync_playwright() as p:
    br=p.chromium.launch()
    for w,cols in ((360,1),(768,2),(1024,4),(1440,4),(1920,4)):
        pg=br.new_page(viewport={'width':w,'height':900}); errs=[]
        pg.on('pageerror',lambda e:errs.append(str(e))); pg.on('response',lambda r:errs.append(r.url) if r.status>=400 and 'ledron' not in r.url else None)
        pg.goto(BASE + 'index.html'); pg.locator('#interiors').scroll_into_view_if_needed()
        pg.evaluate("document.querySelectorAll('#interiors img').forEach(i=>i.loading='eager')"); pg.wait_for_timeout(1500)
        n=pg.locator('.fk-interior').count()
        if n!=7: bad.append((w,'count',n))
        okimg=pg.evaluate("[...document.querySelectorAll('#interiors img')].filter(i=>i.naturalWidth>0).length")
        if okimg!=7: bad.append((w,'imgs',okimg))
        xs={round(pg.locator('.fk-interior').nth(i).bounding_box()['x']) for i in range(7)}
        if len(xs)!=cols: bad.append((w,'cols',len(xs)))
        if pg.evaluate('document.documentElement.scrollWidth>innerWidth'): bad.append((w,'hscroll'))
        ell=pg.evaluate("[...document.querySelectorAll('.fk-interior__title')].filter(t=>t.scrollWidth>t.clientWidth).length"); print(w,'titles truncated',ell)
        if w==1440: pg.screenshot(path=OUT + 'interiors.png')
        if errs: bad.append((w,errs[:3]))
print(bad or 'OK')
