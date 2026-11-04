"""M4: корзина, избранное, авторизация в V4 — API, счётчики, ключи хранения, сохранение между загрузками и вкладками, паритет данных с V3.
Запуск: python3 -I storage_v4.py (сервер на 8081 из prototype/). FK_BASE — адрес V4; V3 берётся рядом (../homepage-v3/)."""
import os
from playwright.sync_api import sync_playwright
B=os.environ.get('FK_BASE','http://localhost:8081/homepage-v4/'); V3=B.rstrip('/').rsplit('/',1)[0]+'/homepage-v3/'; r=[]
def ck(n,c): r.append((n,bool(c)))
with sync_playwright() as p:
    b=p.chromium.launch(); ctx=b.new_context(viewport={'width':1280,'height':800}); pg=ctx.new_page(); errs=[]
    pg.on('pageerror',lambda e:errs.append(str(e))); pg.on('console',lambda m:m.type=='error' and errs.append(m.text))
    pg.on('response',lambda x:x.status>=400 and errs.append(f'{x.status} {x.url}'))
    pg.goto(B+'page-template.html')
    ck('API loaded',pg.evaluate("!!(window.FKCart&&window.FKFav&&window.FKAuth&&window.FKData)"))
    ck('badges hidden initially',pg.evaluate("[...document.querySelectorAll('.fk-badge')].every(b=>b.hidden)"))
    pid=pg.evaluate("FKData.PRODUCTS[0].id")
    pg.evaluate("FKCart.add({kind:'product',id:%s},2)"%repr(pid)); pg.evaluate("FKCart.add({kind:'product',id:%s},1)"%repr(pid))
    ck('cart count 3',pg.evaluate("FKCart.count()")==3 and pg.inner_text('[data-cart-count]')=='3' and pg.evaluate("!document.querySelector('[data-cart-count]').hidden"))
    ck('cart sr text',', ' in pg.inner_text('[data-cart-sr]') or pg.evaluate("document.querySelector('[data-cart-sr]').textContent.length>0"))
    pg.evaluate("FKFav.toggle(%s)"%repr(pid)); ck('fav count 1',pg.inner_text('[data-fav-count]')=='1' and pg.evaluate("FKFav.has(%s)"%repr(pid)))
    pg.evaluate("FKAuth.login({name:'Демо',email:'a@b.c'})"); ck('account marked',pg.evaluate("document.querySelector('[data-account]').classList.contains('is-in')"))
    ck('keys',pg.evaluate("[localStorage.getItem('fk-demo-cart-v1')!==null,localStorage.getItem('fk-demo-fav-v1')!==null,sessionStorage.getItem('fk-demo-auth-v1')!==null]")==[True,True,True])
    pg.goto(B+'page-template.html')
    ck('persist after reload',pg.inner_text('[data-cart-count]')=='3' and pg.inner_text('[data-fav-count]')=='1' and pg.evaluate("document.querySelector('[data-account]').classList.contains('is-in')"))
    p2=ctx.new_page(); p2.goto(B+'page-template.html'); ck('second tab sees cart',p2.inner_text('[data-cart-count]')=='3')
    p2.evaluate("FKCart.remove && FKCart.items().forEach(i=>FKCart.remove(FKCart.describe(i).key))"); pg.wait_for_timeout(200)
    ck('storage event syncs other tab',pg.evaluate("document.querySelector('[data-cart-count]').hidden"))
    pg.evaluate("FKFav.clear()"); pg.evaluate("FKAuth.logout()"); ck('clear+logout',pg.evaluate("FKFav.count()==0 && !document.querySelector('[data-account]').classList.contains('is-in')"))
    pg.evaluate("localStorage.setItem('fk-demo-cart-v1','{bad')"); pg.reload(); ck('corrupt storage ok',pg.evaluate("FKCart.count()")==0)
    ck('no errors',not errs); 
    # data parity with V3
    pg.goto(V3+'catalog.html')
    v3=pg.evaluate("[FKData.PRODUCTS.length,Object.keys(FKData.CATEGORIES||{}).length,Object.keys(FKData.COLORS||{}).length]")
    pg.goto(B+'page-template.html'); v4=pg.evaluate("[FKData.PRODUCTS.length,Object.keys(FKData.CATEGORIES||{}).length,Object.keys(FKData.COLORS||{}).length]")
    print('data v3/v4',v3,v4); ck('data parity',v3==v4)
    b.close()
for n,c in r: print('OK ' if c else 'FAIL',n)
print('OK' if all(c for _,c in r) else 'FAIL: есть отклонения')
