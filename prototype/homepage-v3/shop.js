/* FORKEL — прототип каталога и карточки товара. Данные демонстрационные: товаров, артикулов, цен и остатков от владельца нет.
   Нет запросов к серверу, корзина не работает. Названия категорий — из требований проекта (розетки, выключатели, рамки). */
(function () {
  'use strict';

  var D = window.FKData, C = window.FKCart;
  var CATEGORIES = D.CATEGORIES, COLORS = D.COLORS, STOCKS = D.STOCKS, PRODUCTS = D.PRODUCTS.filter(function (p) { return !p.hide; });
  var catName = D.catName, tag = D.tag, illu = D.illu;
  /* «Новинки» (new.html): признака новинки в исходных данных нет — условно показаны первые 8 демо-товаров, на главной — первые 4 */
  var NEW_IDS = [1, 2, 3, 4, 5, 6, 7, 8];
  var newPage = !!document.querySelector('[data-new-page]');
  var POOL = newPage ? PRODUCTS.filter(function (p) { return NEW_IDS.indexOf(p.id) >= 0; }) : PRODUCTS;

  function card(p) {
    var url = 'product.html?id=' + p.id;
    return '<li class="fk-product-card">' +
      '<a class="fk-product-card__media tone-' + p.tone + '" href="' + url + '" tabindex="-1" aria-hidden="true">' + illu(p) + '</a>' +
      '<h3 class="fk-product-card__name"><a href="' + url + '">' + p.name + '</a></h3>' +
      '<p class="fk-product-card__sku">Арт. <span class="fk-stub">' + p.sku + '</span></p>' +
      '<p class="fk-product-card__sku">Цвет: ' + COLORS[p.color] + '</p>' +
      '<p class="fk-product-card__sku">' + p.col + ' · ' + D.MATERIALS[p.mat] + '</p>' +
      '<div class="fk-variants">' + D.dots(p) + '</div>' +
      '<p class="fk-product-card__price"><span class="fk-stub">' + D.fmt(D.price(p)) + '</span> ' + (p.real ? '' : tag('демо')) + '</p>' +
      '<p class="fk-product-card__stock">' + STOCKS[p.stock] + (p.real ? '' : ' (демо)') + '</p>' +
      '<div class="fk-product-card__actions">' +
      '<button class="fk-btn fk-btn--primary fk-btn--card" type="button" data-add="' + p.id + '">В корзину<span class="fk-vh"> — ' + p.name + ', ' + COLORS[p.color] + '</span><span aria-hidden="true"></span></button>' +
      '<a class="fk-btn fk-btn--secondary fk-btn--card" href="' + url + '">Подробнее<span class="fk-vh"> — ' + p.name + '</span></a>' +
      '</div>' +
      '</li>';
  }
  /* Карточка от заказчика (offer 13238, см. D.REAL в data.js) */
  // function realCard() {
  //   var R = D.REAL, o = R.offers[0], url = 'product.html?id=' + o.id;
  //   var dots = R.offers.map(function (x) {
  //     return '<li style="--finish:' + x.hex + '" title="' + x.color + '"><span class="fk-vh">' + x.color + '</span></li>';
  //   }).join('');
  //   return '<li class="fk-product-card">' +
  //       '<a class="fk-product-card__media tone-light" href="' + url + '" tabindex="-1" aria-hidden="true"><img src="' + encodeURI(o.img) + '" alt="" loading="lazy" style="width:100%;height:100%;object-fit:contain"></a>' +
  //       '<h3 class="fk-product-card__name"><a href="' + url + '">' + R.name + '</a></h3>' +
  //       '<p class="fk-product-card__sku">Арт. ' + o.sku + '</p>' +
  //       '<p class="fk-product-card__sku">Цвет: ' + o.color + '</p>' +
  //       '<div class="fk-variants"><ul class="fk-dots" aria-label="Цветовые варианты">' + dots + '</ul></div>' +
  //       '<p class="fk-product-card__price">' + D.fmt(R.price) + '</p>' +
  //       '<p class="fk-product-card__stock">В наличии</p>' +
  //       '<div class="fk-product-card__actions">' +
  //       '<button class="fk-btn fk-btn--primary fk-btn--card" type="button" data-stub="Корзина для этой карточки в прототипе не подключена.">В корзину<span class="fk-vh"> — ' + R.name + '</span></button>' +
  //       '<a class="fk-btn fk-btn--secondary fk-btn--card" href="' + url + '">Подробнее<span class="fk-vh"> — ' + R.name + '</span></a>' +
  //       '</div></li>';
  // }
  function param(name) {
    var m = new RegExp('[?&]' + name + '=([^&]*)').exec(location.search);
    return m ? decodeURIComponent(m[1]) : '';
  }

  /* ---------- Каталог ---------- */
  function initCatalog() {
    var list = document.getElementById('cat-list');
    var count = document.getElementById('cat-count');
    var empty = document.getElementById('cat-empty');
    var search = document.getElementById('cat-search');
    var sort = document.getElementById('cat-sort');
    var panel = document.getElementById('cat-filters');
    var toggle = document.getElementById('cat-filters-toggle');
    var category = param('category');
    if (!CATEGORIES.some(function (c) { return c.key === category; })) category = '';
    /* Страница коллекции (collection.html?collection=F70|F55): список товаров этой коллекции (демо-данные) */
    var collection = '';
    if (document.querySelector('[data-collection-page]')) {
      collection = param('collection');
      if (!D.COLLECTIONS[collection]) collection = 'F70';
    }

    /* Категории: ссылки, текущая отмечена */
    [].forEach.call(document.querySelectorAll('.fk-cat-nav a'), function (a) {
      var on = (a.getAttribute('data-cat') || '') === category;
      if (on) a.setAttribute('aria-current', 'page'); else a.removeAttribute('aria-current');
      if (collection) a.setAttribute('href', 'collection.html?collection=' + collection + (a.getAttribute('data-cat') ? '&category=' + a.getAttribute('data-cat') : ''));
    });
    if (collection) {
      [].forEach.call(document.querySelectorAll('[data-col]'), function (a) {
        if (a.getAttribute('data-col') === collection) a.setAttribute('aria-current', 'page'); else a.removeAttribute('aria-current');
      });
      [].forEach.call(document.querySelectorAll('[data-col-name]'), function (n) { n.textContent = D.COLLECTIONS[collection]; });
      /* В фильтре цветов — только цвета этой коллекции */
      [].forEach.call(panel.querySelectorAll('input[name="color"]'), function (i) {
        if (D.COMPAT[collection].colors.indexOf(i.value) < 0) i.closest('label').hidden = true;
      });
      var cl = document.getElementById('col-cfg-link');
      if (cl) cl.setAttribute('href', 'configurator.html?collection=' + collection);
    }
    var h1 = document.getElementById('cat-title');
    h1.textContent = newPage ? 'Новинки' : collection ? D.COLLECTIONS[collection] + (category ? ': ' + catName(category).toLowerCase() : '') : (category ? catName(category) : 'Каталог');
    document.title = 'FORKEL — ' + h1.textContent + ' (прототип)';
    var crumb = document.getElementById('cat-crumb');
    if (crumb) crumb.textContent = h1.textContent;

    function checked(name) {
      return [].map.call(panel.querySelectorAll('input[name="' + name + '"]:checked'), function (i) { return i.value; });
    }
    var sorters = {
      default: function (a, b) { return a.id - b.id; },
      name: function (a, b) { return a.name.localeCompare(b.name, 'ru') || a.id - b.id; },
      sku: function (a, b) { return a.sku.localeCompare(b.sku); },
      'price-asc': function (a, b) { return D.price(a) - D.price(b) || a.id - b.id; },
      'price-desc': function (a, b) { return D.price(b) - D.price(a) || b.id - a.id; }
    };
    /* Поиск, фильтры и сортировка хранятся в адресе: после перехода в товар и возврата они сохраняются */
    function persist(q, colors, stocks, groups, mats, cols) {
      var parts = [];
      if (collection) parts.push('collection=' + collection);
      if (category) parts.push('category=' + category);
      if (q) parts.push('q=' + encodeURIComponent(q));
      if (sort.value !== 'default') parts.push('sort=' + sort.value);
      if (colors.length) parts.push('color=' + colors.join(','));
      if (stocks.length) parts.push('stock=' + stocks.join(','));
      if (groups.length) parts.push('group=' + groups.join(','));
      if (mats.length) parts.push('mat=' + mats.join(','));
      if (cols.length) parts.push('col=' + cols.join(','));
      try { history.replaceState(null, '', location.pathname + (parts.length ? '?' + parts.join('&') : '')); } catch (e) { /* адрес не обновляется */ }
    }
    (function restore() {
      search.value = param('q');
      var sv = param('sort');
      if ([].some.call(sort.options, function (o) { return o.value === sv; })) sort.value = sv;
      ['color', 'stock', 'group', 'mat', 'col'].forEach(function (name) {
        var vals = param(name).split(',');
        [].forEach.call(panel.querySelectorAll('input[name="' + name + '"]'), function (i) { i.checked = vals.indexOf(i.value) >= 0; });
      });
    })();
    function render() {
      var q = search.value.trim().toLowerCase();
      var colors = checked('color'), stocks = checked('stock'), groups = checked('group'), mats = checked('mat'), cols = checked('col');
      var items = POOL.filter(function (p) {
        if (category && p.cat !== category) return false;
        if (q && (p.name + ' ' + p.sku + ' ' + COLORS[p.color]).toLowerCase().indexOf(q) < 0) return false;
        if (collection && p.col !== collection) return false;
        if (colors.length && colors.indexOf(p.color) < 0) return false;
        if (mats.length && mats.indexOf(p.mat) < 0) return false;
        if (cols.length && cols.indexOf(p.col) < 0) return false;
        if (stocks.length && stocks.indexOf(p.stock) < 0) return false;
        if (groups.length && groups.indexOf(String(p.group)) < 0) return false;
        return true;
      }).sort(sorters[sort.value] || sorters.default);
      list.innerHTML = items.map(card).join('');

      // if ((!category || category === 'sockets') && !q && !colors.length && !stocks.length && !groups.length) list.insertAdjacentHTML('afterbegin', realCard());

      empty.hidden = items.length > 0;
      persist(q ? search.value.trim() : '', colors, stocks, groups, mats, cols);
      count.textContent = 'Показано демонстрационных товаров: ' + items.length + ' из ' + POOL.filter(function (p) { return (!category || p.cat === category) && (!collection || p.col === collection); }).length;
    }
    panel.addEventListener('change', render);
    sort.addEventListener('change', render);
    search.addEventListener('input', render);
    document.getElementById('cat-search-form').addEventListener('submit', function (e) { e.preventDefault(); render(); });
    function reset() {
      search.value = ''; sort.value = 'default';
      [].forEach.call(panel.querySelectorAll('input[type="checkbox"]'), function (i) { i.checked = false; });
      render();
    }
    [].forEach.call(document.querySelectorAll('[data-reset]'), function (b) { b.addEventListener('click', reset); });
    /* Фильтры на узких экранах раскрываются кнопкой */
    toggle.addEventListener('click', function () {
      var open = toggle.getAttribute('aria-expanded') !== 'true';
      toggle.setAttribute('aria-expanded', String(open));
      panel.classList.toggle('is-open', open);
    });
    render();
  }

  /* Характеристики: структура из примера заказчика (три группы). Значения — демонстрационные, подробно показаны только
     для одного товара (id 4), чтобы показать плотность страницы; у остальных — «Уточняется владельцем». Это не данные FORKEL
     и не данные Kollinger Eclipse. Поля, не относящиеся к типу изделия, не показываются. */
  var DEMO_FULL_ID = 4;
  function chars(p) {
    var frame = p.type === 'frame', full = p.id === DEMO_FULL_ID;
    var U = '<span class="fk-stub">Уточняется владельцем</span>';
    function dm(v) { return v + ' ' + tag('демо'); }
    function val(v) { return full ? dm(v) : U; }
    var sku = '<span class="fk-stub" id="pdp-sku-val">' + p.sku + '</span> ' + tag('заглушка') +
      '<button type="button" class="fk-copy" data-copy="' + p.sku + '" aria-label="Копировать артикул ' + p.sku + '">Копировать</button>';
    var groups = [
      ['Основные', [
        ['Бренд', 'FORKEL'], ['Серия', dm(p.collection)], ['Артикул', sku],
        ['Страна', U], ['Страна производства', U]
      ]],
      ['Размеры и габариты', [
        ['Длина, мм', val('90')], ['Ширина, мм', val('90')], ['Глубина, мм', val('10')],
        ['Вес нетто, г', val('40')], ['Вес брутто, г', val('55')], ['Размер упаковки, мм', val('100 × 100 × 20')]
      ]],
      ['Дополнительно', [
        ['Материал рамки', dm(D.MATERIALS[p.mat])],
        frame ? ['Цвет рамки', dm(COLORS[p.color])] : null,
        frame ? ['Количество постов', val('1')] : null,
        ['Вид/марка материала', val('Демо-материал')],
        ['Отделка поверхности', val('Матовая')],
        ['Не содержит галогенов', val('Да')],
        frame ? null : ['Степень защиты (IP)', U],
        frame ? null : ['Способ монтажа', U],
        frame ? null : ['Тип крепления', U]
      ]]
    ];
    return '<section class="fk-pdp__specs" aria-labelledby="specs-title">' +
      '<h2 id="specs-title">Характеристики ' + tag('демо-данные') + '</h2>' +
      '<p class="fk-note fk-note--proto">Структура — по примеру заказчика. Значения показывают плотность страницы и не подтверждены: это не данные FORKEL и не характеристики Kollinger Eclipse. «Уточняется владельцем» — данных нет, значение не придумано.</p>' +
      '<div class="fk-chars-groups">' + groups.map(function (g) {
        return '<div class="fk-chars"><h3>' + g[0] + '</h3><dl>' + g[1].filter(Boolean).map(function (r) {
          return '<div class="fk-char"><dt>' + r[0] + '</dt><dd>' + r[1] + '</dd></div>';
        }).join('') + '</dl></div>';
      }).join('') + '</div></section>';
  }

  /* ---------- Карточка товара ---------- */
  /* Кнопки материалов и попап с видео (только реальная карточка) */
  function resButtons() {
    var L = D.REAL.links, ext = ' target="_blank" rel="noopener noreferrer"';
    return '<div class="fk-res" role="group" aria-label="Материалы к товару">' +
        '<a class="fk-chip fk-btn--secondary" href="' + L.model + '"' + ext + '>3D-модель<span class="fk-vh"> (откроется в новой вкладке)</span></a>' +
        '<button class="fk-chip fk-btn--secondary" type="button" data-video>Видео</button>' +
        '<a class="fk-chip fk-btn--secondary" href="' + L.manual + '"' + ext + '>Инструкция<span class="fk-vh"> (PDF, откроется в новой вкладке)</span></a>' +
        '</div>';
  }
  function openVideo(opener) {
    var m = document.getElementById('fk-video');
    if (!m) {
      m = document.createElement('div');
      m.id = 'fk-video'; m.className = 'fk-modal'; m.hidden = true;
      m.innerHTML = '<div class="fk-modal__box" role="dialog" aria-modal="true" aria-label="Видео о товаре">' +
          '<button type="button" class="fk-modal__x" aria-label="Закрыть видео">×</button>' +
          '<div class="fk-modal__frame"><iframe title="Видео о товаре" allow="autoplay; encrypted-media; fullscreen; picture-in-picture" allowfullscreen></iframe></div></div>';
      document.body.appendChild(m);
      m.addEventListener('click', function (e) { if (e.target === m || e.target.closest('.fk-modal__x')) closeVideo(); });
      document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !m.hidden) closeVideo(); });
      document.addEventListener('focusin', function (e) { if (!m.hidden && !m.contains(e.target)) m.querySelector('.fk-modal__x').focus(); });
    }
    m._opener = opener;
    m.querySelector('iframe').src = D.REAL.links.video;
    m.hidden = false;
    document.body.style.overflow = 'hidden';
    m.querySelector('.fk-modal__x').focus();
  }
  function closeVideo() {
    var m = document.getElementById('fk-video');
    m.querySelector('iframe').removeAttribute('src');
    m.hidden = true;
    document.body.style.overflow = '';
    if (m._opener) m._opener.focus();
  }
  /* Описание под карточкой товара: по центру, не шире 900 px (стили .fk-pdp__desc) */
  function descBlock(html) {
    return '<section class="fk-pdp__desc" aria-labelledby="desc-title"><h2 id="desc-title">Описание</h2>' + html + '</section>';
  }
  /* Скачивание фото: через fetch и blob. Если сайт-источник запрещает такой запрос (CORS), открываем фото в новой вкладке */
  function downloadPhoto(url, name) {
    fetch(url).then(function (r) { if (!r.ok) throw new Error(r.status); return r.blob(); }).then(function (b) {
      var a = document.createElement('a');
      a.href = URL.createObjectURL(b); a.download = name;
      document.body.appendChild(a); a.click(); a.remove();
      setTimeout(function () { URL.revokeObjectURL(a.href); }, 1000);
    }).catch(function () {
      window.open(url, '_blank', 'noopener');
      if (window.fkNote) window.fkNote('Прямое скачивание недоступно: фото открыто в новой вкладке, сохраните его оттуда.');
    });
  }

  /* Копирование характеристик: клик или Enter/Пробел по строке копирует значение */
  function charText(c) {
    var dd = c.querySelector('dd').cloneNode(true);
    [].forEach.call(dd.querySelectorAll('.fk-tag, .fk-copy, .fk-vh'), function (n) { n.remove(); });
    return dd.textContent.trim();
  }
  function enhanceChars() {
    [].forEach.call(document.querySelectorAll('.fk-char'), function (c) {
      if (c.querySelector('.fk-copy')) return;
      c.setAttribute('data-copyable', '');
      c.tabIndex = 0; c.setAttribute('role', 'button');
      c.setAttribute('aria-label', 'Скопировать: ' + c.querySelector('dt').textContent.trim() + ' — ' + charText(c));
    });
  }
  function copyText(t, done) {
    function fallback() {
      var ta = document.createElement('textarea');
      ta.value = t; ta.style.position = 'fixed'; ta.style.opacity = '0';
      document.body.appendChild(ta); ta.select();
      try { document.execCommand('copy'); } catch (e) { /* буфер недоступен */ }
      ta.remove(); done();
    }
    if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(t).then(done, fallback); else fallback();
  }
  function doCopy(c) {
    var t = charText(c);
    copyText(t, function () {
      c.classList.add('is-copied'); setTimeout(function () { c.classList.remove('is-copied'); }, 1200);
      if (window.fkNote) window.fkNote('Скопировано: ' + t);
    });
  }
  document.addEventListener('click', function (e) { var c = e.target.closest('[data-copyable]'); if (c) doCopy(c); });
  document.addEventListener('keydown', function (e) {
    var c = e.target.closest && e.target.closest('[data-copyable]');
    if (c && c === e.target && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); doCopy(c); }
  });
  function initReal(id) {
    var R = D.REAL, o = R.offers.filter(function (x) { return x.id === id; })[0];
    var root = document.getElementById('pdp');
    document.title = 'FORKEL — ' + R.name + ' (прототип)';
    document.getElementById('pdp-crumb').textContent = R.name;
    var cl = document.getElementById('pdp-cat-crumb'); cl.textContent = 'Розетки'; cl.href = 'catalog.html?category=sockets';
    var rel = document.getElementById('pdp-related'); if (rel) rel.closest('section').hidden = true;
    document.querySelector('.fk-pdp__proto').textContent = 'Данные карточки предоставлены заказчиком (источник — ledron.ru). Изображения загружаются с сайта-источника: права и пригодность для публикации не подтверждены. Корзина и покупка для этой карточки в прототипе не работают.';
    function rows(a) { return a.map(function (r) { return '<div class="fk-char"><dt>' + r[0] + '</dt><dd>' + r[1] + '</dd></div>'; }).join(''); }
    var groups = [
      ['Основные', [['Бренд', 'FORKEL'], ['Серия', R.series], ['Тип', 'Розетка'], ['Артикул', o.sku], ['Гарантия', R.warranty]]],
      ['Электрические', [['Питание', '250V 50–60 Гц'], ['Номинальный ток', '16 А'], ['Степень защиты', 'IP20'], ['Количество постов', '1'], ['Исполнение', 'с заземляющим штырём']]],
      ['Монтаж и материал', [['Тип монтажа', 'встраиваемый'], ['Способ подключения', 'винтовой зажим, клемма'], ['Тип крепления', 'в распор (лапками) и винтами'], ['Материал', 'термопласт матовый'], ['Особенность', 'рамка на 1–5 постов комплектуется отдельно']]]
    ];
    var sw = R.offers.map(function (x) {
      return '<a class="fk-swatch" href="product.html?id=' + x.id + '" style="--finish:' + x.hex + '" aria-label="' + x.color + '"' + (x.id === id ? ' aria-current="true"' : '') + '></a>';
    }).join('');
    root.innerHTML =
        '<div class="fk-pdp__media tone-light"><img src="' + encodeURI(o.img) + '" alt="' + R.name + ', ' + o.color.toLowerCase() + '" style="width:100%;height:100%;object-fit:contain" onerror="this.style.display=\'none\'">' +
        '<button type="button" class="fk-dl" data-dl aria-label="Скачать фото"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4v11M7 11l5 5 5-5M5 20h14"/></svg></button></div>' +
        '<div class="fk-pdp__info">' +
        '<h1 class="fk-pdp__title">' + R.name + '</h1>' +
        '<p class="fk-pdp__sku">Артикул: ' + o.sku + '</p>' +
        '<p class="fk-pdp__price">' + D.fmt(R.price) + '</p>' +
        '<p class="fk-pdp__stock">В наличии: ' + o.qty.toLocaleString('ru-RU') + ' шт.</p>' +
        resButtons() +
        '<div><p class="fk-step__label">Цвет: ' + o.color + '</p><div class="fk-swatches">' + sw + '</div></div>' +
        '<button class="fk-btn fk-btn--primary fk-pdp__cart" type="button" data-add="' + id + '">В корзину</button>' +
        '<button class="fk-btn fk-btn--secondary fk-pdp__oneclick" type="button" data-stub="Покупка в один клик не реализована.">Купить в 1 клик</button>' +
        '<button class="fk-btn fk-btn--secondary fk-pdp__oneclick" type="button" data-stub="Форма вопроса не реализована в прототипе.">Задать вопрос</button>' +
        '</div>' +
        descBlock('<p>Розетка одинарная с заземлением и защитными шторками из серии ' + R.series + '. Номинальный ток 16 А, напряжение 250 В, 50–60 Гц, степень защиты IP20. ' +
            'Монтаж встраиваемый: механизм крепится в распор лапками и винтами, провода подключаются винтовым зажимом. ' +
            'Рамка на 1–5 постов приобретается отдельно. Гарантия ' + R.warranty + '.</p>') +
        '<section class="fk-pdp__specs"><h2>Характеристики</h2><div class="fk-chars-groups">' +
        groups.map(function (g) { return '<div class="fk-chars"><h3>' + g[0] + '</h3><dl>' + rows(g[1]) + '</dl></div>'; }).join('') +
        '</div></section>';

    root.addEventListener('click', function (e) {
      var v = e.target.closest('[data-video]');
      if (v) openVideo(v);
      if (e.target.closest('[data-dl]')) downloadPhoto(o.img, o.sku.replace(/\s+/g, '-') + '.webp');
    });
    enhanceChars();
  }

  function initProduct() {
    var root = document.getElementById('pdp');
    var id = parseInt(param('id'), 10);

    if (D.REAL.offers.some(function (o) { return o.id === id; })) return initReal(id);

    var p = PRODUCTS.filter(function (x) { return x.id === id; })[0];
    if (!p) {
      document.title = 'FORKEL — товар не найден (прототип)';
      document.getElementById('pdp-crumb').textContent = 'Товар не найден';
      root.innerHTML = '<div class="fk-empty"><h1 class="fk-pdp__title">Товар не найден</h1><p>В прототипе есть только демонстрационные товары.</p><p><a class="fk-btn fk-btn--primary" href="catalog.html">Перейти в каталог</a></p></div>';
      return;
    }
    var cname = catName(p.cat);
    document.title = 'FORKEL — ' + p.name + ' (прототип)';
    document.getElementById('pdp-crumb').textContent = p.name;
    var catLink = document.getElementById('pdp-cat-crumb');
    catLink.textContent = cname; catLink.href = 'catalog.html?category=' + p.cat;
    var unk = '<span class="fk-stub">не указано</span> ' + tag('заглушка');
    var rel = PRODUCTS.filter(function (x) { return x.id !== p.id && x.cat === p.cat; })
      .concat(PRODUCTS.filter(function (x) { return x.id !== p.id && x.cat !== p.cat; })).slice(0, 4);
    root.innerHTML =
      '<div class="fk-pdp__media tone-' + p.tone + '">' + illu(p).replace('aria-hidden="true"', 'role="img" aria-label="Демонстрационная иллюстрация: ' + p.name.toLowerCase() + ', цвет ' + COLORS[p.color].toLowerCase() + '"') +
        '<p class="fk-cfg__tag">Иллюстрация, не фото</p></div>' +
      '<div class="fk-pdp__info">' +
        '<h1 class="fk-pdp__title">' + p.name + '</h1>' +
        '<p class="fk-pdp__sku">Артикул: <span class="fk-stub">' + p.sku + '</span> ' + tag('заглушка') + '</p>' +
        '<p class="fk-pdp__price"><span class="fk-stub">' + D.fmt(D.price(p)) + '</span> ' + tag('демо') + '</p>' +
        '<p class="fk-pdp__stock">' + STOCKS[p.stock] + ' ' + tag('демо') + '</p>' +
        '<div class="fk-pdp__buy">' +
          '<div class="fk-qty" role="group" aria-label="Количество">' +
            '<button type="button" class="fk-qty__btn" data-qty="-1" aria-label="Уменьшить количество">−</button>' +
            '<input class="fk-qty__input" id="pdp-qty" type="number" inputmode="numeric" min="1" max="99" value="1" aria-label="Количество">' +
            '<button type="button" class="fk-qty__btn" data-qty="1" aria-label="Увеличить количество">+</button>' +
          '</div>' +
          '<button class="fk-btn fk-btn--primary fk-pdp__cart" type="button" id="pdp-add">В корзину</button>' +
        '</div>' +
        '<button class="fk-btn fk-btn--secondary fk-pdp__oneclick" type="button" data-stub="Демонстрационное действие: покупка в один клик не реализована.">Купить в один клик</button>' +
        '<p class="fk-pdp__more fk-pdp__cartlink" id="pdp-cartlink" hidden><a class="fk-link fk-link--tap" href="cart.html">Перейти в корзину</a></p>' +
        '<p class="fk-pdp__more"><a class="fk-link fk-link--tap" href="configurator.html?collection=' + p.col + '">Собрать в подборе</a></p>' +
        '<div class="fk-variants">Цвета: ' + D.dots(p) + '</div>' +
      '</div>' +
      descBlock('<p>Демонстрационное описание: текст товара предоставит владелец. ' + tag('заглушка') + '</p>') + chars(p);
    document.getElementById('pdp-related').innerHTML = rel.map(card).join('');
    enhanceChars();
    root.addEventListener('click', function (e) {
      var c = e.target.closest('[data-copy]');
      if (!c) return;
      var v = c.getAttribute('data-copy');
      function done() { if (window.fkNote) window.fkNote('Демо-артикул скопирован: ' + v); }
      if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(v).then(done, done);
      else done();
    });
    var input = document.getElementById('pdp-qty');
    root.addEventListener('click', function (e) {
      var b = e.target.closest('[data-qty]');
      if (!b) return;
      var v = (parseInt(input.value, 10) || 1) + parseInt(b.getAttribute('data-qty'), 10);
      input.value = Math.min(99, Math.max(1, v));
    });
    document.getElementById('pdp-add').addEventListener('click', function () {
      var q = Math.min(99, Math.max(1, parseInt(input.value, 10) || 1));
      input.value = q;
      C.announceAdd(C.add({ kind: 'product', id: p.id }, q));
      document.getElementById('pdp-cartlink').hidden = false;
    });
    input.addEventListener('change', function () {
      var v = parseInt(input.value, 10);
      input.value = isNaN(v) ? 1 : Math.min(99, Math.max(1, v));
    });
  }

  window.FKShopCard = card;
  if (document.getElementById('cat-list')) initCatalog();
  if (document.getElementById('pdp')) initProduct();
})();
