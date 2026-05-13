/**
 * assets/js/scroll3d.js
 * ─────────────────────────────────────────────────────────────────────────────
 * SD Muhammadiyah 1 Gentasari — Scroll-Based 3D Animation Engine
 * Light Mode | Cyberpunk-Formal | Pink × Gold × Sky Blue
 *
 * Features:
 *  - GSAP ScrollTrigger parallax layers
 *  - Three.js floating geometry background
 *  - Tilt-on-hover 3D cards
 *  - Section reveal with depth
 *  - Scroll progress indicator
 *  - Reduced-motion fallback
 * ─────────────────────────────────────────────────────────────────────────────
 */

'use strict';

/* ══════════════════════════════════════════════════════════
   0. REDUCED MOTION GUARD
══════════════════════════════════════════════════════════ */
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* ══════════════════════════════════════════════════════════
   1. SCROLL PROGRESS BAR
══════════════════════════════════════════════════════════ */
function initScrollProgress() {
  const bar = document.createElement('div');
  bar.id = 'scroll-progress';
  Object.assign(bar.style, {
    position: 'fixed', top: '0', left: '0', height: '3px',
    background: 'linear-gradient(90deg, #f472b6, #d4aa3a, #38bdf8)',
    zIndex: '9999', width: '0%',
    transition: 'width 0.1s linear',
    boxShadow: '0 0 8px rgba(244,114,182,0.6)',
    pointerEvents: 'none',
  });
  document.body.appendChild(bar);

  window.addEventListener('scroll', () => {
    const pct = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100;
    bar.style.width = Math.min(pct, 100) + '%';
  }, { passive: true });
}

/* ══════════════════════════════════════════════════════════
   2. THREE.JS FLOATING GEOMETRY BACKGROUND
══════════════════════════════════════════════════════════ */
function initThreeBackground() {
  if (prefersReducedMotion) return;
  if (!window.THREE) return;
  const canvas = document.getElementById('three-canvas');
  if (!canvas) return;

  const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  renderer.setSize(window.innerWidth, window.innerHeight);
  renderer.setClearColor(0x000000, 0);

  const scene  = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 100);
  camera.position.z = 5;

  // Color palette: pink, gold, sky-blue
  const colors = [0xf9a8d4, 0xd4aa3a, 0x7dd3fc, 0xfbcfe8, 0xe8c860, 0xbae6fd];

  // Create floating geometric shapes
  const meshes = [];
  const geoTypes = [
    new THREE.TetrahedronGeometry(0.25, 0),
    new THREE.OctahedronGeometry(0.22, 0),
    new THREE.IcosahedronGeometry(0.2, 0),
    new THREE.TorusGeometry(0.18, 0.07, 8, 6),
    new THREE.BoxGeometry(0.28, 0.28, 0.28),
  ];

  for (let i = 0; i < 28; i++) {
    const geo = geoTypes[i % geoTypes.length];
    const mat = new THREE.MeshPhongMaterial({
      color: colors[i % colors.length],
      transparent: true,
      opacity: 0.55,
      wireframe: i % 3 === 0,
      shininess: 80,
    });
    const mesh = new THREE.Mesh(geo, mat);

    mesh.position.set(
      (Math.random() - 0.5) * 12,
      (Math.random() - 0.5) * 8,
      (Math.random() - 0.5) * 4
    );
    mesh.rotation.set(
      Math.random() * Math.PI,
      Math.random() * Math.PI,
      Math.random() * Math.PI
    );
    mesh.userData = {
      rotSpeed: { x: (Math.random() - 0.5) * 0.008, y: (Math.random() - 0.5) * 0.008 },
      floatAmp: 0.004 + Math.random() * 0.006,
      floatFreq: 0.3 + Math.random() * 0.5,
      phase: Math.random() * Math.PI * 2,
      origY: mesh.position.y,
    };
    scene.add(mesh);
    meshes.push(mesh);
  }

  // Lights
  const ambientLight = new THREE.AmbientLight(0xfef3c7, 1.2);
  scene.add(ambientLight);
  const pointLight1 = new THREE.PointLight(0xf9a8d4, 2, 20);
  pointLight1.position.set(4, 3, 3);
  scene.add(pointLight1);
  const pointLight2 = new THREE.PointLight(0x38bdf8, 1.5, 20);
  pointLight2.position.set(-4, -2, 2);
  scene.add(pointLight2);

  // Mouse parallax
  let mouseX = 0, mouseY = 0;
  document.addEventListener('mousemove', e => {
    mouseX = (e.clientX / window.innerWidth  - 0.5) * 0.4;
    mouseY = (e.clientY / window.innerHeight - 0.5) * 0.3;
  });

  // Scroll parallax on Z
  let scrollY = 0;
  window.addEventListener('scroll', () => { scrollY = window.scrollY; }, { passive: true });

  // Resize
  window.addEventListener('resize', () => {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
  });

  // Animate
  const clock = new THREE.Clock();
  (function animate() {
    requestAnimationFrame(animate);
    const t = clock.getElapsedTime();

    meshes.forEach(m => {
      m.rotation.x += m.userData.rotSpeed.x;
      m.rotation.y += m.userData.rotSpeed.y;
      m.position.y  = m.userData.origY + Math.sin(t * m.userData.floatFreq + m.userData.phase) * m.userData.floatAmp * 30;
    });

    // Camera follows mouse + scroll parallax
    camera.position.x += (mouseX - camera.position.x) * 0.03;
    camera.position.y += (-mouseY - camera.position.y) * 0.03;
    camera.position.z = 5 - scrollY * 0.001;
    camera.lookAt(scene.position);

    renderer.render(scene, camera);
  })();
}

