/* FORKEL — демонстрационные данные прототипа. Реальных товаров, артикулов, цен, остатков и правил совместимости нет.
   Цены 100/200/300 ₽, артикулы «ХХХ-…» и правила совместимости — условные, везде помечаются «демо» или «заглушка». */
(function () {
  'use strict';

  var CATEGORIES = [
    { key: 'sockets', name: 'Розетки' },
    { key: 'switches', name: 'Выключатели' },
    { key: 'frames', name: 'Рамки' }
  ];
  var COLORS = { white: 'Белый глянцевый', whitem: 'Белый матовый', gold: 'Золотой', cashmere: 'Кашемир матовый', gray: 'Серый', black: 'Чёрный матовый' };
  var COLOR_CODE = { white: 'WHG', whitem: 'WHM', gold: 'GLD', cashmere: 'CSH', gray: 'GRY', black: 'BLM' };
  var STOCKS = { in: 'В наличии', order: 'Под заказ' };
  /* Демонстрационные цены по условной ценовой группе */
  var PRICE_BY_GROUP = { 1: 100, 2: 200, 3: 300 };
  var TYPES = {
    switch: { symbol: 'p-switch', cat: 'switches', name: 'Демонстрационный выключатель', mech: 'Выключатель', code: 'SW', price: 100 },
    double: { symbol: 'p-double', cat: 'switches', name: 'Демонстрационный двойной выключатель', mech: 'Двойной выключатель', code: 'DB', price: 200 },
    socket: { symbol: 'p-socket', cat: 'sockets', name: 'Демонстрационная розетка', mech: 'Розетка', code: 'SK', price: 200 },
    frame: { symbol: 'p-frame', cat: 'frames', name: 'Демонстрационная рамка', mech: 'Рамка', code: 'FR', price: 100 }
  };
  var FRAME_PRICE = 100;
  var TONES = ['light', 'sand', 'stone', 'light', 'stone', 'sand'];
  var ROWS = [
    ['switch', 'white', 'A', 2, 'in'], ['socket', 'black', 'B', 1, 'in'], ['double', 'whitem', 'C', 3, 'order'], ['frame', 'cashmere', 'D', 2, 'in'],
    ['switch', 'black', 'B', 1, 'order'], ['socket', 'white', 'A', 3, 'in'], ['double', 'gray', 'C', 2, 'in'], ['frame', 'white', 'A', 1, 'order'],
    ['switch', 'gold', 'D', 3, 'in'], ['socket', 'whitem', 'B', 2, 'in'], ['double', 'black', 'A', 1, 'order'], ['frame', 'black', 'C', 3, 'in']
  ];
  var PRODUCTS = ROWS.map(function (r, i) {
    var t = TYPES[r[0]];
    return {
      id: i + 1, type: r[0], symbol: t.symbol, cat: t.cat, name: t.name, color: r[1], collection: 'Коллекция ' + r[2],
      group: r[3], stock: r[4], sku: 'ХХХ-' + ('00' + (i + 1)).slice(-3), tone: TONES[i % TONES.length]
    };
  });
  /* Правила совместимости рамок и механизмов в исходных данных неизвестны, поэтому в демо-режиме ограничений нет:
     в каждой коллекции доступны все шесть цветов и все три типа изделий. Цвета и их порядок — по примеру фильтра, присланного заказчиком. */
  var ALL = ['white', 'whitem', 'gold', 'cashmere', 'gray', 'black'];
  var ALLTYPES = ['switch', 'double', 'socket'];
  var COMPAT = { A: { colors: ALL, types: ALLTYPES }, B: { colors: ALL, types: ALLTYPES }, C: { colors: ALL, types: ALLTYPES }, D: { colors: ALL, types: ALLTYPES } };

  /* Демонстрационные цветовые варианты: собственный цвет и два следующих из списка. Реальных вариантов товара нет. */
  function variants(p) {
    var i = ALL.indexOf(p.color);
    return [0, 1, 2].map(function (k) { return ALL[(i + k) % ALL.length]; });
  }
  function dots(p) {
    return '<ul class="fk-dots" aria-label="Цветовые варианты (демо)">' + variants(p).map(function (c) {
      return '<li class="fin-' + c + '" title="' + COLORS[c] + '"><span class="fk-vh">' + COLORS[c] + '</span></li>';
    }).join('') + '</ul><span class="fk-tag">демо</span>';
  }
  function byId(id) { return PRODUCTS.filter(function (p) { return p.id === id; })[0]; }
  function catName(key) { return CATEGORIES.filter(function (c) { return c.key === key; })[0].name; }
  function price(p) { return PRICE_BY_GROUP[p.group]; }
  function fmt(n) { return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ₽'; }
  function tag(t) { return '<span class="fk-tag">' + t + '</span>'; }
  function illu(p) {
    return '<svg class="fin-' + p.color + '" viewBox="0 0 240 240" aria-hidden="true" focusable="false">' +
      '<use href="#shade150" transform="translate(45 45)"/><use href="#' + p.symbol + '" x="45" y="45" width="150" height="150"/></svg>';
  }
  /* Комплект: рамка и механизм одной позицией. spec: {col, mech, mc, fc} */
  function kitValid(s) {
    var c = COMPAT[s.col];
    return !!c && c.types.indexOf(s.mech) >= 0 && c.colors.indexOf(s.mc) >= 0 && c.colors.indexOf(s.fc) >= 0;
  }
  function kit(s) {
    var t = TYPES[s.mech];
    var parts = [
      { role: 'Рамка', name: 'Демонстрационная рамка', color: s.fc, sku: 'ХХХ-Р-' + COLOR_CODE[s.fc], price: FRAME_PRICE, symbol: 'p-frame' },
      { role: 'Механизм', name: t.mech, color: s.mc, sku: 'ХХХ-М-' + t.code + '-' + COLOR_CODE[s.mc], price: t.price, symbol: t.symbol }
    ];
    return {
      key: 'k:' + [s.col, s.mech, s.mc, s.fc].join(':'), name: 'Комплект: Коллекция ' + s.col + ', ' + t.mech.toLowerCase(),
      collection: 'Коллекция ' + s.col, sku: 'ХХХ-КМП-' + s.col + '-' + t.code + '-' + COLOR_CODE[s.mc] + '-' + COLOR_CODE[s.fc],
      parts: parts, price: parts[0].price + parts[1].price
    };
  }
  /* Образцы цвета из присланных заказчиком файлов (prototype/assets/images, список — about_photo.md).
     Это слои из примера Kollinger Eclipse, а не продукция FORKEL. Механизм — нижний слой, рамка — верхний. */
  var IMG_BASE = '../assets/images/';
  var IMG = {
    frame: { white: 'tcvet_ramki_belyy_glyantsevyy', whitem: 'tcvet_ramki_belyy_matovyy', gold: 'tcvet_ramki_zolotoy', cashmere: 'tcvet_ramki_kashemir-matovyy', gray: 'tcvet_ramki_seryy', black: 'tcvet_ramki_chernyy_matovyy' },
    mech: { white: 'tcvet_mehanizma_belyy_glyantsevyy', whitem: 'tcvet_mehanizma_belyy_matovyy', gold: 'tcvet_mehanizma_zolotoy', cashmere: 'tcvet_mehanizma_kashemir-matovyy', gray: 'tcvet_mehanizma_seryy', black: 'tcvet_mehanizma_chyernyy_matovyy' }
  };
  function imgUrl(part, color) { return IMG_BASE + IMG[part][color] + '.png'; }
  function kitImg(s) {
    return '<span class="fk-kitimg" aria-hidden="true"><img src="' + imgUrl('mech', s.mc) + '" alt="" width="500" height="500" loading="lazy">' +
      '<img src="' + imgUrl('frame', s.fc) + '" alt="" width="500" height="500" loading="lazy"></span>';
  }
  function kitIllu(s) {
    var t = TYPES[s.mech];
    return '<svg viewBox="0 0 240 240" aria-hidden="true" focusable="false"><use href="#shade150" transform="translate(30 30) scale(1.2)"/>' +
      '<g class="fin-' + s.fc + '"><use href="#p-frame" x="30" y="30" width="180" height="180"/></g>' +
      '<g class="fin-' + s.mc + '"><use href="#' + t.symbol + '" x="60" y="60" width="120" height="120"/></g></svg>';
  }

  window.FKData = {
    CATEGORIES: CATEGORIES, COLORS: COLORS, COLOR_CODE: COLOR_CODE, STOCKS: STOCKS, PRICE_BY_GROUP: PRICE_BY_GROUP, TYPES: TYPES,
    PRODUCTS: PRODUCTS, COMPAT: COMPAT, byId: byId, catName: catName, price: price, fmt: fmt, tag: tag, illu: illu,
    kitValid: kitValid, kit: kit, kitIllu: kitIllu, kitImg: kitImg, imgUrl: imgUrl, ALL: ALL, variants: variants, dots: dots
  };
  [].forEach.call(document.querySelectorAll('[data-variants]'), function (el) {
    var p = byId(parseInt(el.getAttribute('data-variants'), 10));
    if (p) el.innerHTML = dots(p);
  });
})();
