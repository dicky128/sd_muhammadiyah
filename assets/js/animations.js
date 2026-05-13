/**
 * assets/js/animations.js
 * ─────────────────────────────────────────────────────────────────────────────
 * SD Muhammadiyah 1 Gentasari
 * Page Transitions, Micro-interactions & UI Polish
 * Light Mode | Pink × Gold × Sky Blue
 * ─────────────────────────────────────────────────────────────────────────────
 */

'use strict';

const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* ══════════════════════════════════════════════════════════
   1. PAGE LOADER
══════════════════════════════════════════════════════════ */
(function initLoader() {
  const loader = document.createElement('div');
  loader.id = 'page-loader';
  Object.assign(loader.style, {
    position: 'fixed', inset: '0', zIndex: '99999',
    background: 'linear-gradient(135deg, #fdf2f8 0%, #fef9e7 50%, #f0f9ff 100%)',
    display: 'flex', alignItems: 'center', justifyContent: 'center',
    transition: 'opacity 0.5s ease, visibility 0.5s ease',
  });

  // Animated logo
  loader.innerHTML = `
    <div style="text-align:center">
      <div id="loader-ring" style="
        width:60px;height:60px;border-radius:50%;
        border:3px solid rgba(244,114,182,0.2);
        border-top-color:#f472b6;
        animation:loaderSpin .8s linear infinite;
        margin:0 auto 16px;
      "></div>
      <p style="font-family:'Playfair Display',serif;font-size:1rem;color:#be185d;font-weight:600;letter-spacing:.05em">
        SD Muhammadiyah 1
      </p>
      <p style="font-size:.65rem;color:#9ca3af;letter-spacing:.15em;text-transform:uppercase;margin-top:4px">
        Gentasari · Cilacap
      </p>
    </div>`;

  const style = document.createElement('style');
  style.textContent = '@keyframes loaderSpin{to{transform:rotate(360deg)}}';
  document.head.appendChild(style);
  document.body.appendChild(loader);

  window.addEventListener('load', () => {
    setTimeout(() => {
      loader.style.opacity = '0';
      loader.style.visibility = 'hidden';
      setTimeout(() => loader.remove(), 500);
    }, prefersReduced ? 0 : 300);
  });
})();

/* ══════════════════════════════════════════════════════════
   2. PAGE TRANSITION OVERLAY
══════════════════════════════════════════════════════════ */
(function initPageTransition() {
  if (prefersReduced) return;

  const overlay = document.createElement('div');
  overlay.id = 'page-transition';
  Object.assign(overlay.style, {
    position: 'fixed', inset: '0', zIndex: '99998',
    background: 'linear-gradient(135deg, #fdf2f8, #fef9e7)',
    transform: 'translateY(100%)',
    transition: 'transform 0.45s cubic-bezier(0.76, 0, 0.24, 1)',
    pointerEvents: 'none',
  });
  document.body.appendChild(overlay);

  // Intercept internal links
  document.addEventListener('click', e => {
    const link = e.target.closest('a[href]');
    if (!link) return;
    const href = link.getAttribute('href');

    // Skip: external, anchor, admin, download, target=_blank, mailto, tel
    if (!href
      || href.startsWith('#')
      || href.startsWith('http')
      || href.startsWith('//')
      || href.startsWith('mailto:')
      || href.startsWith('tel:')
      || href.includes('admin/')
      || link.target === '_blank'
      || link.download
    ) return;

    e.preventDefault();
    overlay.style.transform = 'translateY(0)';
    setTimeout(() => { window.location.href = href; }, 450);
  });

  // Slide out on page show (back/forward cache)
  window.addEventListener('pageshow', () => {
    overlay.style.transition = 'none';
    overlay.style.transform  = 'translateY(0)';
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        overlay.style.transition = 'transform 0.45s cubic-bezier(0.76, 0, 0.24, 1)';
        overlay.style.transform  = 'translateY(-100%)';
      });
    });
  });
})();

