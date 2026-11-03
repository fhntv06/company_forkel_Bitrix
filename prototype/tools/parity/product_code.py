"""Проверка паритета FORKEL (Playwright, Chromium). Запуск: python3 -I prototype/tools/parity/product_code.py [ширины]. FK_BASE — адрес версии, FK_OUT — папка для скриншотов."""
import os
BASE = os.environ.get("FK_BASE", "http://localhost:8081/homepage-v3/")
OUT = os.environ.get("FK_OUT", "/tmp/")
from playwright.sync_api import sync_playwright
bad=[]
with sync_playwright() as p:
    br=p.chromium.launch()
    for w in (360,768,1440):
        for u in ('product.html?id=13238','product.html?id=1'):
            ctx=br.new_context(viewport={'width':w,'height':900}, permissions=['clipboard-read','clipboard-write']); pg=ctx.new_page(); errs=[]
            pg.on('pageerror',lambda e:errs.append(str(e)))
            pg.goto(BASE + ''+u)
            code=pg.locator('.fk-pdp__code'); sku=pg.locator('.fk-pdp__sku')
            if code.count()!=1: bad.append((w,u,'no code')); continue
            if code.bounding_box()['y']>=sku.bounding_box()['y']: bad.append((w,u,'order'))
            b=pg.locator('[data-copy-code]'); bb=b.bounding_box()
            if bb['width']<44 or bb['height']<44: bad.append((w,u,'size'))
            b.click(); pg.wait_for_timeout(150)
            clip=pg.evaluate('navigator.clipboard.readText()')
            if clip!='00000019596': bad.append((w,u,'clip',clip))
            if 'is-copied' not in (b.get_attribute('class') or ''): bad.append((w,u,'state'))
            if pg.evaluate('document.documentElement.scrollWidth>innerWidth'): bad.append((w,u,'hscroll'))
            if w==1440 and u.endswith('13238'): pg.screenshot(path=OUT + 'product_code.png',clip={'x':700,'y':100,'width':700,'height':450})
            errs=[e for e in errs]
            if errs: bad.append((w,u,errs))
            ctx.close()
print(bad or 'OK')
