"""Проверка паритета FORKEL (Playwright, Chromium). Запуск: python3 -I prototype/tools/parity/favorites_header.py [ширины]. FK_BASE — адрес версии, FK_OUT — папка для скриншотов."""
import os
BASE = os.environ.get("FK_BASE", "http://localhost:8081/homepage-v3/")
OUT = os.environ.get("FK_OUT", "/tmp/")
from playwright.sync_api import sync_playwright
B=BASE + ''
bad=[]
with sync_playwright() as p:
    br=p.chromium.launch()
    for w in (360,390,768,1024,1280,1440,1920):
        pg=br.new_page(viewport={'width':w,'height':900}); errs=[]
        pg.on('console',lambda m:errs.append(m.text) if m.type=='error' else None)
        pg.on('pageerror',lambda e:errs.append(str(e)))
        pg.on('response',lambda r:errs.append(r.url) if r.status>=400 else None)
        pg.goto(B+'favorites.html')
        if not pg.is_visible('#fav-empty'): bad.append((w,'no empty'))
        pg.goto(B+'catalog.html')
        btns=pg.locator('#cat-list .fk-fav-btn')
        btns.nth(0).click(); btns.nth(2).click()
        if pg.inner_text('[data-fav-count]')!='2': bad.append((w,'badge',pg.inner_text('[data-fav-count]')))
        if btns.nth(0).get_attribute('aria-pressed')!='true': bad.append((w,'pressed'))
        bb=btns.nth(0).bounding_box()
        if bb['width']<44 or bb['height']<44: bad.append((w,'size'))
        pg.goto(B+'index.html')
        if pg.locator('.fk-product-card .fk-fav-btn').count()<1: bad.append((w,'static'))
        # header link target
        for sel in ('.fk-fav-link','.fk-cart-link','.fk-menu-btn'):
            r=pg.locator(sel).first.bounding_box()
            if r and (r['width']<44 or r['height']<44): bad.append((w,sel,r))
        pg.goto(B+'favorites.html')
        n=pg.locator('#fav-list > li').count()
        if n!=2: bad.append((w,'count',n))
        if pg.evaluate('document.documentElement.scrollWidth>innerWidth'): bad.append((w,'hscroll'))
        if pg.locator('h1').count()!=1: bad.append((w,'h1'))
        pg.locator('#fav-list .fk-fav-btn').first.click()
        if pg.locator('#fav-list > li').count()!=1: bad.append((w,'remove'))
        pg.click('[data-fav-clear]')
        if not pg.is_visible('#fav-empty') or pg.locator('[data-fav-count]').first.is_visible(): bad.append((w,'clear'))
        for u in ('index.html','catalog.html','product.html?id=1','cart.html'):
            pg.goto(B+u)
            if pg.evaluate('document.documentElement.scrollWidth>innerWidth'): bad.append((w,u,'hscroll'))
        errs=[e for e in errs if 'ledron' not in e and 'Failed to load' not in e]
        if errs: bad.append((w,errs[:3]))
        pg.close()
    br.close()
print(bad or 'OK')
