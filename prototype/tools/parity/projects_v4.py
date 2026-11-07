"""P8: страница «Проекты» V4 (projects.html) — список карточек проектов; разметка V3 без изменений, css/projects.css идентичен V3.
Проверяет: css/projects.css идентичен V3; 7 ширин (нет горизонтальной прокрутки, ошибок, цели >= 44 px, один h1, SVG-превью отрисованы); 6 карточек со ссылками project.html?id=1..6,
метки «Заглушка», крошки, aria-current в шапке, вводный текст; колонки: 1 на 360, >1 на 1280; страница не создаёт состояния в localStorage; фокус по Tab доходит до карточек.
Запуск: python3 -I projects_v4.py (сервер на 8081 из prototype/)."""
import os, filecmp
from playwright.sync_api import sync_playwright
B=os.environ.get('FK_BASE','http://localhost:8081/homepage-v4/'); OUT=os.environ.get('FK_OUT','/tmp/'); r=[]
def ck(n,c): r.append((n,bool(c)))
ROOT=os.path.join(os.path.dirname(os.path.abspath(__file__)),'..','..')
ck('css/projects.css identical to V3',filecmp.cmp(ROOT+'/homepage-v3/css/projects.css',ROOT+'/homepage-v4/css/projects.css',shallow=False))
with sync_playwright() as p:
    b=p.chromium.launch()
    for w in (360,390,768,1024,1280,1440,1920):
        pg=b.new_page(viewport={'width':w,'height':800}); errs=[]
        pg.on('pageerror',lambda e:errs.append(str(e)))
        pg.on('response',lambda x:x.status>=400 and 'localhost' in x.url and errs.append(f'{x.status} {x.url}'))
        pg.on('console',lambda m:m.type=='error' and 'Failed to load resource' not in m.text and errs.append(m.text))
        pg.goto(B+'projects.html'); pg.evaluate('document.fonts.ready'); pg.wait_for_timeout(200)
        o=pg.evaluate("""()=>{const q=s=>[...document.querySelectorAll(s)];
          const t=q('main a,main button').filter(e=>e.getClientRects().length);
          const small=t.filter(e=>{const r=e.getBoundingClientRect();return r.height<43.5||r.width<43.5}).map(e=>(e.className||e.tagName)+'|'+Math.round(e.getBoundingClientRect().width)+'x'+Math.round(e.getBoundingClientRect().height));
          const xs=new Set(q('.fk-project-card').map(e=>Math.round(e.getBoundingClientRect().left)));
          const svgs=q('.fk-project-card__media svg').every(s=>{const r=s.getBoundingClientRect();return r.width>100&&r.height>60});
          return {sw:document.documentElement.scrollWidth,cw:document.documentElement.clientWidth,small,h1:q('h1').length,n:q('.fk-project-card').length,cols:xs.size,svgs}}""")
        good=o['sw']<=o['cw'] and not o['small'] and o['h1']==1 and o['n']==6 and o['svgs'] and not errs
        ck(f'{w}px health (no overflow, targets>=44, one h1, 6 cards, previews drawn, no errors)',good)
        if not good: print(w,o,errs)
        if w==360: ck('360px: one column',o['cols']==1)
        if w==1280: ck('1280px: several columns',o['cols']>=2)
        if w in(390,1280): pg.screenshot(path=OUT+f'p8-{w}.png',full_page=True)
        pg.close()
    pg=b.new_page(viewport={'width':1280,'height':900}); errs=[]; pg.on('pageerror',lambda e:errs.append(str(e)))
    pg.goto(B+'projects.html'); pg.evaluate("localStorage.clear()"); pg.reload(); pg.wait_for_timeout(200)
    ck('h1, crumbs, intro, demo note','Проекты'==pg.inner_text('h1') and pg.inner_text('.fk-crumbs [aria-current=page]')=='Проекты' and pg.locator('.fk-prj-intro').count()==1 and pg.locator('.fk-note--proto').count()>=1)
    ck('cards link to project.html?id=1..6',pg.evaluate("[...document.querySelectorAll('.fk-project-card')].map(a=>a.getAttribute('href'))")==[f'project.html?id={i}' for i in range(1,7)])
    ck('each card has «Заглушка» tag and name',pg.locator('.fk-project-card .fk-tag').count()==6 and all(t.startswith('Демонстрационный проект') for t in pg.evaluate("[...document.querySelectorAll('.fk-project-card__name')].map(e=>e.textContent.trim())")))
    ck('header «Проекты» is current',pg.get_attribute('.fk-nav__link[href="projects.html"]','aria-current')=='page')
    pg.focus('.fk-nav__link[href="projects.html"]')
    for _ in range(60):
        pg.keyboard.press('Tab')
        if pg.evaluate("!!document.activeElement.closest('.fk-project-card')"): break
    ck('Tab reaches a project card',pg.evaluate("!!document.activeElement.closest('.fk-project-card')"))
    ck('no state written to localStorage',pg.evaluate("Object.keys(localStorage).length")==0)
    ck('no page errors',not errs)
    b.close()
for n,c in r: print('OK ' if c else 'FAIL',n)
print('OK' if all(c for _,c in r) else 'FAIL: есть отклонения')
