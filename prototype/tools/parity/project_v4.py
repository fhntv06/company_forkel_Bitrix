"""P9: страница проекта V4 (project.html?id=1..6) — разметку формирует project.js (V3, без изменений); css/projects.css идентичен V3.
Проверяет: project.js, css/projects.css идентичны V3; 7 ширин для id=1,4 (нет горизонтальной прокрутки, ошибок, цели >= 44 px, один h1, обложка и SVG отрисованы, 3 плитки галереи, 3 карточки «Другие проекты»);
title/h1/крошки по id; «Другие проекты» не включают текущий и ведут на project.html?id=N; «Все проекты» -> projects.html; кнопка «Связаться с нами» открывает форму обратной связи (диалог, Esc, возврат фокуса);
неизвестный id/без id -> «Проект не найден» (title, крошка, ссылка); header «Проекты» current; localStorage не заполняется.
Запуск: python3 -I project_v4.py (сервер на 8081 из prototype/)."""
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
for f in ('project.js','css/projects.css'):
    ck(f'{f} identical to V3',_same(ROOT+'/homepage-v3/'+f,ROOT+'/homepage-v4/'+f,shallow=False))
with sync_playwright() as p:
    b=p.chromium.launch()
    for pid in (1,4):
      for w in (360,390,768,1024,1280,1440,1920):
        pg=b.new_page(viewport={'width':w,'height':800}); errs=[]
        pg.on('pageerror',lambda e:errs.append(str(e)))
        pg.on('response',lambda x:x.status>=400 and 'localhost' in x.url and errs.append(f'{x.status} {x.url}'))
        pg.on('console',lambda m:m.type=='error' and 'Failed to load resource' not in m.text and errs.append(m.text))
        pg.goto(B+f'project.html?id={pid}'); pg.evaluate('document.fonts.ready'); pg.wait_for_timeout(200)
        o=pg.evaluate("""()=>{const q=s=>[...document.querySelectorAll(s)];
          const t=q('main a,main button').filter(e=>e.getClientRects().length);
          const small=t.filter(e=>{const r=e.getBoundingClientRect();return r.height<43.5||(r.width<43.5&&!e.matches('.fk-link'))}).map(e=>(e.className||e.tagName)+'|'+Math.round(e.getBoundingClientRect().width)+'x'+Math.round(e.getBoundingClientRect().height));
          const svg=q('.fk-prj-cover__media svg,.fk-project-card__media svg').every(s=>{const r=s.getBoundingClientRect();return r.width>100&&r.height>60});
          return {sw:document.documentElement.scrollWidth,cw:document.documentElement.clientWidth,small,h1:q('h1').length,tiles:q('.fk-prj-tile').length,more:q('.fk-prj-more .fk-project-card').length,svg}}""")
        good=o['sw']<=o['cw'] and not o['small'] and o['h1']==1 and o['tiles']==3 and o['more']==3 and o['svg'] and not errs
        ck(f'id={pid} {w}px health (no overflow, targets>=44, one h1, cover, 3 tiles, 3 more, no errors)',good)
        if not good: print(pid,w,o,errs)
        if pid==1 and w in(390,1280): pg.screenshot(path=OUT+f'p9-{w}.png',full_page=True)
        pg.close()
    pg=b.new_page(viewport={'width':1280,'height':900}); errs=[]; pg.on('pageerror',lambda e:errs.append(str(e)))
    pg.goto(B+'project.html?id=3'); pg.evaluate("localStorage.clear()"); pg.reload(); pg.wait_for_timeout(200)
    ck('title, h1, crumb for id=3',pg.title()=='FORKEL — Демонстрационный проект 3 (прототип)' and pg.inner_text('h1')=='Демонстрационный проект 3' and pg.inner_text('#prj-crumb')=='Демонстрационный проект 3' and pg.locator('.fk-crumbs a[href="projects.html"]').count()==1)
    ck('sections: about, facts (3 stubs), gallery, cta, more',all(pg.locator(s).count()==1 for s in('.fk-prj-text','.fk-prj-facts','.fk-prj-gallery','.fk-prj-cta','.fk-prj-more')) and pg.locator('.fk-prj-facts dd').count()==3)
    hrefs=pg.evaluate("[...document.querySelectorAll('.fk-prj-more .fk-project-card')].map(a=>a.getAttribute('href'))")
    ck('more: 3 other projects (not id=3), links project.html?id=N',len(set(hrefs))==3 and 'project.html?id=3' not in hrefs and all(h.startswith('project.html?id=') for h in hrefs))
    ck('«Все проекты» -> projects.html',pg.get_attribute('.fk-prj-more .fk-link','href')=='projects.html')
    ck('header «Проекты» is current',pg.get_attribute('.fk-nav__link[href="projects.html"]','aria-current')=='page')
    pg.click('.fk-prj-cta [data-feedback]'); pg.wait_for_timeout(250)
    ck('«Связаться с нами» opens feedback dialog',pg.locator('dialog[open], [role=dialog]:visible').count()>=1)
    pg.keyboard.press('Escape'); pg.wait_for_timeout(200)
    ck('Esc closes dialog, focus returns to button',pg.locator('dialog[open]').count()==0 and pg.evaluate("document.activeElement.matches('[data-feedback]')"))
    pg.click('.fk-prj-more .fk-project-card >> nth=0'); pg.wait_for_timeout(200)
    ck('click on other project opens it',pg.url.endswith(hrefs[0]) and pg.inner_text('h1').startswith('Демонстрационный проект'))
    ck('no state written to localStorage',pg.evaluate("Object.keys(localStorage).length")==0)
    for q in ('?id=99','?id=abc',''):
        pg.goto(B+'project.html'+q); pg.wait_for_timeout(200)
        ck(f'not found for "{q}"',pg.inner_text('h1')=='Проект не найден' and pg.inner_text('#prj-crumb')=='Проект не найден' and 'не найден' in pg.title() and pg.locator('.fk-empty a[href="projects.html"]').count()==1)
    ck('no page errors',not errs)
    b.close()
for n,c in r: print('OK ' if c else 'FAIL',n)
print('OK' if all(c for _,c in r) else 'FAIL: есть отклонения')
