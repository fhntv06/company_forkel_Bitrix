/* FORKEL — форма заявки на странице «О компании». Демонстрация: данные никуда не отправляются и не сохраняются. */
(function () {
  'use strict';
  var form = document.getElementById('ab-form');
  if (!form) return;
  function $(id) { return document.getElementById(id); }
  var box = form.querySelector('.fk-co-errors'), submit = form.querySelector('.fk-ab-form__submit'), done = $('ab-done'), timer = 0;
  function setErr(id, msg) {
    var inp = $(id), er = $(id + '-err');
    er.textContent = msg; er.hidden = !msg;
    if (msg) inp.setAttribute('aria-invalid', 'true'); else inp.removeAttribute('aria-invalid');
  }
  function validContact(v) {
    v = v.trim();
    if (/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) return true;
    var d = v.replace(/\D/g, '');
    return /^[+\d][\d\s()\-+]*$/.test(v) && d.length >= 10 && d.length <= 15;
  }
  form.addEventListener('input', function (e) { if (e.target.id && e.target.getAttribute('aria-invalid')) setErr(e.target.id, ''); });
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (submit.disabled) return;
    done.hidden = true;
    var rules = [
      ['ab-name', $('ab-name').value.trim().length >= 2, 'Укажите имя (не короче 2 символов).'],
      ['ab-contact', validContact($('ab-contact').value), 'Укажите телефон (10–15 цифр) или email.'],
      ['ab-consent', $('ab-consent').checked, 'Нужно согласие на обработку данных.']
    ];
    var bad = rules.filter(function (r) { setErr(r[0], r[1] ? '' : r[2]); return !r[1]; });
    box.hidden = !bad.length;
    box.innerHTML = bad.length ? 'Исправьте ошибки: ' + bad.map(function (r) { return '<a href="#' + r[0] + '" data-focus="' + r[0] + '">' + r[2].replace(/\.$/, '') + '</a>'; }).join('') : '';
    if (bad.length) { $(bad[0][0]).focus(); return; }
    submit.disabled = true; submit.textContent = 'Отправка…';
    timer = setTimeout(function () {
      form.reset(); submit.disabled = false; submit.textContent = 'Отправить (демо)';
      done.hidden = false; done.focus();
    }, 900);
  });
  box.addEventListener('click', function (e) {
    var a = e.target.closest('[data-focus]');
    if (a) { e.preventDefault(); $(a.getAttribute('data-focus')).focus(); }
  });
})();
