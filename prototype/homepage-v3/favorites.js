/* FORKEL — демонстрационное «Избранное». Хранится в localStorage этого браузера (если доступен); сервера и аккаунта нет. */
(function () {
  'use strict';
  var D = window.FKData;
  if (!D) return;
  var KEY = 'fk-demo-fav-v1';
  var memory = [];

  function load() {
    try {
      var raw = window.localStorage.getItem(KEY);
      if (raw) {
        var a = JSON.parse(raw);
        if (Array.isArray(a)) return a.filter(function (id, i) { return typeof id === 'number' && a.indexOf(id) === i && !!D.byId(id); });
      }
      return [];
    } catch (e) { return memory.slice(); }
  }
  function save(ids) {
    memory = ids;
    try { window.localStorage.setItem(KEY, JSON.stringify(ids)); } catch (e) { /* хранилище недоступно: список живёт до перезагрузки */ }
  }
  function has(id) { return load().indexOf(id) >= 0; }
  function toggle(id) {
    var ids = load(), i = ids.indexOf(id);
    if (i >= 0) ids.splice(i, 1); else if (D.byId(id)) ids.push(id);
    save(ids); emit();
    return i < 0;
  }
  function clear() { save([]); emit(); }

  function renderBadge() {
    var n = load().length;
    [].forEach.call(document.querySelectorAll('[data-fav-count]'), function (b) { b.textContent = n > 99 ? '99+' : String(n); b.hidden = n === 0; });
    [].forEach.call(document.querySelectorAll('[data-fav-sr]'), function (s) { s.textContent = n ? ', товаров: ' + n : ''; });
    var ids = load();
    [].forEach.call(document.querySelectorAll('[data-fav]'), function (b) {
      var on = ids.indexOf(parseInt(b.getAttribute('data-fav'), 10)) >= 0;
      b.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
  }
  function emit() { renderBadge(); renderPage(); }

  var HEART = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20s-7-4.4-7-10a4 4 0 017-2.6A4 4 0 0119 10c0 5.6-7 10-7 10z"/></svg>';
  /* статичные карточки (главная) не проходят через shop.js: добавляем сердце по id из ссылки */
  function decorate() {
    [].forEach.call(document.querySelectorAll('.fk-product-card'), function (li) {
      if (li.querySelector('[data-fav]')) return;
      var a = li.querySelector('a[href*="product.html?id="]'), m = a && /id=(\d+)/.exec(a.getAttribute('href'));
      if (!m) return;
      var h = li.querySelector('.fk-product-card__name a'), name = h ? h.textContent : '';
      var b = document.createElement('button');
      b.type = 'button'; b.className = 'fk-fav-btn'; b.setAttribute('data-fav', m[1]); b.setAttribute('aria-pressed', 'false');
      b.setAttribute('aria-label', 'В избранное — ' + name);
      b.innerHTML = HEART;
      li.insertBefore(b, li.firstChild);
    });
  }

  function renderPage() {
    var list = document.getElementById('fav-list');
    if (!list) return;
    var ids = load(), empty = document.getElementById('fav-empty'), tools = document.getElementById('fav-tools');
    var card = window.FKShopCard;
    list.innerHTML = card ? ids.map(function (id) { return card(D.byId(id)); }).join('') : '';
    empty.hidden = ids.length > 0;
    tools.hidden = ids.length === 0;
    document.getElementById('fav-count').textContent = ids.length ? 'Товаров в избранном: ' + ids.length : '';
    renderBadge();
  }

  document.addEventListener('click', function (e) {
    var b = e.target.closest && e.target.closest('[data-fav]');
    if (b) { toggle(parseInt(b.getAttribute('data-fav'), 10)); return; }
    if (e.target.closest && e.target.closest('[data-fav-clear]')) clear();
  });
  window.addEventListener('storage', function (e) { if (e.key === KEY) emit(); });

  window.FKFav = { has: has, toggle: toggle, clear: clear, ids: load, count: function () { return load().length; } };
  function init() { decorate(); emit(); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
