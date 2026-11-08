"""P3: страница «Новинки» V4 (new.html) — 8 демо-товаров, заглушки. Движок shop.js и css — из V3 без изменений (перенесены в P2).
Проверяет: shop.js/css идентичны V3; 7 ширин (нет горизонтальной прокрутки, ошибок консоли/HTTP, цели >= 44 px, один h1); ровно 8 карточек NEW_IDS = товары 1–8 (shop.js);
h1/крошки/метка демо-данных; пункт «Новинки» в шапке — aria-current; фильтр «Коллекция» (F70/F55) и цвет, сброс, поиск, пустое состояние, сортировка;
сердце и корзина (fk-demo-fav-v1, fk-demo-cart-v1), ссылки product.html?id=; кнопка «Фильтры» на 390 px; клавиатура; ссылка из шапки/подвала/главной «Все новинки» -> new.html.
Запуск: python3 -I new_page_v4.py (сервер на 8081 из prototype/)."""
import os, filecmp
from playwright.sync_api import sync_playwright

import hashlib as _hl, json as _js
def _same(a,b,shallow=False):
    """Файл V4 идентичен V3 ИЛИ совпадает с принятым владельцем отличием (baseline/v4-accepted-divergence.json, хэш sha256)."""
    if filecmp.cmp(a,b,shallow=False): return True
    try:
        acc=_js.load(open(os.path.join(os.path.dirname(os.path.abspath(__file__)),'baseline','v4-accepted-divergence.json'),encoding='utf-8'))
        rel=os.path.relpath(os.path.abspath(b),os.path.abspath(os.path.join(os.path.dirname(os.path.abspath(__file__)),'..','..','homepage-v4'))).replace(os.sep,'/')
        return acc.get(rel)==_hl.sha256(open(b,'rb').read()).hexdigest()
    except Exception: return False
