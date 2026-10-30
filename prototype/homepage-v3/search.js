/* FORKEL — окно поиска (7.1). Поиск идёт только по демонстрационным данным (FKData) в браузере, без запросов к серверу.
   Проекты — заглушки; страница всех результатов (7.2) пока не сделана. */
(function () {
  'use strict';
  var D = window.FKData;
  var buttons = document.querySelectorAll('.fk-search-btn');
  if (!D) return;

  var PROJECTS = [1, 2, 3, 4, 5, 6];
  var MAX_P = 6, MAX_PRJ = 3;
  function esc(s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
  function norm(s) { return String(s).toLowerCase().replace(/ё/g, 'е').trim(); }

  window.FKSearch = { find: find, norm: norm, esc: esc };

  var dlg = document.createElement('dialog');
  dlg.className = 'fk-sdlg';
  dlg.setAttribute('aria-labelledby', 'fk-search-title');
  dlg.innerHTML =
    '<div class="fk-search__panel">' +
    '<div class="fk-search__head"><h2 class="fk-search__title" id="fk-search-title">Поиск по сайту</h2>' +
    '<button class="fk-search__close" type="button" aria-label="Закрыть окно поиска"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 5l14 14M19 5L5 19"/></svg></button></div>' +
    '<form class="fk-search__form" role="search" novalidate>' +
    '<label class="fk-vh" for="fk-search-input">Запрос</label>' +
    '<svg class="fk-search__icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="M16 16l4.5 4.5"/></svg>' +
    '<input class="fk-search__input" id="fk-search-input" type="text" autocomplete="off" placeholder="Название, артикул, цвет, коллекция" aria-describedby="fk-search-demo">' +
    '<button class="fk-search__clear" type="button" aria-label="Очистить запрос" hidden><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg></button>' +
    '</form>' +
    '<p class="fk-search__demo" id="fk-search-demo"><span class="fk-tag">демо</span> Поиск идёт по демонстрационным данным.</p>' +
    '<div class="fk-search__body" aria-live="polite"></div></div>';
  document.body.appendChild(dlg);

  var input = dlg.querySelector('.fk-search__input');
  var clear = dlg.querySelector('.fk-search__clear');
  var body = dlg.querySelector('.fk-search__body');
  var opener = null;

  function hint() {
    var cols = ['F70', 'F55'].map(function (c) { return '<li><a class="fk-chip" href="collection.html?collection=' + c + '">Коллекция ' + c + '</a></li>'; }).join('');
    return '<p class="fk-search__lead">Введите запрос — например, «розетка», «чёрный» или «ХХХ-003».</p>' +
      '<p class="fk-search__sub">Или перейдите сразу:</p><ul class="fk-search__chips">' +
      '<li><a class="fk-chip" href="catalog.html">Весь каталог</a></li>' + cols + '<li><a class="fk-chip" href="projects.html">Проекты</a></li></ul>';
  }
  function productRow(p) {
    var url = 'product.html?id=' + p.id;
    return '<li><a class="fk-search__item" href="' + url + '"><span class="fk-search__thumb tone-' + p.tone + '">' + D.illu(p) + '</span>' +
      '<span class="fk-search__text"><span class="fk-search__name">' + esc(p.name) + '</span>' +
      '<span class="fk-search__meta">' + esc(D.COLORS[p.color]) + ' · ' + esc(p.col) + ', ' + esc(D.MATERIALS[p.mat].toLowerCase()) + ' · Арт. ' + esc(p.sku) + '</span></span>' +
      '<span class="fk-search__price">' + D.fmt(D.price(p)) + ' <span class="fk-tag">демо</span></span></a></li>';
  }
  function projectRow(i) {
    return '<li><a class="fk-search__item fk-search__item--prj" href="project.html?id=' + i + '"><span class="fk-search__thumb fk-search__thumb--prj" aria-hidden="true">' + i + '</span>' +
      '<span class="fk-search__text"><span class="fk-search__name">Демонстрационный проект ' + i + '</span><span class="fk-search__meta">Заглушка: данные проекта не заполнены</span></span></a></li>';
  }
  function find(q) {
    var words = norm(q).split(/\s+/).filter(Boolean);
    var prod = D.PRODUCTS.filter(function (p) {
      var hay = norm([p.name, D.COLORS[p.color], p.collection, D.MATERIALS[p.mat], p.sku, D.catName(p.cat), D.TYPES[p.type].mech].join(' '));
      return words.every(function (w) { return hay.indexOf(w) >= 0; });
    });
    var prj = PROJECTS.filter(function (i) {
      var hay = norm('Демонстрационный проект ' + i);
      return words.every(function (w) { return hay.indexOf(w) >= 0; });
    });
    return { prod: prod, prj: prj };
  }
  function render() {
    var q = input.value;
    clear.hidden = !q;
    if (!norm(q)) { body.innerHTML = hint(); return; }
    var r = find(q), html = '';
    if (!r.prod.length && !r.prj.length) {
      body.innerHTML = '<div class="fk-search__none"><p class="fk-search__lead">По запросу «' + esc(q.trim()) + '» ничего не найдено.</p>' +
        '<p class="fk-search__sub">Проверьте написание или откройте каталог целиком.</p><a class="fk-btn fk-btn--secondary" href="catalog.html">Открыть каталог</a></div>';
      return;
    }
    if (r.prod.length) {
      html += '<h3 class="fk-search__group">Товары</h3><ul class="fk-search__list">' + r.prod.slice(0, MAX_P).map(productRow).join('') + '</ul>';
    }
    if (r.prj.length) {
      html += '<h3 class="fk-search__group">Проекты</h3><ul class="fk-search__list">' + r.prj.slice(0, MAX_PRJ).map(projectRow).join('') + '</ul>';
    }
    var more = r.prod.length > MAX_P || r.prj.length > MAX_PRJ;
    html += '<p class="fk-search__all"><a class="fk-link fk-search__allbtn" href="search.html?q=' + encodeURIComponent(q.trim()) + '">Все результаты' +
      (more ? ' (' + (r.prod.length + r.prj.length) + ')' : '') + '</a></p>';
    body.innerHTML = html;
  }

  function open(btn) {
    opener = btn;
    if (!dlg.open) dlg.showModal();
    document.documentElement.classList.add('fk-search-open');
    render();
    input.focus();
  }
  function close() { if (dlg.open) dlg.close(); }
  dlg.addEventListener('close', function () {
    document.documentElement.classList.remove('fk-search-open');
    if (opener) opener.focus();
  });
  /* Клик вне панели (по подложке) закрывает окно */
  dlg.addEventListener('mousedown', function (e) { if (e.target === dlg) close(); });
  dlg.querySelector('.fk-search__close').addEventListener('click', close);
  clear.addEventListener('click', function () { input.value = ''; render(); input.focus(); });
  input.addEventListener('input', render);
  dlg.querySelector('.fk-search__form').addEventListener('submit', function (e) {
    e.preventDefault();
    if (norm(input.value)) location.href = 'search.html?q=' + encodeURIComponent(input.value.trim());
  });
  body.addEventListener('click', function (e) {
    var a = e.target.closest('a');
    if (a) close();
  });
  [].forEach.call(buttons, function (b) {
    b.removeAttribute('data-stub');
    b.addEventListener('click', function (e) { e.stopPropagation(); open(b); });
  });
})();
