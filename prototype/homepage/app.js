/* FORKEL — прототип главной. Только поведение интерфейса: меню, панель каталога, пояснения к неработающим элементам.
   Нет запросов к серверу, нет хранения данных. */
(function () {
  'use strict';

  var desktop = window.matchMedia('(min-width: 1024px)');
  var nav = document.getElementById('site-nav');
  var menuBtn = document.querySelector('.fk-menu-btn');
  var catalogBtn = document.querySelector('.fk-catalog-btn');
  var inertTargets = [document.getElementById('main'), document.querySelector('.fk-footer')];
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
    if (!open && returnFocus) menuBtn.focus();
  }
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
})();
