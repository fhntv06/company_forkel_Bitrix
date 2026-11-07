/* FORKEL V4 — форма «Анкета партнёра» (cooperation.html): вкладки направления, счётчик 0/500, демо-отправка.
   Валидация — нативная HTML (required, type, maxlength). Данные никуда не отправляются и не сохраняются. */
(function () {
  'use strict';
  var form = document.getElementById('partner-form');
  if (!form) return;
  var tabs = [].slice.call(document.querySelectorAll('.fk-partner__tab'));
  var dir = document.getElementById('partner-direction');
  function select(tab, focus) {
    tabs.forEach(function (t) { var on = t === tab; t.setAttribute('aria-selected', on ? 'true' : 'false'); t.tabIndex = on ? 0 : -1; });
    dir.value = tab.getAttribute('data-role');
    if (focus) tab.focus();
  }
  tabs.forEach(function (t, i) {
    t.addEventListener('click', function () { select(t); });
    t.addEventListener('keydown', function (e) {
      var n = e.key === 'ArrowRight' ? i + 1 : e.key === 'ArrowLeft' ? i - 1 : e.key === 'Home' ? 0 : e.key === 'End' ? tabs.length - 1 : -1;
      if (n < 0) return;
      e.preventDefault(); select(tabs[(n + tabs.length) % tabs.length], true);
    });
  });
  var task = document.getElementById('pf-task'), count = document.getElementById('pf-count');
  task.addEventListener('input', function () { count.textContent = task.value.length; });
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    form.setAttribute('aria-busy', 'true');
    var note = document.getElementById('pf-note');
    note.hidden = false;
    form.setAttribute('aria-busy', 'false');
  });
})();
