/**
 * assets/js/main.js
 * SD Muhammadiyah 1 Gentasari — Main JavaScript
 * ─────────────────────────────────────────────────────────────────────────────
 * Handles: Navbar, Mobile Menu, Scroll Reveal, Counter Animation,
 *          Gallery Lightbox, Form Helpers, Utility Functions
 */

'use strict';

/* ══════════════════════════════════════════════════════════
   1. NAVBAR — Scroll opacity & sticky effect
══════════════════════════════════════════════════════════ */
(function initNavbar() {
  const inner = document.getElementById('navbar-inner');
  if (!inner) return;

  let lastScroll = 0;

  window.addEventListener('scroll', () => {
    const scrollY = window.scrollY;

    // Frosted glass on scroll
    if (scrollY > 40) {
      inner.style.background       = 'rgba(0,0,0,0.88)';
      inner.style.backdropFilter   = 'blur(24px) saturate(2)';
      inner.style.webkitBackdropFilter = 'blur(24px) saturate(2)';
    } else {
      inner.style.background       = '';
      inner.style.backdropFilter   = '';
      inner.style.webkitBackdropFilter = '';
    }

    lastScroll = scrollY;
  }, { passive: true });
})();

/* ══════════════════════════════════════════════════════════
   2. MOBILE MENU — Slide in/out
══════════════════════════════════════════════════════════ */
(function initMobileMenu() {
  const hamburger   = document.getElementById('hamburger');
  const closeBtn    = document.getElementById('close-menu');
  const mobileMenu  = document.getElementById('mobile-menu');
  const overlay     = document.getElementById('menu-overlay');

  if (!hamburger || !mobileMenu) return;

  function openMenu() {
    mobileMenu.classList.add('open');
    if (overlay) overlay.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    // Re-init icons inside menu
    if (window.lucide) window.lucide.createIcons();
  }

  function closeMenu() {
    mobileMenu.classList.remove('open');
    if (overlay) overlay.classList.add('hidden');
    document.body.style.overflow = '';
  }

  hamburger.addEventListener('click', openMenu);
  if (closeBtn) closeBtn.addEventListener('click', closeMenu);
  if (overlay)  overlay.addEventListener('click', closeMenu);

  // Close on Escape
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeMenu();
  });
})();

/* ══════════════════════════════════════════════════════════
   3. SCROLL REVEAL — IntersectionObserver
══════════════════════════════════════════════════════════ */
(function initScrollReveal() {
  const els = document.querySelectorAll('.reveal, .reveal-stagger');
  if (!els.length) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, {
    threshold:  0.1,
    rootMargin: '0px 0px -40px 0px',
  });

  els.forEach(el => observer.observe(el));
})();

/* ══════════════════════════════════════════════════════════
   4. COUNTER ANIMATION — Animate numbers on scroll
══════════════════════════════════════════════════════════ */
(function initCounters() {
  const counters = document.querySelectorAll('[data-counter]');
  if (!counters.length) return;

  function animateCounter(el, target, suffix = '') {
    const duration = 1800;
    const start    = performance.now();
    const from     = 0;

    function step(now) {
      const elapsed  = now - start;
      const progress = Math.min(elapsed / duration, 1);
      // Ease out cubic
      const eased    = 1 - Math.pow(1 - progress, 3);
      const current  = Math.round(from + (target - from) * eased);

      el.textContent = current.toLocaleString('id-ID') + suffix;

      if (progress < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const el     = entry.target;
      const target = parseInt(el.dataset.counter, 10) || 0;
      const suffix = el.dataset.suffix || '';
      animateCounter(el, target, suffix);
      observer.unobserve(el);
    });
  }, { threshold: 0.5 });

  counters.forEach(el => observer.observe(el));
})();

/* ══════════════════════════════════════════════════════════
   5. GALLERY LIGHTBOX
══════════════════════════════════════════════════════════ */
(function initLightbox() {
  const lb = document.getElementById('lightbox');
  if (!lb) return;

  let items = [], currentIdx = 0;

  // Gather all gallery items on page load
  function refreshItems() {
    items = Array.from(document.querySelectorAll('.gallery-item[data-src]'));
  }

  // Open lightbox
  window.openLightbox = function(el) {
    refreshItems();
    currentIdx = items.indexOf(el);
    render();
    lb.classList.add('open');
    document.body.style.overflow = 'hidden';
  };

  window.closeLightbox = function() {
    lb.classList.remove('open');
    document.body.style.overflow = '';
  };

  window.navLb = function(dir) {
    if (!items.length) return;
    currentIdx = (currentIdx + dir + items.length) % items.length;
    render();
  };

  function render() {
    const el      = items[currentIdx];
    if (!el) return;
    const img     = document.getElementById('lb-img');
    const title   = document.getElementById('lb-title');
    const caption = document.getElementById('lb-caption');
    const counter = document.getElementById('lb-counter');

    if (img)     img.src          = el.dataset.src   || '';
    if (title)   title.textContent  = el.dataset.title   || '';
    if (caption) caption.textContent= el.dataset.caption || '';
    if (counter) counter.textContent= `${currentIdx + 1} / ${items.length}`;
  }

  // Close on backdrop click
  lb.addEventListener('click', e => {
    if (e.target === lb) window.closeLightbox();
  });

  // Keyboard nav
  document.addEventListener('keydown', e => {
    if (!lb.classList.contains('open')) return;
    if (e.key === 'Escape')      window.closeLightbox();
    if (e.key === 'ArrowLeft')   window.navLb(-1);
    if (e.key === 'ArrowRight')  window.navLb(1);
  });

  // Touch swipe
  let touchStartX = null;
  lb.addEventListener('touchstart', e => {
    touchStartX = e.touches[0].clientX;
  }, { passive: true });
  lb.addEventListener('touchend', e => {
    if (touchStartX === null) return;
    const delta = e.changedTouches[0].clientX - touchStartX;
    if (Math.abs(delta) > 50) window.navLb(delta < 0 ? 1 : -1);
    touchStartX = null;
  });
})();

