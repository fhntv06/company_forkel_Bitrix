"""I4: главная V4 — «Проекты» (H-10), «Готовые решения» (H-11), «Интерьеры» (H-12) после «Новинок».
Проверяет: css projects/solutions/interiors и solutions.js идентичны V3; порядок секций; 7 ширин (нет горизонтальной прокрутки, ошибок консоли/HTTP кроме внешних,
цели >= 44 px — точки слайдера 32x44 как в V3); проекты: 3 карточки, ссылки project.html?id=1..3, раскладка (1 колонка / крупная слева на >=768);
готовые решения: 4 слайда, 4 точки, стрелки (prev отключена в начале), точки переключают слайд, клавиатура на ленте, точки-маркеры открывают блок товаров (id как в data-ids), Esc закрывает и возвращает фокус,
сердце в блоке товаров пишет fk-demo-fav-v1; интерьеры: 7 карточек, фото загружены, колонки 4/2/1, карточки не уводят со страницы; нет дублей id.
Запуск: python3 -I sections_v4.py (сервер на 8081 из prototype/)."""
import os, filecmp
from playwright.sync_api import sync_playwright
B=os.environ.get('FK_BASE','http://localhost:8081/homepage-v4/'); OUT=os.environ.get('FK_OUT','/tmp/'); r=[]
def ck(n,c): r.append((n,bool(c)))
ROOT=os.path.join(os.path.dirname(os.path.abspath(__file__)),'..','..')
for f in ('css/projects.css','css/solutions.css','css/interiors.css','solutions.js'):
    ck(f+' identical to V3',filecmp.cmp(ROOT+'/homepage-v3/'+f,ROOT+'/homepage-v4/'+f,shallow=False))
