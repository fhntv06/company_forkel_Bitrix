/* FORKEL — демонстрационный подбор: коллекция → тип изделия → цвет рамки → цвет механизма.
   Правила совместимости, артикулы и цены — демонстрационные (data.js). Результат добавляется в корзину одной позицией «Комплект». */
(function () {
  'use strict';
  var D = window.FKData, C = window.FKCart;
  var root = document.getElementById('cfg');
  if (!root || !D || !C) return;

  var groups = [].slice.call(root.querySelectorAll('[role="radiogroup"]'));
  var imgFrame = document.getElementById('cfg-img-frame');
  var imgMech = document.getElementById('cfg-img-mech');
  var photo = document.getElementById('cfg-photo');
  var summary = document.getElementById('cfg-summary');
  var cartLink = document.getElementById('cfg-cart-link');
  var state = { collection: 'A', type: 'switch', mech: 'white', frame: 'white' };

  function radios(g) { return [].slice.call(g.querySelectorAll('[role="radio"]')); }
  function keyOf(part, r) {
    if (part === 'collection') return r.getAttribute('data-key');
    if (part === 'type') return r.getAttribute('data-symbol').slice(2);
    return r.getAttribute('data-fin').slice(4);
  }
  function allowed(part, key) {
    var c = D.COMPAT[state.collection];
    return part === 'collection' || (part === 'type' ? c.types : c.colors).indexOf(key) >= 0;
  }
  function out(name, html) {
    [].forEach.call(root.querySelectorAll('[data-out="' + name + '"]'), function (el) { el.innerHTML = html; });
  }
  function spec() { return { col: state.collection, mech: state.type, mc: state.mech, fc: state.frame }; }

  function sync() {
    var changed = false;
    groups.forEach(function (g) {
      var part = g.getAttribute('data-part'), rs = radios(g), first = null;
      rs.forEach(function (r) {
        var k = keyOf(part, r), ok = allowed(part, k);
        r.disabled = !ok;
        if (ok && !first) first = k;
      });
      if (!allowed(part, state[part])) { state[part] = first; changed = true; }
      rs.forEach(function (r) {
        var on = keyOf(part, r) === state[part];
        r.setAttribute('aria-checked', String(on));
        r.tabIndex = on ? 0 : -1;
      });
    });
    ['frame', 'mech'].forEach(function (k) {
      var el = root.querySelector('[data-val="' + k + '"]');
      if (el) el.textContent = D.COLORS[state[k]];
    });
    imgFrame.src = D.imgUrl('frame', state.frame);
    imgMech.src = D.imgUrl('mech', state.mech);
    photo.setAttribute('aria-label', 'Превью (образцы цвета): рамка — ' + D.COLORS[state.frame].toLowerCase() + ', механизм — ' + D.COLORS[state.mech].toLowerCase());
    var k = D.kit(spec());
    out('collection', k.collection);
    out('frame', D.COLORS[state.frame] + ' · ' + D.fmt(k.parts[0].price) + ' ' + D.tag('демо'));
    out('mech', D.TYPES[state.type].mech + ', ' + D.COLORS[state.mech].toLowerCase() + ' · ' + D.fmt(k.parts[1].price) + ' ' + D.tag('демо'));
    out('kit-sku', '<span class="fk-stub">' + k.sku + '</span> ' + D.tag('заглушка'));
    out('kit-price', D.fmt(k.price) + ' ' + D.tag('демо'));
    var cl = document.getElementById('cfg-catalog-link');
    if (cl) cl.setAttribute('href', 'catalog.html?category=' + (state.type === 'socket' ? 'sockets' : 'switches'));
    summary.textContent = 'Выбрано (демо): ' + k.collection + ', ' + D.TYPES[state.type].mech.toLowerCase() + ', механизм — ' +
      D.COLORS[state.mech] + ', рамка — ' + D.COLORS[state.frame] + '. Цена комплекта: ' + D.fmt(k.price) + ' (демо).' +
      (changed ? ' Недоступные в этой коллекции варианты заменены.' : '');
  }

  // Образцы цвета в кнопках — те же изображения слоёв
  groups.forEach(function (g) {
    var part = g.getAttribute('data-part');
    if (part !== 'frame' && part !== 'mech') return;
    radios(g).forEach(function (r) { r.style.setProperty('--img', 'url(' + D.imgUrl(part, keyOf(part, r)) + ')'); });
  });

  groups.forEach(function (g) {
    var part = g.getAttribute('data-part');
    g.addEventListener('click', function (e) {
      var r = e.target.closest('[role="radio"]');
      if (!r || r.disabled) return;
      state[part] = keyOf(part, r);
      sync();
    });
    g.addEventListener('keydown', function (e) {
      var rs = radios(g).filter(function (r) { return !r.disabled; });
      var i = rs.indexOf(document.activeElement);
      var step = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 }[e.key];
      if (i < 0 || !step) return;
      e.preventDefault();
      var next = rs[(i + step + rs.length) % rs.length];
      state[part] = keyOf(part, next);
      sync();
      next.focus();
    });
  });

  // Карточки коллекций A–D ведут в подбор с выбранной коллекцией (E–H — демонстрационные заглушки)
  [].forEach.call(document.querySelectorAll('[data-collection]'), function (a) {
    a.addEventListener('click', function () {
      state.collection = a.getAttribute('data-collection');
      sync();
    });
  });

  // Переход со страницы коллекции: index.html?collection=A…D#configurator
  var pm = /[?&]collection=([A-D])(&|$)/.exec(location.search);
  if (pm) state.collection = pm[1];

  // Сброс параметров (страница подбора)
  var resetBtn = document.getElementById('cfg-reset');
  if (resetBtn) resetBtn.addEventListener('click', function () {
    state.collection = 'A'; state.type = 'switch'; state.mech = 'white'; state.frame = 'white';
    sync();
    if (window.fkNote) window.fkNote('Параметры подбора сброшены.');
  });

  document.getElementById('cfg-add').addEventListener('click', function () {
    var r = C.add({ kind: 'kit', col: state.collection, mech: state.type, mc: state.mech, fc: state.frame }, 1);
    C.announceAdd(r, 'Комплект добавлен в корзину (демо)');
    if (cartLink) cartLink.hidden = false;
  });
  sync();
})();
