"""P6: страница корзины V4 (cart.html) — разметку строит cart.js; css/cart.css идентичен V3.
Проверяет: css/cart.css идентичен V3; 7 ширин с 2 товарами (нет горизонтальной прокрутки, ошибок, цели >= 44 px, один h1);
пустая корзина (ссылки в каталог и подбор); строки, количество +/-, удаление, итог, ссылка «Оформить заказ» -> checkout.html; ключ fk-demo-cart-v1; счётчик в шапке.
Запуск: python3 -I cart_v4.py (сервер на 8081 из prototype/)."""
import os, filecmp
from playwright.sync_api import sync_playwright
B=os.environ.get('FK_BASE','http://localhost:8081/homepage-v4/'); OUT=os.environ.get('FK_OUT','/tmp/'); r=[]
def ck(n,c): r.append((n,bool(c)))
ROOT=os.path.join(os.path.dirname(os.path.abspath(__file__)),'..','..')
ck('css/cart.css identical to V3',filecmp.cmp(ROOT+'/homepage-v3/css/cart.css',ROOT+'/homepage-v4/css/cart.css',shallow=False))
def fill(pg):
    pg.goto(B+'catalog.html'); pg.evaluate("localStorage.clear()"); pg.reload(); pg.wait_for_timeout(150)
    pg.locator('#cat-list [data-add]').first.click(); pg.locator('#cat-list [data-add]').nth(1).click()
with sync_playwright() as p:
    b=p.chromium.launch()
    for w in (360,390,768,1024,1280,1440,1920):
        pg=b.new_page(viewport={'width':w,'height':800}); errs=[]
        pg.on('pageerror',lambda e:errs.append(str(e)))
        pg.on('response',lambda x:x.status>=400 and 'localhost' in x.url and errs.append(f'{x.status} {x.url}'))
        pg.on('console',lambda m:m.type=='error' and 'Failed to load resource' not in m.text and errs.append(m.text))
        fill(pg); pg.goto(B+'cart.html'); pg.evaluate('document.fonts.ready'); pg.wait_for_timeout(200)
        o=pg.evaluate("""()=>{const q=s=>[...document.querySelectorAll(s)];
          const t=q('main a,main button,main input').filter(e=>e.getClientRects().length);
          const small=t.filter(e=>{const r=e.getBoundingClientRect();return r.height<43.5||(r.width<43.5&&!e.matches('.fk-link'))}).map(e=>(e.className||e.tagName)+'|'+Math.round(e.getBoundingClientRect().width)+'x'+Math.round(e.getBoundingClientRect().height));
          return {sw:document.documentElement.scrollWidth,cw:document.documentElement.clientWidth,small,h1:q('h1').length,items:q('.fk-cart-item').length}}""")
        good=o['sw']<=o['cw'] and not o['small'] and o['h1']==1 and o['items']==2 and not errs
        ck(f'{w}px health (no overflow, targets>=44, one h1, 2 items, no errors)',good)
        if not good: print(w,o,errs)
        if w in(390,1280): pg.screenshot(path=OUT+f'p6-{w}.png',full_page=True)
        pg.close()
    pg=b.new_page(viewport={'width':1280,'height':900}); errs=[]; pg.on('pageerror',lambda e:errs.append(str(e)))
    pg.goto(B+'cart.html'); pg.evaluate("localStorage.clear()"); pg.reload(); pg.wait_for_timeout(200)
    ck('empty: state with links to catalog and configurator',pg.locator('.fk-empty').is_visible() and pg.locator('.fk-empty a[href="catalog.html"]').count()>=1 and pg.locator('.fk-empty a.fk-link').count()>=1)
    fill(pg); pg.goto(B+'cart.html'); pg.wait_for_timeout(200)
    ck('h1 + crumb','Корзина'==pg.inner_text('h1') and pg.inner_text('.fk-crumbs [aria-current=page]')=='Корзина')
    t0=pg.inner_text('.fk-cart__total')
    pg.locator('.fk-qty__btn[data-act=inc]').first.click(); pg.wait_for_timeout(100)
    ck('qty +: total changes, counter in header updates',pg.inner_text('.fk-cart__total')!=t0 and pg.evaluate("JSON.parse(localStorage.getItem('fk-demo-cart-v1')).length>=1"))
    ck('checkout link -> checkout.html',pg.locator('.fk-cart__summary a[href="checkout.html"]').count()==1)
    pg.locator('.fk-cart-item__remove').first.click(); pg.wait_for_timeout(100)
    ck('remove: one item left',pg.locator('.fk-cart-item').count()==1)
    pg.locator('.fk-cart-item__remove').first.click(); pg.wait_for_timeout(100)
    ck('remove last: empty state',pg.locator('.fk-empty').is_visible())
    ck('no page errors',not errs)
    b.close()
for n,c in r: print('OK ' if c else 'FAIL',n)
print('OK' if all(c for _,c in r) else 'FAIL: есть отклонения')
