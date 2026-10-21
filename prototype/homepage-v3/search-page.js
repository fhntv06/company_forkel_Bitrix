/* FORKEL — страница результатов поиска (7.2): search.html?q=…&type=all|products|projects. Поиск — в браузере по демонстрационным
   данным; проекты — заглушки. Счётчики показывают реально найденное количество. */
(function () {
  'use strict';
  var D = window.FKData, S = window.FKSearch, card = window.FKShopCard;
  var form = document.getElementById('sr-form');
  if (!form || !D || !S || !card) return;
  var ART = [{"tone": "tone-graphite", "svg": "<svg viewBox=\"0 0 400 300\" preserveAspectRatio=\"xMidYMid slice\" aria-hidden=\"true\" focusable=\"false\"><use href=\"#room\"/><use href=\"#shade150\" transform=\"translate(250 120) scale(0.6667)\"/><use class=\"fin-white\" href=\"#p-switch\" x=\"250\" y=\"120\" width=\"100\" height=\"100\"/><use href=\"#shade150\" transform=\"translate(150 210) scale(0.4000)\"/><use class=\"fin-white\" href=\"#p-socket\" x=\"150\" y=\"210\" width=\"60\" height=\"60\"/></svg>"}, {"tone": "tone-light", "svg": "<svg viewBox=\"0 0 400 300\" preserveAspectRatio=\"xMidYMid slice\" aria-hidden=\"true\" focusable=\"false\"><use href=\"#room\"/><use href=\"#shade150\" transform=\"translate(165 100) scale(0.4667)\"/><use class=\"fin-black\" href=\"#p-socket\" x=\"165\" y=\"100\" width=\"70\" height=\"70\"/></svg>"}, {"tone": "tone-sand", "svg": "<svg viewBox=\"0 0 400 300\" preserveAspectRatio=\"xMidYMid slice\" aria-hidden=\"true\" focusable=\"false\"><use href=\"#room\"/><use href=\"#shade150\" transform=\"translate(140 92) scale(0.4267)\"/><use class=\"fin-white\" href=\"#p-double\" x=\"140\" y=\"92\" width=\"64\" height=\"64\"/></svg>"}, {"tone": "tone-stone", "svg": "<svg viewBox=\"0 0 400 300\" preserveAspectRatio=\"xMidYMid slice\" aria-hidden=\"true\" focusable=\"false\"><use href=\"#room\"/><use href=\"#shade150\" transform=\"translate(165 100) scale(0.4667)\"/><use class=\"fin-gold\" href=\"#p-socket\" x=\"165\" y=\"100\" width=\"70\" height=\"70\"/></svg>"}, {"tone": "tone-gold", "svg": "<svg viewBox=\"0 0 400 300\" preserveAspectRatio=\"xMidYMid slice\" aria-hidden=\"true\" focusable=\"false\"><use href=\"#room\"/><use href=\"#shade150\" transform=\"translate(140 92) scale(0.4267)\"/><use class=\"fin-cashmere\" href=\"#p-double\" x=\"140\" y=\"92\" width=\"64\" height=\"64\"/></svg>"}, {"tone": "tone-light", "svg": "<svg viewBox=\"0 0 400 300\" preserveAspectRatio=\"xMidYMid slice\" aria-hidden=\"true\" focusable=\"false\"><use href=\"#room\"/><use href=\"#shade150\" transform=\"translate(250 120) scale(0.6667)\"/><use class=\"fin-gray\" href=\"#p-switch\" x=\"250\" y=\"120\" width=\"100\" height=\"100\"/><use href=\"#shade150\" transform=\"translate(150 210) scale(0.4000)\"/><use class=\"fin-gray\" href=\"#p-socket\" x=\"150\" y=\"210\" width=\"60\" height=\"60\"/></svg>"}];
  var input = document.getElementById('sr-input');
  var title = document.getElementById('sr-title');
  var bar = document.getElementById('sr-bar');
  var count = document.getElementById('sr-count');
  var tabs = document.getElementById('sr-tabs');
  var secP = document.getElementById('sr-products'), secJ = document.getElementById('sr-projects');
  var listP = document.getElementById('sr-products-list'), listJ = document.getElementById('sr-projects-list');
  var empty = document.getElementById('sr-empty');
  function param(n) { var m = new RegExp('[?&]' + n + '=([^&]*)').exec(location.search); return m ? decodeURIComponent(m[1].replace(/\+/g, ' ')) : ''; }
  var q = param('q').trim(), type = param('type');
  if (['all', 'products', 'projects'].indexOf(type) < 0) type = 'all';

  function projCard(i) {
    var a = ART[i - 1];
    return '<li><a class="fk-project-card ' + a.tone + '" href="project.html?id=' + i + '">' +
      '<span class="fk-project-card__media">' + a.svg + '<span class="fk-tag fk-tag--float">Заглушка: фото проекта</span></span>' +
      '<span class="fk-project-card__cap"><h3 class="fk-project-card__name">Демонстрационный проект ' + i + '</h3>' +
      '<span class="fk-project-card__meta">Помещение — заглушка · Коллекция — заглушка</span></span></a></li>';
  }
  function chips() {
    return '<ul class="fk-search__chips">' +
      '<li><a class="fk-chip" href="catalog.html">Весь каталог</a></li>' +
      ['A', 'B', 'C', 'D'].map(function (c) { return '<li><a class="fk-chip" href="collection.html?collection=' + c + '">Коллекция ' + c + '</a></li>'; }).join('') +
      '<li><a class="fk-chip" href="projects.html">Проекты</a></li></ul>';
  }
  function url() { return 'search.html' + (q ? '?q=' + encodeURIComponent(q) + (type !== 'all' ? '&type=' + type : '') : ''); }
  function plural(n, a, b, c) { var m = n % 100, k = n % 10; return (m > 10 && m < 20) ? c : k === 1 ? a : (k > 1 && k < 5) ? b : c; }

  function render() {
    input.value = q;
    var r = q ? S.find(q) : { prod: [], prj: [] };
    var total = r.prod.length + r.prj.length;
    document.title = 'FORKEL — ' + (q ? 'поиск: ' + q : 'результаты поиска') + ' (прототип)';
    title.textContent = q ? 'Результаты поиска: «' + q + '»' : 'Результаты поиска';
    bar.hidden = true; secP.hidden = true; secJ.hidden = true; empty.hidden = true;
    if (!q) {
      empty.hidden = false;
      empty.innerHTML = '<h2>Введите запрос</h2><p>Например, «розетка», «чёрный» или «ХХХ-003».</p>' + chips();
      return;
    }
    if (!total) {
      empty.hidden = false;
      empty.innerHTML = '<h2>Ничего не найдено</h2><p>По запросу «' + S.esc(q) + '» ничего нет. Проверьте написание или откройте каталог.</p>' + chips();
      return;
    }
    bar.hidden = false;
    count.textContent = 'Найдено: ' + total + ' (товаров — ' + r.prod.length + ', проектов — ' + r.prj.length + '). Данные демонстрационные.';
    var defs = [['all', 'Все', total], ['products', 'Товары', r.prod.length], ['projects', 'Проекты', r.prj.length]];
    tabs.innerHTML = defs.map(function (d) {
      return '<button class="fk-chip" type="button" data-type="' + d[0] + '" aria-pressed="' + (type === d[0]) + '"' + (d[2] ? '' : ' disabled') + '>' + d[1] + ' <span class="fk-sr-n">' + d[2] + '</span></button>';
    }).join('');
    if (type === 'products' && !r.prod.length || type === 'projects' && !r.prj.length) type = 'all';
    if (r.prod.length && type !== 'projects') { secP.hidden = false; listP.innerHTML = r.prod.map(card).join(''); }
    if (r.prj.length && type !== 'products') { secJ.hidden = false; listJ.innerHTML = r.prj.map(projCard).join(''); }
  }
  function sync() { try { history.replaceState(null, '', url()); } catch (e) { /* адрес не обновляется */ } }
  form.addEventListener('submit', function (e) { e.preventDefault(); q = input.value.trim(); type = 'all'; sync(); render(); });
  tabs.addEventListener('click', function (e) {
    var b = e.target.closest('[data-type]');
    if (!b || b.disabled) return;
    type = b.getAttribute('data-type'); sync(); render();
    var nb = tabs.querySelector('[data-type="' + type + '"]'); if (nb) nb.focus();
  });
  render();
})();
