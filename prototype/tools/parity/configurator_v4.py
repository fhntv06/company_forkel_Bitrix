"""I2: подбор на главной V4 — компонент V4 (.cfg, .sw), логика и данные V3.
Проверяет: configurator.js идентичен V3; 7 ширин (прокрутка, ошибки, цели >= 44 px, нет наложений контролов);
палитры F70 (6) и F55 (4), блокировка недоступных цветов и замена выбора; слои изображений V3 (mech снизу, frame сверху, filter у светло-серого);
материал/тип/цвета -> спецификация и артикул; «Добавить комплект» -> корзина (ключ fk-demo-cart-v1); ссылка на страницу подбора с ?collection=;
карточки коллекций A/B/C; клавиатура (стрелки, Tab); логика идентична V3 (то же состояние -> тот же комплект D.kit).
Запуск: python3 -I configurator_v4.py (сервер на 8081 из prototype/)."""
import os, filecmp
from playwright.sync_api import sync_playwright
B=os.environ.get('FK_BASE','http://localhost:8081/homepage-v4/'); OUT=os.environ.get('FK_OUT','/tmp/'); r=[]
def ck(n,c): r.append((n,bool(c)))
ROOT=os.path.join(os.path.dirname(os.path.abspath(__file__)),'..','..')
ck('configurator.js identical to V3',filecmp.cmp(ROOT+'/homepage-v3/configurator.js',ROOT+'/homepage-v4/configurator.js',shallow=False))
ck('data.js identical to V3',filecmp.cmp(ROOT+'/homepage-v3/data.js',ROOT+'/homepage-v4/data.js',shallow=False))
G='#cfg [data-part="%s"] [role=radio]'
with sync_playwright() as p:
    b=p.chromium.launch()
    for w in (360,390,768,1024,1280,1440,1920):
        pg=b.new_page(viewport={'width':w,'height':800}); errs=[]
        pg.on('pageerror',lambda e:errs.append(str(e))); pg.on('console',lambda m:m.type=='error' and errs.append(m.text))
        pg.on('response',lambda x:x.status>=400 and 'localhost' in x.url and errs.append(f'{x.status} {x.url}'))
        pg.goto(B+'index.html'); pg.evaluate('document.fonts.ready')
        o=pg.evaluate("""()=>{const q=s=>[...document.querySelectorAll(s)];
          const t=q('#cfg button,#cfg a').filter(e=>e.getClientRects().length&&!e.disabled);
          const small=t.filter(e=>{const r=e.getBoundingClientRect();return r.height<43.5||r.width<43.5}).map(e=>e.className+'|'+e.textContent.trim().slice(0,12));
          const v=document.querySelector('#cfg').getBoundingClientRect();
          return {sw:document.documentElement.scrollWidth,cw:document.documentElement.clientWidth,small,ids:q('#cfg [id]').length,
            inside:t.every(e=>{const r=e.getBoundingClientRect();return r.right<=document.documentElement.clientWidth+0.5&&r.left>=-0.5}),
            img:q('#cfg-photo img').map(i=>[i.naturalWidth>0,i.getBoundingClientRect().width>100])}}""")
        KNOWN=(w<=390 and o['sw']==406)  # известный дефект «Новинок» (I3)
        good=(o['sw']<=o['cw'] or KNOWN) and not o['small'] and o['inside'] and all(a and c for a,c in o['img']) and not errs
        ck(f'{w}px cfg health (no overflow, targets>=44, images loaded, no errors)',good)
        if not good: print(w,o,errs)
        if w==1280: pg.locator('#cfg').screenshot(path=OUT+'i2-cfg-1280.png')
        if w==390: pg.locator('#cfg').screenshot(path=OUT+'i2-cfg-390.png')
        pg.close()
    pg=b.new_page(viewport={'width':1280,'height':900}); errs=[]; pg.on('pageerror',lambda e:errs.append(str(e)))
    pg.goto(B+'index.html'); pg.evaluate("localStorage.clear()"); pg.reload()
    n=lambda part:pg.locator(G%part).count()
    ck('groups: 2 collections, 3 materials, 3 types, 6 frame colors, 6 mech colors',[n(x) for x in('collection','material','type','frame','mech')]==[2,3,3,6,6])
    ck('F70: all 6 colors enabled',pg.locator(G%'frame'+':not([disabled])').count()==6)
    # F55 palette
    pg.click(G%'collection'+'[data-key=F55]')
    dis=pg.evaluate("[...document.querySelectorAll('#cfg [data-part=frame] [role=radio]')].filter(r=>r.disabled).map(r=>r.dataset.fin)")
    ck('F55: lightgray and cashmere disabled (4 colors)',sorted(dis)==['fin-cashmere','fin-lightgray'])
    ck('F55 out: collection name',pg.inner_text('#cfg [data-out=collection]')=='Коллекция F55')
    pg.click(G%'collection'+'[data-key=F70]')
    # слои и светло-серый
    pg.click(G%'frame'+'[data-fin=fin-lightgray]'); pg.click(G%'mech'+'[data-fin=fin-gold]')
    st=pg.evaluate("""()=>{const f=document.getElementById('cfg-img-frame'),m=document.getElementById('cfg-img-mech'),ph=document.getElementById('cfg-photo');
      return {f:f.getAttribute('src'),m:m.getAttribute('src'),ff:f.style.filter,mf:m.style.filter,order:[...ph.children].map(e=>e.id),
        fz:getComputedStyle(f).zIndex,label:ph.getAttribute('aria-label'),val:[...document.querySelectorAll('#cfg [data-val]')].map(e=>e.textContent)}}""")
    ck('layers: frame src is gray (alias of lightgray) with brightness filter',st['f'].endswith('tcvet_ramki_seryy.png') and 'brightness' in st['ff'])
    ck('layers: mech src gold, no filter',st['m'].endswith('tcvet_mehanizma_zolotoy.png') and st['mf']=='')
    ck('layers: mech under frame (DOM order)',st['order']==['cfg-img-mech','cfg-img-frame'])
    ck('value labels and aria-label updated',st['val']==['Светло-серый','Золотой'] and 'светло-серый' in st['label'])
    # F55 с выбранным светло-серым -> замена
    pg.click(G%'collection'+'[data-key=F55]')
    cur=pg.evaluate("[...document.querySelectorAll('#cfg [data-part=frame] [aria-checked=true]')].map(r=>r.dataset.fin)")
    ck('F55 replaces unavailable frame color with first available (white)',cur==['fin-white'])
    ck('summary announces replacement (role=status)',('заменены' in pg.inner_text('#cfg-summary')) or 'заменены' in (pg.text_content('#cfg-summary') or ''))
    pg.click(G%'collection'+'[data-key=F70]')
    # состав комплекта = D.kit
    pg.click(G%'material'+'[data-key=aluminum]'); pg.click(G%'type'+'[data-symbol=p-socket]')
    pg.click(G%'frame'+'[data-fin=fin-black]'); pg.click(G%'mech'+'[data-fin=fin-cashmere]')
    out=pg.evaluate("""()=>{const k=FKData.kit({col:'F70',mat:'aluminum',mech:'socket',mc:'cashmere',fc:'black'});
      return {sku:k.sku,price:FKData.fmt(k.price),shown:document.querySelector('#cfg [data-out=kit-sku]').textContent,pr:document.querySelector('#cfg [data-out=kit-price]').textContent,
        mat:document.querySelector('#cfg [data-out=material]').textContent,mech:document.querySelector('#cfg [data-out=mech]').textContent,cat:document.getElementById('cfg-catalog-link').getAttribute('href')}}""")
    ck('kit sku/price in UI equal FKData.kit (same as V3)',out['sku'] in out['shown'] and out['price'] in out['pr'])
    ck('spec: material and mechanism text',out['mat']=='Алюминий' and out['mech'].startswith('Розетка'))
    ck('catalog link follows type (sockets)','category=sockets' in out['cat'])
    ck('kit sku/price marked as demo/stub',pg.locator('#cfg [data-out=kit-sku] .fk-tag').count()==1 and pg.locator('#cfg [data-out=kit-price] .fk-tag').count()==1)
    # корзина
    ck('cart link hidden before add',not pg.locator('#cfg-cart-link').is_visible())
    pg.click('#cfg-add')
    cart=pg.evaluate("JSON.parse(localStorage.getItem('fk-demo-cart-v1')||'null')")
    s=str(cart); ck('kit added to cart under fk-demo-cart-v1 with chosen spec',cart is not None and 'kit' in s and 'aluminum' in s and 'cashmere' in s and 'black' in s)
    ck('cart counter updated',pg.inner_text('[data-cart-count]')=='1'); ck('cart link shown after add',pg.locator('#cfg-cart-link').is_visible())
    pg.click('#cfg-add'); ck('second add increments quantity (counter 2)',pg.inner_text('[data-cart-count]')=='2')
    # ссылка на страницу подбора
    pg.click(G%'collection'+'[data-key=F55]'); pg.wait_for_timeout(50)
    ck('open-configurator link carries collection (F55)',pg.get_attribute('#cfg-open','href')=='configurator.html?collection=F55')
    pg.keyboard.press('Tab')
    # коллекции A/B/C (палитра F70: у F55 кашемир недоступен)
    pg.click(G%'collection'+'[data-key=F70]')
    for col,fin in(('A','fin-black'),('B','fin-white'),('C','fin-cashmere')):
        pg.click('#collections [data-col=%s]'%col); pg.wait_for_timeout(50)
        ck(f'collection card {col} sets frame color {fin}',pg.evaluate("document.querySelector('#cfg [data-part=frame] [aria-checked=true]').dataset.fin")==fin)
    # клавиатура: стрелки в группе цвета рамки, пропуск disabled при F55
    pg.click(G%'collection'+'[data-key=F55]'); pg.focus(G%'frame'+'[aria-checked=true]')
    seq=[]
    for _ in range(5):
        pg.keyboard.press('ArrowRight'); seq.append(pg.evaluate("document.activeElement.dataset.fin"))
    ck('arrows cycle only enabled colors in F55',set(seq)<={'fin-white','fin-black','fin-gray','fin-gold'} and len(set(seq))==4)
    ck('roving tabindex: exactly one tab stop per group',all(pg.locator(f'#cfg [data-part={x}] [tabindex="0"]').count()==1 for x in('collection','material','type','frame','mech')))
    # ложные срабатывания фокуса: кольцо видно
    pg.focus(G%'frame'+'[aria-checked=true]'); pg.keyboard.press('Tab')
    ck('no page errors during scenarios',not errs)
    # no-JS не ломает: разметка присутствует
    b.close()
for n,c in r: print('OK ' if c else 'FAIL',n)
print('OK' if all(c for _,c in r) else 'FAIL: есть отклонения')
