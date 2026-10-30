/* FORKEL — демонстрационная корзина прототипа. Хранится в localStorage этого браузера (если доступен), иначе в памяти страницы.
   Нет заказа, оплаты и сервера. Цены демонстрационные (см. data.js). */
(function () {
  'use strict';
  var D = window.FKData;
  if (!D) return;
  var KEY = 'fk-demo-cart-v1';
  var memory = [];
  var listeners = [];

  function valid(it) {
    if (!it || typeof it.qty !== 'number' || it.qty < 1 || it.qty > 99 || it.qty % 1 !== 0) return false;
    if (it.kind === 'product') return !!D.byId(it.id);
    if (it.kind === 'kit') return D.kitValid(it);
    return false;
  }
  function load() {
    try {
      var raw = window.localStorage.getItem(KEY);
      if (raw) {
        var a = JSON.parse(raw);
        if (Array.isArray(a)) return a.filter(valid);
      }
      return [];
    } catch (e) { return memory.slice(); }
  }
  function save(items) {
    memory = items;
    try { window.localStorage.setItem(KEY, JSON.stringify(items)); } catch (e) { /* хранилище недоступно: корзина живёт до перезагрузки */ }
  }
  function keyOf(spec) {
    return spec.kind === 'kit' ? D.kit(spec).key : 'p:' + spec.id;
  }
  function describe(it) {
    if (it.kind === 'kit') {
      var k = D.kit(it);
      return { key: k.key, kind: 'kit', name: k.name, sku: k.sku, unit: k.price, url: '', parts: k.parts, media: D.kitImg(it), meta: k.collection };
    }
    var p = D.byId(it.id);
    return { key: 'p:' + p.id, kind: 'product', name: p.name, sku: p.sku, unit: D.price(p), url: 'product.html?id=' + p.id,
      media: D.illu(p), meta: 'Цвет: ' + D.COLORS[p.color], tone: p.tone };
  }
  function items() { return load(); }
  function count() { return load().reduce(function (s, it) { return s + it.qty; }, 0); }
  function total() { return load().reduce(function (s, it) { return s + it.qty * describe(it).unit; }, 0); }
  function emit() { renderBadge(); listeners.forEach(function (f) { f(); }); }

  /* spec: {kind:'product', id} или {kind:'kit', col, mech, mc, fc}. Повторное добавление увеличивает количество */
  function add(spec, qty) {
    qty = Math.max(1, Math.min(99, parseInt(qty, 10) || 1));
    var list = load(), key = keyOf(spec), found = null, clamped = false;
    list.forEach(function (it) { if (keyOf(it) === key) found = it; });
    if (found) {
      if (found.qty + qty > 99) clamped = true;
      found.qty = Math.min(99, found.qty + qty);
    } else {
      var it = { kind: spec.kind, qty: qty };
      if (spec.kind === 'product') it.id = spec.id; else { it.col = spec.col; it.mat = spec.mat; it.mech = spec.mech; it.mc = spec.mc; it.fc = spec.fc; }
      list.push(it);
    }
    save(list); emit();
    return { name: describe(found || list[list.length - 1]).name, clamped: clamped, count: count() };
  }
  function setQty(key, q) {
    var list = load();
    list.forEach(function (it) { if (keyOf(it) === key) it.qty = Math.max(1, Math.min(99, parseInt(q, 10) || 1)); });
    save(list); emit();
  }
  function remove(key) {
    save(load().filter(function (it) { return keyOf(it) !== key; })); emit();
  }

  function renderBadge() {
    var n = count();
    [].forEach.call(document.querySelectorAll('[data-cart-count]'), function (b) { b.textContent = n > 99 ? '99+' : String(n); b.hidden = n === 0; });
    [].forEach.call(document.querySelectorAll('[data-cart-sr]'), function (s) { s.textContent = n ? ', товаров: ' + n : ', пусто'; });
  }
  function note(text) { if (window.fkNote) window.fkNote(text); }
  function announceAdd(r, label) {
    note((label || 'Добавлено в корзину (демо)') + ': ' + r.name + '. ' + (r.clamped ? 'Количество ограничено 99. ' : '') + 'Товаров в корзине: ' + r.count + '.');
  }

  document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-add]');
    if (!b) return;
    e.preventDefault();
    announceAdd(add({ kind: 'product', id: parseInt(b.getAttribute('data-add'), 10) }, 1));
  });
  window.addEventListener('storage', function (e) { if (e.key === KEY) emit(); });

  window.FKCart = { items: items, count: count, total: total, add: add, setQty: setQty, remove: remove, describe: describe,
    onChange: function (f) { listeners.push(f); }, announceAdd: announceAdd };

  /* ---------- Страница корзины ---------- */
  function initCartPage() {
    var root = document.getElementById('cart-root');
    var live = document.getElementById('cart-live');
    var fmt = D.fmt, tag = D.tag;
    function line(it) {
      var d = describe(it), k = d.key;
      var parts = '';
      if (d.kind === 'kit') {
        parts = '<details class="fk-cart-item__parts"><summary>Состав комплекта</summary><ul>' + d.parts.map(function (p) {
          return '<li><span class="fk-cart-item__role">' + p.role + '</span> ' + p.name + ', ' + D.COLORS[p.color] +
            '<span class="fk-cart-item__psku">Арт. <span class="fk-stub">' + p.sku + '</span> ' + tag('заглушка') + '</span>' +
            '<span class="fk-cart-item__pprice">' + fmt(p.price) + ' ' + tag('демо') + '</span></li>';
        }).join('') + '</ul></details>';
      }
      var name = d.url ? '<a href="' + d.url + '">' + d.name + '</a>' : d.name;
      return '<li class="fk-cart-item" data-key="' + k + '">' +
        '<div class="fk-cart-item__media tone-' + (d.tone || 'light') + '">' + d.media + '</div>' +
        '<div class="fk-cart-item__body">' +
          '<h2 class="fk-cart-item__name">' + name + (d.kind === 'kit' ? ' ' + tag('комплект') : '') + '</h2>' +
          '<p class="fk-cart-item__meta">' + d.meta + '</p>' +
          '<p class="fk-cart-item__meta">Арт. <span class="fk-stub">' + d.sku + '</span> ' + tag('заглушка') + '</p>' +
          '<p class="fk-cart-item__meta">Цена за шт.: ' + fmt(d.unit) + ' ' + tag('демо') + '</p>' + parts +
        '</div>' +
        '<div class="fk-cart-item__side">' +
          '<div class="fk-qty" role="group" aria-label="Количество: ' + d.name + '">' +
            '<button type="button" class="fk-qty__btn" data-act="dec" data-key="' + k + '" aria-label="Уменьшить количество: ' + d.name + '">−</button>' +
            '<input class="fk-qty__input" type="number" inputmode="numeric" min="1" max="99" value="' + it.qty + '" data-act="qty" data-key="' + k + '" aria-label="Количество: ' + d.name + '">' +
            '<button type="button" class="fk-qty__btn" data-act="inc" data-key="' + k + '" aria-label="Увеличить количество: ' + d.name + '">+</button>' +
          '</div>' +
          '<p class="fk-cart-item__sum">' + fmt(it.qty * d.unit) + '</p>' +
          '<button type="button" class="fk-cart-item__remove" data-act="remove" data-key="' + k + '" aria-label="Удалить из корзины: ' + d.name + '">Удалить</button>' +
        '</div></li>';
    }
    function render(focusSel) {
      var list = load();
      if (!list.length) {
        root.innerHTML = '<div class="fk-empty"><h2>Корзина пуста</h2><p>Добавьте демонстрационные товары из каталога или соберите комплект в подборе.</p>' +
          '<p class="fk-empty__actions"><a class="fk-btn fk-btn--primary" href="catalog.html">Перейти в каталог</a> <a class="fk-link fk-link--tap" href="index.html#configurator">Собрать в подборе</a></p></div>';
        return;
      }
      var units = list.reduce(function (s, it) { return s + it.qty; }, 0);
      root.innerHTML = '<div class="fk-cart">' +
        '<ul class="fk-cart__list">' + list.map(line).join('') + '</ul>' +
        '<aside class="fk-cart__summary" aria-labelledby="cart-sum-title">' +
          '<h2 id="cart-sum-title">Итого ' + tag('демо') + '</h2>' +
          '<dl class="fk-spec"><div><dt>Позиций</dt><dd data-sum="positions">' + list.length + '</dd></div><div><dt>Товаров</dt><dd data-sum="units">' + units + '</dd></div>' +
          '<div><dt>Доставка</dt><dd>не рассчитана</dd></div>' +
          '<div class="fk-cart__total"><dt>Сумма</dt><dd data-sum="total">' + fmt(total()) + '</dd></div></dl>' +
          '<a class="fk-btn fk-btn--primary" href="checkout.html">Оформить заказ (демо)</a>' +
          '<a class="fk-btn fk-btn--secondary" href="catalog.html">Продолжить покупки</a>' +
          '<p class="fk-note">Цены и артикулы демонстрационные. Корзина хранится только в этом браузере.</p>' +
        '</aside></div>';
      if (focusSel) { var f = root.querySelector(focusSel); if (f) f.focus(); }
    }
    /* Изменение количества обновляет цифры на месте, без перерисовки списка (фокус и нажатия не теряются) */
    function refresh() {
      var list = load();
      [].forEach.call(root.querySelectorAll('.fk-cart-item'), function (li) {
        var it = list.filter(function (x) { return keyOf(x) === li.getAttribute('data-key'); })[0];
        if (!it) return;
        li.querySelector('input').value = it.qty;
        li.querySelector('.fk-cart-item__sum').textContent = fmt(it.qty * describe(it).unit);
      });
      var set = function (n, v) { var el = root.querySelector('[data-sum="' + n + '"]'); if (el) el.textContent = v; };
      set('positions', list.length);
      set('units', list.reduce(function (s, it) { return s + it.qty; }, 0));
      set('total', fmt(total()));
    }
    function say(t) { if (live) live.textContent = t; }
    root.addEventListener('click', function (e) {
      var b = e.target.closest('[data-act]');
      if (!b || b.tagName === 'INPUT') return;
      var key = b.getAttribute('data-key'), act = b.getAttribute('data-act');
      var it = load().filter(function (x) { return keyOf(x) === key; })[0];
      if (!it) return;
      var name = describe(it).name;
      if (act === 'remove') {
        remove(key); render();
        var t = document.getElementById('cart-title'); if (t) t.focus();
        say('Удалено из корзины: ' + name + '. Сумма: ' + fmt(total()) + '.');
      } else {
        setQty(key, it.qty + (act === 'inc' ? 1 : -1));
        refresh();
        say(name + ': количество ' + load().filter(function (x) { return keyOf(x) === key; })[0].qty + '. Сумма: ' + fmt(total()) + '.');
      }
    });
    root.addEventListener('change', function (e) {
      var i = e.target.closest('input[data-act="qty"]');
      if (!i) return;
      var key = i.getAttribute('data-key');
      setQty(key, i.value);
      refresh();
      say('Количество изменено. Сумма: ' + fmt(total()) + '.');
    });
    window.addEventListener('storage', function (e) { if (e.key === KEY) render(); });
    render();
  }

  renderBadge();
  if (document.getElementById('cart-root')) initCartPage();
})();