/* ══════════════════════════════════════════════════════════
   6. FORM ENHANCEMENTS
══════════════════════════════════════════════════════════ */
(function initForms() {
  // Auto-resize textareas
  document.querySelectorAll('textarea.auto-resize').forEach(el => {
    function resize() {
      el.style.height = 'auto';
      el.style.height = el.scrollHeight + 'px';
    }
    el.addEventListener('input', resize);
    resize();
  });

  // Character counter
  document.querySelectorAll('[data-maxlength]').forEach(el => {
    const max     = parseInt(el.dataset.maxlength, 10);
    const countEl = document.querySelector(`[data-count-for="${el.id}"]`);
    if (!countEl) return;
    function update() {
      const remaining = max - el.value.length;
      countEl.textContent = `${remaining} karakter tersisa`;
      countEl.style.color = remaining < 20 ? '#f87171' : 'rgba(255,255,255,.3)';
    }
    el.addEventListener('input', update);
    update();
  });

  // Float label animation
  document.querySelectorAll('.input-float').forEach(wrap => {
    const input = wrap.querySelector('input, textarea, select');
    const label = wrap.querySelector('label');
    if (!input || !label) return;
    function check() {
      wrap.classList.toggle('has-value', !!input.value);
    }
    input.addEventListener('focus',  () => wrap.classList.add('focused'));
    input.addEventListener('blur',   () => { wrap.classList.remove('focused'); check(); });
    input.addEventListener('input',  check);
    check();
  });
})();

/* ══════════════════════════════════════════════════════════
   7. ANNOUNCEMENT — "Terbaru" live badge timer
══════════════════════════════════════════════════════════ */
(function initNewBadges() {
  // Badges are rendered server-side; this just ensures animation is applied
  document.querySelectorAll('.badge-new').forEach(el => {
    el.setAttribute('title', 'Diterbitkan dalam 7 hari terakhir');
  });
})();

/* ══════════════════════════════════════════════════════════
   8. SMOOTH ANCHOR SCROLL
══════════════════════════════════════════════════════════ */
(function initSmoothScroll() {
  document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', e => {
      const id = link.getAttribute('href').slice(1);
      const el = document.getElementById(id);
      if (!el) return;
      e.preventDefault();
      const offset = 90; // navbar height
      const top    = el.getBoundingClientRect().top + window.scrollY - offset;
      window.scrollTo({ top, behavior: 'smooth' });
    });
  });
})();

/* ══════════════════════════════════════════════════════════
   9. COPY TO CLIPBOARD utility
══════════════════════════════════════════════════════════ */
window.copyToClipboard = function(text, feedbackEl) {
  navigator.clipboard.writeText(text).then(() => {
    if (feedbackEl) {
      const original = feedbackEl.textContent;
      feedbackEl.textContent = 'Tersalin!';
      feedbackEl.style.color = '#6ee7b7';
      setTimeout(() => {
        feedbackEl.textContent = original;
        feedbackEl.style.color = '';
      }, 2000);
    }
  }).catch(() => {
    // Fallback
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.cssText = 'position:fixed;opacity:0;top:0;left:0';
    document.body.appendChild(ta);
    ta.select();
    document.execCommand('copy');
    document.body.removeChild(ta);
  });
};

/* ══════════════════════════════════════════════════════════
   10. TOAST HELPER (global, usable outside SweetAlert pages)
══════════════════════════════════════════════════════════ */
window.showToast = function(icon, title, timer = 3000) {
  if (window.Swal) {
    Swal.fire({
      toast: true, position: 'top-end',
      icon, title,
      showConfirmButton: false,
      timer, timerProgressBar: true,
      background: 'rgba(15,15,15,0.97)',
      color: '#fff',
      customClass: { popup: 'rounded-2xl' },
    });
  } else {
    console.info(`[Toast] ${icon}: ${title}`);
  }
};

/* ══════════════════════════════════════════════════════════
   11. LAZY IMAGE LOADING (fallback for older browsers)
══════════════════════════════════════════════════════════ */
(function initLazyImages() {
  if ('loading' in HTMLImageElement.prototype) return; // native support

  const imgs = document.querySelectorAll('img[loading="lazy"]');
  const observer = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        const img = e.target;
        if (img.dataset.src) img.src = img.dataset.src;
        observer.unobserve(img);
      }
    });
  });
  imgs.forEach(img => observer.observe(img));
})();

/* ══════════════════════════════════════════════════════════
   12. INIT on DOMContentLoaded
══════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
  // Init Lucide icons if available
  if (window.lucide) window.lucide.createIcons();

  // Active nav link highlight (based on current path)
  const path = window.location.pathname;
  document.querySelectorAll('.nav-link').forEach(link => {
    const href = link.getAttribute('href') || '';
    if (href && path.endsWith(href.split('/').pop())) {
      link.classList.add('active');
    }
  });
});

/* ══════════════════════════════════════════════════════════
   13. DEBOUNCE / THROTTLE utilities
══════════════════════════════════════════════════════════ */
window.debounce = function(fn, delay = 300) {
  let timer;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => fn.apply(this, args), delay);
  };
};

window.throttle = function(fn, limit = 100) {
  let inThrottle;
  return (...args) => {
    if (!inThrottle) {
      fn.apply(this, args);
      inThrottle = true;
      setTimeout(() => inThrottle = false, limit);
    }
  };
};