COLS={360:1,390:1,768:2,1024:4,1280:4,1440:4,1920:4}
with sync_playwright() as p:
    b=p.chromium.launch()
    for w in (360,390,768,1024,1280,1440,1920):
        pg=b.new_page(viewport={'width':w,'height':800}); errs=[]
        pg.on('pageerror',lambda e:errs.append(str(e)))
        pg.on('response',lambda x:x.status>=400 and 'localhost' in x.url and errs.append(f'{x.status} {x.url}'))
        pg.on('console',lambda m:m.type=='error' and 'Failed to load resource' not in m.text and errs.append(m.text))
        pg.goto(B+'index.html'); pg.evaluate('document.fonts.ready')
        o=pg.evaluate("""()=>{const q=s=>[...document.querySelectorAll(s)];
          const t=q('#projects a,#projects button,#solutions button,#interiors a').filter(e=>e.getClientRects().length);
          const small=t.filter(e=>{const r=e.getBoundingClientRect();return r.height<43.5||r.width<(e.classList.contains('fk-sol-dot')?31.5:43.5)}).map(e=>e.className+'|'+Math.round(e.getBoundingClientRect().width)+'x'+Math.round(e.getBoundingClientRect().height));
          const ids=q('[id]').map(e=>e.id),dup=ids.filter((x,i)=>ids.indexOf(x)!==i);
          const ic=q('.fk-grid--interiors > li').map(e=>Math.round(e.getBoundingClientRect().top));
          const pc=q('.fk-grid--projects > li').map(e=>e.getBoundingClientRect());
          return {sw:document.documentElement.scrollWidth,cw:document.documentElement.clientWidth,small,dup,cols:ic.filter(x=>x===ic[0]).length,
            order:q('main > section').map(s=>s.id).filter(Boolean),h1:q('h1').length,
            prj:pc.map(r=>[Math.round(r.left),Math.round(r.top),Math.round(r.width),Math.round(r.height)])}}""")
        good=o['sw']<=o['cw'] and not o['small'] and not o['dup'] and not errs and o['h1']==1
        ck(f'{w}px health (no overflow, targets, no dup ids, no errors, one h1)',good)
        if not good: print(w,o,errs)
        ck(f'{w}px interiors columns = {COLS[w]}',min(o['cols'],COLS[w]) and o['cols']==COLS[w] or (w==1024 and o['cols']==4))
        if w==1280: ck('order: new > projects > solutions > interiors > showroom',(lambda L:L.index('new')<L.index('projects')<L.index('solutions')<L.index('interiors')<L.index('showroom') and L.index('projects')==L.index('new')+1 and L.index('interiors')==L.index('solutions')+1)(o['order']))
        pr=o['prj']
        if w<768: ck(f'{w}px projects one column',len(pr)==3 and len({x[0] for x in pr})==1)
        else: ck(f'{w}px projects: big card left spans two rows',len(pr)==3 and pr[0][0]<pr[1][0] and pr[1][0]==pr[2][0] and pr[0][3]>=pr[1][3]*1.8)
        pg.close()
    pg=b.new_page(viewport={'width':1280,'height':900}); pg.goto(B+'index.html'); pg.evaluate("localStorage.clear()"); pg.reload()
    hrefs=pg.evaluate("[...document.querySelectorAll('.fk-grid--projects a')].map(a=>a.getAttribute('href'))")
    ck('projects links project.html?id=1..3',hrefs==['project.html?id=1','project.html?id=2','project.html?id=3'])
    ck('"Все проекты" -> projects.html',pg.get_attribute('#projects .head2 a','href')=='projects.html')
    ck('project cards marked as placeholders (tag "Заглушка: фото проекта" x3)',pg.locator('#projects .fk-tag--float').count()==3)
    # решения
    pg.locator('#solutions').scroll_into_view_if_needed()
    ck('4 slides and 4 dots',pg.locator('.fk-sol__slide').count()==4 and pg.locator('.fk-sol-dot').count()==4)
    ck('slider is native (no Swiper)',pg.evaluate("typeof window.Swiper==='undefined' && !document.querySelector('.swiper')"))
    ck('prev disabled at start, next enabled',pg.locator('[data-sol-prev]').is_disabled() and pg.locator('[data-sol-next]').is_enabled())
    ck('first dot current',pg.get_attribute('.fk-sol-dot >> nth=0','aria-current')=='true')
    pg.click('[data-sol-next]'); pg.wait_for_timeout(1300)
    ck('next -> second dot current, prev enabled',pg.get_attribute('.fk-sol-dot >> nth=1','aria-current')=='true' and pg.locator('[data-sol-prev]').is_enabled())
    pg.click('.fk-sol-dot >> nth=3'); pg.wait_for_timeout(1400)
    ck('dot 4 -> last slide, next disabled',pg.get_attribute('.fk-sol-dot >> nth=3','aria-current')=='true' and pg.locator('[data-sol-next]').is_disabled())
    pg.click('.fk-sol-dot >> nth=0'); pg.wait_for_timeout(1400)
    pg.focus('.fk-sol__track'); pg.keyboard.press('ArrowRight'); pg.wait_for_timeout(1300)
    ck('ArrowRight on track moves to slide 2',pg.get_attribute('.fk-sol-dot >> nth=1','aria-current')=='true')
    pg.click('.fk-sol-dot >> nth=0'); pg.wait_for_timeout(1400)
    hs=pg.locator('.fk-sol__slide >> nth=0').locator('.fk-hs').first
    ids=hs.get_attribute('data-ids').split(','); hs.click(); pg.wait_for_timeout(150)
    ck('hotspot opens popover, aria-expanded true',hs.get_attribute('aria-expanded')=='true' and pg.locator('.fk-hs-pop:visible').count()==1)
    ck('popover has product mini-cards for data-ids',pg.locator('.fk-hs-pop:visible .fk-mini').count()==len(ids))
    ck('mini-card links product.html?id=',pg.locator('.fk-hs-pop:visible .fk-mini__name a').first.get_attribute('href')=='product.html?id='+ids[0])
    pg.locator('.fk-hs-pop:visible [data-fav]').first.click()
    fv=pg.evaluate("localStorage.getItem('fk-demo-fav-v1')"); ck('heart in popover saves fk-demo-fav-v1',fv and ids[0] in fv and pg.inner_text('[data-fav-count]')=='1')
    pg.keyboard.press('Escape'); ck('Esc closes popover and returns focus to hotspot',hs.get_attribute('aria-expanded')=='false' and pg.evaluate("document.activeElement.classList.contains('fk-hs')"))
    # интерьеры
    pg.locator('#interiors').scroll_into_view_if_needed(); pg.evaluate("document.querySelectorAll('#interiors img').forEach(i=>i.loading='eager')"); pg.wait_for_timeout(3000)
    ck('7 interior cards',pg.locator('.fk-interior').count()==7)
    ck('interior photos loaded',pg.evaluate("[...document.querySelectorAll('.fk-interior img')].every(i=>i.complete&&i.naturalWidth>0)"))
    y0=pg.evaluate("scrollY"); u0=pg.url
    pg.locator('.fk-interior').first.click(); pg.wait_for_timeout(300)
    ck('interior card click does not navigate or jump to top',pg.url.split('#')[0]==u0.split('#')[0] and abs(pg.evaluate("scrollY")-y0)<5)
    pg.locator('#projects').screenshot(path=OUT+'i4-projects.png')
    b.close()
for n,c in r: print('OK ' if c else 'FAIL',n)
print('OK' if all(c for _,c in r) else 'FAIL: есть отклонения')
