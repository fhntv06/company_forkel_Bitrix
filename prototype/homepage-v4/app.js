/* FORKEL — прототип главной V4. Только поведение интерфейса главной: связки подбора, карточки новинок.
   Нет запросов к серверу и хранения данных. Цвета и карточки — демонстрационные. */
(function () {
  'use strict';

  var COLORS = [
    ['white', 'Белый глянцевый', '#FBFBFA'], ['whitem', 'Белый матовый', '#EDEDEA'], ['gold', 'Золотой', '#D9BE7A'],
    ['cashmere', 'Кашемир матовый', '#D8CBB8'], ['gray', 'Серый', '#9A9DA2'], ['black', 'Чёрный матовый', '#2F3033']
  ];

  /* Пояснения [data-stub], меню и шапка — site.js (общие для всех страниц) */

  /* ---------- Подбор: логика V3 — configurator.js (без изменений); здесь только связки главной ---------- */
  /* Карточка коллекции (демо) подставляет цвет рамки в подборе */
  [].forEach.call(document.querySelectorAll('[data-col]'), function (a) {
    a.addEventListener('click', function () {
      var fin = { A: 'black', B: 'white', C: 'cashmere' }[a.getAttribute('data-col')];
      var r = document.querySelector('#cfg [data-part="frame"] [data-fin="fin-' + fin + '"]');
      if (r) r.click();
    });
  });
  /* Ссылка «Открыть подбор» несёт выбранную коллекцию (страница подбора читает ?collection=) */
  var cfg = document.getElementById('cfg'), open = document.getElementById('cfg-open');
  if (cfg && open) {
    var upd = function () {
      var c = cfg.querySelector('[data-part="collection"] [aria-checked="true"]');
      if (c) open.setAttribute('href', 'configurator.html?collection=' + c.getAttribute('data-key'));
    };
    cfg.addEventListener('click', function () { setTimeout(upd, 0); });
    cfg.addEventListener('keydown', function () { setTimeout(upd, 0); });
    upd();
  }

  /* ---------- Новинки (демонстрационные карточки, без цен и остатков) ---------- */
  var items = [['Демонстрационный выключатель', 0], ['Демонстрационная розетка', 2], ['Демонстрационная рамка', 4], ['Демонстрационный светорегулятор', 5]];
  var prod = document.getElementById('prod');
  if (prod) items.forEach(function (it, i) {
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
