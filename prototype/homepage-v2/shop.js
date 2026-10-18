/* FORKEL — прототип каталога и карточки товара. Данные демонстрационные: товаров, артикулов, цен и остатков от владельца нет.
   Нет запросов к серверу, корзина не работает. Названия категорий — из требований проекта (розетки, выключатели, рамки). */
(function () {
  'use strict';

  var CATEGORIES = [
    { key: 'sockets', name: 'Розетки' },
    { key: 'switches', name: 'Выключатели' },
    { key: 'frames', name: 'Рамки' }
  ];
  var COLORS = { white: 'Белый', black: 'Чёрный', gray: 'Серый', nickel: 'Никель', cashmere: 'Кашемир', gold: 'Золото' };
  var STOCKS = { in: 'В наличии', order: 'Под заказ' };
  /* Ценовая группа вместо цены: настоящих цен нет, числа не придумываются */
  var PRICE_GROUPS = { 1: 'Группа 1', 2: 'Группа 2', 3: 'Группа 3' };
  var TYPES = {
    switch: { symbol: 'p-switch', cat: 'switches', name: 'Демонстрационный выключатель' },
    double: { symbol: 'p-double', cat: 'switches', name: 'Демонстрационный двойной выключатель' },
    socket: { symbol: 'p-socket', cat: 'sockets', name: 'Демонстрационная розетка' },
    frame: { symbol: 'p-frame', cat: 'frames', name: 'Демонстрационная рамка' }
  };
  var TONES = ['light', 'sand', 'stone', 'light', 'stone', 'sand'];
  var ROWS = [
    ['switch', 'white', 'A', 2, 'in'], ['socket', 'black', 'B', 1, 'in'], ['double', 'nickel', 'C', 3, 'order'], ['frame', 'cashmere', 'D', 2, 'in'],
    ['switch', 'black', 'B', 1, 'order'], ['socket', 'white', 'A', 3, 'in'], ['double', 'gray', 'C', 2, 'in'], ['frame', 'white', 'A', 1, 'order'],
    ['switch', 'gold', 'D', 3, 'in'], ['socket', 'nickel', 'B', 2, 'in'], ['double', 'black', 'A', 1, 'order'], ['frame', 'black', 'C', 3, 'in']
  ];
  var PRODUCTS = ROWS.map(function (r, i) {
    var t = TYPES[r[0]];
    return {
      id: i + 1, type: r[0], symbol: t.symbol, cat: t.cat, name: t.name, color: r[1], collection: 'Коллекция ' + r[2],
      group: r[3], stock: r[4], sku: 'ХХХ-' + ('00' + (i + 1)).slice(-3), tone: TONES[i % TONES.length]
    };
  });
  var STUB_CART = 'Демонстрационное действие: корзина не реализована, товар не добавлен.';

  function catName(key) { return CATEGORIES.filter(function (c) { return c.key === key; })[0].name; }
  function tag(t) { return '<span class="fk-tag">' + t + '</span>'; }
  function illu(p) {
    return '<svg class="fin-' + p.color + '" viewBox="0 0 240 240" aria-hidden="true" focusable="false">' +
      '<use href="#shade150" transform="translate(45 45)"/><use href="#' + p.symbol + '" x="45" y="45" width="150" height="150"/></svg>';
  }
  function card(p) {
    var url = 'product.html?id=' + p.id;
    return '<li class="fk-product-card">' +
      '<a class="fk-product-card__media tone-' + p.tone + '" href="' + url + '" tabindex="-1" aria-hidden="true">' + illu(p) + '</a>' +
      '<h3 class="fk-product-card__name"><a href="' + url + '">' + p.name + '</a></h3>' +
      '<p class="fk-product-card__sku">Арт. <span class="fk-stub">' + p.sku + '</span></p>' +
      '<p class="fk-product-card__sku">Цвет: ' + COLORS[p.color] + '</p>' +
      '<p class="fk-product-card__price"><span class="fk-stub">ХХХХ ₽</span></p>' +
      '<p class="fk-product-card__stock">' + STOCKS[p.stock] + ' (демо)</p>' +
      '<button class="fk-btn fk-btn--primary fk-btn--card" type="button" data-stub="' + STUB_CART + '">В корзину<span class="fk-vh"> (демо) — ' + p.name + ', ' + COLORS[p.color] + '</span><span aria-hidden="true">&nbsp;(демо)</span></button>' +
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

    /* Категории: ссылки, текущая отмечена */
    [].forEach.call(document.querySelectorAll('.fk-cat-nav a'), function (a) {
      var on = (a.getAttribute('data-cat') || '') === category;
      if (on) a.setAttribute('aria-current', 'page'); else a.removeAttribute('aria-current');
    });
    var h1 = document.getElementById('cat-title');
    h1.textContent = category ? catName(category) : 'Каталог';
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
        '<p class="fk-pdp__price"><span class="fk-stub">ХХХХ ₽</span> ' + tag('заглушка') + '</p>' +
        '<p class="fk-pdp__stock">' + STOCKS[p.stock] + ' ' + tag('демо') + '</p>' +
        '<p class="fk-pdp__lead">Демонстрационное описание: текст товара предоставит владелец. ' + tag('заглушка') + '</p>' +
        '<div class="fk-pdp__buy">' +
          '<div class="fk-qty" role="group" aria-label="Количество">' +
            '<button type="button" class="fk-qty__btn" data-qty="-1" aria-label="Уменьшить количество">−</button>' +
            '<input class="fk-qty__input" id="pdp-qty" type="number" inputmode="numeric" min="1" max="99" value="1" aria-label="Количество">' +
            '<button type="button" class="fk-qty__btn" data-qty="1" aria-label="Увеличить количество">+</button>' +
          '</div>' +
          '<button class="fk-btn fk-btn--primary fk-pdp__cart" type="button" data-stub="' + STUB_CART + '">В корзину (демо)</button>' +
        '</div>' +
        '<button class="fk-btn fk-btn--secondary fk-pdp__oneclick" type="button" data-stub="Демонстрационное действие: покупка в один клик не реализована.">Купить в один клик (демо)</button>' +
        '<p class="fk-pdp__more"><a class="fk-link fk-link--tap" href="index.html#configurator">Собрать в подборе</a></p>' +
        '<h2 class="fk-pdp__h">Характеристики ' + tag('демо') + '</h2>' +
        '<dl class="fk-spec">' +
          '<div><dt>Категория</dt><dd>' + cname + '</dd></div>' +
          '<div><dt>Коллекция</dt><dd>' + p.collection + ' ' + tag('демо') + '</dd></div>' +
          '<div><dt>Цвет</dt><dd>' + COLORS[p.color] + ' ' + tag('демо') + '</dd></div>' +
          '<div><dt>Материал</dt><dd>' + unk + '</dd></div>' +
          '<div><dt>Размеры</dt><dd>' + unk + '</dd></div>' +
          '<div><dt>Технические параметры</dt><dd>' + unk + '</dd></div>' +
        '</dl>' +
      '</div>';
    document.getElementById('pdp-related').innerHTML = rel.map(card).join('');
    var input = document.getElementById('pdp-qty');
    root.addEventListener('click', function (e) {
      var b = e.target.closest('[data-qty]');
      if (!b) return;
      var v = (parseInt(input.value, 10) || 1) + parseInt(b.getAttribute('data-qty'), 10);
      input.value = Math.min(99, Math.max(1, v));
    });
    input.addEventListener('change', function () {
      var v = parseInt(input.value, 10);
      input.value = isNaN(v) ? 1 : Math.min(99, Math.max(1, v));
    });
  }

  if (document.getElementById('cat-list')) initCatalog();
  if (document.getElementById('pdp')) initProduct();
})();