/* ══════════════════════════════════════════════════════════
   3. NAVBAR ACTIVE LINK HIGHLIGHT
══════════════════════════════════════════════════════════ */
(function initActiveNav() {
  const path = window.location.pathname;
  document.querySelectorAll('.nav-link-light').forEach(link => {
    const href = link.getAttribute('href') || '';
    if (!href || href === '#') return;
    // Extract filename for comparison
    const linkFile = href.split('/').pop().split('?')[0];
    const pathFile = path.split('/').pop().split('?')[0];
    if (linkFile && pathFile && linkFile === pathFile) {
      link.classList.add('active');
    }
  });
})();

/* ══════════════════════════════════════════════════════════
   4. SMOOTH SECTION SCROLL with active indicator
══════════════════════════════════════════════════════════ */
(function initSmoothScroll() {
  document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', e => {
      const id = link.getAttribute('href').slice(1);
      const el = document.getElementById(id);
      if (!el) return;
      e.preventDefault();
      const offset = 90;
      const top    = el.getBoundingClientRect().top + window.scrollY - offset;
      window.scrollTo({ top, behavior: prefersReduced ? 'instant' : 'smooth' });
    });
  });
})();

/* ══════════════════════════════════════════════════════════
   5. INTERSECTION OBSERVER for .reveal-* classes
   (fallback if GSAP/scroll3d not loaded)
══════════════════════════════════════════════════════════ */
(function initReveal() {
  const els = document.querySelectorAll('.reveal-3d, .reveal-fade, .reveal-left, .reveal-right, .reveal-heading');
  if (!els.length) return;

  const obs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const el    = entry.target;
      const delay = parseFloat(el.style.animationDelay || el.dataset.delay || '0') * 1000;
      setTimeout(() => {
        el.classList.add('visible');
        obs.unobserve(el);
      }, prefersReduced ? 0 : delay);
    });
  }, { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });

  els.forEach(el => obs.observe(el));
})();

/* ══════════════════════════════════════════════════════════
   6. STAGGER GRID OBSERVER
══════════════════════════════════════════════════════════ */
(function initStaggerGrids() {
  if (prefersReduced) {
    document.querySelectorAll('.stagger-grid').forEach(g => {
      g.querySelectorAll(':scope > *').forEach(c => c.classList.add('visible'));
    });
    return;
  }

  const obs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const children = entry.target.querySelectorAll(':scope > *');
      children.forEach((child, i) => {
        setTimeout(() => child.classList.add('visible'), i * 90);
      });
      obs.unobserve(entry.target);
    });
  }, { threshold: 0.05 });

  document.querySelectorAll('.stagger-grid').forEach(g => {
    g.querySelectorAll(':scope > *').forEach(c => {
      if (!c.classList.contains('visible')) {
        c.style.opacity    = '0';
        c.style.transform  = 'translateY(28px) scale(0.97)';
        c.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
      }
    });
    obs.observe(g);
  });

  // Make visible callback
  const style = document.createElement('style');
  style.textContent = `.stagger-grid > *.visible{opacity:1!important;transform:none!important}`;
  document.head.appendChild(style);
})();

/* ══════════════════════════════════════════════════════════
   7. ANIMATED COUNTER (fallback, no GSAP needed)
══════════════════════════════════════════════════════════ */
(function initCounters() {
  if (prefersReduced) {
    document.querySelectorAll('[data-count]').forEach(el => {
      el.textContent = parseInt(el.dataset.count, 10).toLocaleString('id-ID');
    });
    return;
  }

  const obs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const el     = entry.target;
      const target = parseInt(el.dataset.count, 10) || 0;
      const dur    = 1800;
      const start  = performance.now();

      function step(now) {
        const p = Math.min((now - start) / dur, 1);
        const v = Math.round(target * (1 - Math.pow(1 - p, 3)));
        el.textContent = v.toLocaleString('id-ID');
        if (p < 1) requestAnimationFrame(step);
      }
      requestAnimationFrame(step);
      obs.unobserve(el);
    });
  }, { threshold: 0.5 });

  document.querySelectorAll('[data-count]').forEach(el => obs.observe(el));
})();

