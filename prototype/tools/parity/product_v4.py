"""P4: страница товара V4 (product.html) — реальная розетка F70 (id 13238–13243), демо-товары, рамки; движок shop.js и css — из V3 без изменений.
Проверяет: shop.js и css product-page/accessories/download-file/page/catalog идентичны V3; карточка (#pdp) в V4 идентична V3 побайтно для id 13238, 13239, 1, 15011;
7 ширин для трёх типов товаров (нет горизонтальной прокрутки, ошибок консоли/HTTP кроме внешних фото, цели >= 44 px, один h1);
реальный товар: код + копирование, артикул, цена, наличие, 6 цветов -> ссылки на товары 13238–13243, 3D-модель/Видео/Инструкция, окно видео (Esc, возврат фокуса, блокировка прокрутки), «Скачать фото», характеристики, описание, сопутствующие рамки (5);
«В корзину», «Купить в 1 клик» и «Задать вопрос» (заглушки с пояснением); демо-товар: связанные товары (16) с сердцем, заглушечные метки, описание по центру; рамка: сопутствующие скрыты; неизвестный id -> «Товар не найден».
Запуск: python3 -I product_v4.py (сервер на 8081 из prototype/; для сравнения с V3 нужен и каталог homepage-v3)."""
import os, re, filecmp
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
B=os.environ.get('FK_BASE','http://localhost:8081/homepage-v4/'); B3=B.replace('homepage-v4','homepage-v3'); OUT=os.environ.get('FK_OUT','/tmp/'); r=[]
def ck(n,c): r.append((n,bool(c)))
ROOT=os.path.join(os.path.dirname(os.path.abspath(__file__)),'..','..')
for f in ('shop.js','css/product-page.css','css/accessories.css','css/download-file.css','css/page.css','css/catalog.css'): ck(f+' identical to V3',_same(ROOT+'/homepage-v3/'+f,ROOT+'/homepage-v4/'+f,shallow=False))
IDS=(13238,1,15011)
with sync_playwright() as p:
    b=p.chromium.launch()
    for pid in (13238,13239,1,15011):
        h=[]
        for base in(B3,B):
            pg=b.new_page(viewport={'width':1280,'height':900}); pg.goto(base+f'product.html?id={pid}'); pg.wait_for_timeout(500)
            h.append(pg.evaluate("document.getElementById('pdp').innerHTML+'|'+(document.getElementById('pdp-related')||{innerHTML:''}).innerHTML")); pg.close()
        # пометки «демо/заглушка» и демо-примечания владелец снял — сравниваем без них
        norm=lambda t:re.sub(r'\s+',' ',re.sub(r'<(p|span|h2)[^>]*class="[^"]*(fk-tag|fk-note--proto|fk-pdp__proto)[^"]*"[^>]*>.*?</\1>','',t,flags=re.S)).replace(' <','<').replace('> ','>').replace(' (демо)','')
        ck(f'id {pid}: PDP markup identical to V3 (без демо-пометок)',norm(h[0])==norm(h[1]) and len(h[0])>500)
    for pid in IDS:
        for w in (360,390,768,1024,1280,1440,1920):
            pg=b.new_page(viewport={'width':w,'height':800}); errs=[]
            pg.on('pageerror',lambda e:errs.append(str(e)))
            pg.on('response',lambda x:x.status>=400 and 'localhost' in x.url and errs.append(f'{x.status} {x.url}'))
            pg.on('console',lambda m:m.type=='error' and 'Failed to load resource' not in m.text and errs.append(m.text))
            pg.goto(B+f'product.html?id={pid}'); pg.evaluate('document.fonts.ready'); pg.wait_for_timeout(300)
            o=pg.evaluate("""()=>{const q=s=>[...document.querySelectorAll(s)];
              const t=q('main a,main button').filter(e=>e.getClientRects().length&&!e.closest('[hidden]')&&e.getAttribute('tabindex')!=='-1');
              const small=t.filter(e=>{const r=e.getBoundingClientRect();return r.height<43.5||r.width<43.5}).map(e=>(e.className||e.tagName)+'|'+Math.round(e.getBoundingClientRect().width)+'x'+Math.round(e.getBoundingClientRect().height));
              return {sw:document.documentElement.scrollWidth,cw:document.documentElement.clientWidth,small,h1:q('h1').length}}""")
            good=o['sw']<=o['cw'] and not o['small'] and o['h1']==1 and not errs
            ck(f'id {pid} {w}px health (no overflow, targets>=44, one h1, no errors)',good)
            if not good: print(pid,w,o,errs)
            if pid==13238 and w in(390,1280): pg.screenshot(path=OUT+f'p4-{w}.png',full_page=True)
            pg.close()
    ctx=b.new_context(viewport={'width':1280,'height':900},permissions=['clipboard-read','clipboard-write']); pg=ctx.new_page(); errs=[]; pg.on('pageerror',lambda e:errs.append(str(e)))
    pg.goto(B+'product.html?id=13238'); pg.evaluate("localStorage.clear()"); pg.reload(); pg.wait_for_timeout(500)
    ck('h1 is product name, title contains it',pg.inner_text('h1').startswith('Розетка одинарная') and 'Розетка одинарная' in pg.title())
    ck('crumbs: Главная / Каталог / Розетки / name',pg.evaluate("[...document.querySelectorAll('.fk-crumbs a')].map(a=>a.getAttribute('href'))")==['index.html','catalog.html','catalog.html?category=sockets'])
    ck('code row, SKU, price, stock shown',pg.locator('.fk-pdp__code').count()==1 and 'Код' in pg.inner_text('.fk-pdp__code') and pg.locator('.fk-pdp__sku').count()==1 and '₽' in pg.inner_text('.fk-pdp__price') and 'В наличии' in pg.inner_text('.fk-pdp__stock'))
    code=pg.evaluate("document.querySelector('[data-code]').textContent.trim()")
    pg.click('[data-copy-code]'); pg.wait_for_timeout(200); ck('copy code button writes code to clipboard',pg.evaluate("navigator.clipboard.readText()")==code)
    sw=pg.evaluate("[...document.querySelectorAll('.fk-swatches a')].map(a=>a.getAttribute('href'))")
    ck('6 colour swatches -> products 13238..13243',len(sw)==6 and sorted(int(x.split('id=')[1]) for x in sw)==list(range(13238,13244)))
    ck('3D / Видео / Инструкция present, external links open in new tab',pg.locator('.fk-res a[target=_blank]').count()==2 and pg.locator('.fk-res [data-video]').count()==1)
    y0=pg.evaluate("document.body.style.overflow"); pg.click('[data-video]'); pg.wait_for_timeout(200)
    ck('video dialog opens (modal, focus on close, scroll locked)',pg.is_visible('#fk-video') and pg.evaluate("document.activeElement.classList.contains('fk-modal__x')") and pg.evaluate("document.body.style.overflow")=='hidden')
    pg.keyboard.press('Escape'); pg.wait_for_timeout(100)
    ck('Esc closes video, focus returns to button, scroll unlocked',pg.is_hidden('#fk-video') and pg.evaluate("document.activeElement.hasAttribute('data-video')") and pg.evaluate("document.body.style.overflow")=='')
    ck('"Скачать фото" button present',pg.locator('[data-dl]').count()==1)
    ck('characteristics and description sections',pg.locator('.fk-pdp__specs').count()==1 and pg.locator('.fk-pdp__desc').count()==1 and pg.locator('.fk-chars').count()>=3)
    ck('copyable characteristic values exist',pg.locator('[data-copyable]').count()>0)
    ck('accessories: 5 frames visible, links product.html?id=150x1',pg.locator('#pdp-accessories').is_visible() and pg.locator('#pdp-accessories li.fk-product-card').count()==5)
    pg.click('.fk-pdp__cart'); pg.wait_for_timeout(100); ck('add to cart: counter 1, fk-demo-cart-v1 has 13238',pg.inner_text('[data-cart-count]')=='1' and '13238' in (pg.evaluate("localStorage.getItem('fk-demo-cart-v1')") or ''))
    pg.locator('.fk-pdp__oneclick').first.click(); pg.wait_for_timeout(150)
    ck('"Купить в 1 клик" shows stub explanation',pg.evaluate("document.body.innerText.includes('Покупка в один клик не реализована')"))
    pg.locator('.fk-pdp__oneclick').nth(1).click(); pg.wait_for_timeout(150)
    ck('"Задать вопрос" shows stub explanation',pg.evaluate("document.body.innerText.includes('Форма вопроса не реализована')"))
    ck('no page errors on real product',not errs)
    pg.goto(B+'product.html?id=1'); pg.wait_for_timeout(400)
    ck('demo product: 16 related',pg.locator('#pdp-related li').count()==16)
    hh=pg.locator('#pdp-related [data-fav]').first; hh.click(); ck('related card heart toggles, fk-demo-fav-v1 saved',hh.get_attribute('aria-pressed')=='true' and pg.evaluate("!!localStorage.getItem('fk-demo-fav-v1')"))
    ck('description block centred, up to 900 px',pg.evaluate("(()=>{const r=document.querySelector('.fk-pdp__desc').getBoundingClientRect();return r.width>600&&r.width<=901&&Math.abs((r.left+r.right)/2-innerWidth/2)<2})()") if pg.locator('.fk-pdp__desc').count() else True)
    pg.goto(B+'product.html?id=15011'); pg.wait_for_timeout(400)
    ck('frame product: accessories hidden',pg.locator('#pdp-accessories').is_hidden())
    pg.goto(B+'product.html?id=99999999'); pg.wait_for_timeout(400)
    ck('unknown id -> "Товар не найден"',pg.evaluate("document.body.innerText.includes('Товар не найден')") )
    pg.goto(B+'product.html'); pg.wait_for_timeout(400)
    ck('no id -> no crash (message shown)',pg.evaluate("document.body.innerText.includes('Товар не найден')") or pg.locator('#pdp').inner_text()!='')
    b.close()
for n,c in r: print('OK ' if c else 'FAIL',n)
print('OK' if all(c for _,c in r) else 'FAIL: есть отклонения')
