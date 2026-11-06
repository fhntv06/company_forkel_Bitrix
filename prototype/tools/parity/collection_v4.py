"""P2: страница коллекции V4 (collection.html) — баннер, товары коллекции, ссылка в подбор. Движок shop.js и css — из V3 без изменений.
Проверяет: shop.js и css page/catalog/categories/collections идентичны V3; 7 ширин для F70 и F55 (нет горизонтальной прокрутки, ошибок консоли/HTTP, цели >= 44 px, один h1);
F70 = 12 товаров, F55 = 7; заголовок и хлебные крошки; ссылка «Собрать в подборе» с ?collection=; переключатель коллекций (aria-current); неизвестная коллекция -> F70;
?category= (розетки F70 = 2); цвета, которых нет у F55 (светло-серый, кашемир), в фильтре скрыты; фильтры цвета, сброс, поиск, пустое состояние, сортировка;
карточки: сердце (fk-demo-fav-v1) и «В корзину» (fk-demo-cart-v1), ссылки product.html?id=; кнопка «Фильтры» на узких экранах; клавиатура.
Запуск: python3 -I collection_v4.py (сервер на 8081 из prototype/)."""
import os, filecmp
from playwright.sync_api import sync_playwright
B=os.environ.get('FK_BASE','http://localhost:8081/homepage-v4/'); OUT=os.environ.get('FK_OUT','/tmp/'); r=[]
def ck(n,c): r.append((n,bool(c)))
ROOT=os.path.join(os.path.dirname(os.path.abspath(__file__)),'..','..')
for f in ('shop.js','css/page.css','css/catalog.css','css/categories.css','css/collections.css'):
    ck(f+' identical to V3',filecmp.cmp(ROOT+'/homepage-v3/'+f,ROOT+'/homepage-v4/'+f,shallow=False))