/* ══════════════════════════════════════════════════════════
   8. TILT CARD EFFECT (CSS-only fallback + JS enhancement)
══════════════════════════════════════════════════════════ */
(function initTilt() {
  if (prefersReduced || window.innerWidth < 768) return;

  document.querySelectorAll('.tilt-card').forEach(card => {
    const inner = card.querySelector('.tilt-inner') || card;
    const shine = card.querySelector('.tilt-shine');

    card.addEventListener('mousemove', e => {
      const rect = card.getBoundingClientRect();
      const cx   = rect.left + rect.width  / 2;
      const cy   = rect.top  + rect.height / 2;
      const rx   = ((e.clientY - cy) / (rect.height / 2)) * -10;
      const ry   = ((e.clientX - cx) / (rect.width  / 2)) *  10;
      inner.style.transform  = `perspective(900px) rotateX(${rx}deg) rotateY(${ry}deg) scale3d(1.02,1.02,1.02)`;
      inner.style.transition = 'transform 0.08s ease-out';
      if (shine) {
        shine.style.background = `radial-gradient(circle at ${e.clientX - rect.left}px ${e.clientY - rect.top}px, rgba(255,255,255,0.3) 0%, transparent 60%)`;
      }
    }, { passive: true });

    card.addEventListener('mouseleave', () => {
      inner.style.transform  = 'perspective(900px) rotateX(0deg) rotateY(0deg) scale3d(1,1,1)';
      inner.style.transition = 'transform 0.5s ease';
      if (shine) shine.style.background = 'transparent';
    });
  });
})();

/* ══════════════════════════════════════════════════════════
   9. BUTTON RIPPLE EFFECT
══════════════════════════════════════════════════════════ */
(function initRipple() {
  if (prefersReduced) return;

  document.querySelectorAll('.btn-primary-light, .btn-outline-light').forEach(btn => {
    btn.style.position = 'relative';
    btn.style.overflow = 'hidden';

    btn.addEventListener('click', e => {
      const rect   = btn.getBoundingClientRect();
      const size   = Math.max(rect.width, rect.height) * 2;
      const ripple = document.createElement('span');

      Object.assign(ripple.style, {
        position: 'absolute',
        width:    size + 'px',
        height:   size + 'px',
        left:     (e.clientX - rect.left - size / 2) + 'px',
        top:      (e.clientY - rect.top  - size / 2) + 'px',
        borderRadius: '50%',
        background: 'rgba(255,255,255,0.35)',
        transform: 'scale(0)',
        animation: 'ripple 0.6s ease-out forwards',
        pointerEvents: 'none',
      });

      btn.appendChild(ripple);
      setTimeout(() => ripple.remove(), 650);
    });
  });

  const style = document.createElement('style');
  style.textContent = '@keyframes ripple{to{transform:scale(1);opacity:0}}';
  document.head.appendChild(style);
})();

/* ══════════════════════════════════════════════════════════
   10. FORM VALIDATION FEEDBACK (light mode)
══════════════════════════════════════════════════════════ */
(function initFormValidation() {
  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', e => {
      let firstError = null;

      form.querySelectorAll('input[required], textarea[required], select[required]').forEach(field => {
        const wrapper = field.closest('.field-wrap') || field.parentElement;
        const errEl   = wrapper?.querySelector('.field-error');

        field.style.borderColor = '';
        if (errEl) errEl.remove();

        if (!field.value.trim()) {
          field.style.borderColor = '#f472b6';
          field.style.boxShadow   = '0 0 0 3px rgba(244,114,182,0.15)';

          const err = document.createElement('p');
          err.className = 'field-error';
          err.style.cssText = 'font-size:.72rem;color:#be185d;margin-top:4px;font-weight:600';
          err.textContent = 'Field ini wajib diisi.';
          field.parentElement.appendChild(err);

          if (!firstError) firstError = field;
        } else {
          field.style.borderColor = '';
          field.style.boxShadow   = '';
        }
      });

      if (firstError) {
        e.preventDefault();
        firstError.focus();
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    });

    // Clear error on type
    form.querySelectorAll('input, textarea, select').forEach(field => {
      field.addEventListener('input', () => {
        if (field.value.trim()) {
          field.style.borderColor = '';
          field.style.boxShadow   = '';
          const err = field.parentElement.querySelector('.field-error');
          if (err) err.remove();
        }
      });
    });
  });
})();

