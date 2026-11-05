"""M6 (на странице есть и окно звонка — селекторы привязаны к окну обратной связи): окно обратной связи V4 — геометрия на 7 ширинах, проверка полей, фокус, Esc, имитация отправки без запросов и без хранения данных."""
import os
from playwright.sync_api import sync_playwright
B=os.environ.get('FK_BASE','http://localhost:8081/homepage-v4/'); OUT=os.environ.get('FK_OUT','/tmp/'); r=[]
def ck(n,c): r.append((n,bool(c)))
with sync_playwright() as p:
    b=p.chromium.launch()
    for w in (360,390,768,1024,1280,1440,1920):
        pg=b.new_page(viewport={'width':w,'height':700}); errs=[]
        pg.on('pageerror',lambda e:errs.append(str(e))); pg.on('console',lambda m:m.type=='error' and errs.append(m.text))
        pg.on('response',lambda x:x.status>=400 and 'localhost' in x.url and errs.append(f'{x.status} {x.url}'))
        pg.goto(B+'page-template.html'); pg.click('[data-feedback]'); pg.click('dialog:has(#fb-name) .fk-fb__submit')
        g=pg.evaluate("""()=>{const d=document.querySelector('.fk-fb').getBoundingClientRect(),V=document.documentElement;
          const small=[...document.querySelectorAll('.fk-fb input:not([type=checkbox]),.fk-fb textarea,.fk-fb button')].filter(e=>e.getClientRects().length&&e.getBoundingClientRect().height<43.5).map(e=>e.className||e.id);
          return {l:d.left,r:d.right,t:d.top,b:d.bottom,vw:V.clientWidth,vh:innerHeight,sw:V.scrollWidth,small,open:document.querySelector('.fk-fb').open}}""")
        good=g['open'] and g['l']>=0 and g['r']<=g['vw'] and g['t']>=0 and g['b']<=g['vh'] and g['sw']<=g['vw'] and not g['small'] and not errs
        ck(f'{w}px geometry',good)
        if not good: print(w,g,errs)
        if w in (390,1280): pg.screenshot(path=f'{OUT}fb{w}.png')
        pg.close()
    ctx=b.new_context(viewport={'width':1280,'height':800}); pg=ctx.new_page(); reqs=[]
    pg.on('request',lambda q:reqs.append((q.method,q.url)))
    pg.goto(B+'page-template.html'); n0=len(reqs); btn=pg.locator('[data-feedback]'); dlg=pg.locator('dialog:has(#fb-name)')
    btn.focus(); pg.keyboard.press('Enter'); ck('keyboard opens, focus in name',pg.evaluate("document.activeElement.id")=='fb-name')
    pg.click('dialog:has(#fb-name) .fk-fb__submit'); ck('empty submit: 4 errors, summary, focus first',pg.locator('dialog:has(#fb-name) .fk-co-error:visible').count()==4 and pg.locator('dialog:has(#fb-name) .fk-fb__errors:visible').count()==1 and pg.evaluate("document.activeElement.id")=='fb-name')
    pg.click('dialog:has(#fb-name) .fk-fb__errors a >> nth=2'); ck('summary link focuses field',pg.evaluate("document.activeElement.id")=='fb-msg')
    pg.fill('#fb-name','Иван'); ck('error clears on input',pg.locator('#fb-name-err:visible').count()==0)
    pg.fill('#fb-contact','abc'); pg.fill('#fb-msg','Привет!'); pg.click('dialog:has(#fb-name) .fk-fb__submit'); ck('bad contact rejected',pg.locator('#fb-contact-err:visible').count()==1 and pg.locator('#fb-consent-err:visible').count()==1)
    pg.fill('#fb-contact','+7 (900) 123-45-67'); pg.click('dialog:has(#fb-name) .fk-fb__policy'); ck('policy link shows note, no navigation',pg.locator('dialog:has(#fb-name) .fk-fb__policy-note:visible').count()==1 and pg.url.endswith('page-template.html'))
    pg.check('#fb-consent'); pg.click('dialog:has(#fb-name) .fk-fb__submit'); ck('submit busy state',pg.locator('dialog:has(#fb-name) .fk-fb__submit').is_disabled())
    pg.wait_for_selector('dialog:has(#fb-name) .fk-fb__done:visible',timeout=3000); ck('demo confirmation shown','не отправлено' in pg.inner_text('dialog:has(#fb-name) .fk-fb__done') and pg.inner_text('dialog:has(#fb-name) .fk-fb__title')=='Спасибо!')
    ck('focus moved to confirmation',pg.evaluate("document.activeElement.classList.contains('fk-fb__done-title')"))
    ck('no requests, no storage',all(m=='GET' for m,_ in reqs[n0:]) and len(reqs[n0:])==0 and pg.evaluate("localStorage.length+sessionStorage.length")==0)
    pg.click('dialog:has(#fb-name) .fk-fb__ok'); ck('close returns focus',not dlg.is_visible() and pg.evaluate("document.activeElement.hasAttribute('data-feedback')"))
    btn.click(); ck('reopen is reset',pg.input_value('#fb-name')=='' and pg.locator('dialog:has(#fb-name) .fk-fb__form:visible').count()==1)
    out=False
    for _ in range(16):
        pg.keyboard.press('Tab'); out=out or pg.evaluate("(a=>a!==document.body&&!document.querySelector('.fk-fb').contains(a))(document.activeElement)")
    ck('focus never leaves dialog',not out)
    pg.keyboard.press('Escape'); ck('esc closes, focus returns',not dlg.is_visible() and pg.evaluate("document.activeElement.hasAttribute('data-feedback')"))
    btn.click(); pg.mouse.click(5,5); ck('backdrop closes',not dlg.is_visible())
    b.close()
for n,c in r: print('OK ' if c else 'FAIL',n)
print('OK' if all(c for _,c in r) else 'FAIL: есть отклонения')
