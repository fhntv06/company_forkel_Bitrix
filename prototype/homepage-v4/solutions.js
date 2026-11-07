/* FORKEL — «Готовые решения» (прототип): слайдер на нативной прокрутке + точки-маркеры с блоком демо-товаров.
   Товары берутся из data.js (помечены «демо»); ids заданы атрибутом data-ids у точки. */
(function () {
  'use strict';
  var D = window.FKData, root = document.getElementById('solutions-slider');
  if (!D || !root) return;
  var track = root.querySelector('.fk-sol__track'), slides = [].slice.call(track.children);
  var prev = document.querySelector('[data-sol-prev]'), next = document.querySelector('[data-sol-next]');
  var dotsBox = document.querySelector('.fk-sol-dots');
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  var HEART = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20s-7-4.4-7-10a4 4 0 017-2.6A4 4 0 0119 10c0 5.6-7 10-7 10z"/></svg>';
  var openBtn = null, timer = 0;

  /* ---------- слайдер ---------- */
  var dots = slides.map(function (s, i) {
    var b = document.createElement('button');
    b.type = 'button'; b.className = 'fk-sol-dot'; b.setAttribute('aria-label', 'Слайд ' + (i + 1) + ' из ' + slides.length);
    b.addEventListener('click', function () { goTo(i); });
    dotsBox.appendChild(b); return b;
  });
  function goTo(i) { closePop(); track.scrollTo({ left: slides[i].offsetLeft - track.offsetLeft, behavior: reduce ? 'auto' : 'smooth' }); }
  function current() {
    var max = track.scrollWidth - track.clientWidth;
    if (track.scrollLeft >= max - 2) return slides.length - 1;
    var best = 0, d = Infinity;
    slides.forEach(function (s, i) { var x = Math.abs(s.offsetLeft - track.offsetLeft - track.scrollLeft); if (x < d) { d = x; best = i; } });
    return best;
  }
  function sync() {
    var i = current(), max = track.scrollWidth - track.clientWidth;
    dots.forEach(function (b, k) { b.setAttribute('aria-current', k === i ? 'true' : 'false'); });
    prev.disabled = track.scrollLeft <= 2; next.disabled = track.scrollLeft >= max - 2;
  }
  prev.addEventListener('click', function () { goTo(Math.max(0, current() - 1)); });
  next.addEventListener('click', function () { goTo(Math.min(slides.length - 1, current() + 1)); });
  track.addEventListener('keydown', function (e) {
    if (e.target !== track) return;
    if (e.key === 'ArrowRight') { e.preventDefault(); goTo(Math.min(slides.length - 1, current() + 1)); }
    if (e.key === 'ArrowLeft') { e.preventDefault(); goTo(Math.max(0, current() - 1)); }
  });
  var raf = 0;
  track.addEventListener('scroll', function () { closePop(); cancelAnimationFrame(raf); raf = requestAnimationFrame(sync); }, { passive: true });
  window.addEventListener('resize', function () { closePop(); sync(); });

  /* ---------- точки и блок товаров ---------- */
  function build(btn, n) {
    var pop = document.createElement('ul');
    pop.className = 'fk-hs-pop'; pop.hidden = true; pop.id = 'hs-pop-' + n;
    pop.setAttribute('aria-label', 'Товары из этого элемента');
    pop.innerHTML = btn.getAttribute('data-ids').split(',').map(function (s) {
      var p = D.byId(parseInt(s, 10)); if (!p) return '';
      var url = 'product.html?id=' + p.id, on = !!(window.FKFav && window.FKFav.has(p.id));
      return '<li class="fk-mini">' +
        '<a class="fk-mini__media tone-' + p.tone + '" href="' + url + '" tabindex="-1" aria-hidden="true">' + D.illu(p) + '</a>' +
        '<button class="fk-fav-btn" type="button" data-fav="' + p.id + '" aria-pressed="' + on + '" aria-label="В избранное — ' + p.name + '">' + HEART + '</button>' +
        '<p class="fk-mini__series">Серия ' + p.col + '</p>' +
        '<h3 class="fk-mini__name"><a href="' + url + '" title="' + p.name + '">' + p.name + '</a></h3>' +
        '<p class="fk-mini__price">' + D.fmt(D.price(p)) + ' ' + '</p>' +
        '<a class="fk-btn fk-btn--secondary" href="' + url + '">К товару<span class="fk-vh"> — ' + p.name + '</span></a>' +
        '</li>';
    }).join('');
    btn.setAttribute('aria-controls', pop.id);
    btn.closest('.fk-sol__slide').appendChild(pop);
    btn._pop = pop; pop._btn = btn;
    return pop;
  }
  function place(btn) {
    var slide = btn.closest('.fk-sol__slide'), pop = btn._pop;
    var sr = slide.getBoundingClientRect(), br = btn.getBoundingClientRect();
    var sw = slide.clientWidth, sh = slide.clientHeight, w = pop.offsetWidth, h = pop.offsetHeight;
    var cx = br.left - sr.left + br.width / 2, cy = br.top - sr.top + br.height / 2, left, top;
    function clamp(v, a, b) { return Math.max(a, Math.min(v, Math.max(a, b))); }
    pop.style.maxWidth = '';
    var spR = sw - 8 - (cx + 36), spL = cx - 36 - 8;
    if (w <= spR) { left = cx + 36; top = cy - h / 2; }                          /* справа от точки */
    else if (w <= spL) { left = cx - 36 - w; top = cy - h / 2; }                 /* слева */
    else if (Math.max(spR, spL) >= 200) {                                        /* сбоку, но уже: блок прокручивается */
      pop.style.maxWidth = Math.max(spR, spL) + 'px'; w = pop.offsetWidth;
      left = spR >= spL ? cx + 36 : cx - 36 - w; top = cy - h / 2;
    } else {                                                                     /* узкий слайд: под точкой или над ней */
      left = clamp(cx - w / 2, 8, sw - w - 8);
      top = cy + 36 + h <= sh - 8 ? cy + 36 : cy - 36 - h;
    }
    pop.style.left = left + 'px'; pop.style.top = clamp(top, 8, sh - h - 8) + 'px';
  }
  function closePop() {
    clearTimeout(timer);
    if (!openBtn) return;
    openBtn._pop.hidden = true; openBtn.setAttribute('aria-expanded', 'false'); openBtn = null;
  }
  function open(btn, byHover) {
    clearTimeout(timer);
    if (openBtn === btn) return;
    btn._hover = !!byHover;
    closePop();
    btn._pop.hidden = false; btn.setAttribute('aria-expanded', 'true'); openBtn = btn; place(btn);
  }
  function later() { clearTimeout(timer); timer = setTimeout(closePop, 250); }

  [].forEach.call(root.querySelectorAll('.fk-hs'), function (btn, n) {
    var pop = build(btn, n);
    /* наведение открывает блок; клик по уже открытому наведением — закрепляет его, следующий клик закрывает */
    btn.addEventListener('click', function () {
      if (openBtn !== btn) open(btn, false);
      else if (btn._hover) btn._hover = false;
      else closePop();
    });
    [btn, pop].forEach(function (el) {
      el.addEventListener('pointerenter', function (e) { if (e.pointerType === 'mouse') open(btn, true); });
      el.addEventListener('pointerleave', function (e) { if (e.pointerType === 'mouse' && btn._hover) later(); });
    });
    pop.addEventListener('focusout', function (e) { if (!pop.contains(e.relatedTarget) && e.relatedTarget !== btn) later(); });
  });
  document.addEventListener('click', function (e) { if (openBtn && !openBtn._pop.contains(e.target) && !openBtn.contains(e.target)) closePop(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && openBtn) { var b = openBtn; closePop(); b.focus(); } });
  sync();
})();
