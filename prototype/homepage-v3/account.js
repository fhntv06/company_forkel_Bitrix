/* FORKEL — демонстрационный личный кабинет (11). Вход, регистрация и восстановление — mock: ничего не отправляется, пароль не
   проверяется и не сохраняется. Заказы, статусы и адреса — условные данные; «Повторить заказ» кладёт демо-товары в демо-корзину. */
(function () {
  'use strict';
  var root = document.getElementById('acc-root');
  var A = window.FKAuth, D = window.FKData, C = window.FKCart;
  if (!root || !A || !D || !C) return;

  function esc(s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
  function tag(t) { return '<span class="fk-tag">' + t + '</span>'; }
  function $(id) { return document.getElementById(id); }

  var AUTH_VIEWS = ['login', 'register', 'recover'];
  var CAB_VIEWS = ['profile', 'orders', 'addresses'];
  var TITLES = { login: 'Вход', register: 'Регистрация', recover: 'Восстановление доступа', profile: 'Профиль', orders: 'История заказов', addresses: 'Адреса доставки' };

  /* Условные заказы: состав из демо-товаров, статусы выбраны для показа состояний */
  var ORDERS = [
    { no: 'ДЕМО-0003', status: 'new', lines: [[1, 2], [4, 1]] },
    { no: 'ДЕМО-0002', status: 'shipping', lines: [[2, 1], [6, 2], [8, 1]] },
    { no: 'ДЕМО-0001', status: 'done', lines: [[3, 1]] }
  ];
  var STATUS = { new: 'В обработке', shipping: 'Передан в доставку', done: 'Выполнен' };
  var addresses = [{ id: 1, label: 'Дом', text: 'Адрес — заглушка' }];
  var nextAddr = 2;

  function view() {
    var h = (location.hash || '').replace('#', '');
    var u = A.user();
    if (u) return CAB_VIEWS.indexOf(h) >= 0 ? h : 'profile';
    return AUTH_VIEWS.indexOf(h) >= 0 ? h : 'login';
  }
  function go(h) { if (location.hash === '#' + h) render(); else location.hash = h; }

  function field(id, label, attrs, hint) {
    return '<div class="fk-co-field"><label class="fk-field__label" for="' + id + '">' + label + '</label>' +
      '<input class="fk-field" id="' + id + '" ' + attrs + ' aria-describedby="' + id + '-err"' + '>' +
      (hint ? '<p class="fk-note">' + hint + '</p>' : '') + '<p class="fk-co-error" id="' + id + '-err" hidden></p></div>';
  }
  function setErr(id, msg) {
    var inp = $(id), er = $(id + '-err');
    if (!inp || !er) return;
    er.textContent = msg; er.hidden = !msg;
    if (msg) inp.setAttribute('aria-invalid', 'true'); else inp.removeAttribute('aria-invalid');
  }
  function check(form, rules) {
    var errs = [];
    rules.forEach(function (r) { var ok = r[1](); setErr(r[0], ok ? '' : r[2]); if (!ok) errs.push(r[0]); });
    var box = form.querySelector('.fk-co-errors');
    if (box) {
      box.hidden = !errs.length;
      box.innerHTML = errs.length ? 'Исправьте ошибки: ' + rules.filter(function (r) { return errs.indexOf(r[0]) >= 0; })
        .map(function (r) { return '<a href="#' + r[0] + '" data-focus="' + r[0] + '">' + r[2].replace(/\.$/, '') + '</a>'; }).join('') : '';
    }
    if (errs.length) $(errs[0]).focus();
    return !errs.length;
  }
  function validEmail(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim()); }
  function nameFrom(email) { return email.split('@')[0] || 'Пользователь'; }

  var DEMO_NOTE = '<p class="fk-note fk-note--proto">Демонстрационный вход: сервера и настоящих учётных записей нет. Подойдут любой корректный email и пароль от 6 символов — пароль не проверяется и не сохраняется.</p>';
  var ERRBOX = '<div class="fk-co-errors" role="alert" hidden></div>';

  function authTabs(cur) {
    return '<nav class="fk-acc-tabs" aria-label="Вход и регистрация">' + ['login', 'register'].map(function (v) {
      return '<a class="fk-chip" href="#' + v + '"' + (v === cur ? ' aria-current="page"' : '') + '>' + TITLES[v] + '</a>';
    }).join('') + '</nav>';
  }
  function loginView() {
    return '<section class="fk-acc-card" aria-labelledby="acc-h">' + authTabs('login') +
      '<h2 id="acc-h" tabindex="-1">Вход ' + tag('демо') + '</h2>' + DEMO_NOTE +
      '<form id="acc-form" novalidate>' + ERRBOX +
      field('acc-email', 'Email', 'type="email" autocomplete="email" required') +
      field('acc-pass', 'Пароль', 'type="password" autocomplete="current-password" required') +
      '<p><a class="fk-link fk-link--tap" href="#recover">Забыли пароль?</a></p>' +
      '<div class="fk-acc-actions"><button class="fk-btn fk-btn--primary" type="submit">Войти (демо)</button>' +
      '<button class="fk-btn fk-btn--secondary" type="button" id="acc-demo">Войти как демо-пользователь</button></div></form></section>';
  }
  function registerView() {
    return '<section class="fk-acc-card" aria-labelledby="acc-h">' + authTabs('register') +
      '<h2 id="acc-h" tabindex="-1">Регистрация ' + tag('демо') + '</h2>' + DEMO_NOTE +
      '<form id="acc-form" novalidate>' + ERRBOX +
      field('acc-name', 'Имя', 'type="text" autocomplete="name" required') +
      field('acc-email', 'Email', 'type="email" autocomplete="email" required') +
      field('acc-pass', 'Пароль', 'type="password" autocomplete="new-password" required', 'Не короче 6 символов.') +
      field('acc-pass2', 'Повторите пароль', 'type="password" autocomplete="new-password" required') +
      '<div class="fk-co-field"><label class="fk-check"><input type="checkbox" id="acc-consent" required aria-describedby="acc-consent-err"><span>Согласен с обработкой персональных данных (<a href="#" data-stub="Страница политики не создана. Текст предоставит владелец.">политика — заглушка</a>)</span></label><p class="fk-co-error" id="acc-consent-err" hidden></p></div>' +
      '<div class="fk-acc-actions"><button class="fk-btn fk-btn--primary" type="submit">Зарегистрироваться (демо)</button></div></form></section>';
  }
  function recoverView() {
    return '<section class="fk-acc-card" aria-labelledby="acc-h"><h2 id="acc-h" tabindex="-1">Восстановление доступа ' + tag('демо') + '</h2>' +
      '<p class="fk-note fk-note--proto">Демонстрация: письмо не отправляется.</p>' +
      '<form id="acc-form" novalidate>' + ERRBOX + field('acc-email', 'Email', 'type="email" autocomplete="email" required') +
      '<div class="fk-acc-actions"><button class="fk-btn fk-btn--primary" type="submit">Восстановить (демо)</button>' +
      '<a class="fk-btn fk-btn--secondary" href="#login">Назад ко входу</a></div></form>' +
      '<div class="fk-acc-done" id="acc-done" role="status" hidden><strong>Демонстрационное подтверждение.</strong> Письмо для восстановления <strong>не отправлено</strong>: в прототипе нет почтового сервера.</div></section>';
  }
  function cabTabs(cur) {
    return '<nav class="fk-acc-tabs" aria-label="Разделы кабинета">' + CAB_VIEWS.map(function (v) {
      return '<a class="fk-chip" href="#' + v + '"' + (v === cur ? ' aria-current="page"' : '') + '>' + TITLES[v] + '</a>';
    }).join('') + '<button class="fk-chip fk-acc-logout" type="button" id="acc-logout">Выйти</button></nav>';
  }
  function profileView() {
    var u = A.user();
    return '<section class="fk-acc-card" aria-labelledby="acc-h">' + cabTabs('profile') +
      '<h2 id="acc-h" tabindex="-1">Профиль ' + tag('демо') + '</h2>' +
      '<p class="fk-note fk-note--proto">Демо-вход выполнен. Данные хранятся только в этой вкладке до её закрытия и никуда не передаются.</p>' +
      '<form id="acc-form" novalidate>' + ERRBOX +
      field('acc-name', 'Имя', 'type="text" autocomplete="name" value="' + esc(u.name || '') + '" required') +
      field('acc-email', 'Email', 'type="email" autocomplete="email" value="' + esc(u.email) + '" required') +
      field('acc-phone', 'Телефон', 'type="tel" autocomplete="tel" inputmode="tel" value="' + esc(u.phone || '') + '" placeholder="+7 ___ ___-__-__"') +
      '<div class="fk-acc-actions"><button class="fk-btn fk-btn--primary" type="submit">Сохранить (демо)</button></div>' +
      '<p class="fk-note" id="acc-saved" role="status" hidden>Сохранено в демо-режиме (только в этой вкладке).</p></form></section>';
  }
  function ordersView() {
    var list = ORDERS.map(function (o) {
      var total = 0;
      var lines = o.lines.map(function (l) {
        var p = D.byId(l[0]), sum = D.price(p) * l[1]; total += sum;
        return '<li><span class="fk-acc-line__name"><a href="product.html?id=' + p.id + '">' + esc(p.name) + '</a><span class="fk-acc-line__meta"> · Арт. ' + esc(p.sku) + ' · ' + esc(D.COLORS[p.color]) + '</span></span><span>' + l[1] + ' × ' + D.fmt(D.price(p)) + '</span></li>';
      }).join('');
      return '<li class="fk-acc-order"><details><summary><span class="fk-acc-order__no">Заказ ' + o.no + '</span>' +
        '<span class="fk-acc-status fk-acc-status--' + o.status + '">' + STATUS[o.status] + '</span>' +
        '<span class="fk-acc-order__sum">' + D.fmt(total) + '</span></summary>' +
        '<div class="fk-acc-order__body"><p class="fk-note">Дата — заглушка. Статус и состав условные. ' + tag('демо') + '</p><ul class="fk-acc-lines">' + lines + '</ul>' +
        '<p class="fk-acc-order__total">Итого: <strong>' + D.fmt(total) + '</strong> ' + tag('демо') + '</p>' +
        '<button class="fk-btn fk-btn--secondary" type="button" data-repeat="' + o.no + '">Повторить заказ (демо)</button></div></details></li>';
    }).join('');
    return '<section class="fk-acc-card" aria-labelledby="acc-h">' + cabTabs('orders') +
      '<h2 id="acc-h" tabindex="-1">История заказов ' + tag('демо') + '</h2>' +
      '<p class="fk-note fk-note--proto">Реальных заказов нет: список условный. «Повторить заказ» кладёт демо-товары в демо-корзину, заказ не создаётся.</p>' +
      '<ul class="fk-acc-orders">' + list + '</ul><p class="fk-acc-repeat" id="acc-repeat" role="status" hidden></p></section>';
  }
  function addressesView() {
    var list = addresses.length ? '<ul class="fk-acc-addr">' + addresses.map(function (a) {
      return '<li><span><strong>' + esc(a.label) + '</strong><br>' + esc(a.text) + '</span><button class="fk-btn fk-btn--secondary" type="button" data-del="' + a.id + '">Удалить<span class="fk-vh"> адрес «' + esc(a.label) + '»</span></button></li>';
    }).join('') + '</ul>' : '<div class="fk-empty"><p>Адресов нет. Добавьте демонстрационный адрес.</p></div>';
    return '<section class="fk-acc-card" aria-labelledby="acc-h">' + cabTabs('addresses') +
      '<h2 id="acc-h" tabindex="-1">Адреса доставки ' + tag('демо') + '</h2>' +
      '<p class="fk-note fk-note--proto">Демонстрация: адреса живут только на этой странице и пропадают при перезагрузке.</p>' + list +
      '<h3 class="fk-acc-sub">Добавить адрес</h3><form id="acc-form" novalidate>' + ERRBOX +
      field('acc-label', 'Название', 'type="text" required placeholder="Например, Офис"') +
      field('acc-text', 'Адрес', 'type="text" autocomplete="street-address" required') +
      '<div class="fk-acc-actions"><button class="fk-btn fk-btn--primary" type="submit">Добавить (демо)</button></div></form></section>';
  }

  var VIEWS = { login: loginView, register: registerView, recover: recoverView, profile: profileView, orders: ordersView, addresses: addressesView };

  function bind(v) {
    var form = $('acc-form');
    if (form) form.addEventListener('input', function (e) { if (e.target.id && e.target.getAttribute('aria-invalid')) setErr(e.target.id, ''); });
    if (v === 'login') {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!check(form, [
          ['acc-email', function () { return validEmail($('acc-email').value); }, 'Укажите корректный email.'],
          ['acc-pass', function () { return $('acc-pass').value.length >= 6; }, 'Пароль — не короче 6 символов.']
        ])) return;
        A.login({ email: $('acc-email').value.trim(), name: nameFrom($('acc-email').value.trim()) }); go('profile');
      });
      $('acc-demo').addEventListener('click', function () { A.login({ email: 'demo@example.com', name: 'Демо-пользователь' }); go('profile'); });
    }
    if (v === 'register') {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!check(form, [
          ['acc-name', function () { return $('acc-name').value.trim().length >= 2; }, 'Укажите имя (не короче 2 символов).'],
          ['acc-email', function () { return validEmail($('acc-email').value); }, 'Укажите корректный email.'],
          ['acc-pass', function () { return $('acc-pass').value.length >= 6; }, 'Пароль — не короче 6 символов.'],
          ['acc-pass2', function () { return $('acc-pass2').value === $('acc-pass').value && $('acc-pass2').value.length > 0; }, 'Пароли не совпадают.'],
          ['acc-consent', function () { return $('acc-consent').checked; }, 'Нужно согласие на обработку данных.']
        ])) return;
        A.login({ email: $('acc-email').value.trim(), name: $('acc-name').value.trim() }); go('profile');
      });
    }
    if (v === 'recover') {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!check(form, [['acc-email', function () { return validEmail($('acc-email').value); }, 'Укажите корректный email.']])) return;
        form.hidden = true; $('acc-done').hidden = false; $('acc-done').setAttribute('tabindex', '-1'); $('acc-done').focus();
      });
    }
    if (v === 'profile') {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!check(form, [
          ['acc-name', function () { return $('acc-name').value.trim().length >= 2; }, 'Укажите имя (не короче 2 символов).'],
          ['acc-email', function () { return validEmail($('acc-email').value); }, 'Укажите корректный email.'],
          ['acc-phone', function () { var d = $('acc-phone').value.replace(/\D/g, ''); return !$('acc-phone').value.trim() || (d.length >= 10 && d.length <= 15); }, 'Телефон — 10–15 цифр или оставьте пустым.']
        ])) return;
        A.login({ name: $('acc-name').value.trim(), email: $('acc-email').value.trim(), phone: $('acc-phone').value.trim() });
        $('acc-saved').hidden = false;
      });
    }
    if (v === 'orders') {
      root.addEventListener('click', onRepeat);
    }
    if (v === 'addresses') {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!check(form, [
          ['acc-label', function () { return $('acc-label').value.trim().length >= 2; }, 'Укажите название (не короче 2 символов).'],
          ['acc-text', function () { return $('acc-text').value.trim().length >= 5; }, 'Укажите адрес (не короче 5 символов).']
        ])) return;
        addresses.push({ id: nextAddr++, label: $('acc-label').value.trim(), text: $('acc-text').value.trim() }); render(true);
      });
    }
    var lo = $('acc-logout');
    if (lo) lo.addEventListener('click', function () { A.logout(); go('login'); });
  }
  function onRepeat(e) {
    var r = e.target.closest('[data-repeat]'), d = e.target.closest('[data-del]');
    if (r) {
      var o = ORDERS.filter(function (x) { return x.no === r.getAttribute('data-repeat'); })[0];
      o.lines.forEach(function (l) { C.add({ kind: 'product', id: l[0] }, l[1]); });
      var box = $('acc-repeat');
      box.hidden = false;
      box.innerHTML = 'Товары заказа ' + o.no + ' добавлены в демо-корзину (в корзине: ' + C.count() + '). <a class="fk-link fk-link--tap" href="cart.html">Перейти в корзину</a>';
    }
    if (d) {
      var id = parseInt(d.getAttribute('data-del'), 10);
      addresses = addresses.filter(function (a) { return a.id !== id; }); render(true);
    }
  }
  root.addEventListener('click', function (e) {
    var f = e.target.closest('[data-focus]');
    if (f) { e.preventDefault(); $(f.getAttribute('data-focus')).focus(); }
    if (e.target.closest('[data-del]')) onRepeat(e);
  });

  var first = true;
  function render(keepFocus) {
    var v = view();
    if (location.hash.replace('#', '') !== v && !(v === 'login' && !location.hash)) { try { history.replaceState(null, '', '#' + v); } catch (e) { /* адрес не меняется */ } }
    document.title = 'FORKEL — личный кабинет: ' + TITLES[v].toLowerCase() + ' (прототип)';
    var crumb = $('acc-crumb'); if (crumb) crumb.textContent = TITLES[v];
    root.removeEventListener('click', onRepeat);
    root.innerHTML = VIEWS[v]();
    bind(v);
    if (!first && !keepFocus) { var h = $('acc-h'); if (h) h.focus(); }
    first = false;
  }
  window.addEventListener('hashchange', function () { render(); });
  render();
})();
