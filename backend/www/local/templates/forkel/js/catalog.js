/* FORKEL — каталог: переключатель фильтров на мобильных, автоотправка сортировки. */
(function () {
  'use strict';
  var toggle = document.getElementById('cat-filters-toggle');
  var panel = document.getElementById('cat-filters');
  if (toggle && panel) {
    toggle.addEventListener('click', function () {
      var open = toggle.getAttribute('aria-expanded') !== 'true';
      toggle.setAttribute('aria-expanded', String(open));
      panel.classList.toggle('is-open', open);
    });
  }
  [].forEach.call(document.querySelectorAll('[data-submit-on-change]'), function (el) {
    el.addEventListener('change', function () { if (el.form) el.form.submit(); });
  });
})();
