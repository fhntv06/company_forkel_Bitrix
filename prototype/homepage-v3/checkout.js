/* FORKEL — демонстрационное оформление заказа (4.2). Заказ НЕ создаётся: данные формы не отправляются, не сохраняются и не
   передаются никуда; корзина остаётся без изменений. Способы доставки и оплаты — заглушки, условия не утверждены. */
(function () {
  'use strict';
  var D = window.FKData, C = window.FKCart;
  var root = document.getElementById('co-root');
  if (!root || !D || !C) return;

  var DELIVERY = [
    { key: 'courier', name: 'Курьер', address: true },
    { key: 'pickup', name: 'Самовывоз', address: false },
    { key: 'cdek', name: 'СДЭК', address: true }
  ];
  var PAYMENT = [
    { key: 'online', name: 'Онлайн-оплата' },
    { key: 'receipt', name: 'При получении' }
  ];
  function esc(s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
  function tag(t) { return '<span class="fk-tag">' + t + '</span>'; }

  function field(id, label, attrs, hint) {
    return '<div class="fk-co-field">' +
      '<label class="fk-field__label" for="' + id + '">' + label + '</label>' +
      '<input class="fk-field" id="' + id + '" ' + attrs + ' aria-describedby="' + id + '-err">' +
      '<p class="fk-co-error" id="' + id + '-err" hidden></p></div>';
  }
  function choice(name, list, note) {
    return '<div class="fk-co-choices" role="radiogroup" aria-labelledby="' + name + '-label">' + list.map(function (o, i) {
      return '<label class="fk-co-choice"><input type="radio" name="' + name + '" value="' + o.key + '"' + (i === 0 ? ' checked' : '') + '>' +
        '<span class="fk-co-choice__body"><span class="fk-co-choice__name">' + o.name + '</span>' + tag('не утверждено') + '</span></label>';
    }).join('') + '</div><p class="fk-note">' + note + '</p>';
  }

  function summary() {
    var items = C.items();
    return '<aside class="fk-cart__summary fk-co-summary" aria-labelledby="co-sum-title">' +
      '<h2 id="co-sum-title">Ваш заказ ' + tag('демо') + '</h2>' +
      '<ul class="fk-co-lines">' + items.map(function (it) {
        var d = C.describe(it);
        return '<li><span class="fk-co-lines__name">' + esc(d.name) + '<span class="fk-co-lines__meta"> × ' + it.qty + '</span></span>' +
          '<span class="fk-co-lines__sum">' + D.fmt(d.unit * it.qty) + '</span></li>';
      }).join('') + '</ul>' +
      '<dl class="fk-spec"><div><dt>Доставка</dt><dd>не рассчитана</dd></div>' +
      '<div class="fk-cart__total"><dt>Сумма</dt><dd>' + D.fmt(C.total()) + '</dd></div></dl>' +
      '<button class="fk-btn fk-btn--primary" type="submit" form="co-form">Подтвердить заказ (демо)</button>' +
      '<p class="fk-note">Цены демонстрационные. Нажатие не создаёт заказ и не списывает оплату.</p>' +
      '<p><a class="fk-link fk-link--tap" href="cart.html">Вернуться в корзину</a></p></aside>';
  }

  function render() {
    if (!C.items().length) {
      root.innerHTML = '<div class="fk-empty"><h2>В корзине пока ничего нет</h2><p>Добавьте демонстрационные товары, чтобы увидеть сценарий оформления.</p>' +
        '<div class="fk-empty__actions"><a class="fk-btn fk-btn--primary" href="catalog.html">Перейти в каталог</a><a class="fk-link fk-link--tap" href="configurator.html">Собрать в подборе</a></div></div>';
      return;
    }
    root.innerHTML = '<div class="fk-cart fk-co">' +
      '<form class="fk-co-form" id="co-form" novalidate>' +
        '<p class="fk-co-errors" id="co-errors" role="alert" tabindex="-1" hidden></p>' +
        '<fieldset class="fk-co-card"><legend><span class="fk-step__n" aria-hidden="true">1</span>Контактные данные</legend>' +
          field('co-name', 'ФИО', 'type="text" autocomplete="name" required') +
          '<div class="fk-co-row">' + field('co-phone', 'Телефон', 'type="tel" autocomplete="tel" inputmode="tel" placeholder="Любой номер для демо" required') +
          field('co-email', 'Email', 'type="email" autocomplete="email" placeholder="name@example.com" required') + '</div>' +
        '</fieldset>' +
        '<fieldset class="fk-co-card"><legend id="delivery-label"><span class="fk-step__n" aria-hidden="true">2</span>Способ доставки</legend>' +
          choice('delivery', DELIVERY, 'Условия, сроки и стоимость доставки не утверждены.') +
          '<div id="co-address-wrap">' + field('co-address', 'Адрес доставки', 'type="text" autocomplete="street-address" required') + '</div>' +
          '<p class="fk-note" id="co-pickup-note" hidden>Пункт самовывоза не утверждён: адрес владелец предоставит позже.</p>' +
        '</fieldset>' +
        '<fieldset class="fk-co-card"><legend id="payment-label"><span class="fk-step__n" aria-hidden="true">3</span>Способ оплаты</legend>' +
          choice('payment', PAYMENT, 'Способы оплаты не утверждены. Оплата в прототипе не подключена.') +
        '</fieldset>' +
        '<fieldset class="fk-co-card"><legend><span class="fk-step__n" aria-hidden="true">4</span>Комментарий к заказу</legend>' +
          '<label class="fk-field__label" for="co-comment">Необязательно</label>' +
          '<textarea class="fk-field fk-co-textarea" id="co-comment" rows="3"></textarea>' +
        '</fieldset>' +
        '<div class="fk-co-card fk-co-consent"><label class="fk-check"><input type="checkbox" id="co-consent" required aria-describedby="co-consent-err">' +
          '<span>Согласен с обработкой персональных данных (<a href="#" data-stub="Страница политики не создана. Текст предоставит владелец.">политика — заглушка</a>)</span></label>' +
          '<p class="fk-co-error" id="co-consent-err" hidden></p></div>' +
      '</form>' + summary() + '</div>';
    bind();
  }

  function setErr(id, msg) {
    var inp = document.getElementById(id), err = document.getElementById(id + '-err');
    if (!err) return;
    err.textContent = msg || ''; err.hidden = !msg;
    if (inp) { if (msg) inp.setAttribute('aria-invalid', 'true'); else inp.removeAttribute('aria-invalid'); }
  }
  function selected(name) { var r = root.querySelector('input[name="' + name + '"]:checked'); return r ? r.value : ''; }
  function needAddress() { return DELIVERY.filter(function (d) { return d.key === selected('delivery'); })[0].address; }

  function validate() {
    var errs = [];
    function check(id, ok, msg) { setErr(id, ok ? '' : msg); if (!ok) errs.push({ id: id, msg: msg }); }
    var v = function (id) { return document.getElementById(id).value.trim(); };
    check('co-name', v('co-name').length >= 2, 'Укажите ФИО.');
    check('co-phone', v('co-phone').replace(/\D/g, '').length >= 6, 'Укажите телефон (не меньше 6 цифр).');
    check('co-email', /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v('co-email')), 'Укажите email в формате name@example.com.');
    check('co-address', !needAddress() || v('co-address').length >= 4, 'Укажите адрес доставки.');
    var consent = document.getElementById('co-consent');
    setErr('co-consent', consent.checked ? '' : 'Нужно согласие на обработку данных.');
    if (!consent.checked) errs.push({ id: 'co-consent', msg: 'Нужно согласие на обработку данных.' });
    return errs;
  }

  function bind() {
    var form = document.getElementById('co-form'), box = document.getElementById('co-errors');
    function toggleAddress() {
      var on = needAddress();
      document.getElementById('co-address-wrap').hidden = !on;
      document.getElementById('co-pickup-note').hidden = on;
      var a = document.getElementById('co-address'); if (a) a.required = on;
    }
    [].forEach.call(form.querySelectorAll('input[name="delivery"]'), function (r) { r.addEventListener('change', toggleAddress); });
    toggleAddress();
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var errs = validate();
      if (errs.length) {
        box.innerHTML = 'Проверьте поля: ' + errs.map(function (x) { return '<a href="#' + x.id + '">' + esc(x.msg) + '</a>'; }).join(' ');
        box.hidden = false;
        document.getElementById(errs[0].id).focus();
        return;
      }
      box.hidden = true;
      done();
    });
    form.addEventListener('input', function (e) { if (e.target.id && e.target.getAttribute('aria-invalid')) setErr(e.target.id, ''); });
  }

  function done() {
    var total = C.total(), n = C.count();
    root.innerHTML = '<section class="fk-co-done" aria-labelledby="co-done-title">' +
      '<h2 id="co-done-title" tabindex="-1">Демонстрация завершена</h2>' +
      '<p>Заказ <strong>не создан</strong>: форма проверена только на странице, данные никуда не отправлялись и не сохранялись, оплата не выполнялась.</p>' +
      '<p class="fk-co-done__sum">В корзине осталось товаров: ' + n + ', сумма (демо): ' + D.fmt(total) + ' ' + tag('демо') + '</p>' +
      '<div class="fk-empty__actions"><a class="fk-btn fk-btn--primary" href="catalog.html">Продолжить покупки</a>' +
      '<a class="fk-link fk-link--tap" href="cart.html">Вернуться в корзину</a></div></section>';
    var h = document.getElementById('co-done-title'); if (h) h.focus();
    window.scrollTo(0, 0);
  }

  render();
})();
