"""M7: окно «Обратный звонок» V4 — геометрия на 7 ширинах, проверка телефона и согласия, выбор времени, фокус, Esc, имитация отправки без запросов и хранения; независимость от окна обратной связи."""
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
        pg.goto(B+'page-template.html'); pg.click('[data-callback]'); pg.click('#cb-phone ~ * >> nth=0') if False else None
        pg.locator('dialog[open] .fk-fb__submit').click()
        g=pg.evaluate("""()=>{const D=[...document.querySelectorAll('dialog.fk-fb')].find(d=>d.open),d=D.getBoundingClientRect(),V=document.documentElement;
          const small=[...D.querySelectorAll('input:not([type=checkbox]),select,button')].filter(e=>e.getClientRects().length&&e.getBoundingClientRect().height<43.5).map(e=>e.className||e.id);
          return {l:d.left,r:d.right,t:d.top,b:d.bottom,vw:V.clientWidth,vh:innerHeight,sw:V.scrollWidth,small,title:D.querySelector('.fk-fb__title').textContent}}""")
        good=g['title']=='Обратный звонок' and g['l']>=0 and g['r']<=g['vw'] and g['t']>=0 and g['b']<=g['vh'] and g['sw']<=g['vw'] and not g['small'] and not errs
        ck(f'{w}px geometry',good)
        if not good: print(w,g,errs)
        if w==390: pg.screenshot(path=f'{OUT}cb{w}.png')
        pg.close()
    ctx=b.new_context(viewport={'width':1280,'height':800}); pg=ctx.new_page(); reqs=[]
    pg.on('request',lambda q:reqs.append(q.url)); pg.goto(B+'page-template.html'); n0=len(reqs); btn=pg.locator('[data-callback]')
    D=pg.locator('dialog:has(#cb-phone)')
    btn.focus(); pg.keyboard.press('Enter'); ck('keyboard opens, focus in phone',pg.evaluate("document.activeElement.id")=='cb-phone')
    pg.locator('dialog[open] .fk-fb__submit').click(); ck('empty: 2 errors, focus phone',pg.locator('dialog[open] .fk-co-error:visible').count()==2 and pg.evaluate("document.activeElement.id")=='cb-phone')
    pg.fill('#cb-phone','12345'); pg.locator('dialog[open] .fk-fb__submit').click(); ck('short phone rejected',pg.locator('#cb-phone-err:visible').count()==1)
    pg.fill('#cb-phone','+7 900 123-45-67'); ck('phone error clears on input',pg.locator('#cb-phone-err:visible').count()==0)
    opts=pg.locator('#cb-time option').count(); pg.select_option('#cb-time',index=1); ck('time select has options',opts>=3)
    pg.check('#cb-consent'); pg.locator('dialog[open] .fk-fb__submit').click(); ck('busy state',pg.locator('dialog[open] .fk-fb__submit').is_disabled())
    pg.wait_for_selector('dialog[open] .fk-fb__done:visible',timeout=3000); ck('demo confirmation','не отправлена' in pg.inner_text('dialog[open] .fk-fb__done'))
    ck('no requests, no storage',len(reqs)==n0 and pg.evaluate("localStorage.length+sessionStorage.length")==0)
    pg.click('dialog[open] .fk-fb__ok'); ck('close returns focus',pg.locator('dialog[open]').count()==0 and pg.evaluate("document.activeElement.hasAttribute('data-callback')"))
    btn.click(); ck('reopen reset',pg.input_value('#cb-phone')=='')
    out=False
    for _ in range(14):
        pg.keyboard.press('Tab'); out=out or pg.evaluate("(a=>a!==document.body&&!a.closest('dialog[open]'))(document.activeElement)")
    ck('focus never leaves dialog',not out)
    pg.keyboard.press('Escape'); ck('esc closes, focus returns',pg.locator('dialog[open]').count()==0 and pg.evaluate("document.activeElement.hasAttribute('data-callback')"))
    pg.click('[data-feedback]'); ck('feedback window still independent',pg.locator('dialog[open] #fb-name').count()==1 and pg.locator('dialog[open] #cb-phone').count()==0)
    b.close()
for n,c in r: print('OK ' if c else 'FAIL',n)
print('OK' if all(c for _,c in r) else 'FAIL: есть отклонения')
