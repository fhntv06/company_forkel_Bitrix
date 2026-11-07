/* FORKEL — демонстрационные данные прототипа. Реальных товаров, артикулов, цен, остатков и правил совместимости нет.
   Цены 100/200/300 ₽, артикулы «ХХХ-…» и правила совместимости — условные, везде помечаются «демо» или «заглушка». */
(function () {
  'use strict';

  var CATEGORIES = [
    { key: 'sockets', name: 'Розетки' },
    { key: 'switches', name: 'Выключатели' },
    { key: 'frames', name: 'Рамки' },
    { key: 'blinds', name: 'Управление жалюзи' },
    { key: 'thermostats', name: 'Терморегуляторы' },
    { key: 'dimmers', name: 'Диммеры' }
  ];
  /* Цвета и коллекции — по обратной связи клиента: серии F70 и F55, у каждой три исполнения по материалу рамки.
     Базовые цвета F70: белый, чёрный, серый, светло-серый, кашемир, золотой; F55: белый, чёрный, серый, золотой (список может расширяться). */
  var COLORS = { white: 'Белый', black: 'Чёрный', gray: 'Серый', lightgray: 'Светло-серый', cashmere: 'Кашемир', gold: 'Золотой' };
  var COLOR_CODE = { white: 'WHT', black: 'BLK', gray: 'GRY', lightgray: 'LGR', cashmere: 'CSH', gold: 'GLD' };
  var COLLECTIONS = { F70: 'Коллекция F70', F55: 'Коллекция F55' };
  var MATERIALS = { glass: 'Стекло', aluminum: 'Алюминий', plastic: 'Термопласт',  };
  var MAT_CODE = { glass: 'GL', aluminum: 'AL', plastic: 'TP' };
  var STOCKS = { in: 'В наличии', order: 'Под заказ', unknown: 'Наличие уточняется' };
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
  /* [тип, цвет, коллекция, ценовая группа, наличие, материал рамки] — условный набор; цвет всегда входит в палитру своей коллекции */
  var ROWS = [
    ['switch', 'white', 'F70', 2, 'in', 'glass'], ['socket', 'black', 'F70', 1, 'in', 'aluminum'], ['double', 'lightgray', 'F70', 3, 'order', 'plastic'], ['frame', 'cashmere', 'F70', 2, 'in', 'glass'],
    ['switch', 'black', 'F55', 1, 'order', 'plastic'], ['socket', 'white', 'F55', 3, 'in', 'aluminum'], ['double', 'gray', 'F55', 2, 'in', 'glass'], ['frame', 'white', 'F55', 1, 'order', 'aluminum'],
    ['switch', 'gold', 'F70', 3, 'in', 'aluminum'], ['socket', 'gray', 'F55', 2, 'in', 'plastic'], ['double', 'black', 'F55', 1, 'order', 'glass'], ['frame', 'gold', 'F55', 3, 'in', 'plastic']
  ];
  var PRODUCTS = ROWS.map(function (r, i) {
    var t = TYPES[r[0]];
    return {
      id: i + 1, type: r[0], symbol: t.symbol, cat: t.cat, name: t.name, color: r[1], collection: COLLECTIONS[r[2]], col: r[2], mat: r[5],
      group: r[3], stock: r[4], sku: 'ХХХ-' + ('00' + (i + 1)).slice(-3), tone: TONES[i % TONES.length]
    };
  });
  /* Правила совместимости рамок и механизмов в исходных данных неизвестны, поэтому в демо-режиме ограничений нет:
     в каждой коллекции доступны все шесть цветов и все три типа изделий. Цвета и их порядок — по примеру фильтра, присланного заказчиком. */
  var ALL = ['white', 'black', 'gray', 'lightgray', 'cashmere', 'gold'];
  var ALLTYPES = ['switch', 'double', 'socket'];
  var COMPAT = {
    F70: { colors: ['white', 'black', 'gray', 'lightgray', 'cashmere', 'gold'], types: ALLTYPES },
    F55: { colors: ['white', 'black', 'gray', 'gold'], types: ALLTYPES }
  };

  /* Демонстрационные цветовые варианты: собственный цвет и два следующих из списка. Реальных вариантов товара нет. */
  function variants(p) {
    var pal = COMPAT[p.col].colors, i = pal.indexOf(p.color);
    return [0, 1, 2].map(function (k) { return pal[(i + k) % pal.length]; });
  }
  function dots(p) {
    if (p.real) return '<ul class="fk-dots" aria-label="Цветовые варианты">' + p.spec.items.map(function (it) {
      return '<li style="--finish:' + PALETTE[it.color].hex + '" title="' + COLORS[it.color] + '"><span class="fk-vh">' + COLORS[it.color] + '</span></li>';
    }).join('') + '</ul>';

    return '<ul class="fk-dots" aria-label="Цветовые варианты">' + variants(p).map(function (c) {
      return '<li class="fin-' + c + '" title="' + COLORS[c] + '"><span class="fk-vh">' + COLORS[c] + '</span></li>';
    }).join('') + '</ul>';
  }
  function byId(id) { return PRODUCTS.filter(function (p) { return p.id === id; })[0]; }
  function catName(key) { return CATEGORIES.filter(function (c) { return c.key === key; })[0].name; }
  function price(p) { return p.price || PRICE_BY_GROUP[p.group]; }
  function fmt(n) { return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ₽'; }
  function tag(t) { return '<span class="fk-tag">' + t + '</span>'; }
  function illu(p) {
    if (p.img) return '<img src="' + p.img + '" alt="" aria-hidden="true" loading="lazy" style="width:100%;height:100%;object-fit:contain">';
    return '<svg class="fin-' + p.color + '" viewBox="0 0 240 240" aria-hidden="true" focusable="false">' +
        '<use href="#shade150" transform="translate(45 45)"/><use href="#' + p.symbol + '" x="45" y="45" width="150" height="150"/></svg>';
  }
  /* Комплект: рамка и механизм одной позицией. spec: {col, mat, mech, mc, fc} (коллекция, материал рамки, тип изделия, цвет механизма, цвет рамки) */
  function kitValid(s) {
    var c = COMPAT[s.col];
    return !!c && !!MATERIALS[s.mat] && c.types.indexOf(s.mech) >= 0 && c.colors.indexOf(s.mc) >= 0 && c.colors.indexOf(s.fc) >= 0;
  }
  function kit(s) {
    var t = TYPES[s.mech];
    var parts = [
      { role: 'Рамка', name: 'Демонстрационная рамка, ' + MATERIALS[s.mat].toLowerCase(), color: s.fc, sku: 'ХХХ-Р-' + MAT_CODE[s.mat] + '-' + COLOR_CODE[s.fc], price: FRAME_PRICE, symbol: 'p-frame' },
      { role: 'Механизм', name: t.mech, color: s.mc, sku: 'ХХХ-М-' + t.code + '-' + COLOR_CODE[s.mc], price: t.price, symbol: t.symbol }
    ];
    return {
      key: 'k:' + [s.col, s.mat, s.mech, s.mc, s.fc].join(':'), name: 'Комплект: Коллекция ' + s.col + ', ' + MATERIALS[s.mat].toLowerCase() + ', ' + t.mech.toLowerCase(),
      collection: COLLECTIONS[s.col], material: MATERIALS[s.mat], sku: 'ХХХ-КМП-' + s.col + '-' + MAT_CODE[s.mat] + '-' + t.code + '-' + COLOR_CODE[s.mc] + '-' + COLOR_CODE[s.fc],
      parts: parts, price: parts[0].price + parts[1].price
    };
  }
  /* Образцы цвета из присланных заказчиком файлов (prototype/assets/images, список — about_photo.md).
     Это слои из примера Kollinger Eclipse, а не продукция FORKEL. Механизм — нижний слой, рамка — верхний. */
  var IMG_BASE = 'assets/images/';
  var IMG = {
    frame: { white: 'tcvet_ramki_belyy_glyantsevyy', gold: 'tcvet_ramki_zolotoy', cashmere: 'tcvet_ramki_kashemir-matovyy', gray: 'tcvet_ramki_seryy', black: 'tcvet_ramki_chernyy_matovyy' },
    mech: { white: 'tcvet_mehanizma_belyy_glyantsevyy', gold: 'tcvet_mehanizma_zolotoy', cashmere: 'tcvet_mehanizma_kashemir-matovyy', gray: 'tcvet_mehanizma_seryy', black: 'tcvet_mehanizma_chyernyy_matovyy' }
  };
  /* Светло-серого образца в присланных файлах нет: показан серый слой, осветлённый фильтром (заглушка) */
  var IMG_ALIAS = { lightgray: 'gray' };
  var IMG_FILTER = { lightgray: 'brightness(1.32) saturate(0.8)' };
  function imgUrl(part, color) { return IMG_BASE + IMG[part][IMG_ALIAS[color] || color] + '.png'; }
  function imgStyle(color) { return IMG_FILTER[color] ? ' style="filter:' + IMG_FILTER[color] + '"' : ''; }
  function kitImg(s) {
    return '<span class="fk-kitimg" aria-hidden="true"><img src="' + imgUrl('mech', s.mc) + '" alt="" width="500" height="500" loading="lazy"' + imgStyle(s.mc) + '>' +
      '<img src="' + imgUrl('frame', s.fc) + '" alt="" width="500" height="500" loading="lazy"' + imgStyle(s.fc) + '></span>';
  }
  function kitIllu(s) {
    var t = TYPES[s.mech];
    return '<svg viewBox="0 0 240 240" aria-hidden="true" focusable="false"><use href="#shade150" transform="translate(30 30) scale(1.2)"/>' +
      '<g class="fin-' + s.fc + '"><use href="#p-frame" x="30" y="30" width="180" height="180"/></g>' +
      '<g class="fin-' + s.mc + '"><use href="#' + t.symbol + '" x="60" y="60" width="120" height="120"/></g></svg>';
  }

  /* ---------- Реальные товары заказчика (ledron.ru). Один товар = один цвет; варианты изделия — spec.items ---------- */
  var enc = encodeURI; // адреса приводятся к нормальному виду здесь, один раз
  var PALETTE = {
    white:     { hex: '#FBFBFA', swatch: enc('https://ledron.ru/upload/uf/953/1ywuq63930l1nn4rcsvnehlwai3dl6ie/72949bc6a6ea1b8d6cb46b03ffcb18f4.jpg') },
    black:     { hex: '#2A2C2F', swatch: enc('https://ledron.ru/upload/uf/a10/pwgb9aymlzlb1xp4dg5gw1gwhv7q1h7h/b25af1a341c05ae8323d09287b90aa2a.jpg') },
    gray:      { hex: '#9AA0A6', swatch: enc('https://ledron.ru/upload/uf/9ce/34x8ivqx0hr897eym0npo3vg3pnxkplv/seriy — копия.jpg') },
    lightgray: { hex: '#D3D6D9', swatch: enc('https://ledron.ru/upload/uf/8ff/7myqqqx2n18uvl8t0wixivsc3hl27abv/svetlo-seriy.jpg') },
    cashmere:  { hex: '#CDBFAA', swatch: enc('https://ledron.ru/upload/uf/bf2/xtiisyu3se8374tywl9bn8qvla1c90pt/bej.JPG') },
    gold:      { hex: '#C3A464', swatch: enc('https://ledron.ru/upload/uf/980/whu1ek5k91v33qvy3mm7ml2px9b8ivhn/jeltiy.jpg') }
  };
  function im(path) { return enc('https://ledron.ru/upload/webp/upload/resize_cache/iblock/' + path); }
  var MANUAL = enc('https://ledron.ru/upload/iblock/696/930imjoqbfaq3dgr0yx0bvm8r3as8203/Инструкция для розеток и выключателей FORKEL.pdf');

  function addReal(g) {
    g.items.forEach(function (it, i) {
      PRODUCTS.push({ id: it.id, type: g.type, symbol: g.symbol, cat: g.cat, name: g.name, color: it.color,
        collection: 'Серия ' + g.series, col: 'F70', mat: 'plastic', group: 0, stock: it.qty ? 'in' : 'unknown', qty: it.qty,
        sku: it.sku, tone: 'light', real: true, hide: i > 0, grp: g.grp, price: g.price, img: it.img, spec: g });
    });
  }

  /* Розетка F70 (offer 13238…) */
  addReal({ grp: 'sock-f70', type: 'socket', symbol: 'p-socket', cat: 'sockets', name: 'Розетка одинарная с заземлением с защитными шторками F70', series: 'F70', price: 665,
    links: { model: 'https://disk.yandex.ru/d/GfGSGaCafXjPKg', video: 'https://vkvideo.ru/video_ext.php?oid=-109431961&id=456240249', manual: MANUAL },
    desc: '<p>Розетка одинарная с заземлением и защитными шторками из серии F70. Номинальный ток 16 А, напряжение 250 В, 50–60 Гц, степень защиты IP20. Монтаж встраиваемый: механизм крепится в распор лапками и винтами, провода подключаются винтовым зажимом. Рамка на 1–5 постов приобретается отдельно. Гарантия 5 лет.</p>',
    groups: [
      ['Основные', [['Бренд', 'FORKEL'], ['Серия', 'F70'], ['Тип', 'Розетка'], ['Гарантия', '5 лет']]],
      ['Электрические', [['Питание', '250V 50–60 Гц'], ['Номинальный ток', '16 А'], ['Степень защиты', 'IP20'], ['Количество постов', '1'], ['Исполнение', 'с заземляющим штырём']]],
      ['Монтаж и материал', [['Тип монтажа', 'встраиваемый'], ['Способ подключения', 'винтовой зажим, клемма'], ['Тип крепления', 'в распор (лапками) и винтами'], ['Материал', 'термопласт матовый'], ['Особенность', 'рамка на 1–5 постов комплектуется отдельно']]]
    ],
    items: [
      { id: 13238, color: 'white',     sku: 'EU Socket White',      qty: 3702, img: im('063/a837nl6ruvlyujex721ocaehtz59ikui/1366_600_0/EU white (1).webp') },
      { id: 13239, color: 'black',     sku: 'EU Socket Black',      qty: 3246, img: im('487/agi91vi6iajop7p1b8ggquhu4ybo60k8/1366_600_0/EU black (1).webp') },
      { id: 13243, color: 'gray',      sku: 'EU Socket Grey',       qty: 1328, img: im('572/xvax9p8582kr1p3wlsvc30wd3nloytc1/1366_600_0/EU grey (1).webp') },
      { id: 13242, color: 'lightgray', sku: 'EU Socket Light Grey', qty: 4638, img: im('1d9/0bn5jkstynckk8owtz1t44z5xgi1jc1h/1366_600_0/EU light grey (1).webp') },
      { id: 13240, color: 'cashmere',  sku: 'EU Socket Cashmere',   qty: 3447, img: im('b08/l3uurhvmk0b43egr48d3qk7mgq5dbphh/1366_600_0/EU cashmere (1).webp') },
      { id: 13241, color: 'gold',      sku: 'EU Socket Gold',       qty: 1128, img: im('6e2/2exhlzd6uq419adyy7g054nm18rkwk9o/1366_600_0/EU gold (1).webp') }
    ] });

  /* Одноклавишный выключатель F70 (id 14001–14006 — идентификаторы прототипа: в материале заказчика кодов offer нет) */
  addReal({ grp: 'sw1-f70', type: 'switch', symbol: 'p-switch', cat: 'switches', name: 'Одноклавишный выключатель F70', series: 'F70', price: 635,
    links: { model: 'https://disk.yandex.ru/d/Je7RNhpVuPPd8Q', video: '', manual: MANUAL },
    desc: '<p>Выключатель FORKEL 1 клавиша, без подсветки (механизм без рамки). Классическое решение для управления освещением в доме или офисе. Механизм без рамки позволяет собирать модульные конструкции под любые задачи. Покрытие Soft Touch отличается приятной бархатистой матовой фактурой, устойчивостью к загрязнениям и отпечаткам пальцев.</p>' +
        '<p>Увеличенные клавиши удобны для нажатия. Выключатель рассчитан на управление одной линией света: подходит для прихожих, спален, кухонь и гостиных.</p>' +
        '<p>Скрытая установка в круглый подрозетник 60–65 мм (рекомендуемая глубина от 40 мм). Подключение через быстрозажимные клеммы (автозажимы), крепление на распорные лапки.</p>' +
        '<p>Механизм совместим с ультратонкими рамками (толщина 4 мм), которые приобретаются отдельно. В ассортименте <a href="catalog.html?category=frames">рамки</a> на 1, 2, 3, 4 и 5 постов в шести цветах: белый, серый, светло-серый, кашемир, золотой, чёрный.</p>' +
        '<p>Напряжение 220 В, ток 10 А, тип одноклавишный, монтаж скрытый. Комплектация: механизм, инструкция, упаковка. Гарантия FORKEL — 5 лет.</p>' +
        '<p>Остались вопросы по подбору? Переходите в раздел <a href="contacts.html">контактов</a>.</p>',
    groups: [
      ['Основные', [['Бренд', 'FORKEL'], ['Серия', 'F70'], ['Тип', 'Выключатель'], ['Гарантия', '5 лет']]],
      ['Электрические', [['Питание', '100–250 В, 50–60 Гц'], ['Номинальный ток', '10 А'], ['Степень защиты', 'IP20'], ['Количество клавиш', '1'], ['Схема подключения', 'выключатель 1-полюс'], ['Коммутация', 'схема 1']]],
      ['Монтаж и материал', [['Тип монтажа', 'встраиваемый'], ['Способ подключения', 'автозажим'], ['Тип крепления', 'в распор (лапками) и винтами'], ['Материал', 'термопласт матовый'], ['Особенность', 'рамка на 1, 2, 3, 4, 5 постов комплектуется отдельно']]]
    ],
    items: [
      { id: 14001, color: 'white',     sku: '1 Gang 1 Way White',      qty: 829, img: im('5b4/sab75qx4iltzxaffhez1ngmhqv43l2hg/1366_600_0/1 Gang 1 Way white копия.webp') },
      { id: 14002, color: 'black',     sku: '1 Gang 1 Way Black',      qty: 684, img: im('961/u9hh9b0vu0ny3mrjuvr1j1ecuno4uxde/1366_600_0/1 Gang 1 Way black копия.webp') },
      { id: 14003, color: 'gray',      sku: '1 Gang 1 Way Grey',       qty: 624, img: im('d78/pkfi732p9t99d04ljf8yyeo0vzznq4mp/1366_600_0/1 Gang 1 Way grey копия.webp') },
      { id: 14004, color: 'lightgray', sku: '1 Gang 1 Way Light Grey', qty: 852, img: im('06b/ithyscl3sn9s50shrt0fsz1dlllo0tbx/1366_600_0/1 Gang 1 Way light grey копия.webp') },
      { id: 14005, color: 'cashmere',  sku: '1 Gang 1 Way Cashmere',   qty: 690, img: im('6cc/gyd96dd1mey10c33cuk7ywa03yb1xfu1/1366_600_0/1 Gang 1 Way cashmere копия.webp') },
      { id: 14006, color: 'gold',      sku: '1 Gang 1 Way Gold',       qty: 626, img: im('36f/r2e7b7a1j97rhxsn1ibyddwke0mj89td/1366_600_0/1 Gang 1 Way gold копия.webp') }
    ] });

  /* Рамки F70 на 1–5 постов. Порядок каталогов изображений: белый, светло-серый, золотой, кашемир, чёрный, серый.
     id = 15000 + постов × 10 + номер цвета (белая рамка на 1 пост — 15011). Артикулов и наличия в материале нет. */
  var FR_COLORS = ['white', 'lightgray', 'gold', 'cashmere', 'black', 'gray'];
  var FR_FILE = { white: 'white', lightgray: 'light grey', gold: 'gold', cashmere: 'cashmere', black: 'black', gray: 'grey' };
  var POSTS = { 1: ['Рамка на 1 пост F70', 175, '8fa/s6w8xm4yetk9xr3011ppo4hqkhb0dpp2', '8bd/wxbh3u817jocllao6fm0ltt5hp4donzi', '17d/rwwssftuxgloe0cppqhvz9zvju0mduvw', '2ea/wv0t2wv05xie7t5xav8f01utz7vj8y54', '9e3/7xb64sczgtd8onmn0wuadldl7y5i0nyb', '492/dw6l17eejxu0e1og40szz4qyvot5p0kz'],
    2: ['Рамка на 2 поста F70', 360, 'fe5/rfjoifz53v2gdsb0svg09edtn868lkz0', '03a/rv3zlpgf1fjlz6o6iqux0lvzo3fx3vgm', '9de/c4qtdlqxmooxvdy8urtl1dvfgld28f1t', '058/9ujp6xv443mv8udgua9lx4daeznw53jr', '3cd/nl1kpldocaqc84mcwx54pbfvonjkz7qh', '4aa/4196y0nkl1eaf31yspdzcnljur8tyv3w'],
    3: ['Рамка на 3 поста F70', 550, 'db4/ifgartchbl324i5f66b1fc6md2ui8na0', '5eb/o2gih0ebr1yg6tj453q40hu1skjhmhp2', '7d7/8wb12e7hi15e44ew19ld2scz5tq626k3', '610/3k31j9perpchz00crchppgsrac9b6dgz', '5ac/ouxj99a50m26j62jtmc305cqpgu7kcvy', 'a08/283yq73e7wh1avrefhgfold2fw1785iq'],
    4: ['Рамка на 4 поста F70', 730, 'afc/5bek5xqx33owrnqxnc9azxb401bex9z7', 'ef1/ldufyk1a3vv0bma62ywcebly5ve859hs', '928/zq1tw7t01cim8vwvdt44vbf6iyjszt3o', 'e78/jf0e8b89zgsg8xwb5v8dr585howf0pfg', '715/xpswj0lwcnju2cxictldu9m1z23otr4b', '79e/pvwpy6tcsqgit81ep3hytom3k3vuh2ab'],
    5: ['Рамка на 5 постов F70', 910, 'aef/eh9ly3uudhv1mmjz43965x0o2pggqf3q', '58d/ipviatg5xj5t4zx2uhiomp4i1f8u1vpb', 'e83/qm2lahkhk6dx078go8fyr4dzbj7hq9hc', '780/10zkltzoufihbbunz55q45eu9k0ru3wh', 'a8b/yga0ac2i1hl0uo0y3mzuwuaqvhy3k6a6', '356/yin7uth10dceb974gt0f9m84ccumczpb'] };
  Object.keys(POSTS).forEach(function (n) {
    var f = POSTS[n];
    addReal({ grp: 'frame' + n + '-f70', type: 'frame', symbol: 'p-frame', cat: 'frames', name: f[0], series: 'F70', price: f[1], links: null, desc: '',
      groups: [['Основные', [['Бренд', 'FORKEL'], ['Серия', 'F70'], ['Тип', 'Рамка'], ['Количество постов', n]]]],
      items: ALL.map(function (c, i) {
        var k = FR_COLORS.indexOf(c);
        var file = (n === '5' && c === 'gold' ? '5 frame gold' : n + ' Frame PC ' + FR_FILE[c]) + '.webp';
        return { id: 15000 + n * 10 + i + 1, color: c, sku: '—', qty: 0,
          img: enc('https://ledron.ru/upload/webp/upload/iblock/' + f[2 + k] + '/' + file) };
      }) });
  });

  window.FKData = {
    CATEGORIES: CATEGORIES, COLLECTIONS: COLLECTIONS, MATERIALS: MATERIALS, COLORS: COLORS, COLOR_CODE: COLOR_CODE, STOCKS: STOCKS, PRICE_BY_GROUP: PRICE_BY_GROUP, TYPES: TYPES,
    PRODUCTS: PRODUCTS, COMPAT: COMPAT, byId: byId, catName: catName, price: price, fmt: fmt, tag: tag, illu: illu,
    kitValid: kitValid, kit: kit, kitIllu: kitIllu, kitImg: kitImg, imgUrl: imgUrl, imgFilter: function (c) { return IMG_FILTER[c] || ''; }, ALL: ALL, variants: variants, dots: dots, PALETTE: PALETTE
  };
  [].forEach.call(document.querySelectorAll('[data-variants]'), function (el) {
    var p = byId(parseInt(el.getAttribute('data-variants'), 10));
    if (p) el.innerHTML = dots(p);
  });
})();
