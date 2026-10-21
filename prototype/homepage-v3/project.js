/* FORKEL — демонстрационная страница проекта (5.2): project.html?id=1…6. Проектов, фотографий, текстов и использованных
   изделий нет — все данные заглушки; связь с коллекциями и товарами не подтверждена, поэтому ссылок на них нет. */
(function () {
  'use strict';
  var root = document.getElementById('prj-root');
  if (!root) return;
  var ART = [{"tone": "tone-graphite", "svg": "<svg viewBox=\"0 0 400 300\" preserveAspectRatio=\"xMidYMid slice\" aria-hidden=\"true\" focusable=\"false\"><use href=\"#room\"/><use href=\"#shade150\" transform=\"translate(250 120) scale(0.6667)\"/><use class=\"fin-white\" href=\"#p-switch\" x=\"250\" y=\"120\" width=\"100\" height=\"100\"/><use href=\"#shade150\" transform=\"translate(150 210) scale(0.4000)\"/><use class=\"fin-white\" href=\"#p-socket\" x=\"150\" y=\"210\" width=\"60\" height=\"60\"/></svg>"}, {"tone": "tone-light", "svg": "<svg viewBox=\"0 0 400 300\" preserveAspectRatio=\"xMidYMid slice\" aria-hidden=\"true\" focusable=\"false\"><use href=\"#room\"/><use href=\"#shade150\" transform=\"translate(165 100) scale(0.4667)\"/><use class=\"fin-black\" href=\"#p-socket\" x=\"165\" y=\"100\" width=\"70\" height=\"70\"/></svg>"}, {"tone": "tone-sand", "svg": "<svg viewBox=\"0 0 400 300\" preserveAspectRatio=\"xMidYMid slice\" aria-hidden=\"true\" focusable=\"false\"><use href=\"#room\"/><use href=\"#shade150\" transform=\"translate(140 92) scale(0.4267)\"/><use class=\"fin-white\" href=\"#p-double\" x=\"140\" y=\"92\" width=\"64\" height=\"64\"/></svg>"}, {"tone": "tone-stone", "svg": "<svg viewBox=\"0 0 400 300\" preserveAspectRatio=\"xMidYMid slice\" aria-hidden=\"true\" focusable=\"false\"><use href=\"#room\"/><use href=\"#shade150\" transform=\"translate(165 100) scale(0.4667)\"/><use class=\"fin-gold\" href=\"#p-socket\" x=\"165\" y=\"100\" width=\"70\" height=\"70\"/></svg>"}, {"tone": "tone-gold", "svg": "<svg viewBox=\"0 0 400 300\" preserveAspectRatio=\"xMidYMid slice\" aria-hidden=\"true\" focusable=\"false\"><use href=\"#room\"/><use href=\"#shade150\" transform=\"translate(140 92) scale(0.4267)\"/><use class=\"fin-cashmere\" href=\"#p-double\" x=\"140\" y=\"92\" width=\"64\" height=\"64\"/></svg>"}, {"tone": "tone-light", "svg": "<svg viewBox=\"0 0 400 300\" preserveAspectRatio=\"xMidYMid slice\" aria-hidden=\"true\" focusable=\"false\"><use href=\"#room\"/><use href=\"#shade150\" transform=\"translate(250 120) scale(0.6667)\"/><use class=\"fin-gray\" href=\"#p-switch\" x=\"250\" y=\"120\" width=\"100\" height=\"100\"/><use href=\"#shade150\" transform=\"translate(150 210) scale(0.4000)\"/><use class=\"fin-gray\" href=\"#p-socket\" x=\"150\" y=\"210\" width=\"60\" height=\"60\"/></svg>"}];
  function param(n) { var m = new RegExp('[?&]' + n + '=([^&]*)').exec(location.search); return m ? decodeURIComponent(m[1]) : ''; }
  var id = parseInt(param('id'), 10);
  if (!(id >= 1 && id <= ART.length)) id = 0;

  function card(i) {
    var a = ART[i - 1];
    return '<li><a class="fk-project-card ' + a.tone + '" href="project.html?id=' + i + '">' +
      '<span class="fk-project-card__media">' + a.svg + '<span class="fk-tag fk-tag--float">Заглушка: фото проекта</span></span>' +
      '<span class="fk-project-card__cap"><h3 class="fk-project-card__name">Демонстрационный проект ' + i + '</h3>' +
      '<span class="fk-project-card__meta">Помещение — заглушка · Коллекция — заглушка</span></span></a></li>';
  }

  if (!id) {
    document.title = 'FORKEL — проект не найден (прототип)';
    document.getElementById('prj-crumb').textContent = 'Проект не найден';
    root.innerHTML = '<div class="fk-empty"><h1 class="fk-page-title" id="prj-title" tabindex="-1">Проект не найден</h1><p>Такого демонстрационного проекта нет.</p>' +
      '<div class="fk-empty__actions"><a class="fk-btn fk-btn--primary" href="projects.html">Все проекты</a></div></div>';
    return;
  }
  var a = ART[id - 1], name = 'Демонстрационный проект ' + id;
  document.title = 'FORKEL — ' + name + ' (прототип)';
  document.getElementById('prj-crumb').textContent = name;
  var others = [], k;
  for (k = 1; k <= ART.length && others.length < 3; k++) { var n = ((id - 1 + k) % ART.length) + 1; if (n !== id) others.push(n); }
  var tiles = [0, 1, 2].map(function (t) {
    return '<li class="fk-prj-tile"><span class="fk-tag">Заглушка: фото ' + (t + 1) + '</span></li>';
  }).join('');
  root.innerHTML =
    '<section class="fk-prj-cover ' + a.tone + '" aria-labelledby="prj-title">' +
      '<div class="fk-prj-cover__media" role="img" aria-label="Схематичная иллюстрация проекта (заглушка, не фото)">' + a.svg + '</div>' +
      '<div class="fk-prj-cover__text"><p class="fk-prj-cover__label">Проект</p>' +
      '<h1 class="fk-page-title" id="prj-title" tabindex="-1">' + name + '</h1>' +
      '<p class="fk-prj-cover__lead">Краткое описание проекта — заглушка. Текст предоставит владелец.</p>' +
      '<p class="fk-note fk-note--proto">Демонстрационные данные: проекта, фотографий, текстов и использованных изделий нет.</p></div>' +
    '</section>' +
    '<div class="fk-prj-body">' +
      '<section class="fk-prj-text" aria-labelledby="prj-about"><h2 id="prj-about">О проекте</h2>' +
        '<p>Основной текст проекта — заглушка: задача, решение, использованные материалы и результат опишет владелец.</p>' +
        '<p><span class="fk-tag">заглушка</span></p></section>' +
      '<aside class="fk-prj-facts" aria-labelledby="prj-facts"><h2 id="prj-facts">Использовано</h2>' +
        '<dl class="fk-spec"><div><dt>Помещение</dt><dd>заглушка</dd></div><div><dt>Коллекция</dt><dd>заглушка</dd></div><div><dt>Изделия</dt><dd>заглушка</dd></div></dl>' +
        '<p class="fk-note">Связь проекта с коллекциями и товарами не подтверждена, ссылок на них нет.</p></aside>' +
    '</div>' +
    '<section class="fk-prj-gallery" aria-labelledby="prj-gal"><h2 id="prj-gal">Галерея</h2><ul class="fk-prj-tiles">' + tiles + '</ul></section>' +
    '<section class="fk-prj-cta" aria-labelledby="prj-cta"><div><h2 id="prj-cta">Нужна консультация?</h2><p>Текст предложения — заглушка. Контакты предоставит владелец.</p></div>' +
      '<a class="fk-btn fk-btn--primary" href="contacts.html">Связаться с нами</a></section>' +
    '<section class="fk-prj-more" aria-labelledby="prj-more"><div class="fk-section-head fk-section-head__row"><h2 id="prj-more">Другие проекты</h2>' +
      '<a class="fk-link fk-link--tap" href="projects.html">Все проекты</a></div><ul class="fk-grid fk-grid--projects-page fk-grid--projects-more">' + others.map(card).join('') + '</ul></section>';
})();
