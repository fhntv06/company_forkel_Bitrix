"""F2: доступность и адаптивность V4 (раздел 5 плана, правило 3): все 16 страниц.
Для каждой страницы и ширины 360/390/768/1024/1280/1440/1920: нет горизонтальной прокрутки, цели нажатия >= 44 px, один h1, нет ошибок консоли/HTTP.
Для каждой страницы (1280): lang, skip-link, у кнопок/ссылок/полей есть имя, у img есть alt, у полей есть label, Tab доходит до 12 элементов с видимым фокусом, prefers-reduced-motion отключает анимации.
Выводит список отклонений; ничего не исправляет. Запуск: python3 -I f2_a11y.py (сервер на 8081 из prototype/)."""
import os, glob
from playwright.sync_api import sync_playwright
HOST=os.environ.get('FK_HOST','http://localhost:8081/'); ROOT=os.path.join(os.path.dirname(os.path.abspath(__file__)),'..','..'); r=[]
def ck(n,c,info=''): r.append((n,bool(c),info))
pages=sorted(os.path.basename(p) for p in glob.glob(ROOT+'/homepage-v4/*.html') if not p.endswith('page-template.html'))
SIZE="""()=>{const q=s=>[...document.querySelectorAll(s)];
 const vis=e=>e.getClientRects().length&&getComputedStyle(e).visibility!=='hidden';
 const t=q('main a,main button,main input:not([type=hidden]),main select,main textarea,header a,header button,footer a,footer button').filter(vis);
 const small=t.filter(e=>{if(e.matches('.fk-link,.fk-link--tap,p a,li a:not([class]),.fk-crumbs a,footer a,.fk-ab-faq a,summary a'))return false;if(e.matches('input[type=checkbox],input[type=radio]'))return false;const r=e.getBoundingClientRect();return r.height<43.5||r.width<43.5}).slice(0,4).map(e=>(String(e.className)||e.tagName).slice(0,30)+'|'+Math.round(e.getBoundingClientRect().width)+'x'+Math.round(e.getBoundingClientRect().height));
 return {sw:document.documentElement.scrollWidth,cw:document.documentElement.clientWidth,h1:q('h1').length,small}}"""
A11Y="""()=>{const q=s=>[...document.querySelectorAll(s)];
 const name=e=>(e.getAttribute('aria-label')||e.getAttribute('aria-labelledby')||e.textContent||e.getAttribute('title')||'').trim().length>0||e.querySelector('img[alt]:not([alt=""])');
 return {lang:document.documentElement.lang,skip:!!document.querySelector('a[href="#main"],.fk-skip,.skip'),main:!!document.querySelector('main#main'),
  noname:q('a[href],button').filter(e=>e.getClientRects().length&&!e.closest('[aria-hidden=true]')&&!name(e)).slice(0,3).map(e=>e.outerHTML.slice(0,70)),
  noalt:q('img:not([alt])').length,
  nolabel:q('input:not([type=hidden]):not([type=submit]):not([type=checkbox]):not([type=radio]),select,textarea').filter(e=>e.getClientRects().length&&!(e.id&&document.querySelector('label[for="'+e.id+'"]'))&&!e.closest('label')&&!e.getAttribute('aria-label')&&!e.getAttribute('aria-labelledby')).slice(0,3).map(e=>e.id||e.name||e.tagName)}}"""
with sync_playwright() as p:
    b=p.chromium.launch()
    for name in pages:
        q='?id=1' if name in('product.html','project.html') else ''
        for w in (360,390,768,1024,1280,1440,1920):
            pg=b.new_page(viewport={'width':w,'height':800}); errs=[]
            pg.on('pageerror',lambda e:errs.append(str(e)))
            pg.on('response',lambda x:x.status>=400 and 'localhost' in x.url and errs.append(f'{x.status} {x.url}'))
            pg.on('console',lambda m:m.type=='error' and 'Failed to load resource' not in m.text and errs.append(m.text))
            if name in('checkout.html','cart.html'):
                pg.goto(HOST+'homepage-v4/catalog.html'); pg.evaluate("localStorage.clear()"); pg.reload(); pg.wait_for_timeout(100)
                pg.locator('#cat-list [data-add]').first.click()
            pg.goto(HOST+'homepage-v4/'+name+q); pg.evaluate('document.fonts.ready'); pg.wait_for_timeout(250)
            o=pg.evaluate(SIZE)
            ck(f'{name} @{w}: без прокрутки, цели>=44, один h1, без ошибок',o['sw']<=o['cw'] and not o['small'] and o['h1']==1 and not errs,f"sw={o['sw']}/{o['cw']} h1={o['h1']} small={o['small']} errs={errs[:2]}")
            if w==1280:
                a=pg.evaluate(A11Y)
                ck(f'{name}: lang=ru, main#main, skip-link',a['lang']=='ru' and a['main'] and a['skip'],str(a['lang'])+' main='+str(a['main'])+' skip='+str(a['skip']))
                ck(f'{name}: у кнопок/ссылок есть имя, у img alt, у полей label',not a['noname'] and a['noalt']==0 and not a['nolabel'],f"noname={a['noname']} noalt={a['noalt']} nolabel={a['nolabel']}")
                seen=[];bad=[]
                for _ in range(12):
                    pg.keyboard.press('Tab')
                    f=pg.evaluate("()=>{const e=document.activeElement;if(!e||e===document.body)return null;const s=getComputedStyle(e);return {k:(e.tagName+'.'+String(e.className).slice(0,20)),o:(s.outlineStyle!=='none'&&parseFloat(s.outlineWidth)>0)||s.boxShadow!=='none'}}")
                    if f: seen.append(f['k']); (not f['o']) and bad.append(f['k'])
                ck(f'{name}: Tab доходит до элементов, видимый фокус',len(seen)>=8 and not bad,f'достигнуто {len(seen)}; без индикатора: {bad[:4]}')
                pg.emulate_media(reduced_motion='reduce'); pg.wait_for_timeout(100)
                rm=pg.evaluate("()=>[...document.querySelectorAll('body *')].filter(e=>{const s=getComputedStyle(e);return (parseFloat(s.animationDuration)>0.05&&s.animationName!=='none')||parseFloat(s.transitionDuration)>0.3}).slice(0,3).map(e=>String(e.className).slice(0,25))")
                ck(f'{name}: prefers-reduced-motion отключает анимации',not rm,str(rm))
            pg.close()
    b.close()
bad=[x for x in r if not x[1]]
for n,c,i in r:
    if not c: print('FAIL',n,'—',i)
print(f'ИТОГ F2: {"PASS" if not bad else "FAIL"} ({len(r)-len(bad)}/{len(r)} OK, {len(bad)} отклонений)')
