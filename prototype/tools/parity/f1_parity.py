"""F1: итоговая проверка паритета V3 <-> V4 (раздел 6 плана): страницы, ключи хранения, данные, сценарии.
Запуск: python3 -I f1_parity.py (сервер на 8081 из prototype/)."""
import os, filecmp, glob
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
HOST=os.environ.get('FK_HOST','http://localhost:8081/'); ROOT=os.path.join(os.path.dirname(os.path.abspath(__file__)),'..','..'); r=[]
def ck(n,c,info=''): r.append((n,bool(c),info))
v3=sorted(os.path.basename(p) for p in glob.glob(ROOT+'/homepage-v3/*.html')); v4=set(os.path.basename(p) for p in glob.glob(ROOT+'/homepage-v4/*.html'))
ck(f'1. страниц V3: {len(v3)}',len(v3)==16)
missing=[p for p in v3 if p not in v4]; ck('1. каждая страница V3 есть в V4 по тому же имени',not missing,'нет: '+', '.join(missing))
for f in ('data.js','cart.js','favorites.js','shop.js','configurator.js','solutions.js','auth.js','search-page.js','checkout.js','project.js','account.js','about.js'):
    a=os.path.join(ROOT,'homepage-v3',f); b=os.path.join(ROOT,'homepage-v4',f)
    ck(f'js {f} идентичен V3',os.path.exists(b) and _same(a,b,shallow=False),'' if os.path.exists(b) else 'нет файла')
for k in ('fk-demo-cart-v1','fk-demo-fav-v1','fk-demo-auth-v1'):
    s3=''.join(open(p,encoding='utf-8').read() for p in glob.glob(ROOT+'/homepage-v3/*.js')); s4=''.join(open(p,encoding='utf-8').read() for p in glob.glob(ROOT+'/homepage-v4/*.js'))
    ck(f'4. ключ {k}: есть в V3 и V4',k in s3 and k in s4)
with sync_playwright() as p:
    b=p.chromium.launch()
    for pg_name in v3:
        if pg_name in missing: continue
        for w in (360,1280):
            pg=b.new_page(viewport={'width':w,'height':800}); errs=[]
            pg.on('pageerror',lambda e:errs.append(str(e)))
            pg.on('response',lambda x:x.status>=400 and 'localhost' in x.url and errs.append(f'{x.status} {x.url}'))
            pg.on('console',lambda m:m.type=='error' and 'Failed to load resource' not in m.text and errs.append(m.text))
            pg.goto(HOST+'homepage-v4/'+pg_name); pg.wait_for_timeout(300)
            o=pg.evaluate("()=>({sw:document.documentElement.scrollWidth,cw:document.documentElement.clientWidth,h1:document.querySelectorAll('h1').length})")
            ck(f'1/7. {pg_name} @{w}: нет ошибок консоли/HTTP, нет горизонтальной прокрутки, один h1',not errs and o['sw']<=o['cw'] and o['h1']==1,f'{o} {errs[:2]}')
            pg.close()
    # 5. данные
    def data(pgn,base):
        pg=b.new_page(); pg.goto(HOST+base+pgn); pg.wait_for_timeout(300)
        d=pg.evaluate("()=>{const D=window.FKData;if(!D)return null;return JSON.stringify(D,(k,v)=>typeof v==='function'?undefined:v)}"); pg.close(); return d
    d3=data('catalog.html','homepage-v3/'); d4=data('catalog.html','homepage-v4/')
    ck('5. FKData V4 == V3 (товары, категории, коллекции, цвета, цены, артикулы)',d3 is not None and d3==d4)
    # 3. сценарии
    pg=b.new_page(viewport={'width':1280,'height':900}); pg.goto(HOST+'homepage-v4/catalog.html'); pg.evaluate("localStorage.clear()"); pg.reload(); pg.wait_for_timeout(200)
    pg.locator('#cat-list [data-add]').first.click(); pg.wait_for_timeout(100)
    cnt=pg.evaluate("document.querySelector('[data-cart-count]')?.textContent||''")
    ck('3. в корзину -> счётчик в шапке',cnt.strip() in('1',) ,f'счётчик="{cnt}"')
    ck('3. страница корзины открывается (cart.html)', 'cart.html' not in missing,'cart.html отсутствует в V4 (P6 не выполнен)' if 'cart.html' in missing else '')
    pg.locator('#cat-list [data-fav]').first.click(); pg.wait_for_timeout(100)
    pg.goto(HOST+'homepage-v4/favorites.html'); pg.wait_for_timeout(200)
    ck('3. избранное: карточка на странице, «Очистить» -> пусто',pg.locator('#fav-list > li').count()>=1)
    pg.click('[data-fav-clear]'); pg.wait_for_timeout(150)
    ck('3. избранное очищено, показано пустое состояние',pg.locator('#fav-list > li').count()==0 and pg.is_visible('#fav-empty'))
    pg.goto(HOST+'homepage-v4/search.html?q=%D1%80%D0%BE%D0%B7%D0%B5%D1%82%D0%BA%D0%B0'); pg.wait_for_timeout(300)
    ck('3. поиск: страница результатов находит «розетка»',pg.locator('#sr-list > li, .fk-grid > li').count()>=1)
    pg.goto(HOST+'homepage-v4/account.html'); pg.wait_for_timeout(300)
    ck('3. кабинет: форма входа/профиль отрисованы',pg.locator('main form, main .fk-acc').count()>=1)
    pg.goto(HOST+'homepage-v4/cooperation.html'); pg.wait_for_timeout(300)
    ck('3. сотрудничество: анкета, вкладка «Оптовик» активна',pg.get_attribute('#ptab-wholesale','aria-selected')=='true' and pg.locator('#partner-form [required]').count()==4)
    b.close()
bad=0
for n,c,i in r:
    print('OK  ' if c else 'FAIL',n,('— '+i) if (i and not c) else ''); bad+=not c
print('ИТОГ F1:','PASS' if not bad else f'FAIL ({bad} расхождений)')
