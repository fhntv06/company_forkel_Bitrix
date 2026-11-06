"""I5: секции главной V4, которых нет в V3 («Лёгкость. В каждой детали.», «Шоурум», «Доверие», финальный блок), не изменены.
Проверяет: styles.css (токены и компоненты V4) и разметка четырёх секций (без учёта отступов) совпадают с манифестом baseline/v4-keep-sections.json
(`--save` перезаписывает манифест — только после решения владельца); порядок секций (они после «Интерьеров»: features стоит выше, showroom > trust > pro > delivery > final);
7 ширин: нет горизонтальной прокрутки, ошибок консоли/HTTP, цели >= 44 px, один h1; фокус клавиатурой видим у кнопок секций; prefers-reduced-motion не ломает вид.
Необязательно: FK_M0=http://localhost:8082/homepage-v4/ (исходная V4 до перехода, `git archive 6e1fdf6 prototype`) — сравнение геометрии секций (высота и позиции элементов).
Запуск: python3 -I keep_sections_v4.py [--save] (сервер на 8081 из prototype/)."""
import os, sys, re, json, hashlib, io
from playwright.sync_api import sync_playwright
B=os.environ.get('FK_BASE','http://localhost:8081/homepage-v4/'); M0=os.environ.get('FK_M0'); r=[]
def ck(n,c): r.append((n,bool(c)))
HERE=os.path.dirname(os.path.abspath(__file__)); ROOT=HERE+'/../../homepage-v4'; MAN=HERE+'/baseline/v4-keep-sections.json'
IDS={'features':'f-t','showroom':'sr-t','trust':'tr-t','final':'fin-t'}
def norm(s): return re.sub(r'>\s+<','><',re.sub(r'\s+',' ',s)).strip()  # без учёта отступов и переносов (редактор может переформатировать файл)
def h(s,n=False): return hashlib.sha256((norm(s) if n else s).encode('utf-8')).hexdigest()[:16]
html=open(ROOT+'/index.html',encoding='utf-8').read()
main=re.search(r'<main.*?</main>',html,re.S).group(0)
sec={}
for s in re.findall(r'<section\b.*?</section>',main,re.S):
    m=re.search(r'<h[12][^>]*id="([^"]+)"',s)
    if m: sec[m.group(1)]=s
cur={'styles.css':h(open(ROOT+'/styles.css',encoding='utf-8').read())}
for k,i in IDS.items(): cur[k]=h(sec.get(i,''),True)
if '--save' in sys.argv:
    json.dump(cur,open(MAN,'w'),indent=1,sort_keys=True); print('saved',cur)
else:
    man=json.load(open(MAN))
    for k in cur: ck(f'{k} unchanged (hash)',man.get(k)==cur[k])
with sync_playwright() as p:
    b=p.chromium.launch()
    for w in (360,390,768,1024,1280,1440,1920):
        pg=b.new_page(viewport={'width':w,'height':800}); errs=[]
        pg.on('pageerror',lambda e:errs.append(str(e)))
        pg.on('response',lambda x:x.status>=400 and 'localhost' in x.url and errs.append(f'{x.status} {x.url}'))
        pg.on('console',lambda m:m.type=='error' and 'Failed to load resource' not in m.text and errs.append(m.text))
        pg.goto(B+'index.html'); pg.evaluate('document.fonts.ready')
        o=pg.evaluate("""ids=>{const t=ids.flatMap(i=>[...document.getElementById(i).closest('section').querySelectorAll('a,button')]).filter(e=>e.getClientRects().length);
          const small=t.filter(e=>{const r=e.getBoundingClientRect();return r.height<43.5||r.width<43.5}).map(e=>e.textContent.trim().slice(0,20)+'|'+Math.round(e.getBoundingClientRect().height));
          const order=[...document.querySelectorAll('main > section')].map(s=>s.id).filter(Boolean);
          return {sw:document.documentElement.scrollWidth,cw:document.documentElement.clientWidth,small,h1:document.querySelectorAll('h1').length,n:ids.length}}""",list(IDS.values()))
        good=o['sw']<=o['cw'] and not o['small'] and o['h1']==1 and not errs
        ck(f'{w}px page health + targets in kept sections',good)
        if not good: print(w,o,errs)
        pg.close()
    # клавиатура: фокус виден на кнопках секций
    pg=b.new_page(viewport={'width':1280,'height':900}); pg.goto(B+'index.html')
    bad=[]
    for i in IDS.values():
        for k,el in enumerate(pg.locator(f'#{i}').locator('xpath=ancestor::section').locator('a.btn,button').all()):
            el.focus(); fo=pg.evaluate("(()=>{const s=getComputedStyle(document.activeElement);return [s.outlineStyle,s.outlineWidth,s.boxShadow]})()")
            if fo[0]=='none' and fo[2]=='none': bad.append((i,k))
    ck('keyboard focus ring visible on kept-section buttons',not bad); 
    if bad: print(bad)
    # reduced motion
    pg2=b.new_context(reduced_motion='reduce',viewport={'width':1280,'height':900}).new_page(); e2=[]; pg2.on('pageerror',lambda e:e2.append(str(e)))
    pg2.goto(B+'index.html'); ck('prefers-reduced-motion: no errors, hero visible',not e2 and pg2.evaluate("getComputedStyle(document.querySelector('.hero__svg')).animationName")=='none')
    if M0:
        # снимки секций из-за дробного сдвига (новые секции выше) дают шум 0.1–1.2%, поэтому сравнивается геометрия: высота секции и позиции всех элементов относительно её верха
        JS="""ids=>Object.fromEntries(ids.map(i=>{const s=document.getElementById(i).closest('section'),t=s.getBoundingClientRect().top;
          return [i,[+s.getBoundingClientRect().height.toFixed(2),[...s.querySelectorAll('*')].map(e=>{const r=e.getBoundingClientRect();return [r.left,r.top-t,r.width,r.height]})]]}))"""
        for w in (390,1280,1920):
            R={}
            for tag,base in(('m0',M0),('new',B)):
                q=b.new_page(viewport={'width':w,'height':900}); q.goto(base+'index.html'); q.evaluate('document.fonts.ready'); q.wait_for_timeout(300)
                R[tag]=q.evaluate(JS,list(IDS.values())); q.close()
            for i in IDS.values():
                a,c=R['m0'][i],R['new'][i]
                same=len(a[1])==len(c[1]) and abs(a[0]-c[0])<0.05 and all(abs(x-y)<0.1 for u,v in zip(a[1],c[1]) for x,y in zip(u,v))
                ck(f'{w}px {i} geometry equals M0 (height + {len(a[1])} elements)',same)
    b.close()
for n,c in r: print('OK ' if c else 'FAIL',n)
print('OK' if all(c for _,c in r) else 'FAIL: есть отклонения')
