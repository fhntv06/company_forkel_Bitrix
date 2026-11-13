/* FORKEL V4 — общее поведение шапки (из V3 app.js): мобильное меню, панель каталога, пояснения-заглушки [data-stub].
   Нет запросов к серверу. Точка перехода — 1100 px (как в css/header.css). Соцсети — этап M3. */
(function () {
  'use strict';
  var desktop = window.matchMedia('(min-width: 1100px)');
  var nav = document.getElementById('site-nav');
  var menuBtn = document.querySelector('.fk-menu-btn');
  var catalogBtn = document.querySelector('.fk-catalog-btn');
  var header = document.querySelector('.fk-header');
  var note = document.getElementById('toast'), noteTimer;
  /* Пока меню открыто, остальная страница недоступна для клавиатуры и скринридера */
  var inertTargets = [document.getElementById('main'), document.querySelector('.foot'), document.querySelector('.skip')];

  function showNote(text) {
    if (!note) return;
    note.textContent = text; note.classList.add('on');
    clearTimeout(noteTimer); noteTimer = setTimeout(function () { note.classList.remove('on'); }, 3500);
  }
  window.fkNote = showNote;
  document.addEventListener('click', function (e) {
    var stub = e.target.closest('[data-stub]');
    if (!stub) return;
    e.preventDefault(); showNote(stub.getAttribute('data-stub'));
  });
  if (!header) return;

  function placeMenu() {
    if (!nav || !nav.hasAttribute('data-open')) return;
    nav.style.top = Math.max(0, Math.round(header.getBoundingClientRect().bottom)) + 'px';
  }
  window.addEventListener('scroll', placeMenu, { passive: true });
  window.addEventListener('resize', placeMenu);

  function setMenu(open, returnFocus) {
    if (!nav || !menuBtn) return;
    if (open) { nav.setAttribute('data-open', ''); placeMenu(); } else { nav.removeAttribute('data-open'); nav.style.top = ''; }
    menuBtn.setAttribute('aria-expanded', String(open));
    menuBtn.textContent = open ? 'Закрыть' : 'Меню';
    document.body.style.overflow = open ? 'hidden' : '';
    inertTargets.forEach(function (el) { if (el) el.inert = open; });
    if (open) {
      var first = [].slice.call(nav.querySelectorAll('a[href], button')).filter(function (el) { return el.getClientRects().length > 0; })[0];
      if (first) first.focus();
    } else if (returnFocus) menuBtn.focus();
  }

  /* Порядок обхода при открытом меню: логотип, кнопки шапки, затем пункты панели */
  function visibleFocusables() {
    var vis = function (el) { return el.getClientRects().length > 0 && getComputedStyle(el).visibility !== 'hidden'; };
    var all = [].slice.call(header.querySelectorAll('a[href], button:not([disabled])')).filter(vis);
    var inNav = all.filter(function (el) { return nav.contains(el); });
    var bar = all.filter(function (el) { return !nav.contains(el); }).sort(function (a, b) { return a.getBoundingClientRect().left - b.getBoundingClientRect().left; });
    return bar.concat(inNav);
  }
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Tab' || !menuBtn || menuBtn.getAttribute('aria-expanded') !== 'true' || desktop.matches) return;
    var items = visibleFocusables(); if (!items.length) return;
    var i = items.indexOf(document.activeElement), next;
    if (i < 0) next = e.shiftKey ? items.length - 1 : 0;
    else if (e.shiftKey) next = i === 0 ? items.length - 1 : i - 1;
    else next = i === items.length - 1 ? 0 : i + 1;
    e.preventDefault(); items[next].focus();
  });
  if (menuBtn) menuBtn.addEventListener('click', function () { setMenu(menuBtn.getAttribute('aria-expanded') !== 'true'); });
  if (nav) nav.addEventListener('click', function (e) { if (!desktop.matches && e.target.closest('a[href^="#"]')) setMenu(false); });

  /* Панель каталога (от 1100 px): по клику и клавишам, не по наведению */
  function setCatalog(open, returnFocus) {
    if (!catalogBtn) return;
    catalogBtn.setAttribute('aria-expanded', String(open));
    if (!open && returnFocus) catalogBtn.focus();
  }
  if (catalogBtn) catalogBtn.addEventListener('click', function () { setCatalog(catalogBtn.getAttribute('aria-expanded') !== 'true'); });
  document.addEventListener('click', function (e) {
    if (!catalogBtn || catalogBtn.getAttribute('aria-expanded') !== 'true') return;
    if (e.target.closest('.fk-catalog-panel') || e.target.closest('.fk-catalog-btn')) {
      if (e.target.closest('.fk-catalog-panel a')) setCatalog(false);
      return;
    }
    setCatalog(false);
  });
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    if (catalogBtn && catalogBtn.getAttribute('aria-expanded') === 'true') { setCatalog(false, true); return; }
    if (menuBtn && menuBtn.getAttribute('aria-expanded') === 'true') setMenu(false, true);
  });
  desktop.addEventListener('change', function () { setMenu(false); setCatalog(false); });
})();
