=== CSS Variables ===
  --pink-50:  #fdf2f8;  --pink-100: #fce7f3;  --pink-200: #fbcfe8;
  --pink-300: #f9a8d4;  --pink-400: #f472b6;  --pink-500: #ec4899;
  --pink-600: #db2777;
  --gold-100: #fef9e7;  --gold-200: #fef3c7;  --gold-300: #f0d898;
  --gold-400: #e8c860;  --gold-500: #d4aa3a;  --gold-600: #b8921e;
  --sky-50:   #f0f9ff;  --sky-100:  #e0f2fe;  --sky-200:  #bae6fd;
  --sky-300:  #7dd3fc;  --sky-400:  #38bdf8;  --sky-500:  #0ea5e9;
  --bg-base:        #fefcf9;
  --bg-subtle:      #fdf8f0;
  --bg-muted:       #fef3f8;
  --surface:        rgba(255,255,255,0.72);
  --surface-hover:  rgba(255,255,255,0.88);
  --surface-strong: rgba(255,255,255,0.92);
  --border:         rgba(212,170,58,0.18);
  --border-pink:    rgba(244,114,182,0.2);
  --border-sky:     rgba(56,189,248,0.2);
  --text-primary:   #1a1228;
  --text-secondary: #4a3f5c;
  --text-muted:     #8b7aa0;
  --text-pink:      #be185d;
  --text-gold:      #92660a;
  --text-sky:       #075985;
  --grad-pink-gold: linear-gradient(135deg, #f9a8d4 0%, #d4aa3a 100%);
  --grad-sky-pink:  linear-gradient(135deg, #bae6fd 0%, #fbcfe8 100%);
  --grad-gold-sky:  linear-gradient(135deg, #fef3c7 0%, #e0f2fe 100%);
  --grad-hero:      linear-gradient(135deg, #fdf2f8 0%, #fef9e7 40%, #f0f9ff 100%);
  --grad-premium:   linear-gradient(135deg, #f472b6, #d4aa3a, #38bdf8);
  --glass:          rgba(255,255,255,0.60);
  --glass-border:   rgba(255,255,255,0.85);
  --glass-shadow:   0 8px 32px rgba(244,114,182,0.12), 0 2px 8px rgba(0,0,0,0.06);
  --glass-pink:     rgba(249,168,212,0.15);
  --glass-gold:     rgba(212,170,58,0.12);
  --glass-sky:      rgba(125,211,252,0.15);
  --glow-pink: 0 0 20px rgba(244,114,182,0.35), 0 0 60px rgba(244,114,182,0.15);
  --glow-gold: 0 0 20px rgba(212,170,58,0.35),  0 0 60px rgba(212,170,58,0.15);
  --glow-sky:  0 0 20px rgba(56,189,248,0.35),  0 0 60px rgba(56,189,248,0.15);
  --font-display: 'Playfair Display', 'Georgia', serif;
  --font-body:    'Plus Jakarta Sans', 'system-ui', sans-serif;
  --font-mono:    'JetBrains Mono', monospace;
  --ease-spring: cubic-bezier(0.175, 0.885, 0.32, 1.275);
  --ease-out:    cubic-bezier(0.22, 1, 0.36, 1);
  --ease-smooth: cubic-bezier(0.4, 0, 0.2, 1);
  --dur-fast:    180ms;
  --dur-normal:  320ms;
  --dur-slow:    600ms;
  --dur-slower:  900ms;
  --perspective: 1000px;

=== Font families ===
  font-family: var(--font-body);
  font-family: var(--font-display);
  font-family: var(--font-display);
  font-family: var(--font-display);
  font-family: var(--font-display);
  font-family: var(--font-body);
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
*{font-family:'Plus Jakarta Sans',sans-serif}
h1,h2,h3,.font-display{font-family:'Playfair Display',serif}
          <span style="font-family:'Playfair Display',serif;color:#be185d;font-weight:700;font-size:1.1rem">ص</span>
    <span style="font-family:'Playfair Display',serif;font-size:1.25rem;color:#be185d">Menu</span>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
*{font-family:'Plus Jakarta Sans',sans-serif}

=== Color classes ===
.glass-card {
.glass-card-pink {
.glass-card-gold {
.glass-card-sky {
.glass-card::before {
.btn-primary-light {
.btn-primary-light::after {
.btn-primary-light:hover {
.btn-outline-light {
.btn-outline-light:hover {
.section-label {
.section-label-pink {
.section-label-gold {
.section-label-sky {
.icon-badge {
.icon-badge-pink { background: var(--pink-100); color: var(--pink-500); box-shadow: 0 0 20px rgba(244,114,182,0.2); }
.icon-badge-gold { background: var(--gold-100); color: var(--gold-600); box-shadow: 0 0 20px rgba(212,170,58,0.2); }
.icon-badge-sky  { background: var(--sky-100);  color: var(--sky-500);  box-shadow: 0 0 20px rgba(56,189,248,0.2);  }

=== Animation names ===
@keyframes shimmer-lr { to { background-position: -200% 0; } }
@keyframes float-y {
@keyframes float-diagonal {
@keyframes glow-pulse {
@keyframes cyber-scan {
@keyframes shimmer {
@keyframes badgePulse {
@keyframes fadeUp {
@keyframes fadeIn {
@keyframes float {
@keyframes scaleIn {
@keyframes slideDown {
@keyframes modalIn {
  style.textContent = '@keyframes loaderSpin{to{transform:rotate(360deg)}}';
  style.textContent = '@keyframes ripple{to{transform:scale(1);opacity:0}}';

=== JS feature functions ===
function initScrollProgress() {
function initThreeBackground() {
function initGSAPAnimations() {
function initTiltCards() {
function initParticleBurst() {
function initSectionDepth() {
function initOrbParallax() {
(function initLoader() {
(function initPageTransition() {
(function initActiveNav() {
(function initSmoothScroll() {
(function initReveal() {
(function initStaggerGrids() {
(function initCounters() {
(function initTilt() {
(function initRipple() {
(function initFormValidation() {
(function initLazyBlurUp() {
(function initBackToTop() {
(function initGlassHover() {