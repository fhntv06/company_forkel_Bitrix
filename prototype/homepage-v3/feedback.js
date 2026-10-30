/* FORKEL — окно обратной связи (8.1). Демонстрация: данные формы НЕ отправляются, не сохраняются и никуда не передаются
   (нет ни запросов к серверу, ни localStorage). «Успех» — только показ демонстрационного подтверждения. */
(function () {
  'use strict';
  if (typeof HTMLDialogElement === 'undefined') return;
  var triggers = document.querySelectorAll('[data-feedback]');
  var dynamic = !!document.getElementById('prj-root');
  if (!triggers.length && !dynamic) return;

  var dlg = document.createElement('dialog');
  dlg.className = 'fk-fb';
  dlg.setAttribute('aria-labelledby', 'fb-title');
  dlg.innerHTML =
    '<div class="fk-fb__panel">' +
    '<div class="fk-fb__head"><h2 class="fk-fb__title" id="fb-title" tabindex="-1">Написать нам</h2>' +
    '<button class="fk-fb__close" type="button" aria-label="Закрыть окно"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 5l14 14M19 5L5 19"/></svg></button></div>' +
    '<div class="fk-fb__body">' +
    '<form class="fk-fb__form" novalidate>' +
    '<p class="fk-fb__lead">Оставьте сообщение — мы свяжемся с вами. <span class="fk-tag">демо</span> Форма демонстрационная: данные никуда не отправляются.</p>' +
    '<div class="fk-fb__errors fk-co-errors" role="alert" hidden></div>' +
    '<div class="fk-co-field"><label class="fk-field__label" for="fb-name">Имя</label><input class="fk-field" id="fb-name" name="name" type="text" autocomplete="name" required aria-describedby="fb-name-err"><p class="fk-co-error" id="fb-name-err" hidden></p></div>' +
    '<div class="fk-co-field"><label class="fk-field__label" for="fb-contact">Телефон или email</label><input class="fk-field" id="fb-contact" name="contact" type="text" autocomplete="off" required aria-describedby="fb-contact-err"><p class="fk-co-error" id="fb-contact-err" hidden></p></div>' +
    '<div class="fk-co-field"><label class="fk-field__label" for="fb-msg">Сообщение</label><textarea class="fk-field fk-co-textarea" id="fb-msg" name="msg" rows="4" required aria-describedby="fb-msg-err"></textarea><p class="fk-co-error" id="fb-msg-err" hidden></p></div>' +
    '<div class="fk-co-field fk-fb__consent"><label class="fk-check"><input type="checkbox" id="fb-consent" required aria-describedby="fb-consent-err"><span>Согласен с обработкой персональных данных (<a href="#" class="fk-fb__policy">политика — заглушка</a>)</span></label><p class="fk-co-error" id="fb-consent-err" hidden></p></div>' +
    '<p class="fk-note fk-fb__policy-note" role="status" hidden>Страница политики конфиденциальности не создана. Текст предоставит владелец.</p>' +
    '<button class="fk-btn fk-btn--primary fk-fb__submit" type="submit">Отправить (демо)</button>' +
    '</form>' +
    '<div class="fk-fb__done" hidden><h3 class="fk-fb__done-title" tabindex="-1">Демонстрационное подтверждение</h3>' +
    '<p>Это прототип: сообщение <strong>не отправлено</strong> и нигде не сохранено. В рабочей версии здесь появится подтверждение отправки.</p>' +
    '<button class="fk-btn fk-btn--primary fk-fb__ok" type="button">Закрыть</button></div>' +
    '</div></div>';
  document.body.appendChild(dlg);

  var form = dlg.querySelector('.fk-fb__form');
  var done = dlg.querySelector('.fk-fb__done');
  var summary = dlg.querySelector('.fk-fb__errors');
  var submit = dlg.querySelector('.fk-fb__submit');
  var pnote = dlg.querySelector('.fk-fb__policy-note');
  var title = dlg.querySelector('.fk-fb__title');
  var opener = null, timer = 0;
  function $(id) { return document.getElementById(id); }

  function setErr(id, msg) {
    var inp = $(id), er = $(id + '-err');
    er.textContent = msg; er.hidden = !msg;
    if (msg) inp.setAttribute('aria-invalid', 'true'); else inp.removeAttribute('aria-invalid');
  }
  function validContact(v) {
    v = v.trim();
    if (/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) return true;
    var digits = v.replace(/\D/g, '');
    return /^[+\d][\d\s()\-+]*$/.test(v) && digits.length >= 10 && digits.length <= 15;
  }
  function validate() {
    var errs = [];
    function chk(id, ok, msg) { setErr(id, ok ? '' : msg); if (!ok) errs.push({ id: id, msg: msg }); }
    chk('fb-name', $('fb-name').value.trim().length >= 2, 'Укажите имя (не короче 2 символов).');
    chk('fb-contact', validContact($('fb-contact').value), 'Укажите телефон (10–15 цифр) или email.');
    chk('fb-msg', $('fb-msg').value.trim().length >= 5, 'Напишите сообщение (не короче 5 символов).');
    chk('fb-consent', $('fb-consent').checked, 'Нужно согласие на обработку данных.');
    summary.hidden = !errs.length;
    summary.innerHTML = errs.length ? 'Исправьте ошибки: ' + errs.map(function (e) { return '<a href="#' + e.id + '">' + e.msg.replace(/\.$/, '') + '</a>'; }).join('') : '';
    return errs;
  }
  function reset() {
    clearTimeout(timer);
    form.reset(); form.hidden = false; done.hidden = true; summary.hidden = true; pnote.hidden = true;
    ['fb-name', 'fb-contact', 'fb-msg', 'fb-consent'].forEach(function (id) { setErr(id, ''); });
    submit.disabled = false; submit.textContent = 'Отправить (демо)'; form.removeAttribute('aria-busy');
  }
  function open(el) {
    opener = el; reset();
    if (!dlg.open) dlg.showModal();
    document.documentElement.classList.add('fk-fb-open');
    $('fb-name').focus();
  }
  function close() { if (dlg.open) dlg.close(); }
  dlg.addEventListener('close', function () {
    clearTimeout(timer); reset();
    document.documentElement.classList.remove('fk-fb-open');
    if (opener && document.contains(opener)) opener.focus();
  });
  dlg.addEventListener('mousedown', function (e) { if (e.target === dlg) close(); });
  dlg.querySelector('.fk-fb__close').addEventListener('click', close);
  dlg.querySelector('.fk-fb__ok').addEventListener('click', close);
  dlg.querySelector('.fk-fb__policy').addEventListener('click', function (e) { e.preventDefault(); e.stopPropagation(); pnote.hidden = false; });
  summary.addEventListener('click', function (e) {
    var a = e.target.closest('a'); if (!a) return;
    e.preventDefault(); $(a.getAttribute('href').slice(1)).focus();
  });
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (submit.disabled) return;
    var errs = validate();
    if (errs.length) { $(errs[0].id).focus(); return; }
    /* Имитация отправки: запроса нет, данные сразу отбрасываются */
    submit.disabled = true; submit.textContent = 'Отправка…'; form.setAttribute('aria-busy', 'true');
    timer = setTimeout(function () {
      form.reset(); form.hidden = true; done.hidden = false;
      title.textContent = 'Спасибо!';
      done.querySelector('.fk-fb__done-title').focus();
    }, 900);
  });
  /* Ошибка поля исчезает, когда его исправили */
  form.addEventListener('input', function (e) {
    var id = e.target.id; if (id && e.target.getAttribute('aria-invalid')) setErr(id, '');
  });
  document.addEventListener('click', function (e) {
    var t = e.target.closest('[data-feedback]');
    if (!t) return;
    e.preventDefault(); e.stopPropagation(); title.textContent = 'Написать нам'; open(t);
  }, true);
})();