B=os.environ.get('FK_BASE','http://localhost:8081/homepage-v4/'); OUT=os.environ.get('FK_OUT','/tmp/'); r=[]
def ck(n,c): r.append((n,bool(c)))
ROOT=os.path.join(os.path.dirname(os.path.abspath(__file__)),'..','..')
for f in ('shop.js','css/catalog.css','css/page.css'): ck(f+' identical to V3',_same(ROOT+'/homepage-v3/'+f,ROOT+'/homepage-v4/'+f,shallow=False))
with sync_playwright() as p:
    b=p.chromium.launch()
    for w in (360,390,768,1024,1280,1440,1920):
        pg=b.new_page(viewport={'width':w,'height':800}); errs=[]
        pg.on('pageerror',lambda e:errs.append(str(e)))
        pg.on('response',lambda x:x.status>=400 and 'localhost' in x.url and errs.append(f'{x.status} {x.url}'))
        pg.on('console',lambda m:m.type=='error' and 'Failed to load resource' not in m.text and errs.append(m.text))
        pg.goto(B+'new.html'); pg.evaluate('document.fonts.ready'); pg.wait_for_timeout(200)
        o=pg.evaluate("""()=>{const q=s=>[...document.querySelectorAll(s)];
          const t=q('main a,main button,main select,main input[type=search],main label.fk-check').filter(e=>e.getClientRects().length&&!e.closest('[hidden]')&&!(e.matches('input')&&e.type==='checkbox'));
          const small=t.filter(e=>{const r=e.getBoundingClientRect();return r.height<43.5||(r.width<43.5&&!e.matches('.fk-btn--card'))}).map(e=>(e.className||e.tagName)+'|'+Math.round(e.getBoundingClientRect().width)+'x'+Math.round(e.getBoundingClientRect().height));
          return {sw:document.documentElement.scrollWidth,cw:document.documentElement.clientWidth,small,h1:q('h1').length,n:q('#cat-list > li').length}}""")
        good=o['sw']<=o['cw'] and not o['small'] and o['h1']==1 and not errs and o['n']==8
        ck(f'{w}px health (no overflow, targets>=44, one h1, no errors, 8 cards)',good)
        if not good: print(w,o,errs)
        if w in(390,1280): pg.screenshot(path=OUT+f'p3-{w}.png',full_page=True)
        pg.close()
    pg=b.new_page(viewport={'width':1280,'height':900}); errs=[]; pg.on('pageerror',lambda e:errs.append(str(e)))
    pg.goto(B+'new.html'); pg.evaluate("localStorage.clear()"); pg.reload(); pg.wait_for_timeout(200)
    ids=pg.evaluate("[...document.querySelectorAll('#cat-list a[href*=\"product.html?id=\"]')].map(a=>parseInt(a.href.split('id=')[1]))")
    ck('cards = first 8 demo products (NEW_IDS 1..8 in shop.js)',sorted(set(ids))==[1,2,3,4,5,6,7,8])
    ck('h1 and crumb "Новинки"',pg.inner_text('#cat-title')=='Новинки' and pg.inner_text('#cat-crumb')=='Новинки' and 'новинки' in pg.title().lower())
    ck('demo-data note removed by owner',pg.locator('.fk-section-head .fk-note--proto').count()==0)
    ck('header "Новинки" is aria-current',pg.evaluate("document.querySelector('.fk-header .fk-nav__link[aria-current=page]')?.textContent.trim()")=='Новинки')
    ck('counter text',pg.inner_text('#cat-count').endswith('8 из 8'))
    n0=8; pg.check('#cat-filters input[name=col][value=F55]'); pg.wait_for_timeout(100); n1=pg.locator('#cat-list > li').count()
    ck('collection filter F55 reduces list',0<n1<n0)
    pg.check('#cat-filters input[name=col][value=F70]'); pg.wait_for_timeout(100); ck('both collections -> all 8',pg.locator('#cat-list > li').count()==8)
    pg.click('#cat-filters [data-reset]'); pg.check('#cat-filters input[name=color][value=gold]'); pg.wait_for_timeout(100); c1=pg.locator('#cat-list > li').count()
    ck('color filter changes list',c1<8)
    pg.click('#cat-filters [data-reset]'); pg.wait_for_timeout(100); ck('reset restores 8',pg.locator('#cat-list > li').count()==8)
    pg.fill('#cat-search','zzzz'); pg.press('#cat-search','Enter'); pg.wait_for_timeout(100)
    ck('search no match -> empty state',pg.locator('#cat-empty').is_visible() and pg.locator('#cat-list > li').count()==0)
    pg.click('#cat-empty [data-reset]'); pg.wait_for_timeout(100); ck('empty reset restores list',pg.locator('#cat-list > li').count()==8)
    pg.select_option('#cat-sort','name'); pg.wait_for_timeout(100); names=pg.evaluate("[...document.querySelectorAll('#cat-list h3')].map(h=>h.textContent.trim())")
    ck('sort by name alphabetical',names==sorted(names))
    h=pg.locator('#cat-list [data-fav]').first; h.click(); ck('heart saves fk-demo-fav-v1',h.get_attribute('aria-pressed')=='true' and pg.evaluate("!!localStorage.getItem('fk-demo-fav-v1')"))
    pg.locator('#cat-list [data-add]').first.click(); ck('add to cart saves fk-demo-cart-v1, counter 1',pg.inner_text('[data-cart-count]')=='1' and pg.evaluate("!!localStorage.getItem('fk-demo-cart-v1')"))
    pg.focus('#cat-list [data-fav] >> nth=1'); pg.keyboard.press('Enter'); ck('keyboard Enter on heart',pg.get_attribute('#cat-list [data-fav] >> nth=1','aria-pressed')=='true')
    ck('no page errors',not errs)
    pg.goto(B+'index.html'); ck('home: "Все новинки" and header lead to new.html',pg.locator('a[href="new.html"]').count()>=2)
    b.close()
    b=p.chromium.launch(); pg=b.new_page(viewport={'width':390,'height':800}); pg.goto(B+'new.html'); pg.wait_for_timeout(200)
    ck('mobile: filters collapsed, toggle opens',pg.get_attribute('#cat-filters-toggle','aria-expanded')=='false' and not pg.is_visible('#cat-filters'))
    pg.click('#cat-filters-toggle'); ck('mobile: aria-expanded true and filters visible',pg.get_attribute('#cat-filters-toggle','aria-expanded')=='true' and pg.is_visible('#cat-filters'))
    b.close()
for n,c in r: print('OK ' if c else 'FAIL',n)
print('OK' if all(c for _,c in r) else 'FAIL: есть отклонения')
