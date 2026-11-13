/* FORKEL — страница товара: смена цвета (торговые предложения), количество, «В корзину», копирование характеристик.
   Данные предложений лежат в data-offer кнопок цвета; корзина — POST на data-add-url (/local/ajax/add2basket.php). */
(function () {
  'use strict';
  var root = document.querySelector('[data-pdp]');
  if (!root) return;

  var img = root.querySelector('[data-pdp-img]');
  var dl = root.querySelector('[data-pdp-dl]');
  var price = root.querySelector('[data-pdp-price]');
  var stock = root.querySelector('[data-pdp-stock]');
  var skuRow = root.querySelector('[data-pdp-sku-row]');
  var sku = root.querySelector('[data-pdp-sku]');
  var colorLabel = root.querySelector('[data-pdp-color]');
  var codeRow = root.querySelector('[data-pdp-code-row]');
  var code = root.querySelector('[data-pdp-code]');
  var add = document.getElementById('pdp-add');
  var qty = document.getElementById('pdp-qty');
  var cartLink = document.getElementById('pdp-cartlink');
  var msg = document.getElementById('pdp-msg');
  var swatches = [].slice.call(root.querySelectorAll('[data-offer]'));
  var current = null;

  function say(text) { msg.textContent = text; msg.hidden = !text; }

  function select(btn) {
    var o;
    try { o = JSON.parse(btn.getAttribute('data-offer')); } catch (e) { return; }
    current = o;
    swatches.forEach(function (s) { if (s === btn) s.setAttribute('aria-current', 'true'); else s.removeAttribute('aria-current'); });
    if (o.img) { img.src = o.img; dl.href = o.img; }
    price.innerHTML = o.price || '';
    stock.textContent = !o.can ? 'Нет в наличии' : (o.qty > 0 ? 'В наличии: ' + o.qty.toLocaleString('ru-RU') + ' шт.' : 'В наличии');
    if (code) { code.textContent = o.code || ''; codeRow.hidden = !o.code; }
    sku.textContent = o.sku || ''; skuRow.hidden = !o.sku;
    if (colorLabel) colorLabel.textContent = o.color || '';
    add.disabled = !o.can;
    say('');
  }
  swatches.forEach(function (s) { s.addEventListener('click', function () { select(s); }); });
  var first = root.querySelector('[data-offer][aria-current="true"]') || swatches[0];
  if (first) { select(first); } else { current = { id: 0 }; }

  function clampQty() {
    var v = parseInt(qty.value, 10);
    qty.value = isNaN(v) ? 1 : Math.min(99, Math.max(1, v));
    return parseInt(qty.value, 10);
  }
  root.addEventListener('click', function (e) {
    var b = e.target.closest('[data-qty]');
    if (!b) return;
    qty.value = (parseInt(qty.value, 10) || 1) + parseInt(b.getAttribute('data-qty'), 10);
    clampQty();
  });
  qty.addEventListener('change', clampQty);

  add.addEventListener('click', function () {
    if (!current || !current.id) return;
    var id = current.id, count = clampQty();
    add.disabled = true;
    /* sessid берём свежий отдельным запросом: HTML страницы товара кешируется */
    fetch(root.getAttribute('data-sessid-url'), { credentials: 'same-origin', cache: 'no-store' })
      .then(function (r) { return r.json(); })
      .then(function (s) {
        var body = new FormData();
        body.append('sessid', s.sessid);
        body.append('id', id);
        body.append('qty', count);
        return fetch(root.getAttribute('data-add-url'), { method: 'POST', body: body, credentials: 'same-origin' });
      })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res && res.ok) {
          say('Товар добавлен в корзину.');
          cartLink.hidden = false;
          [].forEach.call(document.querySelectorAll('[data-cart-count]'), function (b) {
            b.textContent = res.count; b.hidden = !res.count;
          });
        } else {
          say((res && res.errors && res.errors[0]) || 'Не удалось добавить товар в корзину.');
        }
      })
      .catch(function () { say('Не удалось добавить товар. Проверьте соединение и попробуйте позже.'); })
      .then(function () { add.disabled = !(current && current.can); });
  });

  /* Контекст товара для окон «Купить в 1 клик» и «Задать вопрос» (читает js/feedback.js) */
  window.fkFeedbackContext = function () {
    var parts = ['Товар: ' + (root.querySelector('.fk-pdp__title') || {}).textContent];
    if (current && current.sku) parts.push('артикул ' + current.sku);
    if (current && current.color) parts.push('цвет ' + current.color);
    parts.push('количество ' + clampQty());
    if (price.textContent.trim()) parts.push('цена ' + price.textContent.trim());
    parts.push(location.href);
    return parts.join(', ');
  };

  /* Окно с видео. Ссылки YouTube и Rutube приводятся к адресу для встраивания */
  function embed(u) {
    var m = u.match(/youtu\.be\/([\w-]{6,})/) || u.match(/youtube\.com\/(?:watch\?v=|shorts\/|embed\/)([\w-]{6,})/);
    if (m) return 'https://www.youtube.com/embed/' + m[1];
    m = u.match(/rutube\.ru\/(?:video|play\/embed)\/([0-9a-f]{20,})/i);
    if (m) return 'https://rutube.ru/play/embed/' + m[1];
    return /^https?:\/\//i.test(u) ? u : '';
  }
  var modal = null;
  function closeVideo() {
    if (!modal) return;
    modal.querySelector('iframe').removeAttribute('src');
    modal.hidden = true; document.body.style.overflow = '';
    if (modal._opener) modal._opener.focus();
  }
  root.addEventListener('click', function (e) {
    var v = e.target.closest('[data-video]');
    if (!v) return;
    if (!modal) {
      modal = document.createElement('div');
      modal.className = 'fk-modal'; modal.hidden = true;
      modal.innerHTML = '<div class="fk-modal__box" role="dialog" aria-modal="true" aria-label="Видео о товаре">' +
        '<button type="button" class="fk-modal__x" aria-label="Закрыть видео">×</button>' +
        '<div class="fk-modal__frame"><iframe title="Видео о товаре" allow="autoplay; encrypted-media; fullscreen; picture-in-picture" allowfullscreen></iframe></div></div>';
      document.body.appendChild(modal);
      modal.addEventListener('click', function (ev) { if (ev.target === modal || ev.target.closest('.fk-modal__x')) closeVideo(); });
      document.addEventListener('keydown', function (ev) { if (ev.key === 'Escape' && !modal.hidden) closeVideo(); });
    }
    var src = embed(v.getAttribute('data-video'));
    if (!src) return;
    modal._opener = v;
    modal.querySelector('iframe').src = src;
    modal.hidden = false; document.body.style.overflow = 'hidden';
    modal.querySelector('.fk-modal__x').focus();
  });

  /* Копирование кода товара */
  root.addEventListener('click', function (e) {
    var b = e.target.closest('[data-copy-code]');
    if (!b || !code) return;
    var t = code.textContent.trim();
    function done() {
      b.classList.add('is-copied'); setTimeout(function () { b.classList.remove('is-copied'); }, 1200);
      if (window.fkNote) window.fkNote('Скопировано: ' + t);
    }
    if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(t).then(done, done); else done();
  });

  /* Копирование значения характеристики по клику или Enter/Пробел */
  function copy(c) {
    var t = c.querySelector('dd').textContent.trim();
    function done() {
      c.classList.add('is-copied'); setTimeout(function () { c.classList.remove('is-copied'); }, 1200);
      if (window.fkNote) window.fkNote('Скопировано: ' + t);
    }
    if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(t).then(done, done); else done();
  }
  document.addEventListener('click', function (e) { var c = e.target.closest('[data-copyable]'); if (c) copy(c); });
  document.addEventListener('keydown', function (e) {
    var c = e.target.closest && e.target.closest('[data-copyable]');
    if (c && c === e.target && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); copy(c); }
  });
})();
