"""P7: оформление заказа V4 (checkout.html) — разметку формирует checkout.js (V3, без изменений); css cart/checkout — из V3.
Проверяет: checkout.js, cart.js, css cart/checkout идентичны V3; 7 ширин с товаром в корзине (нет горизонтальной прокрутки, ошибок, цели >= 44 px, один h1);
пустая корзина -> пустое состояние; форма: 4 шага, доставка (курьер/СДЭК -> адрес, самовывоз -> нет адреса), оплата; валидация (ошибки, aria-invalid, фокус на первом поле, role=alert);
успех -> «Демонстрация завершена», корзина не меняется, в localStorage ничего не сохраняется кроме корзины/избранного; крошки, заглушка политики.
Запуск: python3 -I checkout_v4.py (сервер на 8081 из prototype/)."""
import os, filecmp
from playwright.sync_api import sync_playwright
B=os.environ.get('FK_BASE','http://localhost:8081/homepage-v4/'); OUT=os.environ.get('FK_OUT','/tmp/'); r=[]
def ck(n,c): r.append((n,bool(c)))
ROOT=os.path.join(os.path.dirname(os.path.abspath(__file__)),'..','..')
for f in ('checkout.js','cart.js','data.js','css/cart.css','css/checkout.css'):
    ck(f'{f} identical to V3',filecmp.cmp(ROOT+'/homepage-v3/'+f.replace('css/','css/'),ROOT+'/homepage-v4/'+f,shallow=False))
def fill(pg,**k):
    for i,v in k.items(): pg.fill('#co-'+i,v)
