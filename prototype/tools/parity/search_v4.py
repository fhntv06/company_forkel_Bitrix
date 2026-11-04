"""M5: окно поиска V4 — открытие, подсказки, результаты, пустой результат, клавиатура, фокус, геометрия на 7 ширинах, переход на результаты."""
import os
from playwright.sync_api import sync_playwright
B=os.environ.get('FK_BASE','http://localhost:8081/homepage-v4/'); OUT=os.environ.get('FK_OUT','/tmp/'); r=[]
def ck(n,c): r.append((n,bool(c)))
with sync_playwright() as p:
    b=p.chromium.launch()
    for w in (360,390,768,1024,1280,1440,1920):
        pg=b.new_page(viewport={'width':w,'height':760}); errs=[]
        pg.on('pageerror',lambda e:errs.append(str(e))); pg.on('console',lambda m:m.type=='error' and 'ERR_TUNNEL' not in m.text and errs.append(m.text))  # внешние фото ledron.ru из среды недоступны — не дефект
        pg.on('response',lambda x:x.status>=400 and 'localhost' in x.url and errs.append(f'{x.status} {x.url}'))
        pg.goto(B+'page-template.html'); pg.click('.fk-search-btn'); dlg=pg.locator('.fk-sdlg')
        ok=dlg.is_visible(); pg.fill('#fk-search-input','розетка'); pg.wait_for_timeout(100)
        g=pg.evaluate("""()=>{const d=document.querySelector('.fk-sdlg').getBoundingClientRect(),V=document.documentElement;
          const small=[...document.querySelectorAll('.fk-sdlg a,.fk-sdlg button')].filter(e=>e.getClientRects().length).filter(e=>{const r=e.getBoundingClientRect();return r.height<43.5}).map(e=>e.className);
          const th=document.querySelector('.fk-search__thumb svg use'); 
          return {l:d.left,r:d.right,t:d.top,b:d.bottom,vw:V.clientWidth,vh:innerHeight,sw:V.scrollWidth,small,items:document.querySelectorAll('.fk-search__item').length,svgOk:!!th&&document.querySelector('.fk-search__thumb svg').getBoundingClientRect().width>20}}""")
        good=ok and g['l']>=0 and g['r']<=g['vw'] and g['t']>=0 and g['b']<=g['vh'] and g['sw']<=g['vw'] and not g['small'] and g['items']>0 and g['svgOk'] and not errs
        ck(f'{w}px dialog geometry/results',good)
        if not good: print(w,g,errs)
        if w in (390,1280): pg.screenshot(path=f'{OUT}search{w}.png')
        pg.close()
    pg=b.new_page(viewport={'width':1280,'height':800}); pg.goto(B+'page-template.html'); btn=pg.locator('.fk-search-btn')
    btn.focus(); pg.keyboard.press('Enter'); ck('keyboard opens, focus in input',pg.evaluate("document.activeElement.id")=='fk-search-input')
    ck('hint shown (chips)',pg.locator('.fk-chip').count()>=3)
    pg.keyboard.type('чёрный'); ck('typing finds products',pg.locator('.fk-search__item').count()>0 and pg.locator('.fk-search__clear:visible').count()==1)
    pg.fill('#fk-search-input','проект 2'); ck('project result',pg.locator('.fk-search__item--prj').count()>=1)
    pg.fill('#fk-search-input','qqqzzz'); ck('empty result message','ничего не найдено' in pg.inner_text('.fk-search__body'))
    pg.click('.fk-search__clear'); ck('clear resets to hint',pg.input_value('#fk-search-input')=='' and pg.locator('.fk-chip').count()>=3)
    out=False
    for _ in range(14):
        pg.keyboard.press('Tab'); out=out or pg.evaluate("(a=>a!==document.body&&!document.querySelector('.fk-sdlg').contains(a))(document.activeElement)")
    ck('focus never leaves dialog (modal)',not out)
    pg.keyboard.press('Escape'); ck('esc closes, focus returns',not pg.locator('.fk-sdlg').is_visible() and pg.evaluate("document.activeElement.classList.contains('fk-search-btn')"))
    btn.click(); pg.mouse.click(5,5); ck('backdrop closes',not pg.locator('.fk-sdlg').is_visible())
    btn.click(); pg.fill('#fk-search-input','розетка'); 
    with pg.expect_navigation(wait_until='commit'): pg.keyboard.press('Enter')
    ck('submit goes to search.html?q=',pg.url.endswith('search.html?q=%D1%80%D0%BE%D0%B7%D0%B5%D1%82%D0%BA%D0%B0'))
    b.close()
for n,c in r: print('OK ' if c else 'FAIL',n)
print('OK' if all(c for _,c in r) else 'FAIL: есть отклонения')