/* ══════════════════════════════════════════════════════════
   11. IMAGE LAZY-LOAD with blur-up effect
══════════════════════════════════════════════════════════ */
(function initLazyBlurUp() {
  const imgs = document.querySelectorAll('img[loading="lazy"]');

  imgs.forEach(img => {
    img.style.filter     = 'blur(8px)';
    img.style.transition = 'filter 0.5s ease';
    img.addEventListener('load', () => {
      img.style.filter = 'blur(0)';
    }, { once: true });
    // If already cached and loaded
    if (img.complete && img.naturalWidth) img.style.filter = 'blur(0)';
  });
})();

/* ══════════════════════════════════════════════════════════
   12. BACK TO TOP BUTTON
══════════════════════════════════════════════════════════ */
(function initBackToTop() {
  const btn = document.createElement('button');
  btn.id    = 'back-to-top';
  btn.setAttribute('aria-label', 'Kembali ke atas');
  btn.innerHTML = '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>';

  Object.assign(btn.style, {
    position: 'fixed', bottom: '24px', right: '24px',
    width: '46px', height: '46px',
    borderRadius: '14px',
    background: 'linear-gradient(135deg, #f472b6, #d4aa3a)',
    color: '#fff', border: 'none', cursor: 'pointer',
    display: 'flex', alignItems: 'center', justifyContent: 'center',
    zIndex: '9990',
    opacity: '0', visibility: 'hidden',
    transition: 'opacity .3s ease, visibility .3s ease, transform .3s ease',
    boxShadow: '0 8px 24px rgba(244,114,182,.4)',
  });

  document.body.appendChild(btn);

  window.addEventListener('scroll', () => {
    const visible = window.scrollY > 400;
    btn.style.opacity    = visible ? '1' : '0';
    btn.style.visibility = visible ? 'visible' : 'hidden';
  }, { passive: true });

  btn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: prefersReduced ? 'instant' : 'smooth' });
  });

  btn.addEventListener('mouseenter', () => {
    btn.style.transform = 'translateY(-3px) scale(1.08)';
  });
  btn.addEventListener('mouseleave', () => {
    btn.style.transform = 'translateY(0) scale(1)';
  });
})();

/* ══════════════════════════════════════════════════════════
   13. GLASSMORPHISM HOVER INTENSIFY
══════════════════════════════════════════════════════════ */
(function initGlassHover() {
  if (prefersReduced) return;

  document.querySelectorAll('.glass-card').forEach(card => {
    card.addEventListener('mouseenter', () => {
      card.style.backdropFilter         = 'blur(28px) saturate(2)';
      card.style.webkitBackdropFilter   = 'blur(28px) saturate(2)';
      card.style.transition             = 'all 0.3s ease';
    });
    card.addEventListener('mouseleave', () => {
      card.style.backdropFilter         = '';
      card.style.webkitBackdropFilter   = '';
    });
  });
})();

/* ══════════════════════════════════════════════════════════
   14. INIT — DOMContentLoaded
══════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
  // Re-run lucide after all dynamic content
  if (window.lucide) window.lucide.createIcons();

  // Add loaded class to body for CSS transitions
  document.body.classList.add('js-loaded');
});