/* FORKEL — окна «Написать нам» ([data-feedback]) и «Заказать звонок» ([data-callback]). Один <dialog> из partials/feedback_dialog.php.
   Отправка: POST на data-fb-action (/local/ajax/feedback.php), ответ JSON {ok, errors[]}. Логика окна — из прототипа V4 (feedback.js). */
(function () {
  'use strict';
  var dlg = document.querySelector('dialog.fk-fb');
  if (!dlg || typeof dlg.showModal !== 'function') return;

  var form = dlg.querySelector('.fk-fb__form');
  var done = dlg.querySelector('.fk-fb__done');
  var summary = dlg.querySelector('.fk-fb__errors');
  var submit = dlg.querySelector('.fk-fb__submit');
  var title = dlg.querySelector('.fk-fb__title');
  var lead = dlg.querySelector('[data-fb-lead]');
  var contactLabel = dlg.querySelector('[data-fb-contact-label]');
  var msgField = dlg.querySelector('[data-fb-msg-field]');
  var msgInput = document.getElementById('fb-msg');
  var contactInput = document.getElementById('fb-contact');
  var modeInput = form.elements.mode;
  var doneText = dlg.querySelector('[data-fb-done-text]');
  var IDS = ['fb-name', 'fb-contact', 'fb-msg', 'fb-consent'];
  var opener = null;
  var mode = 'feedback';
  function $(id) { return document.getElementById(id); }

  var MODES = {
    feedback: { title: 'Написать нам', lead: 'Оставьте сообщение — мы свяжемся с вами.', contact: 'Телефон или email', btn: 'Отправить', done: 'Сообщение отправлено. Мы свяжемся с вами.' },
    oneclick: { title: 'Купить в 1 клик', lead: 'Оставьте номер телефона — мы свяжемся и оформим заказ.', contact: 'Телефон', btn: 'Отправить заявку', done: 'Заявка принята. Мы свяжемся с вами.' },
    callback: { title: 'Заказать звонок', lead: 'Оставьте номер телефона — мы перезвоним.', contact: 'Телефон', btn: 'Заказать звонок', done: 'Заявка принята. Мы перезвоним вам.' }
  };

  function setMode(m) {
    mode = m;
    var c = MODES[m];
    modeInput.value = m;
    title.textContent = c.title; lead.textContent = c.lead; contactLabel.textContent = c.contact;
    submit.textContent = c.btn; doneText.textContent = c.done;
    var phoneOnly = m !== 'feedback';
    msgField.hidden = phoneOnly;
    msgInput.required = !phoneOnly;
    contactInput.type = phoneOnly ? 'tel' : 'text';
    contactInput.setAttribute('autocomplete', phoneOnly ? 'tel' : 'off');
  }
  function setErr(id, msg) {
    var inp = $(id), er = $(id + '-err');
    if (!inp || !er) return;
    er.textContent = msg; er.hidden = !msg;
    if (msg) inp.setAttribute('aria-invalid', 'true'); else inp.removeAttribute('aria-invalid');
  }
  function validContact(v) {
    v = v.trim();
    if (mode === 'feedback' && /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) return true;
    var digits = v.replace(/\D/g, '');
    return /^[+\d][\d\s()\-+]*$/.test(v) && digits.length >= 10 && digits.length <= 15;
  }
  function showSummary(items) {
    summary.hidden = !items.length;
    summary.textContent = '';
    if (!items.length) return;
    summary.appendChild(document.createTextNode('Исправьте ошибки: '));
    items.forEach(function (e) {
      var a = document.createElement('a');
      a.textContent = e.msg.replace(/\.$/, '');
      a.href = e.id ? '#' + e.id : '#';
      summary.appendChild(a);
    });
  }
  function validate() {
    var errs = [];
    function chk(id, ok, msg) { setErr(id, ok ? '' : msg); if (!ok) errs.push({ id: id, msg: msg }); }
    chk('fb-name', $('fb-name').value.trim().length >= 2, 'Укажите имя (не короче 2 символов).');
    chk('fb-contact', validContact(contactInput.value), mode !== 'feedback' ? 'Укажите телефон (10–15 цифр).' : 'Укажите телефон (10–15 цифр) или email.');
    if (mode === 'feedback') chk('fb-msg', msgInput.value.trim().length >= 5, 'Напишите сообщение (не короче 5 символов).');
    chk('fb-consent', $('fb-consent').checked, 'Нужно согласие на обработку данных.');
    showSummary(errs);
    return errs;
  }
  function reset() {
    form.reset(); form.hidden = false; done.hidden = true; summary.hidden = true;
    IDS.forEach(function (id) { setErr(id, ''); });
    submit.disabled = false; form.removeAttribute('aria-busy');
    setMode(mode);
  }
  function open(el, m) {
    opener = el; mode = m; reset();
    if (!dlg.open) dlg.showModal();
    document.documentElement.classList.add('fk-fb-open');
    /* Контекст товара (страница товара): в 1 клик уходит скрытым текстом, в вопрос подставляется в сообщение */
    var ctx = el && el.hasAttribute('data-fb-context') && typeof window.fkFeedbackContext === 'function' ? window.fkFeedbackContext() : '';
    if (ctx) msgInput.value = m === 'feedback' ? 'Вопрос по товару. ' + ctx + '\n\n' : ctx;
    $('fb-name').focus();
  }
  function close() { if (dlg.open) dlg.close(); }

  dlg.addEventListener('close', function () {
    document.documentElement.classList.remove('fk-fb-open');
    reset();
    if (opener && document.contains(opener)) opener.focus();
  });
  dlg.addEventListener('mousedown', function (e) { if (e.target === dlg) close(); });
  dlg.querySelector('.fk-fb__close').addEventListener('click', close);
  dlg.querySelector('.fk-fb__ok').addEventListener('click', close);
  summary.addEventListener('click', function (e) {
    var a = e.target.closest('a');
    if (!a) return;
    e.preventDefault();
    var id = (a.getAttribute('href') || '').slice(1);
    if (id && $(id)) $(id).focus();
  });
  form.addEventListener('input', function (e) {
    var id = e.target.id;
    if (id && e.target.getAttribute('aria-invalid')) setErr(id, '');
  });
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (submit.disabled) return;
    var errs = validate();
    if (errs.length) { $(errs[0].id).focus(); return; }
    var label = submit.textContent;
    submit.disabled = true; submit.textContent = 'Отправка…'; form.setAttribute('aria-busy', 'true');
    fetch(dlg.getAttribute('data-fb-action'), { method: 'POST', body: new FormData(form), credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res && res.ok) {
          form.hidden = true; done.hidden = false;
          done.querySelector('.fk-fb__done-title').focus();
        } else {
          throw (res && res.errors && res.errors.length ? res.errors : ['Не удалось отправить. Попробуйте позже.']);
        }
      })
      .catch(function (err) {
        var list = Array.isArray(err) ? err : ['Не удалось отправить. Проверьте соединение и попробуйте позже.'];
        showSummary(list.map(function (m) { return { id: '', msg: String(m) }; }));
        submit.disabled = false; submit.textContent = label; form.removeAttribute('aria-busy');
      });
  });
  document.addEventListener('click', function (e) {
    var t = e.target.closest('[data-feedback], [data-callback], [data-oneclick]');
    if (!t) return;
    e.preventDefault(); e.stopPropagation();
    open(t, t.hasAttribute('data-callback') ? 'callback' : (t.hasAttribute('data-oneclick') ? 'oneclick' : 'feedback'));
  }, true);
})();
