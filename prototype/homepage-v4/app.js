/* FORKEL — прототип главной V4. Только поведение интерфейса: меню, выбор цвета в подборе, пояснения для неработающих элементов.
   Нет запросов к серверу и хранения данных. Цвета и карточки — демонстрационные. */
(function () {
  'use strict';

  var COLORS = [
    ['white', 'Белый глянцевый', '#FBFBFA'], ['whitem', 'Белый матовый', '#EDEDEA'], ['gold', 'Золотой', '#D9BE7A'],
    ['cashmere', 'Кашемир матовый', '#D8CBB8'], ['gray', 'Серый', '#9A9DA2'], ['black', 'Чёрный матовый', '#2F3033']
  ];

  /* ---------- Пояснения для неработающих элементов ---------- */
  var toast = document.getElementById('toast'), timer;
  function note(t) {
    toast.textContent = t; toast.classList.add('on');
    clearTimeout(timer); timer = setTimeout(function () { toast.classList.remove('on'); }, 3500);
  }
  document.addEventListener('click', function (e) {
    var s = e.target.closest('[data-stub]');
    if (!s) return;
    e.preventDefault(); note(s.getAttribute('data-stub'));
  });

  /* ---------- Мобильное меню ---------- */
  var btn = document.querySelector('.menu'), nav = document.getElementById('nav');
  var wide = window.matchMedia('(min-width: 1100px)');
  function setMenu(open) {
    if (open) nav.setAttribute('data-open', ''); else nav.removeAttribute('data-open');
    btn.setAttribute('aria-expanded', String(open));
    document.body.style.overflow = open ? 'hidden' : '';
  }
  btn.addEventListener('click', function () { setMenu(btn.getAttribute('aria-expanded') !== 'true'); });
  nav.addEventListener('click', function (e) { if (e.target.closest('a')) setMenu(false); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && btn.getAttribute('aria-expanded') === 'true') { setMenu(false); btn.focus(); }
  });
  wide.addEventListener('change', function () { setMenu(false); });

  /* ---------- Подбор по цвету: две радиогруппы, превью перекрашивается ---------- */
  var view = document.getElementById('cfg-view');
  var state = { frame: 0, mech: 0 };
  var out = { frame: document.getElementById('o-f'), mech: document.getElementById('o-m') };

  function paint(part) {
    var c = COLORS[state[part]];
    view.style.setProperty(part === 'frame' ? '--frame' : '--mech', c[2]);
    out[part].textContent = c[1];
  }
  [].forEach.call(document.querySelectorAll('.sw'), function (group) {
    var part = group.getAttribute('data-part');
    COLORS.forEach(function (c) {
      var b = document.createElement('button');
      b.type = 'button'; b.setAttribute('role', 'radio'); b.title = c[1];
      b.style.setProperty('--c', c[2]);
      b.innerHTML = '<span class="vh">' + c[1] + '</span>';
      group.appendChild(b);
    });
    var radios = [].slice.call(group.children);
    function select(i, focus) {
      state[part] = i;
      radios.forEach(function (r, k) { r.setAttribute('aria-checked', String(k === i)); r.tabIndex = k === i ? 0 : -1; });
      paint(part);
      if (focus) radios[i].focus();
    }
    group.addEventListener('click', function (e) { var r = e.target.closest('[role="radio"]'); if (r) select(radios.indexOf(r), false); });
    group.addEventListener('keydown', function (e) {
      var i = radios.indexOf(document.activeElement);
      var step = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 }[e.key];
      if (i < 0 || !step) return;
      e.preventDefault(); select((i + step + radios.length) % radios.length, true);
    });
    select(0, false);
  });

  /* Карточка коллекции переносит к подбору и подставляет цвет рамки коллекции (демо) */
  [].forEach.call(document.querySelectorAll('[data-col]'), function (a) {
    a.addEventListener('click', function () {
      var map = { A: 5, B: 1, C: 3 };
      var i = map[a.getAttribute('data-col')];
      var radios = document.querySelectorAll('.sw[data-part="frame"] [role="radio"]');
      if (radios[i]) radios[i].click();
    });
  });

  /* ---------- Новинки (демонстрационные карточки, без цен и остатков) ---------- */
  var items = [['Демонстрационный выключатель', 0], ['Демонстрационная розетка', 2], ['Демонстрационная рамка', 4], ['Демонстрационный светорегулятор', 5]];
  var prod = document.getElementById('prod');
  items.forEach(function (it, i) {
    var c = COLORS[it[1]];
    var li = document.createElement('li');
    li.className = 'card';
    li.innerHTML =
      '<div class="card__img"><svg viewBox="0 0 120 120" aria-hidden="true"><rect x="6" y="6" width="108" height="108" rx="12" fill="' + c[2] + '" stroke="#2B2B2B" stroke-opacity=".18"/>' +
      '<rect x="30" y="30" width="60" height="60" rx="8" fill="#fff" fill-opacity=".45" stroke="#2B2B2B" stroke-opacity=".14"/></svg></div>' +
      '<h3>' + it[0] + '</h3><small>Арт. ХХХ-00' + (i + 1) + ' <span class="tag">заглушка</span></small>' +
      '<p class="price">Цена уточняется</p><small>Наличие уточняется</small>' +
      '<button class="btn btn--dark btn--sm" type="button" data-stub="Корзина не подключена в прототипе главной.">В корзину (демо)<span class="vh"> — ' + it[0] + '</span></button>';
    prod.appendChild(li);
  });
})();
