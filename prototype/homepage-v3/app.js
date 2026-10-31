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
  window.fkNote = showNote;
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

  /* При переходе на широкий экран сбросить состояние мобильного меню */
  desktop.addEventListener('change', function () {
    setMenu(false);
    setCatalog(false);
  });
  /* Плавающий виджет соцсетей (справа внизу). Адреса пока заглушки: url пустой → показывается пояснение.
   Список общий: window.FKSocial используется и подвалом. */
  var SOC = [
    { id: 'vk', name: 'ВКонтакте', short: 'VK', url: '' },
    { id: 'max', name: 'MAX', short: 'MAX', url: '' },
    { id: 'tg', name: 'Telegram', short: 'TG', url: '' }
  ];
  window.FKSocial = SOC;
  function socLink(s, cls) {
    var a = document.createElement('a');
    a.className = cls; a.textContent = s.short;
    a.setAttribute('aria-label', s.name + (s.url ? ' (откроется в новой вкладке)' : ''));
    if (s.url) { a.href = s.url; a.target = '_blank'; a.rel = 'noopener noreferrer'; }
    else { a.href = '#'; a.setAttribute('data-stub', 'Ссылка на ' + s.name + ' появится после получения адреса от владельца.'); }
    return a;
  }
  (function () {
    var box = document.createElement('div');
    box.className = 'fk-soc';
    var list = document.createElement('ul');
    list.className = 'fk-soc__list'; list.id = 'fk-soc-list'; list.hidden = true;
    SOC.forEach(function (s) { var li = document.createElement('li'); li.appendChild(socLink(s, 'fk-soc__item')); list.appendChild(li); });
    var toggle = document.createElement('button');
    toggle.type = 'button'; toggle.className = 'fk-soc__btn';
    toggle.setAttribute('aria-expanded', 'false'); toggle.setAttribute('aria-controls', 'fk-soc-list');
    toggle.setAttribute('aria-label', 'Мы в соцсетях');
    toggle.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path class="fk-soc__chat" d="M4 5h16v11H9l-5 4V5z"/><path class="fk-soc__x" d="M6 6l12 12M18 6L6 18"/></svg>';
    box.appendChild(list); box.appendChild(toggle);
    document.body.appendChild(box);
    function set(open, focus) {
      list.hidden = !open;
      toggle.setAttribute('aria-expanded', String(open));
      if (!open && focus) toggle.focus();
    }
    toggle.addEventListener('click', function () { set(list.hidden); });
    document.addEventListener('click', function (e) { if (!list.hidden && !box.contains(e.target)) set(false); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !list.hidden) set(false, true); });
  })();
})();