U=lambda c,extra='':B+'collection.html?collection='+c+extra
with sync_playwright() as p:
    b=p.chromium.launch()
    for c in ('F70','F55'):
        for w in (360,390,768,1024,1280,1440,1920):
            pg=b.new_page(viewport={'width':w,'height':800}); errs=[]
            pg.on('pageerror',lambda e:errs.append(str(e)))
            pg.on('response',lambda x:x.status>=400 and 'localhost' in x.url and errs.append(f'{x.status} {x.url}'))
            pg.on('console',lambda m:m.type=='error' and 'Failed to load resource' not in m.text and errs.append(m.text))
            pg.goto(U(c)); pg.evaluate('document.fonts.ready'); pg.wait_for_timeout(200)
            o=pg.evaluate("""()=>{const q=s=>[...document.querySelectorAll(s)];
              const t=q('main a,main button,main select,main input[type=search],main label.fk-check').filter(e=>e.getClientRects().length&&!e.closest('[hidden]')&&!(e.matches('input')&&e.type==='checkbox'));
              const small=t.filter(e=>{const r=e.getBoundingClientRect();return r.height<43.5||(r.width<43.5&&!e.matches('.fk-btn--card'))}).map(e=>(e.className||e.tagName)+'|'+Math.round(e.getBoundingClientRect().width)+'x'+Math.round(e.getBoundingClientRect().height));
              return {sw:document.documentElement.scrollWidth,cw:document.documentElement.clientWidth,small,h1:q('h1').length,n:q('#cat-list > li').length}}""")
            good=o['sw']<=o['cw'] and not o['small'] and o['h1']==1 and not errs and o['n']==(12 if c=='F70' else 7)
            ck(f'{c} {w}px health (no overflow, targets>=44, one h1, no errors, {12 if c=="F70" else 7} cards)',good)
            if not good: print(c,w,o,errs)
            if w in(390,1280): pg.screenshot(path=OUT+f'p2-{c}-{w}.png',full_page=True)
            pg.close()
    pg=b.new_page(viewport={'width':1280,'height':900}); errs=[]; pg.on('pageerror',lambda e:errs.append(str(e)))
    pg.goto(U('F55')); pg.evaluate("localStorage.clear()"); pg.reload(); pg.wait_for_timeout(200)
    ck('F55 h1, title, crumb','Коллекция F55'==pg.inner_text('#cat-title') and pg.inner_text('#cat-crumb')=='Коллекция F55' and 'F55' in pg.title() or 'коллекция' in pg.title())
    ck('configurator link carries collection',pg.get_attribute('#col-cfg-link','href')=='configurator.html?collection=F55')
    ck('switch chips: F55 current, F70 not',pg.get_attribute('[data-col=F55]','aria-current')=='page' and pg.get_attribute('[data-col=F70]','aria-current') is None)
    vis=pg.evaluate("[...document.querySelectorAll('#cat-filters input[name=color]')].filter(i=>i.closest('label').getClientRects().length).map(i=>i.value)")
    ck('F55 color filter shows 4 colors (no lightgray/cashmere)',sorted(vis)==['black','gold','gray','white'])
    pg.goto(U('F70')); pg.wait_for_timeout(200)
    ck('F70 color filter shows 6 colors',pg.evaluate("[...document.querySelectorAll('#cat-filters input[name=color]')].filter(i=>i.closest('label').getClientRects().length).length")==6)
    pg.goto(U('XX')); pg.wait_for_timeout(200); ck('unknown collection falls back to F70',pg.inner_text('#cat-title')=='Коллекция F70')
    pg.goto(U('F70','&category=sockets')); pg.wait_for_timeout(200)
    ck('F70 sockets = 2 cards, title with category','2 из 2' in pg.inner_text('#cat-count') and 'розетки' in pg.inner_text('#cat-title'))
    ck('category chips keep collection','collection=F70' in pg.get_attribute('[data-cat=switches]','href'))
    pg.goto(U('F70')); pg.wait_for_timeout(200)
    n0=pg.locator('#cat-list > li').count()
    pg.check('#cat-filters input[name=color][value=white]'); pg.wait_for_timeout(100); n1=pg.locator('#cat-list > li').count()
    ck('color filter reduces list',0<n1<n0)
    pg.check('#cat-filters input[name=color][value=black]'); pg.wait_for_timeout(100); ck('second color adds (OR)',pg.locator('#cat-list > li').count()>=n1)
    pg.click('#cat-filters [data-reset]'); pg.wait_for_timeout(100); ck('reset restores 12',pg.locator('#cat-list > li').count()==n0 and not pg.locator('#cat-filters input:checked').count())
    pg.fill('#cat-search','zzzz-нет'); pg.press('#cat-search','Enter'); pg.wait_for_timeout(100)
    ck('search with no match: empty state shown, list empty',pg.locator('#cat-empty').is_visible() and pg.locator('#cat-list > li').count()==0)
    pg.click('#cat-empty [data-reset]'); pg.wait_for_timeout(100); ck('empty-state reset restores list',pg.locator('#cat-list > li').count()==n0 and pg.locator('#cat-empty').is_hidden() and pg.input_value('#cat-search')=='')
    first=pg.inner_text('#cat-list > li >> nth=0 .fk-product-card__name, #cat-list > li >> nth=0 h3') if pg.locator('#cat-list > li >> nth=0 h3').count() else ''
    pg.select_option('#cat-sort','name'); pg.wait_for_timeout(100)
    names=pg.evaluate("[...document.querySelectorAll('#cat-list h3')].map(h=>h.textContent.trim())")
    ck('sort by name is alphabetical',names==sorted(names,key=lambda s:s.lower()) or names==sorted(names))
    pg.select_option('#cat-sort','default')
    # карточки
    h=pg.locator('#cat-list [data-fav]').first; h.click()
    ck('heart pressed + saved fk-demo-fav-v1',h.get_attribute('aria-pressed')=='true' and pg.evaluate("!!localStorage.getItem('fk-demo-fav-v1')") and pg.inner_text('[data-fav-count]')=='1')
    pg.locator('#cat-list [data-add]').first.click(); ck('add to cart -> counter 1, fk-demo-cart-v1',pg.inner_text('[data-cart-count]')=='1' and pg.evaluate("!!localStorage.getItem('fk-demo-cart-v1')"))
    ck('card links go to product.html?id=',pg.evaluate("[...document.querySelectorAll('#cat-list a[href*=\"product.html\"]')].every(a=>/product\\.html\\?id=\\d+/.test(a.getAttribute('href')))") and pg.locator('#cat-list a[href*="product.html"]').count()>0)
    # URL хранит фильтр
    pg.check('#cat-filters input[name=color][value=gold]'); pg.wait_for_timeout(100); ck('filter state kept in URL (replaceState)','color=gold' in pg.url or 'gold' in pg.url)
    pg.reload(); pg.wait_for_timeout(200); ck('filter restored after reload',pg.is_checked('#cat-filters input[name=color][value=gold]'))
    ck('no page errors',not errs)
    b.close()
    # мобильная кнопка «Фильтры»
    b=p.chromium.launch(); pg=b.new_page(viewport={'width':390,'height':800}); pg.goto(U('F70')); pg.wait_for_timeout(200)
    ck('mobile: filters toggle visible and collapsed',pg.is_visible('#cat-filters-toggle') and pg.get_attribute('#cat-filters-toggle','aria-expanded')=='false' and not pg.is_visible('#cat-filters'))
    pg.click('#cat-filters-toggle'); ck('mobile: toggle opens filters (aria-expanded true)',pg.get_attribute('#cat-filters-toggle','aria-expanded')=='true' and pg.is_visible('#cat-filters'))
    pg.focus('#cat-filters-toggle'); pg.keyboard.press('Enter'); ck('mobile: Enter closes filters',pg.get_attribute('#cat-filters-toggle','aria-expanded')=='false')
    b.close()
for n,c in r: print('OK ' if c else 'FAIL',n)
print('OK' if all(c for _,c in r) else 'FAIL: есть отклонения')