/* ══════════════════════════════════════════════════════════
   3. GSAP SCROLL ANIMATIONS
══════════════════════════════════════════════════════════ */
function initGSAPAnimations() {
  if (!window.gsap || !window.ScrollTrigger) return;
  gsap.registerPlugin(ScrollTrigger);

  // ── Hero parallax layers ──────────────────────────────
  const heroLayers = document.querySelectorAll('[data-parallax]');
  heroLayers.forEach(el => {
    const speed  = parseFloat(el.dataset.parallax) || 0.3;
    const dir    = el.dataset.parallaxDir || 'y';
    gsap.to(el, {
      [dir]: () => window.innerHeight * speed * -1,
      ease:  'none',
      scrollTrigger: {
        trigger: el.closest('section') || el,
        start: 'top top',
        end:   'bottom top',
        scrub: 1.5,
      }
    });
  });

  // ── Floating 3D cards reveal ──────────────────────────
  const cards3d = document.querySelectorAll('.card-3d, .reveal-3d');
  if (cards3d.length) {
    gsap.set(cards3d, { opacity: 0, y: 60, rotateX: 15, transformPerspective: 900 });
    ScrollTrigger.batch(cards3d, {
      onEnter: batch => gsap.to(batch, {
        opacity: 1, y: 0, rotateX: 0,
        duration: 0.9, stagger: 0.12, ease: 'power3.out',
      }),
      start: 'top 88%',
    });
  }

  // ── Section headings split reveal ────────────────────
  const headings = document.querySelectorAll('.reveal-heading');
  headings.forEach(h => {
    gsap.from(h, {
      opacity: 0, y: 40, skewY: 2,
      duration: 1, ease: 'power4.out',
      scrollTrigger: { trigger: h, start: 'top 85%' }
    });
  });

  // ── Stat counters ─────────────────────────────────────
  const counters = document.querySelectorAll('[data-count]');
  counters.forEach(el => {
    const target = parseInt(el.dataset.count, 10);
    ScrollTrigger.create({
      trigger: el,
      start: 'top 85%',
      once: true,
      onEnter: () => {
        gsap.to({ val: 0 }, {
          val: target, duration: 2, ease: 'power2.out',
          onUpdate: function() { el.textContent = Math.round(this.targets()[0].val).toLocaleString('id-ID'); }
        });
      }
    });
  });

  // ── Timeline / step lines ────────────────────────────
  const lines = document.querySelectorAll('.anim-line');
  lines.forEach(line => {
    gsap.from(line, {
      scaleX: 0, transformOrigin: 'left center',
      duration: 1.2, ease: 'power3.inOut',
      scrollTrigger: { trigger: line, start: 'top 90%' }
    });
  });

  // ── Stagger grid items ────────────────────────────────
  const grids = document.querySelectorAll('.stagger-grid');
  grids.forEach(grid => {
    const items = grid.querySelectorAll(':scope > *');
    gsap.set(items, { opacity: 0, y: 40, scale: 0.95 });
    ScrollTrigger.create({
      trigger: grid, start: 'top 80%', once: true,
      onEnter: () => gsap.to(items, {
        opacity: 1, y: 0, scale: 1,
        duration: 0.7, stagger: 0.1, ease: 'back.out(1.4)'
      })
    });
  });

  // ── Horizontal scroll strip ───────────────────────────
  const hScroll = document.querySelector('.h-scroll-track');
  if (hScroll) {
    const totalWidth = hScroll.scrollWidth - window.innerWidth;
    gsap.to(hScroll, {
      x: -totalWidth,
      ease: 'none',
      scrollTrigger: {
        trigger: '.h-scroll-section',
        start: 'top top',
        end:   `+=${totalWidth}`,
        pin:   true,
        scrub: 1,
      }
    });
  }

  // ── Pinned feature section ────────────────────────────
  const pinSection = document.querySelector('.pin-section');
  if (pinSection) {
    const panels = pinSection.querySelectorAll('.pin-panel');
    panels.forEach((panel, i) => {
      if (i === 0) return;
      gsap.from(panel, {
        opacity: 0, y: 80,
        scrollTrigger: {
          trigger: pinSection,
          start: `top+=${i * window.innerHeight * 0.5} center`,
          end: `top+=${(i + 1) * window.innerHeight * 0.5} center`,
          scrub: true,
        }
      });
    });
  }
}

