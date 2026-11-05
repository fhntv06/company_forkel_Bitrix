/* FORKEL — окно «Обратный звонок» (8.2). Демонстрация: данные НЕ отправляются и не сохраняются (нет запросов и хранилища).
   Варианты времени — условные, график работы владельцем не предоставлен. Оформление и поведение — как у окна 8.1 (css/feedback.css). */
(function () {
  'use strict';
  if (typeof HTMLDialogElement === 'undefined' || !document.querySelector('[data-callback]')) return;

  var dlg = document.createElement('dialog');
  dlg.className = 'fk-fb';
  dlg.setAttribute('aria-labelledby', 'cb-title');
  dlg.innerHTML =
    '<div class="fk-fb__panel">' +
    '<div class="fk-fb__head"><h2 class="fk-fb__title" id="cb-title" tabindex="-1">Обратный звонок</h2>' +
    '<button class="fk-fb__close" type="button" aria-label="Закрыть окно"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 5l14 14M19 5L5 19"/></svg></button></div>' +
    '<div class="fk-fb__body">' +
    '<form class="fk-fb__form" novalidate>' +
    '<p class="fk-fb__lead">Оставьте номер и удобное время — мы перезвоним. <span class="fk-tag">демо</span> Форма демонстрационная: данные никуда не отправляются.</p>' +
    '<div class="fk-fb__errors fk-co-errors" role="alert" hidden></div>' +
    '<div class="fk-co-field"><label class="fk-field__label" for="cb-phone">Телефон</label><input class="fk-field" id="cb-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" placeholder="+7 ___ ___-__-__" required aria-describedby="cb-phone-err"><p class="fk-co-error" id="cb-phone-err" hidden></p></div>' +
    '<div class="fk-co-field"><label class="fk-field__label" for="cb-time">Удобное время звонка <span class="fk-tag">варианты — заглушка</span></label>' +
    '<select class="fk-field" id="cb-time" name="time"><option value="asap">Как можно скорее</option><option value="morning">Утром</option><option value="day">Днём</option><option value="evening">Вечером</option></select></div>' +
    '<div class="fk-co-field fk-fb__consent"><label class="fk-check"><input type="checkbox" id="cb-consent" required aria-describedby="cb-consent-err"><span>Согласен с обработкой персональных данных (<a href="#" class="fk-fb__policy">политика — заглушка</a>)</span></label><p class="fk-co-error" id="cb-consent-err" hidden></p></div>' +
    '<p class="fk-note fk-fb__policy-note" role="status" hidden>Страница политики конфиденциальности не создана. Текст предоставит владелец.</p>' +
    '<button class="fk-btn fk-btn--primary fk-fb__submit" type="submit">Заказать звонок (демо)</button>' +
    '</form>' +
    '<div class="fk-fb__done" hidden><h3 class="fk-fb__done-title" tabindex="-1">Демонстрационное подтверждение</h3>' +
    '<p>Это прототип: заявка на звонок <strong>не отправлена</strong> и нигде не сохранена. В рабочей версии здесь появится подтверждение.</p>' +
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
  function validPhone(v) {
    var d = v.replace(/\D/g, '');
    return /^[+\d][\d\s()\-+]*$/.test(v.trim()) && d.length >= 10 && d.length <= 15;
  }
  function validate() {
    var errs = [];
    function chk(id, ok, msg) { setErr(id, ok ? '' : msg); if (!ok) errs.push({ id: id, msg: msg }); }
    chk('cb-phone', validPhone($('cb-phone').value), 'Укажите телефон (10–15 цифр).');
    chk('cb-consent', $('cb-consent').checked, 'Нужно согласие на обработку данных.');
    summary.hidden = !errs.length;
    summary.innerHTML = errs.length ? 'Исправьте ошибки: ' + errs.map(function (e) { return '<a href="#' + e.id + '">' + e.msg.replace(/\.$/, '') + '</a>'; }).join('') : '';
    return errs;
  }
  function reset() {
    clearTimeout(timer);
    form.reset(); form.hidden = false; done.hidden = true; summary.hidden = true; pnote.hidden = true;
    ['cb-phone', 'cb-consent'].forEach(function (id) { setErr(id, ''); });
    submit.disabled = false; submit.textContent = 'Заказать звонок (демо)'; form.removeAttribute('aria-busy');
    title.textContent = 'Обратный звонок';
  }
  function open(el) {
    opener = el; reset();
    if (!dlg.open) dlg.showModal();
    document.documentElement.classList.add('fk-fb-open');
    $('cb-phone').focus();
  }
  function close() { if (dlg.open) dlg.close(); }
  dlg.addEventListener('close', function () {
    reset();
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
    submit.disabled = true; submit.textContent = 'Отправка…'; form.setAttribute('aria-busy', 'true');
    timer = setTimeout(function () {
      form.reset(); form.hidden = true; done.hidden = false;
      title.textContent = 'Спасибо!';
      done.querySelector('.fk-fb__done-title').focus();
    }, 900);
  });
  form.addEventListener('input', function (e) {
    var id = e.target.id; if (id && e.target.getAttribute('aria-invalid')) setErr(id, '');
  });
  document.addEventListener('click', function (e) {
    var t = e.target.closest('[data-callback]');
    if (!t) return;
    e.preventDefault(); e.stopPropagation(); open(t);
  }, true);
})();
