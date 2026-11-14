/* FORKEL — встроенные формы страниц («О компании», «Сотрудничество»): form[data-fk-form].
   Проверка полей по атрибутам data-v (name | contact | phone | text | consent), отправка POST на form[action] (/local/ajax/feedback.php),
   ответ JSON {ok, errors[]}. Режим заявки — скрытое поле mode. Разметка полей и ошибок — как в V4 (.fk-co-field, .fk-co-error). */
(function () {
  'use strict';
  function validContact(v, phoneOnly) {
    v = v.trim();
    if (!phoneOnly && /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) return true;
    var d = v.replace(/\D/g, '');
    return /^[+\d][\d\s()\-+]*$/.test(v) && d.length >= 10 && d.length <= 15;
  }
  var RULES = {
    name: function (v) { return v.trim().length >= 2 ? '' : 'Укажите имя (не короче 2 символов).'; },
    contact: function (v) { return validContact(v, false) ? '' : 'Укажите телефон (10–15 цифр) или email.'; },
    phone: function (v) { return validContact(v, true) ? '' : 'Укажите телефон (10–15 цифр).'; },
    text: function (v) { return v.trim().length >= 5 ? '' : 'Заполните поле (не короче 5 символов).'; }
  };

  [].forEach.call(document.querySelectorAll('form[data-fk-form]'), function (form) {
    var box = form.querySelector('.fk-co-errors');
    var submit = form.querySelector('[type="submit"]');
    var done = form.querySelector('[data-form-done]');
    var label = submit ? submit.textContent : '';

    function setErr(inp, msg) {
      var er = inp.id && document.getElementById(inp.id + '-err');
      if (er) { er.textContent = msg; er.hidden = !msg; }
      if (msg) inp.setAttribute('aria-invalid', 'true'); else inp.removeAttribute('aria-invalid');
    }
    function summary(items) {
      if (!box) return;
      box.hidden = !items.length;
      box.textContent = '';
      if (!items.length) return;
      box.appendChild(document.createTextNode('Исправьте ошибки: '));
      items.forEach(function (it) {
        var a = document.createElement('a');
        a.textContent = it.msg.replace(/\.$/, '');
        a.href = it.id ? '#' + it.id : '#';
        if (it.id) a.setAttribute('data-focus', it.id);
        box.appendChild(a);
      });
    }
    function validate() {
      var bad = [];
      [].forEach.call(form.querySelectorAll('[data-v]'), function (inp) {
        var kind = inp.getAttribute('data-v'), msg = '';
        if (kind === 'consent') msg = inp.checked ? '' : 'Нужно согласие на обработку данных.';
        else if (RULES[kind] && (inp.required || inp.value.trim() !== '')) msg = RULES[kind](inp.value);
        setErr(inp, msg);
        if (msg) bad.push({ id: inp.id, msg: msg, el: inp });
      });
      summary(bad);
      return bad;
    }

    form.addEventListener('input', function (e) {
      if (e.target.getAttribute && e.target.getAttribute('aria-invalid')) setErr(e.target, '');
    });
    if (box) box.addEventListener('click', function (e) {
      var a = e.target.closest('[data-focus]');
      if (!a) return;
      e.preventDefault();
      var el = document.getElementById(a.getAttribute('data-focus'));
      if (el) el.focus();
    });
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (submit && submit.disabled) return;
      if (done) done.hidden = true;
      var bad = validate();
      if (bad.length) { bad[0].el.focus(); return; }
      if (submit) { submit.disabled = true; submit.textContent = 'Отправка…'; }
      form.setAttribute('aria-busy', 'true');
      fetch(form.getAttribute('action'), { method: 'POST', body: new FormData(form), credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res && res.ok) {
            form.reset();
            if (done) { done.hidden = false; done.focus(); }
          } else {
            throw (res && res.errors && res.errors.length ? res.errors : ['Не удалось отправить. Попробуйте позже.']);
          }
        })
        .catch(function (err) {
          var list = Array.isArray(err) ? err : ['Не удалось отправить. Проверьте соединение и попробуйте позже.'];
          summary(list.map(function (m) { return { id: '', msg: String(m) }; }));
        })
        .then(function () {
          form.removeAttribute('aria-busy');
          if (submit) { submit.disabled = false; submit.textContent = label; }
        });
    });
  });
})();
