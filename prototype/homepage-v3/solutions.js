/* FORKEL — «Готовые решения» (прототип): одна точка-маркер, блок из двух демо-товаров (из data.js, помечены «демо»). */
(function () {
  'use strict';
  var D = window.FKData, stage = document.getElementById('solutions-stage');
  if (!D || !stage) return;
  var btn = stage.querySelector('.fk-hs'), pop = document.getElementById('hs-pop');
  var IDS = [1, 2], timer = 0, HEART = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20s-7-4.4-7-10a4 4 0 017-2.6A4 4 0 0119 10c0 5.6-7 10-7 10z"/></svg>';

  pop.innerHTML = IDS.map(function (id) {
    var p = D.byId(id); if (!p) return '';
    var url = 'product.html?id=' + p.id, on = !!(window.FKFav && window.FKFav.has(p.id));
    return '<li class="fk-mini">' +
      '<a class="fk-mini__media tone-' + p.tone + '" href="' + url + '" tabindex="-1" aria-hidden="true">' + D.illu(p) + '</a>' +
      '<button class="fk-fav-btn" type="button" data-fav="' + p.id + '" aria-pressed="' + on + '" aria-label="В избранное — ' + p.name + '">' + HEART + '</button>' +
      '<p class="fk-mini__series">Серия ' + p.col + '</p>' +
      '<h3 class="fk-mini__name"><a href="' + url + '" title="' + p.name + '">' + p.name + '</a></h3>' +
      '<p class="fk-mini__price">' + D.fmt(D.price(p)) + ' ' + D.tag('демо') + '</p>' +
      '<a class="fk-btn fk-btn--secondary" href="' + url + '">К товару<span class="fk-vh"> — ' + p.name + '</span></a>' +
      '</li>';
  }).join('');

  function place() {
    var sw = stage.clientWidth, sh = stage.clientHeight, w = pop.offsetWidth, h = pop.offsetHeight;
    var cx = btn.offsetLeft, cy = btn.offsetTop + 24, left, top;
    cx += 24;
    function clamp(v, a, b) { return Math.max(a, Math.min(v, b)); }
    if (sw < w + 96) { /* узкий экран: блок под точкой или над ней */
      left = clamp(cx - w / 2, 8, sw - w - 8);
      top = cy + 36 + h <= sh - 8 ? cy + 36 : cy - 36 - h;
    } else {
      left = cx + 36 + w <= sw - 8 ? cx + 36 : cx - 36 - w;
      top = cy - h / 2;
    }
    pop.style.left = left + 'px'; pop.style.top = clamp(top, 8, sh - h - 8) + 'px';
  }
  function set(open) {
    clearTimeout(timer);
    if (open === !pop.hidden) return;
    pop.hidden = !open; btn.setAttribute('aria-expanded', String(open));
    if (open) place();
  }
  function later() { clearTimeout(timer); timer = setTimeout(function () { set(false); }, 250); }

  btn.addEventListener('click', function () { set(pop.hidden); });
  [btn, pop].forEach(function (el) {
    el.addEventListener('pointerenter', function (e) { if (e.pointerType === 'mouse') set(true); });
    el.addEventListener('pointerleave', function (e) { if (e.pointerType === 'mouse') later(); });
  });
  stage.addEventListener('focusout', function (e) { if (!pop.hidden && !stage.contains(e.relatedTarget)) later(); });
  document.addEventListener('click', function (e) { if (!pop.hidden && !stage.contains(e.target)) set(false); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !pop.hidden) { set(false); btn.focus(); } });
  window.addEventListener('resize', function () { if (!pop.hidden) place(); });
})();
