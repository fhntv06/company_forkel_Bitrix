"""Проверка паритета FORKEL (Playwright, Chromium). Запуск: python3 -I prototype/tools/parity/solutions.py [ширины]. FK_BASE — адрес версии, FK_OUT — папка для скриншотов."""
import os
BASE = os.environ.get("FK_BASE", "http://localhost:8081/homepage-v3/")
OUT = os.environ.get("FK_OUT", "/tmp/")
from playwright.sync_api import sync_playwright
import sys
bad=[]
with sync_playwright() as p:
    br=p.chromium.launch()
    for w in (360,390,768,1024,1280,1440,1920):
        pg=br.new_page(viewport={'width':w,'height':900}); pg.set_default_timeout(4000); errs=[]
        pg.on('pageerror',lambda e:errs.append(str(e))); pg.on('response',lambda r:errs.append(r.url) if r.status>=400 and 'ledron' not in r.url else None)
        pg.goto(BASE + 'index.html'); pg.locator('.fk-sol').scroll_into_view_if_needed()
        sl=pg.locator('.fk-sol__slide'); n=sl.count()
        tr=pg.locator('.fk-sol__track').bounding_box()
        if abs(sl.nth(0).bounding_box()['width']/tr['width']-0.8)>0.01: bad.append((w,'ratio'))
        for i in range(n):
            pg.locator('.fk-sol-dot').nth(i).click(); pg.wait_for_timeout(1300)
            if pg.locator('.fk-sol-dot').nth(i).get_attribute('aria-current')!='true' and i<n-1: bad.append((w,i,'dot'))
            for k in range(sl.nth(i).locator('.fk-hs').count()):
                h=sl.nth(i).locator('.fk-hs').nth(k); h.click(); pg.wait_for_timeout(100)
                pop=sl.nth(i).locator('.fk-hs-pop').nth(k)
                if pop.is_hidden(): bad.append((w,i,k,'closed')); continue
                pb=pop.bounding_box(); sb=sl.nth(i).bounding_box()
                if pb['x']<sb['x']-1 or pb['x']+pb['width']>sb['x']+sb['width']+1 or pb['y']<sb['y']-1 or pb['y']+pb['height']>sb['y']+sb['height']+1: bad.append((w,i,k,'outside'))
                pg.keyboard.press('Escape'); pg.mouse.move(2,2); pg.wait_for_timeout(300)
        if pg.evaluate('document.documentElement.scrollWidth>innerWidth'): bad.append((w,'hscroll'))
        if errs: bad.append((w,errs[:2]))
        pg.close()
print(bad or 'OK')
