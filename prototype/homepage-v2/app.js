/* FORKEL — прототип главной. Только поведение интерфейса: меню, панель каталога, пояснения к неработающим элементам.
   Нет запросов к серверу, нет хранения данных. */
(function () {
  'use strict';

  var desktop = window.matchMedia('(min-width: 1024px)');
  var nav = document.getElementById('site-nav');
  var menuBtn = document.querySelector('.fk-menu-btn');
  var catalogBtn = document.querySelector('.fk-catalog-btn');
  var header = document.querySelector('.fk-header');
  /* Пока меню открыто, недоступны для клавиатуры и скринридера все части страницы вне шапки и панели меню */
  var inertTargets = [document.getElementById('main'), document.querySelector('.fk-footer'), document.querySelector('.fk-skip')];
  var note = document.getElementById('proto-note');
  var noteTimer;

  /* Пояснение для элементов, которых в прототипе нет */
  function showNote(text) {
    if (!note) return;
    note.textContent = text;
    note.classList.add('is-on');
    clearTimeout(noteTimer);
    noteTimer = setTimeout(function () { note.classList.remove('is-on'); }, 3500);
  }
  document.addEventListener('click', function (e) {
    var stub = e.target.closest('[data-stub]');
    if (!stub) return;
    e.preventDefault();
    showNote(stub.getAttribute('data-stub'));
  });

  /* Мобильное меню (до 1024 px) */
  /* Панель начинается под шапкой, где бы она ни находилась (над ней может быть баннер прототипа) */
  function placeMenu() {
    if (!nav || !nav.hasAttribute('data-open')) return;
    nav.style.top = Math.max(0, Math.round(document.querySelector('.fk-header').getBoundingClientRect().bottom)) + 'px';
  }
  window.addEventListener('scroll', placeMenu, { passive: true });
  window.addEventListener('resize', placeMenu);

  function setMenu(open, returnFocus) {
    if (!nav || !menuBtn) return;
    if (open) {
      nav.setAttribute('data-open', '');
      placeMenu();
    } else {
      nav.removeAttribute('data-open');
      nav.style.top = '';
    }
    menuBtn.setAttribute('aria-expanded', String(open));
    menuBtn.textContent = open ? 'Закрыть' : 'Меню';
    document.body.style.overflow = open ? 'hidden' : '';
    inertTargets.forEach(function (el) { if (el) el.inert = open; });
    if (open) {
      /* Фокус переносится внутрь панели: на первый видимый пункт меню
         (кнопка «Каталог» в мобильной панели скрыта и фокус принять не может) */
      var first = [].slice.call(nav.querySelectorAll('a[href], button')).filter(function (el) {
        return el.getClientRects().length > 0;
      })[0];
      if (first) first.focus();
    } else if (returnFocus) {
      menuBtn.focus();
    }
  }

  /* Порядок обхода при открытом меню совпадает с визуальным: логотип, кнопки шапки, затем пункты панели.
     В разметке панель стоит между логотипом и кнопками, поэтому порядок задаётся здесь. */
  function visibleFocusables() {
    var vis = function (el) {
      return el.getClientRects().length > 0 && getComputedStyle(el).visibility !== 'hidden';
    };
    var all = [].slice.call(header.querySelectorAll('a[href], button:not([disabled])')).filter(vis);
    var inNav = all.filter(function (el) { return nav.contains(el); });
    var bar = all.filter(function (el) { return !nav.contains(el); }).sort(function (a, b) {
      return a.getBoundingClientRect().left - b.getBoundingClientRect().left;
    });
    return bar.concat(inNav); /* не зависит от прокрутки панели */
  }
  function trapTab(e) {
    if (e.key !== 'Tab' || !menuBtn || menuBtn.getAttribute('aria-expanded') !== 'true' || desktop.matches) return;
    var items = visibleFocusables();
    if (!items.length) return;
    var i = items.indexOf(document.activeElement), next;
    if (i < 0) next = e.shiftKey ? items.length - 1 : 0;
    else if (e.shiftKey) next = i === 0 ? items.length - 1 : i - 1;
    else next = i === items.length - 1 ? 0 : i + 1;
    e.preventDefault();
    items[next].focus();
  }
  document.addEventListener('keydown', trapTab);
  if (menuBtn) {
    menuBtn.addEventListener('click', function () {
      setMenu(menuBtn.getAttribute('aria-expanded') !== 'true');
    });
  }
  if (nav) {
    nav.addEventListener('click', function (e) {
      if (!desktop.matches && e.target.closest('a[href^="#"]')) setMenu(false);
    });
  }

  /* Панель каталога (от 1024 px): по клику и клавишам, не по наведению */
  function setCatalog(open, returnFocus) {
    if (!catalogBtn) return;
    catalogBtn.setAttribute('aria-expanded', String(open));
    if (!open && returnFocus) catalogBtn.focus();
  }
  if (catalogBtn) {
    catalogBtn.addEventListener('click', function () {
      setCatalog(catalogBtn.getAttribute('aria-expanded') !== 'true');
    });
  }
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

  /* Блок «Подбор» (демо): радиогруппы образцов. Меняется только состояние выбора и подпись;
     превью не меняется, потому что реальных изображений комбинаций нет (H-05). */
  (function () {
    var groups = [].slice.call(document.querySelectorAll('.fk-swatches'));
    var summary = document.getElementById('cfg-summary');
    var chosen = {};
    function update() {
      if (summary) summary.textContent = 'Выбрано (демо): рамка — ' + (chosen.frame || '') + ', механизм — ' + (chosen.mech || '') + '.';
    }
    function select(group, radio, focus) {
      [].forEach.call(group.querySelectorAll('[role="radio"]'), function (r) {
        r.setAttribute('aria-checked', String(r === radio));
        r.tabIndex = r === radio ? 0 : -1;
      });
      chosen[group.getAttribute('data-part')] = radio.getAttribute('data-name');
      if (focus) radio.focus();
      update();
    }
    groups.forEach(function (group) {
      var radios = [].slice.call(group.querySelectorAll('[role="radio"]'));
      var start = radios.filter(function (r) { return r.getAttribute('aria-checked') === 'true'; })[0] || radios[0];
      select(group, start, false);
      group.addEventListener('click', function (e) {
        var r = e.target.closest('[role="radio"]');
        if (r) select(group, r, false);
      });
      group.addEventListener('keydown', function (e) {
        var i = radios.indexOf(document.activeElement);
        var step = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 }[e.key];
        if (i < 0 || !step) return;
        e.preventDefault();
        select(group, radios[(i + step + radios.length) % radios.length], true);
      });
    });
  })();

  /* При переходе на широкий экран сбросить состояние мобильного меню */
  desktop.addEventListener('change', function () {
    setMenu(false);
    setCatalog(false);
  });
})();
