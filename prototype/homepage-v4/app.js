/* FORKEL — прототип главной V4. Только поведение интерфейса главной: связки подбора. Карточки «Новинок» — статичная разметка V3 (избранное — favorites.js, корзина — cart.js, цветовые точки — data.js).
   Нет запросов к серверу и хранения данных. Цвета и карточки — демонстрационные. */
(function () {
  'use strict';

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

})();