/* ══════════════════════════════════════════════════════════
   4. 3D TILT CARDS (mouse hover)
══════════════════════════════════════════════════════════ */
function initTiltCards() {
  if (prefersReducedMotion) return;
  const cards = document.querySelectorAll('.tilt-card');
  cards.forEach(card => {
    const inner = card.querySelector('.tilt-inner') || card;

    card.addEventListener('mousemove', e => {
      const rect   = card.getBoundingClientRect();
      const cx     = rect.left + rect.width  / 2;
      const cy     = rect.top  + rect.height / 2;
      const rx     = ((e.clientY - cy) / (rect.height / 2)) * -12;
      const ry     = ((e.clientX - cx) / (rect.width  / 2)) *  12;
      inner.style.transform = `perspective(800px) rotateX(${rx}deg) rotateY(${ry}deg) scale3d(1.03,1.03,1.03)`;
      inner.style.transition = 'transform 0.1s ease-out';
      // Shine
      const shine = card.querySelector('.tilt-shine');
      if (shine) {
        shine.style.background = `radial-gradient(circle at ${e.clientX - rect.left}px ${e.clientY - rect.top}px, rgba(255,255,255,0.25) 0%, transparent 65%)`;
      }
    });

    card.addEventListener('mouseleave', () => {
      inner.style.transform  = 'perspective(800px) rotateX(0deg) rotateY(0deg) scale3d(1,1,1)';
      inner.style.transition = 'transform 0.6s ease';
      const shine = card.querySelector('.tilt-shine');
      if (shine) shine.style.background = 'transparent';
    });
  });
}



/* ══════════════════════════════════════════════════════════
   6. PARTICLE BURST on section enter
══════════════════════════════════════════════════════════ */
function initParticleBurst() {
  if (prefersReducedMotion || !window.gsap) return;
  const triggers = document.querySelectorAll('[data-burst]');
  triggers.forEach(el => {
    ScrollTrigger.create({
      trigger: el, start: 'top 75%', once: true,
      onEnter: () => burst(el),
    });
  });

  function burst(origin) {
    const rect = origin.getBoundingClientRect();
    for (let i = 0; i < 12; i++) {
      const dot = document.createElement('div');
      Object.assign(dot.style, {
        position: 'fixed',
        width: '6px', height: '6px',
        borderRadius: '50%',
        background: ['#f9a8d4','#d4aa3a','#7dd3fc'][i % 3],
        left: rect.left + rect.width  / 2 + 'px',
        top:  rect.top  + rect.height / 2 + 'px',
        pointerEvents: 'none',
        zIndex: '9990',
      });
      document.body.appendChild(dot);
      const angle  = (i / 12) * Math.PI * 2;
      const radius = 60 + Math.random() * 40;
      gsap.to(dot, {
        x: Math.cos(angle) * radius,
        y: Math.sin(angle) * radius - 30,
        opacity: 0, scale: 0,
        duration: 0.9 + Math.random() * 0.4,
        ease: 'power2.out',
        onComplete: () => dot.remove(),
      });
    }
  }
}

/* ══════════════════════════════════════════════════════════
   7. SMOOTH SECTION TRANSITIONS (page-level depth)
══════════════════════════════════════════════════════════ */
function initSectionDepth() {
  if (prefersReducedMotion || !window.gsap) return;
  const sections = document.querySelectorAll('.depth-section');
  sections.forEach(sec => {
    gsap.fromTo(sec,
      { opacity: 0.4, scale: 0.97, filter: 'blur(4px)' },
      {
        opacity: 1, scale: 1, filter: 'blur(0px)',
        ease: 'power2.out',
        scrollTrigger: {
          trigger: sec, start: 'top 80%', end: 'top 30%',
          scrub: 1,
        }
      }
    );
  });
}

/* ══════════════════════════════════════════════════════════
   8. GLOWING ORBS parallax
══════════════════════════════════════════════════════════ */
function initOrbParallax() {
  if (prefersReducedMotion) return;
  const orbs = document.querySelectorAll('.orb-parallax');
  orbs.forEach(orb => {
    const speed = parseFloat(orb.dataset.speed) || 0.15;
    window.addEventListener('scroll', () => {
      const y = window.scrollY * speed;
      orb.style.transform = `translateY(${y}px)`;
    }, { passive: true });
  });
}

/* ══════════════════════════════════════════════════════════
   9. INIT ALL
══════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
  initScrollProgress();
  initThreeBackground();
  initGSAPAnimations();
  initTiltCards();
  initParticleBurst();
  initSectionDepth();
  initOrbParallax();

  // Lucide icons re-init
  if (window.lucide) window.lucide.createIcons();
});