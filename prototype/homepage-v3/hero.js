/* FORKEL — Hero: демо-выбор отделки и слот под реальную фотографию.
   Без библиотек. Работает на любой странице, где есть .fk-hero. */
(function () {
  'use strict';

  var hero = document.querySelector('.fk-hero');
  if (!hero) return;

  /* ---- Выбор отделки (радио-группа с клавиатурой) ---- */
  var group = hero.querySelector('[role="radiogroup"]');
  var nameEl = hero.querySelector('#finish-name');
  var radios = group ? Array.prototype.slice.call(group.querySelectorAll('[role="radio"]')) : [];

  function select(radio, focus) {
    radios.forEach(function (r) {
      var on = r === radio;
      r.setAttribute('aria-checked', on ? 'true' : 'false');
      r.tabIndex = on ? 0 : -1;
    });
    hero.setAttribute('data-finish', radio.getAttribute('data-finish'));
    if (nameEl) nameEl.textContent = radio.getAttribute('data-name');
    if (focus) radio.focus();
  }

  radios.forEach(function (r) {
    r.addEventListener('click', function () { select(r, false); });
    r.addEventListener('keydown', function (e) {
      var i = radios.indexOf(r), next = null;
      if (e.key === 'ArrowRight' || e.key === 'ArrowDown') next = radios[(i + 1) % radios.length];
      else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') next = radios[(i - 1 + radios.length) % radios.length];
      else if (e.key === 'Home') next = radios[0];
      else if (e.key === 'End') next = radios[radios.length - 1];
      if (next) { e.preventDefault(); select(next, true); }
    });
  });

  /* ---- Слот под реальную фотографию ----
     Когда фото будет: в секции укажите data-photo="assets/hero.jpg" и data-photo-alt="описание".
     Пока атрибут пуст, запросов к файлам нет, показывается временная визуализация. */
  var src = hero.getAttribute('data-photo');
  var img = hero.querySelector('.fk-hero__photo');
  if (src && img) {
    img.onload = function () { hero.classList.add('has-photo'); };
    img.onerror = function () { hero.classList.remove('has-photo'); };
    img.alt = hero.getAttribute('data-photo-alt') || '';
    img.src = src;
  }
})();