with sync_playwright() as p:
    b=p.chromium.launch()
    for w in (360,390,768,1024,1280,1440,1920):
        pg=b.new_page(viewport={'width':w,'height':800}); errs=[]
        pg.on('pageerror',lambda e:errs.append(str(e)))
        pg.on('response',lambda x:x.status>=400 and 'localhost' in x.url and errs.append(f'{x.status} {x.url}'))
        pg.on('console',lambda m:m.type=='error' and 'Failed to load resource' not in m.text and errs.append(m.text))
        pg.goto(B+'catalog.html'); pg.evaluate("localStorage.clear()"); pg.reload(); pg.wait_for_timeout(150)
        pg.locator('#cat-list [data-add]').first.click(); pg.locator('#cat-list [data-add]').nth(1).click()
        pg.goto(B+'checkout.html'); pg.evaluate('document.fonts.ready'); pg.wait_for_timeout(200)
        o=pg.evaluate("""()=>{const q=s=>[...document.querySelectorAll(s)];
          const t=q('main a,main button,main input:not([type=radio]):not([type=checkbox]),main textarea,main label.fk-co-choice,main label.fk-check').filter(e=>e.getClientRects().length&&!e.closest('[hidden]'));
          const small=t.filter(e=>{const r=e.getBoundingClientRect();return r.height<43.5||(r.width<43.5&&!e.matches('.fk-link'))}).map(e=>(e.className||e.tagName)+'|'+Math.round(e.getBoundingClientRect().width)+'x'+Math.round(e.getBoundingClientRect().height));
          return {sw:document.documentElement.scrollWidth,cw:document.documentElement.clientWidth,small,h1:q('h1').length,cards:q('.fk-co-card').length}}""")
        good=o['sw']<=o['cw'] and not o['small'] and o['h1']==1 and o['cards']==5 and not errs
        ck(f'{w}px health (no overflow, targets>=44, one h1, 5 cards, no errors)',good)
        if not good: print(w,o,errs)
        if w in(390,1280): pg.screenshot(path=OUT+f'p7-{w}.png',full_page=True)
        pg.close()
    pg=b.new_page(viewport={'width':1280,'height':900}); errs=[]; pg.on('pageerror',lambda e:errs.append(str(e)))
    pg.goto(B+'checkout.html'); pg.evaluate("localStorage.clear()"); pg.reload(); pg.wait_for_timeout(200)
    ck('empty cart: empty state with links to catalog and configurator',pg.locator('.fk-empty').is_visible() and pg.locator('.fk-empty a[href="catalog.html"]').count()==1 and pg.locator('.fk-empty a[href="configurator.html"]').count()==1 and pg.locator('#co-form').count()==0)
    ck('h1 and crumbs','Оформление заказа'==pg.inner_text('h1') and pg.inner_text('.fk-crumbs [aria-current=page]')=='Оформление заказа' and pg.locator('.fk-crumbs a[href="cart.html"]').count()==1)
    pg.goto(B+'catalog.html'); pg.locator('#cat-list [data-add]').first.click(); pg.locator('#cat-list [data-add]').first.click()
    cart0=pg.evaluate("localStorage.getItem('fk-demo-cart-v1')"); keys0=sorted(pg.evaluate("Object.keys(localStorage)"))
    pg.goto(B+'checkout.html'); pg.wait_for_timeout(200)
    ck('summary: line with qty 2, total, back-to-cart link',pg.locator('.fk-co-lines li').count()==1 and '× 2' in pg.inner_text('.fk-co-lines') and pg.locator('.fk-co-summary a[href="cart.html"]').count()==1 and '₽' in pg.inner_text('.fk-cart__total'))
    ck('delivery: courier default, address field visible',pg.is_checked('input[name=delivery][value=courier]') and pg.is_visible('#co-address'))
    pg.check('input[name=delivery][value=pickup]'); ck('pickup: address hidden, pickup note shown',pg.is_hidden('#co-address') and pg.is_visible('#co-pickup-note'))
    pg.check('input[name=delivery][value=cdek]'); ck('cdek: address visible again',pg.is_visible('#co-address') and pg.is_hidden('#co-pickup-note'))
    ck('payment: online default, 2 options',pg.is_checked('input[name=payment][value=online]') and pg.locator('input[name=payment]').count()==2)
    ck('stub tags «не утверждено» on delivery/payment options',pg.locator('.fk-co-choice .fk-tag').count()==5)
    # валидация
    pg.click('button[form=co-form]'); pg.wait_for_timeout(100)
    ck('empty submit: error box shown (role=alert), 5 invalid fields', pg.is_visible('#co-errors') and pg.get_attribute('#co-errors','role')=='alert' and pg.locator('#co-errors a').count()==5)
    ck('focus moves to first invalid field (name)',pg.evaluate("document.activeElement.id")=='co-name')
    ck('aria-invalid set on name/phone/email/address, consent error visible',all(pg.get_attribute('#co-'+x,'aria-invalid')=='true' for x in('name','phone','email','address')) and pg.is_visible('#co-consent-err'))
    fill(pg,name='А',phone='123',email='bad',address='ул'); pg.click('button[form=co-form]'); pg.wait_for_timeout(100)
    ck('invalid values still rejected (short name/phone, bad email, short address)',pg.locator('#co-errors a').count()==5)
    pg.fill('#co-name','Иван'); ck('typing clears the error of that field',pg.get_attribute('#co-name','aria-invalid') is None and pg.is_hidden('#co-name-err'))
    fill(pg,phone='+7 900 000-00-00',email='a@b.ru',address='Улица Тестовая, 1'); pg.check('#co-consent'); pg.fill('#co-comment','тест')
    pg.click('button[form=co-form]'); pg.wait_for_timeout(150)
    ck('success: «Демонстрация завершена», focus on heading',pg.inner_text('#co-done-title')=='Демонстрация завершена' and pg.evaluate("document.activeElement.id")=='co-done-title')
    ck('success text: order not created; cart sum shown',('не создан' in pg.inner_text('.fk-co-done')) and 'В корзине осталось товаров: 2' in pg.inner_text('.fk-co-done__sum'))
    ck('cart unchanged and no form data stored in localStorage',pg.evaluate("localStorage.getItem('fk-demo-cart-v1')")==cart0 and sorted(pg.evaluate("Object.keys(localStorage)"))==keys0 and 'Иван' not in str(pg.evaluate("JSON.stringify(localStorage)")))
    pg.goto(B+'checkout.html'); pg.wait_for_timeout(200); pg.check('input[name=delivery][value=pickup]')
    fill(pg,name='Иван',phone='+7 900 000-00-00',email='a@b.ru'); pg.check('#co-consent'); pg.click('button[form=co-form]'); pg.wait_for_timeout(100)
    ck('pickup: submits without address',pg.is_visible('.fk-co-done'))
    pg.goto(B+'checkout.html'); pg.wait_for_timeout(200); pg.click('a[data-stub]'); pg.wait_for_timeout(100)
    ck('policy link is a stub (no navigation, URL unchanged)',pg.url.endswith('checkout.html') or pg.url.endswith('checkout.html#'))
    ck('no page errors',not errs)
    b.close()
for n,c in r: print('OK ' if c else 'FAIL',n)
print('OK' if all(c for _,c in r) else 'FAIL: есть отклонения')
