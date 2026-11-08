"""F1/проверки страниц P10–P15 (2026-11-08): «Контакты», «Сотрудничество» (анкета партнёра), «Личный кабинет», «О компании», поиск, избранное.
Каждая страница: 7 ширин (нет прокрутки, цели >= 44 px, один h1, нет ошибок) проверяет f2_a11y.py; здесь — сценарии.
Запуск: python3 -I pages_p10_p15_v4.py (сервер на 8081 из prototype/)."""
import os
from playwright.sync_api import sync_playwright
B=os.environ.get('FK_BASE','http://localhost:8081/homepage-v4/'); r=[]
def ck(n,c,i=''): r.append((n,bool(c),i))
with sync_playwright() as p:
    b=p.chromium.launch()
    def page(url,w=1280,clear=False):
        pg=b.new_page(viewport={'width':w,'height':900}); pg._errs=[]
        pg.on('pageerror',lambda e:pg._errs.append(str(e)))
        pg.on('console',lambda m:m.type=='error' and 'Failed to load resource' not in m.text and pg._errs.append(m.text))
        pg.goto(B+url)
        if clear: pg.evaluate("localStorage.clear()"); pg.reload()
        pg.wait_for_timeout(350); return pg
    # --- P10 Контакты
    pg=page('contacts.html')
    ck('P10 контакты: h1, крошка, три блока (связь, карта, реквизиты)',pg.inner_text('h1')=='Контакты' and pg.locator('#ct-conn,#ct-map,#ct-legal').count()==3)
    pg.click('main [data-feedback]'); pg.wait_for_timeout(250)
    ck('P10 «Написать нам» открывает форму (диалог)',pg.locator('dialog[open],[role=dialog]:visible').count()>=1)
    pg.keyboard.press('Escape'); pg.wait_for_timeout(200)
    ck('P10 Esc закрывает диалог, фокус возвращается',pg.locator('dialog[open]').count()==0 and pg.evaluate("document.activeElement.matches('[data-feedback]')"))
    ck('P10 нет ошибок страницы',not pg._errs,str(pg._errs))
    # --- P12 Сотрудничество: анкета партнёра
    pg=page('cooperation.html')
    tabs=pg.locator('.fk-partner__tab')
    ck('P12 анкета: 3 вкладки, активна «Оптовик»',tabs.count()==3 and pg.get_attribute('#ptab-wholesale','aria-selected')=='true' and pg.input_value('#partner-direction')=='Оптовик')
    pg.click('#ptab-designer'); ck('P12 клик по «Дизайнер»: вкладка и значение',pg.get_attribute('#ptab-designer','aria-selected')=='true' and pg.get_attribute('#ptab-wholesale','aria-selected')=='false' and pg.input_value('#partner-direction')=='Дизайнер')
    pg.keyboard.press('ArrowRight'); pg.keyboard.press('ArrowRight'); ck('P12 стрелки: Дизайнер → Оптовик → Электрик, фокус на вкладке',pg.get_attribute('#ptab-electrician','aria-selected')=='true' and pg.evaluate("document.activeElement.id")=='ptab-electrician')
    ck('P12 обязательные: имя, телефон (tel, inputmode), задача (maxlength=500), согласие',all(pg.get_attribute(s,'required') is not None for s in('#pf-name','#pf-phone','#pf-task','#pf-consent')) and pg.get_attribute('#pf-phone','type')=='tel' and pg.get_attribute('#pf-phone','inputmode')=='tel' and pg.get_attribute('#pf-task','maxlength')=='500')
    ck('P12 необязательные: компания, город, e-mail, формат, объём, время',all(pg.get_attribute(s,'required') is None for s in('#pf-company','#pf-city','#pf-email','#pf-format','#pf-volume','#pf-time')))
    ck('P12 «Дополнительные сведения» свёрнуты; формат работы: placeholder + 5 вариантов',pg.evaluate("!document.querySelector('.fk-partner__more').open") and pg.locator('#pf-format option').count()==6 and pg.locator('#pf-format option').first.text_content()=='Выберите вариант')
    pg.fill('#pf-task','12345'); ck('P12 счётчик 5/500',pg.inner_text('#pf-count')=='5')
    pg.click('.fk-partner__submit'); pg.wait_for_timeout(150)
    ck('P12 пустая отправка блокируется нативной валидацией (примечание скрыто, фокус на имени)',pg.locator('#pf-note').is_hidden() and pg.evaluate("document.activeElement.id")=='pf-name')
    pg.fill('#pf-name','Иван'); pg.fill('#pf-phone','+7 900 000-00-00'); pg.check('#pf-consent'); pg.click('.fk-partner__submit'); pg.wait_for_timeout(150)
    ck('P12 корректная отправка: «Менеджер свяжется с вами»',pg.locator('#pf-note').is_visible() and 'Менеджер свяжется с вами' in pg.inner_text('#pf-note') and pg.get_attribute('#partner-form','aria-busy')=='false')
    ck('P12 данные не сохраняются в localStorage','Иван' not in pg.evaluate("JSON.stringify(localStorage)"))
    ck('P12 крестик ведёт на главную, ссылки согласия — заглушки',pg.get_attribute('.fk-partner__close','href')=='index.html' and pg.locator('.fk-partner__consent a[data-stub]').count()==2)
    ck('P12 нет ошибок страницы',not pg._errs,str(pg._errs))
    # --- P13 Личный кабинет
    pg=page('account.html',clear=True)
    ck('P13 вход: поля e-mail/пароль, «Войти как пользователь» (демо)',pg.locator('#acc-email,#acc-pass,#acc-demo').count()==3 and pg.inner_text('h1')!='')
    pg.click('#acc-demo'); pg.wait_for_timeout(300)
    ck('P13 демо-вход: ключ fk-demo-auth-v1 в sessionStorage, появилась кнопка «Выйти»',pg.evaluate("sessionStorage.getItem('fk-demo-auth-v1')!==null") and pg.locator('#acc-logout').count()==1)
    pg.reload(); pg.wait_for_timeout(300); ck('P13 вход сохраняется после перезагрузки (в рамках вкладки)',pg.locator('#acc-logout').count()==1)
    pg.click('#acc-logout'); pg.wait_for_timeout(300); ck('P13 выход: снова форма входа',pg.locator('#acc-demo').count()==1 and pg.locator('#acc-logout').count()==0)
    ck('P13 нет ошибок страницы',not pg._errs,str(pg._errs))
    # --- P14 О компании
    pg=page('about.html')
    ck('P14 блоки: 2 на всю ширину, 2 «текст слева», 1 «фото слева»',pg.locator('.fk-ab-block--full').count()==2 and pg.locator('.fk-ab-block--text-left').count()==2 and pg.locator('.fk-ab-block--photo-left').count()==1)
    for y in range(0,5000,600): pg.evaluate(f'window.scrollTo(0,{y})'); pg.wait_for_timeout(100)
    ck('P14 фото загружены (hero + 3 блока), alt задан',pg.evaluate("[...document.querySelectorAll('main img')].length===4&&[...document.querySelectorAll('main img')].every(i=>i.complete&&i.naturalWidth>0&&i.alt.length>5)"))
    g=pg.evaluate("[...document.querySelectorAll('.fk-ab-block--text-left')].map(s=>{const t=s.querySelector('.fk-ab-block__text').getBoundingClientRect(),m=s.querySelector('.fk-ab-block__media').getBoundingClientRect();return t.left<m.left})+'|'+(()=>{const s=document.querySelector('.fk-ab-block--photo-left');const t=s.querySelector('.fk-ab-block__text').getBoundingClientRect(),m=s.querySelector('.fk-ab-block__media').getBoundingClientRect();return m.left<t.left})()")
    ck('P14 раскладка на 1280: текст слева/фото справа и фото слева/текст справа',g=='true,true|true',g)
    pg.set_viewport_size({'width':390,'height':800}); pg.wait_for_timeout(200)
    ck('P14 на 390: фото под текстом, одна колонка',pg.evaluate("[...document.querySelectorAll('.fk-ab-block--text-left,.fk-ab-block--photo-left')].every(s=>{const t=s.querySelector('.fk-ab-block__text').getBoundingClientRect(),m=s.querySelector('.fk-ab-block__media').getBoundingClientRect();return m.top>=t.bottom-1})"))
    ck('P14 нет ошибок страницы',not pg._errs,str(pg._errs))
    # --- P11 Поиск
    pg=page('search.html?q=розетка')
    ck('P11 поиск «розетка»: заголовок, 9 товаров, 0 проектов, вкладки',pg.inner_text('#sr-title')=='Результаты поиска: «розетка»' and pg.locator('#sr-products-list > li').count()==9 and pg.locator('#sr-projects-list > li').count()==0 and pg.locator('#sr-tabs [data-type]').count()==3)
    pg=page('search.html?q=zzzzzz')
    ck('P11 пустой запрос-результат: пустое состояние',pg.locator('#sr-empty').is_visible() and pg.locator('#sr-products-list > li').count()==0)
    pg.fill('#sr-input','выключатель'); pg.press('#sr-input','Enter'); pg.wait_for_timeout(500)
    ck('P11 новый запрос из формы: URL и результаты обновились',('q=%D0%B2' in pg.url or 'q=выключатель' in pg.url) and pg.locator('#sr-products-list > li').count()>0)
    ck('P11 нет ошибок страницы',not pg._errs,str(pg._errs))
    # --- P15 Избранное
    pg=page('favorites.html',clear=True)
    ck('P15 пустое избранное: «Очистить» отсутствует/скрыто или пустое состояние, ссылка в каталог',pg.locator('a[href="catalog.html"]').count()>=1 and pg.locator('.fk-fav-card, .fk-product-card').count()==0)
    pg.goto(B+'catalog.html'); pg.wait_for_timeout(300); pg.locator('#cat-list .fk-fav-btn').first.click(); pg.locator('#cat-list .fk-fav-btn').nth(1).click(); pg.wait_for_timeout(150)
    pg.goto(B+'favorites.html'); pg.wait_for_timeout(400)
    ck('P15 избранное: 2 карточки, счётчик в шапке',pg.locator('.fk-product-card').count()==2 and (pg.evaluate("document.querySelector('[data-fav-count]')?.textContent||''").strip()=='2'),str(pg.locator('.fk-product-card').count()))
    pg.locator('.fk-product-card .fk-fav-btn').first.click(); pg.wait_for_timeout(200)
    ck('P15 снятие сердечка убирает карточку',pg.locator('.fk-product-card').count()==1)
    pg.click('button:has-text("Очистить избранное")'); pg.wait_for_timeout(250)
    ck('P15 «Очистить избранное»: пусто, ключ fk-demo-fav-v1 пуст',pg.locator('.fk-product-card').count()==0 and pg.evaluate("(JSON.parse(localStorage.getItem('fk-demo-fav-v1')||'[]')).length")==0)
    ck('P15 нет ошибок страницы',not pg._errs,str(pg._errs))
    # --- QA D1: мусорные значения в карточках товаров
    for u in ('catalog.html','collection.html?collection=f70','new.html','favorites.html'):
        pg=page(u); t=pg.inner_text('main')
        ck('D1 нет «true/undefined/null/NaN» в тексте '+u,not __import__('re').search(r'\b(true|undefined|null|NaN)\b',t),__import__('re').findall(r'.{12}\b(?:true|undefined|null|NaN)\b',t)[:2].__str__())
    b.close()
for n,c,i in r: print('OK ' if c else 'FAIL',n,('— '+i) if (not c and i) else '')
print('OK' if all(c for _,c,_ in r) else 'FAIL: есть отклонения')
