/* FORKEL V4 — плавающий виджет соцсетей (справа внизу) из V3 app.js. Адреса — заглушки: url пустой → пояснение [data-stub].
   Список общий: window.FKSocial. Идентификаторы MAX-иконки получили суффикс w, чтобы не дублировать id иконки подвала. */
(function () {
  'use strict';
  /* Плавающий виджет соцсетей (справа внизу). Адреса пока заглушки: url пустой → показывается пояснение.
   Список общий: window.FKSocial используется и подвалом. */
  var SOC = [
    { id: 'vk', h: 24, w: 24, name: 'ВКонтакте', url: '', icon: '<img src="assets/images/icons/vk-By-Pixel-perfect.webp" alt="Telegram" width="24" height="24"/>' },
    { id: 'max', h: 100, w: 100, name: 'MAX', url: '', icon: '<img src="assets/images/icons/max_v5.webp" alt="MAX" width="24" height="24"/>' },
    { id: 'tg', h: 24, w: 24, name: 'Telegram', url: '', icon: '<img src="assets/images/icons/telegram-By-Freepik.webp" alt="Telegram" width="24" height="24"/>' }
  ];
  window.FKSocial = SOC;
  function socLink(s, cls) {
    var a = document.createElement('a');
    a.className = cls;
    a.innerHTML = s.icon;
    a.setAttribute('aria-label', s.name + (s.url ? ' (откроется в новой вкладке)' : ''));
    if (s.url) { a.href = s.url; a.target = '_blank'; a.rel = 'noopener noreferrer'; }
    else { a.href = '#'; a.setAttribute('data-stub', 'Ссылка на ' + s.name + ' появится после получения адреса от владельца.'); }
    return a;
  }
  (function () {
    var box = document.createElement('div');
    box.className = 'fk-soc';
    var list = document.createElement('ul');
    list.className = 'fk-soc__list'; list.id = 'fk-soc-list'; list.hidden = true;

    SOC.forEach(function (s) { var li = document.createElement('li'); li.appendChild(socLink(s, 'fk-soc__item')); list.appendChild(li); });

    var toggle = document.createElement('button');
    toggle.type = 'button'; toggle.className = 'fk-soc__btn';
    toggle.setAttribute('aria-expanded', 'false'); toggle.setAttribute('aria-controls', 'fk-soc-list');
    toggle.setAttribute('aria-label', 'Мы в соцсетях');
    toggle.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path class="fk-soc__chat" d="M4 5h16v11H9l-5 4V5z"/><path class="fk-soc__x" d="M6 6l12 12M18 6L6 18"/></svg>';
    box.appendChild(list); box.appendChild(toggle);
    document.body.appendChild(box);

    function set(open, focus) {
      list.hidden = !open;
      toggle.setAttribute('aria-expanded', String(open));
      if (!open && focus) toggle.focus();
    }
    toggle.addEventListener('click', function () { set(list.hidden); });
    document.addEventListener('click', function (e) { if (!list.hidden && !box.contains(e.target)) set(false); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !list.hidden) set(false, true); });
  })();
})();
