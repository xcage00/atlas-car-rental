/* ============================================================
   ATLAS AUTOMOTIVE SERVICES — main.js
   ============================================================ */
(function () {
  'use strict';

  /* ---------- Sticky header shadow ---------- */
  const header = document.getElementById('site-header');
  if (header) {
    const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 4);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  /* ---------- Mobile nav ---------- */
  const toggle = document.getElementById('nav-toggle');
  const nav    = document.getElementById('primary-nav');
  if (toggle && nav) {
    const setOpen = (open) => {
      toggle.setAttribute('aria-expanded', String(open));
      nav.classList.toggle('is-open', open);
      document.body.classList.toggle('nav-open', open);
    };
    toggle.addEventListener('click', () => {
      setOpen(toggle.getAttribute('aria-expanded') !== 'true');
    });
    nav.querySelectorAll('a').forEach(a => a.addEventListener('click', () => setOpen(false)));
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') setOpen(false);
    });
  }

  /* ---------- Mobile filters drawer (Cars page) ---------- */
  const filters      = document.getElementById('filters');
  const filtersOpen  = document.getElementById('filters-open');
  const filtersClose = document.getElementById('filters-close');
  const backdrop     = document.getElementById('filters-backdrop');

  if (filters && filtersOpen && backdrop) {
    const setFilters = (open) => {
      filters.classList.toggle('is-open', open);
      document.body.classList.toggle('filters-locked', open);
      backdrop.hidden = !open;
    };
    filtersOpen.addEventListener('click', () => setFilters(true));
    filtersClose?.addEventListener('click', () => setFilters(false));
    backdrop.addEventListener('click', () => setFilters(false));
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') setFilters(false);
    });
  }

  /* ---------- Chip toggle visual (radio group) ---------- */
  document.querySelectorAll('.chip input[type="radio"]').forEach(input => {
    input.addEventListener('change', () => {
      document.querySelectorAll('.chip').forEach(c => c.classList.remove('is-on'));
      const parent = input.closest('.chip');
      if (parent) parent.classList.add('is-on');
    });
  });

  /* ---------- Scroll reveal ---------- */
  const revealEls = document.querySelectorAll('.reveal');
  if (revealEls.length && 'IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-in');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });
    revealEls.forEach(el => io.observe(el));
  } else {
    revealEls.forEach(el => el.classList.add('is-in'));
  }
    /* ---------- Car detail gallery ---------- */
  const galleryMain = document.getElementById('gallery-main-img');
  const galleryData = document.getElementById('gallery-data');
  const thumbs      = document.querySelectorAll('.gallery-thumb');

  if (galleryMain && galleryData && thumbs.length) {
    let images = [];
    try { images = JSON.parse(galleryData.textContent); } catch (e) { images = []; }
    let idx = 0;

    const show = (i) => {
      if (!images.length) return;
      idx = (i + images.length) % images.length;
      galleryMain.src = images[idx];
      thumbs.forEach((t, n) => {
        t.classList.toggle('is-active', n === idx);
        t.setAttribute('aria-selected', n === idx ? 'true' : 'false');
      });
    };

    thumbs.forEach(t => {
      t.addEventListener('click', () => show(parseInt(t.dataset.index, 10)));
    });

    document.querySelector('.gallery-prev')?.addEventListener('click', () => show(idx - 1));
    document.querySelector('.gallery-next')?.addEventListener('click', () => show(idx + 1));

    document.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft')  show(idx - 1);
      if (e.key === 'ArrowRight') show(idx + 1);
    });
  }
})();