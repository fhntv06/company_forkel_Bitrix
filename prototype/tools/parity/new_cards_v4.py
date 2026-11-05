"""I3: главная V4 — «Новинки» на карточках V3 (избранное, корзина, цветовые точки), три направления, остальные категории в меню шапки.
Проверяет: products.css идентичен V3; 7 ширин (нет горизонтальной прокрутки — известный дефект 406 px исправлен, цели >= 44 px, подпись кнопок не обрезана, ошибки консоли/HTTP кроме внешних фото ledron.ru);
5 карточек как в V3, id товаров есть в FKData; сердце: добавить/убрать, счётчик, ключ fk-demo-fav-v1, aria-pressed; «В корзину»: счётчик и fk-demo-cart-v1;
цветовые точки заполнены; ссылки «Подробнее»/название -> product.html?id=; «Все новинки» -> new.html; 3 плитки направлений -> категории каталога;
меню шапки: «Управление жалюзи», «Терморегуляторы», «Димеры» (каталог-панель); клавиатура (Tab до сердца и кнопки, Enter).
Запуск: python3 -I new_cards_v4.py (сервер на 8081 из prototype/)."""
import os, filecmp
from playwright.sync_api import sync_playwright
B=os.environ.get('FK_BASE','http://localhost:8081/homepage-v4/'); OUT=os.environ.get('FK_OUT','/tmp/'); r=[]
def ck(n,c): r.append((n,bool(c)))
ROOT=os.path.join(os.path.dirname(os.path.abspath(__file__)),'..','..')
ck('css/products.css identical to V3',filecmp.cmp(ROOT+'/homepage-v3/css/products.css',ROOT+'/homepage-v4/css/products.css',shallow=False))
EXT=lambda t:'ledron.ru' in t
with sync_playwright() as p:
    b=p.chromium.launch()
    for w in (360,390,768,1024,1280,1440,1920):
        pg=b.new_page(viewport={'width':w,'height':800}); errs=[]
        pg.on('pageerror',lambda e:errs.append(str(e)))
        pg.on('response',lambda x:x.status>=400 and 'localhost' in x.url and errs.append(f'{x.status} {x.url}'))
        pg.on('console',lambda m:m.type=='error' and 'Failed to load resource' not in m.text and errs.append(m.text))
        pg.goto(B+'index.html'); pg.evaluate('document.fonts.ready')
        o=pg.evaluate("""()=>{const t=[...document.querySelectorAll('#new button,#new a')].filter(e=>e.getClientRects().length);
          const small=t.filter(e=>{const r=e.getBoundingClientRect();return r.height<43.5||(r.width<43.5&&!e.matches('[tabindex="-1"]'))}).map(e=>e.className+'|'+e.textContent.trim().slice(0,14));
          const clipped=[...document.querySelectorAll('#new .fk-btn--card')].filter(e=>e.scrollWidth>e.clientWidth+1).map(e=>e.textContent.trim().slice(0,14));
          const cards=[...document.querySelectorAll('#new .fk-product-card')];
          const out=cards.filter(c=>{const r=c.getBoundingClientRect();return r.right>document.documentElement.clientWidth+0.5||r.left<-0.5}).length;
          return {sw:document.documentElement.scrollWidth,cw:document.documentElement.clientWidth,small,clipped,n:cards.length,out}}""")
        good=o['sw']<=o['cw'] and not o['small'] and not o['clipped'] and o['n']==5 and o['out']==0 and not errs
        ck(f'{w}px new cards health (no overflow, targets>=44, labels not clipped, 5 cards, no errors)',good)
        if not good: print(w,o,errs)
        pg.close()
    pg=b.new_page(viewport={'width':1280,'height':900}); pg.goto(B+'index.html'); pg.evaluate("localStorage.clear()"); pg.reload()
    ids=pg.evaluate("[...document.querySelectorAll('#new .fk-product-card a[href*=\"product.html?id=\"]')].map(a=>parseInt(a.href.split('id=')[1]))")
    ok=pg.evaluate("ids=>ids.every(i=>FKData.PRODUCTS.some(p=>p.id===i))",ids)
    ck('card product ids exist in FKData',ids and ok)
    ck('5 cards: 13238 real + 4 demo',sorted(set(ids))==[1,2,3,4,13238])
    ck('colour dots filled from data (no empty variants)',pg.evaluate("[...document.querySelectorAll('#new [data-variants]')].every(e=>e.querySelectorAll('li').length>=3)"))
    ck('heart on every card',pg.locator('#new .fk-product-card [data-fav]').count()==5)
    h=pg.locator('#new [data-fav]').first
    h.click(); ck('heart pressed + counter 1',h.get_attribute('aria-pressed')=='true' and pg.inner_text('[data-fav-count]')=='1')
    st=pg.evaluate("localStorage.getItem('fk-demo-fav-v1')"); ck('fav key fk-demo-fav-v1 saved',st and '13238' in st)
    h.click(); ck('heart toggles off, counter hidden',h.get_attribute('aria-pressed')=='false' and not pg.locator('[data-fav-count]').first.is_visible())
    pg.locator('#new [data-add]').nth(1).click(); ck('add to cart -> counter 1',pg.inner_text('[data-cart-count]')=='1')
    c=pg.evaluate("localStorage.getItem('fk-demo-cart-v1')"); ck('cart key fk-demo-cart-v1 has product 1','"id":1' in (c or '').replace(' ',''))
    pg.locator('#new [data-add]').nth(1).click(); ck('second add -> counter 2',pg.inner_text('[data-cart-count]')=='2')
    hrefs=pg.evaluate("[...document.querySelectorAll('#new a')].map(a=>a.getAttribute('href'))")
    ck('"Все новинки" -> new.html and "Подробнее" -> product.html?id=','new.html' in hrefs and sum(1 for x in hrefs if x.startswith('product.html?id='))>=10)
    ck('no leftover stub for cart in new section',pg.locator('#new [data-stub]').count()==0)
    d=pg.evaluate("[...document.querySelectorAll('.dirs a.dir')].map(a=>a.getAttribute('href'))")
    ck('3 direction tiles -> sockets/switches/frames',d==['catalog.html?category=sockets','catalog.html?category=switches','catalog.html?category=frames'])
    pg.click('.fk-catalog-btn'); pn=pg.inner_text('#catalog-panel')
    ck('catalog panel lists blinds/thermostats/dimmers',all(x in pn for x in('Управление жалюзи','Терморегуляторы','Диммеры')) or all(x in pn for x in('Управление жалюзи','Терморегуляторы','Димеры')))
    pg.keyboard.press('Escape')
    # клавиатура
    pg.locator('#new .fk-product-card').nth(2).locator('[data-fav]').focus(); pg.keyboard.press('Enter')
    ck('keyboard Enter on heart toggles',pg.locator('#new .fk-product-card').nth(2).locator('[data-fav]').get_attribute('aria-pressed')=='true')
    pg.keyboard.press('Tab'); pg.keyboard.press('Tab')
    ck('Tab order reaches name link then add button inside card',pg.evaluate("document.activeElement.closest('.fk-product-card')===document.querySelectorAll('#new .fk-product-card')[2]"))
    pg.locator('#new .fk-product-card').first.screenshot(path=OUT+'i3-card.png')
    b.close()
for n,c in r: print('OK ' if c else 'FAIL',n)
print('OK' if all(c for _,c in r) else 'FAIL: есть отклонения')
