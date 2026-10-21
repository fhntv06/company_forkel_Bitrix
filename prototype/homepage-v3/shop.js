/* FORKEL — прототип каталога и карточки товара. Данные демонстрационные: товаров, артикулов, цен и остатков от владельца нет.
   Нет запросов к серверу, корзина не работает. Названия категорий — из требований проекта (розетки, выключатели, рамки). */
(function () {
  'use strict';

  var D = window.FKData, C = window.FKCart;
  var CATEGORIES = D.CATEGORIES, COLORS = D.COLORS, STOCKS = D.STOCKS, PRODUCTS = D.PRODUCTS;
  var catName = D.catName, tag = D.tag, illu = D.illu;

  function card(p) {
    var url = 'product.html?id=' + p.id;
    return '<li class="fk-product-card">' +
      '<a class="fk-product-card__media tone-' + p.tone + '" href="' + url + '" tabindex="-1" aria-hidden="true">' + illu(p) + '</a>' +
      '<h3 class="fk-product-card__name"><a href="' + url + '">' + p.name + '</a></h3>' +
      '<p class="fk-product-card__sku">Арт. <span class="fk-stub">' + p.sku + '</span></p>' +
      '<p class="fk-product-card__sku">Цвет: ' + COLORS[p.color] + '</p>' +
      '<div class="fk-variants">' + D.dots(p) + '</div>' +
      '<p class="fk-product-card__price"><span class="fk-stub">' + D.fmt(D.price(p)) + '</span> ' + tag('демо') + '</p>' +
      '<p class="fk-product-card__stock">' + STOCKS[p.stock] + ' (демо)</p>' +
      '<div class="fk-product-card__actions">' +
      '<button class="fk-btn fk-btn--primary fk-btn--card" type="button" data-add="' + p.id + '">В корзину<span class="fk-vh"> (демо) — ' + p.name + ', ' + COLORS[p.color] + '</span><span aria-hidden="true">&nbsp;(демо)</span></button>' +
      '<a class="fk-btn fk-btn--secondary fk-btn--card" href="' + url + '">Подробнее<span class="fk-vh"> — ' + p.name + '</span></a>' +
      '</div>' +
      '</li>';
  }
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
    /* Страница коллекции (collection.html?collection=A…D): тот же список; правил совместимости нет, поэтому показаны все демо-товары */
    var collection = '';
    if (document.querySelector('[data-collection-page]')) {
      collection = param('collection');
      if (!/^[A-D]$/.test(collection)) collection = 'A';
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
      [].forEach.call(document.querySelectorAll('[data-col-name]'), function (n) { n.textContent = 'Коллекция ' + collection; });
      var cl = document.getElementById('col-cfg-link');
      if (cl) cl.setAttribute('href', 'index.html?collection=' + collection + '#configurator');
    }
    var h1 = document.getElementById('cat-title');
    h1.textContent = collection ? 'Коллекция ' + collection + (category ? ': ' + catName(category).toLowerCase() : '') : (category ? catName(category) : 'Каталог');
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
      'price-asc': function (a, b) { return a.group - b.group || a.id - b.id; },
      'price-desc': function (a, b) { return b.group - a.group || a.id - b.id; }
    };
    /* Поиск, фильтры и сортировка хранятся в адресе: после перехода в товар и возврата они сохраняются */
    function persist(q, colors, stocks, groups) {
      var parts = [];
      if (collection) parts.push('collection=' + collection);
      if (category) parts.push('category=' + category);
      if (q) parts.push('q=' + encodeURIComponent(q));
      if (sort.value !== 'default') parts.push('sort=' + sort.value);
      if (colors.length) parts.push('color=' + colors.join(','));
      if (stocks.length) parts.push('stock=' + stocks.join(','));
      if (groups.length) parts.push('group=' + groups.join(','));
      try { history.replaceState(null, '', location.pathname + (parts.length ? '?' + parts.join('&') : '')); } catch (e) { /* адрес не обновляется */ }
    }
    (function restore() {
      search.value = param('q');
      var sv = param('sort');
      if ([].some.call(sort.options, function (o) { return o.value === sv; })) sort.value = sv;
      ['color', 'stock', 'group'].forEach(function (name) {
        var vals = param(name).split(',');
        [].forEach.call(panel.querySelectorAll('input[name="' + name + '"]'), function (i) { i.checked = vals.indexOf(i.value) >= 0; });
      });
    })();
    function render() {
      var q = search.value.trim().toLowerCase();
      var colors = checked('color'), stocks = checked('stock'), groups = checked('group');
      var items = PRODUCTS.filter(function (p) {
        if (category && p.cat !== category) return false;
        if (q && (p.name + ' ' + p.sku + ' ' + COLORS[p.color]).toLowerCase().indexOf(q) < 0) return false;
        if (colors.length && colors.indexOf(p.color) < 0) return false;
        if (stocks.length && stocks.indexOf(p.stock) < 0) return false;
        if (groups.length && groups.indexOf(String(p.group)) < 0) return false;
        return true;
      }).sort(sorters[sort.value] || sorters.default);
      list.innerHTML = items.map(card).join('');
      empty.hidden = items.length > 0;
      persist(q ? search.value.trim() : '', colors, stocks, groups);
      count.textContent = 'Показано демонстрационных товаров: ' + items.length + ' из ' + PRODUCTS.filter(function (p) { return !category || p.cat === category; }).length;
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
        frame ? ['Материал рамки', val('Пластик')] : null,
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
  function initProduct() {
    var root = document.getElementById('pdp');
    var id = parseInt(param('id'), 10);
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
        '<p class="fk-pdp__lead">Демонстрационное описание: текст товара предоставит владелец. ' + tag('заглушка') + '</p>' +
        '<div class="fk-pdp__buy">' +
          '<div class="fk-qty" role="group" aria-label="Количество">' +
            '<button type="button" class="fk-qty__btn" data-qty="-1" aria-label="Уменьшить количество">−</button>' +
            '<input class="fk-qty__input" id="pdp-qty" type="number" inputmode="numeric" min="1" max="99" value="1" aria-label="Количество">' +
            '<button type="button" class="fk-qty__btn" data-qty="1" aria-label="Увеличить количество">+</button>' +
          '</div>' +
          '<button class="fk-btn fk-btn--primary fk-pdp__cart" type="button" id="pdp-add">В корзину (демо)</button>' +
        '</div>' +
        '<button class="fk-btn fk-btn--secondary fk-pdp__oneclick" type="button" data-stub="Демонстрационное действие: покупка в один клик не реализована.">Купить в один клик (демо)</button>' +
        '<p class="fk-pdp__more fk-pdp__cartlink" id="pdp-cartlink" hidden><a class="fk-link fk-link--tap" href="cart.html">Перейти в корзину</a></p>' +
        '<p class="fk-pdp__more"><a class="fk-link fk-link--tap" href="index.html#configurator">Собрать в подборе</a></p>' +
        '<div class="fk-variants">Цвета: ' + D.dots(p) + '</div>' +
      '</div>' +
      chars(p);
    document.getElementById('pdp-related').innerHTML = rel.map(card).join('');
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

  if (document.getElementById('cat-list')) initCatalog();
  if (document.getElementById('pdp')) initProduct();
})();
