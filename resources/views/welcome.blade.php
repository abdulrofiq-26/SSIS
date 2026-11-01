<!DOCTYPE html>
<html lang="en" class="ssis-fullwidth">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>SSIS – Smart Sparepart Identification System</title>
  <meta name="description" content="Smart Sparepart Identification System – Simply take a photo or enter the data, and let SSIS do the work.">
  <meta name="keywords" content="sparepart, identification, SSIS, smart, inventory">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

  <!-- SSIS Theme: Dark Mode + Animasi Loading -->
  <link href="{{ asset('assets/css/ssis-theme.css') }}" rel="stylesheet">

  <!-- Terapkan tema tersimpan SEBELUM render agar tidak berkedip putih (anti-FOUC) -->
  <script>
    (function () {
      try {
        var saved = localStorage.getItem('ssis-theme');
        if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
          document.documentElement.classList.add('dark-mode');
        }
      } catch (e) {}
    })();
  </script>

  <style>
    /* ===== CSS Variables ===== */
    :root {
      --font-main: 'Inter', 'Poppins', system-ui, sans-serif;
      --accent: #4154f1;
      --accent-dark: #3040d0;
      --heading: #0d1c6b;
      --text: #4a5568;
      --bg: #f0f4ff;
      --white: #ffffff;
      --radius-btn: 8px;
      --shadow-sm: 0 2px 12px rgba(65, 84, 241, 0.12);
      --shadow-md: 0 8px 32px rgba(65, 84, 241, 0.18);
      --transition: 0.25s ease;
    }

    html.dark-mode {
      --bg: #0f1629;
      --white: #1e2d4a;
      --heading: #e8eeff;
      --text: #a0aec0;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html { scroll-behavior: smooth; }

    body.ssis-landing-page {
      font-family: var(--font-main);
      background-color: var(--bg);
      color: var(--text);
      min-height: 100vh;
      overflow-x: hidden;
      position: relative;
    }

    /* ===== Decorative blobs ===== */
    .bg-blob {
      position: fixed;
      border-radius: 50%;
      filter: blur(80px);
      pointer-events: none;
      z-index: 0;
    }
    .bg-blob-1 {
      width: 600px; height: 600px;
      background: radial-gradient(circle, rgba(193,210,255,0.55) 0%, transparent 70%);
      top: -150px; right: -100px;
    }
    .bg-blob-2 {
      width: 400px; height: 400px;
      background: radial-gradient(circle, rgba(180,200,255,0.4) 0%, transparent 70%);
      bottom: 20%; left: -100px;
    }
    .bg-blob-3 {
      width: 300px; height: 300px;
      background: radial-gradient(circle, rgba(210,220,255,0.35) 0%, transparent 70%);
      top: 40%; left: 30%;
    }

    /* ===== Header ===== */
    .ssis-header {
      position: fixed;
      top: 0; left: 0; right: 0;
      z-index: 100;
      padding: 16px 0;
      background: rgba(240, 244, 255, 0.88);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      border-bottom: 1px solid rgba(65, 84, 241, 0.08);
      transition: box-shadow var(--transition), background var(--transition);
    }
    html.dark-mode .ssis-header {
      background: rgba(15, 22, 41, 0.88);
      border-bottom-color: rgba(65, 84, 241, 0.18);
    }
    .ssis-header.scrolled {
      box-shadow: 0 4px 24px rgba(65, 84, 241, 0.1);
    }
    .ssis-header .header-inner {
      width: 100%;
      max-width: 100%;
      margin: 0;
      padding: 0 clamp(16px, 3.5vw, 64px);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    /* Logo */
    .ssis-logo {
      display: flex; align-items: center; gap: 10px;
      text-decoration: none;
    }
    .ssis-logo-icon svg { width: 30px; height: 30px; }
    .ssis-logo-text {
      font-size: 1.25rem; font-weight: 800;
      color: var(--heading); letter-spacing: -0.01em;
    }

    /* Header actions */
    .header-actions { display: flex; align-items: center; gap: 12px; }

    .btn-dashboard {
      display: inline-flex; align-items: center; gap: 7px;
      background: var(--accent); color: #fff;
      font-family: var(--font-main); font-size: 0.88rem; font-weight: 600;
      padding: 9px 22px; border-radius: var(--radius-btn);
      text-decoration: none; border: none; cursor: pointer;
      transition: background var(--transition), transform var(--transition), box-shadow var(--transition);
      box-shadow: 0 2px 10px rgba(65,84,241,0.3);
      letter-spacing: 0.01em;
    }
    .btn-dashboard:hover {
      background: var(--accent-dark); transform: translateY(-1px);
      box-shadow: 0 4px 16px rgba(65,84,241,0.4); color: #fff; text-decoration: none;
    }

    .theme-toggle {
      width: 40px; height: 40px; border-radius: 50%;
      background: var(--accent); border: none; cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-size: 1rem;
      transition: background var(--transition), transform var(--transition), box-shadow var(--transition);
      box-shadow: 0 2px 8px rgba(65,84,241,0.3); flex-shrink: 0;
    }
    .theme-toggle:hover {
      background: var(--accent-dark);
      transform: translateY(-1px) rotate(-20deg);
      box-shadow: 0 4px 14px rgba(65,84,241,0.4);
    }

    /* ===== Hero ===== */
    .ssis-main {
      position: relative; z-index: 1;
      min-height: 100vh; display: flex; flex-direction: column; justify-content: center;
      width: 100%;
    }
    .ssis-hero {
      padding-top: clamp(80px, 12vh, 120px);
      padding-bottom: clamp(30px, 6vh, 60px);
      min-height: 100vh;
      display: flex;
      align-items: center;
      width: 100%;
    }
    .hero-container {
      width: 100%;
      max-width: 100%;
      margin: 0;
      padding: 0 clamp(16px, 4vw, 80px);
      display: grid;
      grid-template-columns: 1.1fr 1fr;
      align-items: center;
      gap: clamp(24px, 4vw, 64px);
    }
    .hero-content {
      display: flex;
      flex-direction: column;
      gap: clamp(16px, 2.5vh, 26px);
      max-width: 680px;
    }

    .hero-title {
      font-family: var(--font-main);
      font-size: clamp(2rem, 4.2vw, 3.8rem);
      font-weight: 800; color: var(--heading);
      line-height: 1.15; letter-spacing: -0.02em;
    }
    .hero-desc {
      font-size: clamp(0.95rem, 1.35vw, 1.2rem);
      color: var(--text); line-height: 1.7;
      max-width: 580px;
    }

    .btn-find-sparepart {
      display: inline-flex; align-items: center; gap: 10px;
      background: var(--accent); color: #fff;
      font-family: var(--font-main);
      font-size: clamp(0.9rem, 1.1vw, 1.05rem);
      font-weight: 700;
      padding: clamp(12px, 1.6vh, 16px) clamp(24px, 2.5vw, 36px);
      border-radius: var(--radius-btn);
      text-decoration: none; border: none; cursor: pointer;
      transition: background var(--transition), transform var(--transition), box-shadow var(--transition);
      box-shadow: var(--shadow-sm); width: fit-content; letter-spacing: 0.01em;
    }
    .btn-find-sparepart:hover {
      background: var(--accent-dark); transform: translateY(-2px);
      box-shadow: var(--shadow-md); color: #fff; text-decoration: none;
    }
    .btn-find-sparepart i { font-size: 1.1em; transition: transform var(--transition); }
    .btn-find-sparepart:hover i { transform: scale(1.15); }

    /* Right illustration */
    .hero-visual {
      display: flex; align-items: center; justify-content: center; position: relative;
      width: 100%;
    }
    .hero-img-wrapper {
      position: relative;
      width: 100%;
      max-width: clamp(340px, 45vw, 720px);
      display: flex;
      justify-content: center;
    }
    .hero-img-wrapper::before {
      content: '';
      position: absolute; inset: -20px;
      background: radial-gradient(ellipse at center, rgba(193,210,255,0.5) 0%, transparent 70%);
      border-radius: 50%; z-index: 0;
      animation: pulseGlow 4s ease-in-out infinite alternate;
    }
    @keyframes pulseGlow {
      0%   { opacity: 0.5; transform: scale(0.95); }
      100% { opacity: 1;   transform: scale(1.05); }
    }
    .hero-img-wrapper img {
      width: 100%;
      height: auto;
      max-height: 80vh;
      object-fit: contain;
      position: relative; z-index: 1;
      animation: floatImg 5s ease-in-out infinite;
      filter: drop-shadow(0 20px 40px rgba(65,84,241,0.12));
    }
    @keyframes floatImg {
      0%, 100% { transform: translateY(0px); }
      50%       { transform: translateY(-14px); }
    }

    /* ===== Scroll-top ===== */
    .scroll-top {
      position: fixed; bottom: 24px; right: 24px;
      width: 42px; height: 42px;
      background: var(--accent); color: #fff; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.25rem; text-decoration: none;
      opacity: 0; visibility: hidden;
      transition: all 0.3s ease;
      box-shadow: var(--shadow-sm); z-index: 50;
    }
    .scroll-top.active { opacity: 1; visibility: visible; }
    .scroll-top:hover { background: var(--accent-dark); transform: translateY(-3px); color: #fff; }

    /* ===== Responsif multi-rasio lengkap ===== */
    /* Ultrawide & layar besar (> 1800px): konten tetap lapang penuh ke samping */
    @media (min-width: 1800px) {
      .hero-container {
        padding: 0 6vw;
        gap: 5vw;
      }
      .hero-content {
        max-width: 820px;
      }
      .hero-img-wrapper {
        max-width: 850px;
      }
    }

    /* Desktop standar & laptop besar (1200px - 1799px) */
    @media (max-width: 1400px) {
      .hero-container {
        grid-template-columns: 1fr 1fr;
        gap: 32px;
      }
    }

    /* Layar tablet landscape & laptop kecil (992px - 1199px) */
    @media (max-width: 1199px) {
      .hero-container {
        padding: 0 clamp(16px, 3vw, 40px);
        gap: 28px;
      }
    }

    /* Tablet potret & tablet kecil (768px - 991px) */
    @media (max-width: 991px) {
      .ssis-hero {
        padding-top: 100px;
        padding-bottom: 40px;
        min-height: auto;
      }
      .hero-container {
        grid-template-columns: 1fr;
        text-align: center;
        gap: 36px;
      }
      .hero-content {
        align-items: center;
        order: 2;
        max-width: 100%;
      }
      .hero-visual {
        order: 1;
      }
      .hero-desc {
        max-width: 620px;
      }
      .hero-img-wrapper {
        max-width: 460px;
      }
    }

    /* Mobile landscape & HP layar sedang (576px - 767px) */
    @media (max-width: 767px) {
      .hero-container {
        padding: 0 20px;
      }
      .hero-img-wrapper {
        max-width: 380px;
      }
    }

    /* Mobile portrait (<= 575px) */
    @media (max-width: 575px) {
      .ssis-header .header-inner {
        padding: 0 14px;
      }
      .btn-dashboard {
        padding: 7px 14px;
        font-size: 0.8rem;
      }
      .hero-container {
        padding: 0 14px;
        gap: 24px;
      }
      .hero-img-wrapper {
        max-width: 320px;
      }
      .btn-find-sparepart {
        width: 100%;
        justify-content: center;
      }
    }

    /* HP sangat kecil (<= 380px) */
    @media (max-width: 380px) {
      .hero-title {
        font-size: 1.8rem;
      }
      .hero-img-wrapper {
        max-width: 270px;
      }
    }
  </style>
</head>

<body class="ssis-landing-page">

  <!-- Decorative background blobs -->
  <div class="bg-blob bg-blob-1" aria-hidden="true"></div>
  <div class="bg-blob bg-blob-2" aria-hidden="true"></div>
  <div class="bg-blob bg-blob-3" aria-hidden="true"></div>

  <!-- ===== Header ===== -->
  <header class="ssis-header" id="ssis-header">
    <div class="header-inner">

      <a href="#" class="ssis-logo" aria-label="SSIS Home">
        <span class="ssis-logo-icon">
          <svg viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <circle cx="15" cy="15" r="15" fill="#4154f1"/>
            <g stroke="#fff" stroke-width="1.7" stroke-linecap="round">
              <line x1="15" y1="5"  x2="15" y2="25"/>
              <line x1="5"  y1="15" x2="25" y2="15"/>
              <line x1="8.4" y1="8.4" x2="21.6" y2="21.6"/>
              <line x1="21.6" y1="8.4" x2="8.4" y2="21.6"/>
              <circle cx="15" cy="15" r="3.2" fill="#4154f1" stroke="#fff" stroke-width="1.5"/>
            </g>
          </svg>
        </span>
        <span class="ssis-logo-text">SSIS</span>
      </a>

      <div class="header-actions">
        @if (Route::has('login'))
          @auth
            <a href="{{ url('/dashboard') }}" class="btn-dashboard">Dashboard</a>
          @else
            <a href="{{ route('login') }}" class="btn-dashboard">Dashboard</a>
          @endauth
        @endif

        <button type="button" id="themeToggle" class="theme-toggle" aria-label="Ubah tema" title="Ubah tema terang/gelap">
          <i class="bi bi-moon-stars-fill"></i>
        </button>
      </div>

    </div>
  </header>

  <!-- ===== Main ===== -->
  <main class="ssis-main">
    <section class="ssis-hero" id="hero">
      <div class="hero-container">

        <!-- Left -->
        <div class="hero-content">
          <h1 class="hero-title">Smart Sparepart Identification System</h1>
          <p class="hero-desc">
            Simply take a photo or enter the data, and let SSIS do the work.
            Get detailed information, compatibility, and availability instantly.
          </p>
          <a href="{{ route('search.index') }}" class="btn-find-sparepart">
            Find Sparepart
            <i class="bi bi-search"></i>
          </a>
        </div>

        <!-- Right -->
        <div class="hero-visual">
          <div class="hero-img-wrapper">
            <img src="assets/img/hero-img.png"
                 alt="Smart Sparepart Identification System Illustration">
          </div>
        </div>

      </div>
    </section>
  </main>

  <!-- ===== SSIS Loader Overlay ===== -->
  <div id="ssis-loader" class="ssis-loader-overlay" role="status" aria-live="polite">
    <div class="ssis-gear">
      <i class="bi bi-gear-fill gear-large"></i>
      <i class="bi bi-gear-fill gear-small"></i>
    </div>
    <div class="ssis-loader-text">Menyiapkan sistem…</div>
    <div class="ssis-loader-sub">Mohon tunggu sebentar</div>
    <div class="ssis-loader-bar"><span></span></div>
  </div>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top" aria-label="Scroll to top">
    <i class="bi bi-arrow-up-short"></i>
  </a>

  <!-- SSIS Theme Script -->
  <script src="{{ asset('assets/js/ssis-theme.js') }}"></script>

  <script>
    const header  = document.getElementById('ssis-header');
    const scrollBtn = document.getElementById('scroll-top');

    window.addEventListener('scroll', () => {
      if (window.scrollY > 20) {
        header.classList.add('scrolled');
        scrollBtn.classList.add('active');
      } else {
        header.classList.remove('scrolled');
        scrollBtn.classList.remove('active');
      }
    });

    scrollBtn.addEventListener('click', (e) => {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  </script>

</body>

</html>