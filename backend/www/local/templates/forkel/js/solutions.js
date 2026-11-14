/* FORKEL — «Готовые решения»: слайдер на нативной прокрутке + точки-маркеры с блоком товаров.
   Слайды, точки и всплывающие блоки товаров (ul.fk-hs-pop) приходят из шаблона news.list/fk_home_solutions;
   связь точки и блока — атрибут aria-controls. Логика слайдера и позиционирования — из прототипа V4 (solutions.js). */
(function () {
  'use strict';
  var root = document.getElementById('solutions-slider');
  if (!root) return;
  var track = root.querySelector('.fk-sol__track'), slides = [].slice.call(track.children);
  if (!slides.length) return;
  var prev = document.querySelector('[data-sol-prev]'), next = document.querySelector('[data-sol-next]');
  var dotsBox = document.querySelector('.fk-sol-dots');
  if (!prev || !next || !dotsBox) return;
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
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

  [].forEach.call(root.querySelectorAll('.fk-hs'), function (btn) {
    var pop = document.getElementById(btn.getAttribute('aria-controls'));
    if (!pop) return;
    btn._pop = pop; pop._btn = btn;
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
