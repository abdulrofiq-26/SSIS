<!DOCTYPE html>
<html lang="en" class="ssis-fullwidth">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>SSIS – Smart Sparepart Identification System</title>
  <meta name="description" content="Smart Sparepart Identification System – Simply take a photo or enter the data, and let SSIS do the work.">
  <meta name="keywords" content="sparepart, identification, SSIS, smart, inventory">

  <!-- Favicons -->
  <link href="{{ asset('assets/img/favicon.png') }}" rel="icon">
  <link href="{{ asset('assets/img/apple-touch-icon.png') }}" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Vendor -->
  <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">

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
    /* ===== Tema selaras dengan halaman welcome ===== */
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
      /* Kompatibilitas variabel template (dipakai ssis-theme.css) */
      --background-color: #f0f4ff;
      --default-color: #4a5568;
      --heading-color: #0d1c6b;
      --accent-color: #4154f1;
      --surface-color: #ffffff;
      --contrast-color: #ffffff;
      --heading-font: 'Inter', 'Poppins', system-ui, sans-serif;
    }
    html.dark-mode {
      --bg: #0f1629;
      --white: #1e2d4a;
      --heading: #e8eeff;
      --text: #a0aec0;
      --background-color: #0f1629;
      --default-color: #a0aec0;
      --heading-color: #e8eeff;
      --accent-color: #6b7cff;
      --surface-color: #1e2d4a;
    }
    *, *::before, *::after { box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    body.ssis-landing-page {
      font-family: var(--font-main);
      background-color: var(--bg);
      color: var(--text);
      min-height: 100vh;
      overflow-x: hidden;
      position: relative;
    }
    html.dark-mode body.ssis-landing-page { background-color: var(--bg); color: var(--text); }
    html.dark-mode .bg-blob { opacity: .18; }
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
    .ssis-header.scrolled { box-shadow: 0 4px 24px rgba(65, 84, 241, 0.1); }
    .ssis-header .header-inner {
      width: 100%;
      max-width: 100%;
      margin: 0;
      padding: 0 clamp(16px, 3.5vw, 64px);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .ssis-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
    .ssis-logo-icon svg { width: 30px; height: 30px; }
    .ssis-logo-text { font-size: 1.25rem; font-weight: 800; color: var(--heading); letter-spacing: -0.01em; }
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
    .btn-dashboard:hover { background: var(--accent-dark); transform: translateY(-1px); box-shadow: 0 4px 16px rgba(65,84,241,0.4); color: #fff; text-decoration: none; }
    .theme-toggle {
      width: 40px; height: 40px; border-radius: 50%;
      background: var(--accent); border: none; cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-size: 1rem;
      transition: background var(--transition), transform var(--transition), box-shadow var(--transition);
      box-shadow: 0 2px 8px rgba(65,84,241,0.3); flex-shrink: 0;
    }
    .theme-toggle:hover { background: var(--accent-dark); transform: translateY(-1px) rotate(-20deg); box-shadow: 0 4px 14px rgba(65,84,241,0.4); }
    html.dark-mode .theme-toggle { background: var(--accent); border-color: transparent; color: #fff; }
    html.dark-mode .theme-toggle:hover { background: var(--accent-dark); color: #fff; }
    .ssis-search-main {
      position: relative; z-index: 1;
      min-height: 100vh; display: flex; flex-direction: column;
      padding-top: 88px; padding-bottom: 40px;
    }
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
    @media (max-width: 575px) {
      .ssis-header .header-inner { padding: 0 14px; }
      .btn-dashboard { padding: 7px 14px; font-size: 0.8rem; }
    }
    /* ===== Search — selaras welcome ===== */
    .ssis-search-section { width: 100%; padding: 0 clamp(16px, 4vw, 80px); }
    html.dark-mode .ssis-search-section { color: var(--text); }
    html.dark-mode .ssis-search-main { background-color: var(--bg); }
    html.dark-mode .ssis-search-card .card-body { color: var(--text); }
    html.dark-mode .ssis-history-title,
    html.dark-mode .ssis-hint { color: var(--heading); }
    html.dark-mode .ssis-history-link { color: var(--text); }
    html.dark-mode .ssis-results-heading,
    html.dark-mode .ssis-match-name,
    html.dark-mode .ssis-match-meta { color: var(--heading); }
    html.dark-mode .ssis-match-body,
    html.dark-mode .ssis-match-stock { color: var(--text); }
    html.dark-mode .ssis-brands-heading { color: var(--heading); }
    .ssis-card-grid { grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); }
    .ssis-card-grid-sm { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); }
    .ssis-upload-preview {
      display: flex; align-items: center; justify-content: center; gap: .75rem;
      margin: -0.4rem auto 1.2rem; color: var(--text); font-size: .85rem;
    }
    .ssis-upload-preview[hidden] { display: none; }
    .ssis-upload-preview img {
      width: 56px; height: 56px; object-fit: cover; border-radius: 10px;
      border: 1px solid rgba(65,84,241,.35); background: #fff;
    }
    .ssis-upload-preview span { max-width: min(70vw, 360px); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    html.dark-mode .ssis-upload-preview { color: var(--heading); }
    html.dark-mode .ssis-search-card .alert-warning,
    html.dark-mode .ssis-search-card .alert-danger { color: #f0d99b; }
    .ssis-search-title-wrap { text-align: center; margin-bottom: 1.1rem; }
    .ssis-search-heading {
      display: flex; align-items: center; justify-content: center; gap: .6rem;
      font-family: var(--font-main); font-size: clamp(1.4rem, 3.5vw, 2.2rem);
      font-weight: 800; color: var(--heading); letter-spacing: -0.02em;
      margin: 0;
    }
    .ssis-search-heading .bi-stars {
      font-size: .75em;
      background: linear-gradient(135deg, #4154f1, #8ea0ff);
      -webkit-background-clip: text; background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    .ssis-search-container { width: 100%; max-width: 100%; padding: 0; }
    .ssis-search-shell { width: 100%; max-width: none; margin: 0 auto; }
    .ssis-search-shell > .ssis-search-card { width: 100%; max-width: none; }
    .ssis-search-card {
      width: 100% !important;
      border-radius: 20px !important;
      border: 1.5px solid rgba(65,84,241,.18) !important;
      background: rgba(255,255,255,.92) !important;
      box-shadow: 0 18px 50px rgba(65,84,241,.12) !important;
      backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
    }
    html.dark-mode .ssis-search-card {
      border-color: rgba(65,84,241,.35) !important;
      background: rgba(30,45,74,.92) !important;
      box-shadow: 0 18px 50px rgba(0,0,0,.4) !important;
    }
    html.dark-mode .ssis-search-heading { color: var(--heading); }
    .ssis-media-btn {
      border-color: var(--accent) !important;
      color: var(--accent) !important;
      background: #fff !important;
    }
    .ssis-media-btn:hover { background: var(--accent) !important; color: #fff !important; }
    html.dark-mode .ssis-media-btn { background: transparent !important; color: #cdd6ff !important; border-color: #8fa0ff !important; }
    html.dark-mode .ssis-media-btn:hover { background: #8fa0ff !important; color: #0f1629 !important; }
    .ssis-text-input {
      border-color: rgba(65,84,241,.35) !important;
      background: rgba(255,255,255,.85) !important;
      color: var(--text) !important;
    }
    .ssis-text-input:focus { border-color: var(--accent) !important; box-shadow: 0 0 0 .22rem rgba(65,84,241,.18) !important; background: #fff !important; }
    html.dark-mode .ssis-text-input { background: rgba(15,22,41,.85) !important; color: var(--heading) !important; border-color: rgba(120,140,255,.45) !important; }
    html.dark-mode .ssis-text-input:focus { background: #0f1629 !important; box-shadow: 0 0 0 .22rem rgba(65,84,241,.28) !important; }
    .ssis-text-search-btn { background: var(--accent) !important; }
    .ssis-text-search-btn:hover { background: var(--accent-dark) !important; }
    html.dark-mode .ssis-text-search-btn { background: #4a5bff !important; }
    html.dark-mode .ssis-text-search-btn:hover { background: #6b7cff !important; }
    .ssis-results-heading { color: var(--heading) !important; }
    .ssis-results-heading .bi { color: var(--accent) !important; }
    .ssis-results-count { background: rgba(65,84,241,.12) !important; color: var(--accent) !important; }
    html.dark-mode .ssis-results-count { background: rgba(65,84,241,.22) !important; color: #b9a8ff !important; }
    .ssis-match-card { border-color: rgba(65,84,241,.22) !important; }
    .ssis-match-card:hover { box-shadow: 0 14px 30px rgba(65,84,241,.18) !important; }
    html.dark-mode .ssis-match-card { border-color: #4a5bff !important; background: #1e2d4a !important; }
    .ssis-match-card.is-best-match { border-color: var(--accent) !important; box-shadow: 0 0 0 3px rgba(65,84,241,.18) !important; }
    .ssis-best-badge { background: linear-gradient(135deg, #4154f1, #8ea0ff) !important; }
    .ssis-match-bar { background: rgba(65,84,241,.12) !important; border-color: rgba(65,84,241,.25) !important; }
    .ssis-match-bar-fill { background: linear-gradient(90deg, #4154f1, #8ea0ff) !important; }
    .ssis-stock-badge { background: linear-gradient(135deg, #4154f1, #6b7cff) !important; }
    .ssis-query-highlight { color: var(--accent) !important; }
    .ssis-brands-heading { color: var(--heading) !important; font-weight: 800; }
    .ssis-result-main .ssis-title { color: var(--heading) !important; }
    .ssis-result-meta { color: var(--text) !important; }
    .ssis-history-title { color: var(--heading) !important; }
    .ssis-history-link:hover { color: var(--accent) !important; }
    .ssis-modal-panel { border-color: var(--accent) !important; }
    html.dark-mode .ssis-modal-panel { border-color: #4a5bff !important; background: #1e2d4a !important; }
    .ssis-modal-close:hover { background: var(--accent) !important; }
    .ssis-modal-thumb.is-active { border-color: var(--accent) !important; }
    .ssis-modal-desc-title .bi, .ssis-modal-score-value { color: var(--accent) !important; }
    @media (max-width: 991px) {
      .ssis-search-section { padding: 0 clamp(16px, 3vw, 40px); }
    }
    @media (max-width: 575px) {
      .ssis-search-section { padding: 0 14px; }
    }
    /* Thumbnail kecil untuk hasil pencarian: unggahan/kamera tetap berupa hasil sparepart. */
    .ssis-match-card-sm .ssis-match-img { aspect-ratio: 1 / 1; }
    .ssis-match-card-sm .ssis-match-img img { object-fit: contain; padding: .45rem; }
    .ssis-match-card-sm .ssis-match-body { padding: .65rem .8rem; }
    .ssis-match-card-sm .ssis-match-meta { font-size: .78rem; }
    @media (min-width: 576px) {
      .ssis-search-section { padding-left: clamp(16px, 2vw, 32px); padding-right: clamp(16px, 2vw, 32px); }
    }
  </style>
</head>

<body class="ssis-landing-page">

  <!-- Decorative background blobs — sama seperti welcome -->
  <div class="bg-blob bg-blob-1" aria-hidden="true"></div>
  <div class="bg-blob bg-blob-2" aria-hidden="true"></div>
  <div class="bg-blob bg-blob-3" aria-hidden="true"></div>

  <!-- Header — identik dengan welcome -->
  <header class="ssis-header" id="ssis-header">
    <div class="header-inner">
      <a href="{{ route('index') }}" class="ssis-logo" aria-label="SSIS Home">
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

  <main class="ssis-search-main">
    <section class="ssis-search-section">
      {{-- Judul hanya tampil di halaman awal (belum ada hasil pencarian) — gaya selaras welcome --}}
      @if(!isset($results) || empty($query))
      <div class="ssis-search-title-wrap">
        <h2 class="ssis-search-heading">
          <i class="bi bi-stars"></i>
          Find Your Spare Part
          <i class="bi bi-stars"></i>
        </h2>
      </div>
      @endif
      <div class="container-fluid ssis-search-container">
        <div class="row justify-content-center">
          <div class="col-12">

              <div class="ssis-search-shell">
              <div class="ssis-search-card">
                  <div class="card-body p-4 p-md-5">

                      {{-- ================================================================
                           FORM PENCARIAN GAMBAR (Kamera & Upload) — DI ATAS
                           Kedua tombol memakai satu form. Input file dikirim ke
                           route search.image.
                           ================================================================ --}}
                      <form action="{{ route('search.image') }}" method="POST" enctype="multipart/form-data" id="image-search-form">
                          @csrf
                          @if ($errors->has('image') || $errors->has('image_search'))
                              <div class="alert alert-danger text-center mb-3" role="alert">
                                  <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                  {{ $errors->first('image') ?? $errors->first('image_search') }}
                              </div>
                          @endif
                          <div class="ssis-media-actions">
                              {{-- Tombol Gunakan Kamera --}}
                              <label for="camera-input" class="ssis-media-btn" title="Ambil foto dengan kamera">
                                  <i class="bi bi-camera-fill"></i>
                              </label>
                              <input type="file" id="camera-input" class="d-none" accept="image/*" capture="environment">

                              {{-- Tombol Upload File --}}
                              <label for="upload-image-input" class="ssis-media-btn" title="Unggah gambar dari perangkat">
                                  <i class="bi bi-cloud-arrow-up-fill"></i>
                              </label>
                              <input type="file" id="upload-image-input" class="d-none" accept="image/*">
                          </div>
                          <div class="ssis-upload-preview" id="ssis-upload-preview" hidden aria-live="polite">
                              <img id="ssis-upload-preview-image" alt="Pratinjau gambar yang dipilih">
                              <span id="ssis-upload-preview-name"></span>
                          </div>
                      </form>

                      {{-- ================================================================
                           FORM PENCARIAN TEKS
                           ================================================================ --}}
                      <form action="{{ route('search.results') }}" method="GET" class="ssis-search-form">
                        <div class="ssis-text-search">
                            <input type="text" name="search_query" class="ssis-text-input"
                                  placeholder="Enter part number or name..." aria-label="Part search input"
                                  autocomplete="off"
                                  value="{{ $query ?? '' }}">
                            <button class="ssis-text-search-btn" type="submit" id="button-text-search" aria-label="Search">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                      </form>

                      {{-- ================================================================
                           HISTORY SEARCHING
                           Ditampilkan hanya saat belum ada hasil pencarian.
                           ================================================================ --}}
                      @if(!isset($results) || empty($query))
                      <div class="ssis-history" id="ssis-history" hidden>
                          <h6 class="ssis-history-title">History searching....</h6>
                          <ul class="ssis-history-list" id="ssis-history-list"></ul>
                      </div>
                      @endif
                      <div class="row mt-4">
                        <div class="col-12 ssis-results-area">
                            @if(isset($results) && !empty($query))
                                @php
                                    $isImageSearch = $is_image_search ?? false;
                                @endphp
                                @if($isImageSearch)
                                    {{-- =========================================================
                                         HASIL PENCARIAN GAMBAR: kartu grid.
                                         Urutan sudah dari paling mirip; item paling kiri
                                         adalah kandidat dengan kemiripan tertinggi.
                                         ========================================================= --}}
                                    <h4 class="ssis-results-heading">
                                        <i class="bi bi-images"></i>
                                        Hasil Pencarian Gambar
                                        <span class="ssis-results-count">{{ $results['spareparts']->count() }} hasil</span>
                                    </h4>

                                    @if($results['spareparts']->count() > 0)
                                    <div class="ssis-card-grid">
                                        @foreach ($results['spareparts'] as $index => $sparepart)
                                            @php
                                                $mainImage = $sparepart->images->first();
                                                $score = $sparepart->similarity_score ?? null;
                                                $scoreDisplay = $score !== null ? number_format($score, 0) : null;

                                                // Siapkan data untuk modal (disusun di PHP agar Blade
                                                // tidak bingung dengan nested bracket di dalam @json).
                                                // Hanya gambar yang file-nya ADA yang disertakan.
                                                $validImages = $sparepart->images
                                                    ->filter(fn ($img) => \Illuminate\Support\Facades\Storage::disk('public')->exists($img->path))
                                                    ->map(fn ($img) => route('storage.media', ltrim($img->path, '/')))
                                                    ->values()
                                                    ->all();

                                                if (empty($validImages)
                                                    && $sparepart->brand
                                                    && $sparepart->brand->logo_brand
                                                    && \Illuminate\Support\Facades\Storage::disk('public')->exists($sparepart->brand->logo_brand)) {
                                                    $validImages = [route('storage.media', ltrim($sparepart->brand->logo_brand, '/'))];
                                                }

                                                $detailData = [
                                                    'id'        => $sparepart->id,
                                                    'nama'      => $sparepart->nama_barang,
                                                    'kode'      => $sparepart->kode_barang,
                                                    'brand'     => $sparepart->brand->nama_brand ?? null,
                                                    'kategori'  => $sparepart->category->nama_kategori ?? null,
                                                    'lokasi'    => $sparepart->lokasi_penyimpanan,
                                                    'stok'      => $sparepart->jumlah_stok,
                                                    'satuan'    => $sparepart->jumlah_satuan,
                                                    'deskripsi' => $sparepart->deskripsi,
                                                    'score'     => $score,
                                                    'gambar'    => $validImages,
                                                ];
                                            @endphp
                                            <button type="button"
                                               class="ssis-match-card js-open-detail {{ $index === 0 ? 'is-best-match' : '' }}"
                                               data-sparepart-id="{{ $sparepart->id }}"
                                               data-score="{{ $score }}"
                                               data-json="{{ json_encode($detailData, JSON_HEX_APOS | JSON_HEX_QUOT) }}">

                                                @if($index === 0)
                                                    <span class="ssis-best-badge">
                                                        <i class="bi bi-star-fill"></i> Paling Mirip
                                                    </span>
                                                @endif
                                                {{-- Gambar --}}
                                                <div class="ssis-match-img">
                                                    @php
                                                        $imgUrl = null;
                                                        if ($mainImage && \Illuminate\Support\Facades\Storage::disk('public')->exists($mainImage->path)) {
                                                            $imgUrl = route('storage.media', ltrim($mainImage->path, '/'));
                                                        } elseif ($sparepart->brand && $sparepart->brand->logo_brand
                                                            && \Illuminate\Support\Facades\Storage::disk('public')->exists($sparepart->brand->logo_brand)) {
                                                            $imgUrl = route('storage.media', ltrim($sparepart->brand->logo_brand, '/'));
                                                        }
                                                    @endphp
                                                    @if ($imgUrl)
                                                        <img src="{{ $imgUrl }}"
                                                             alt="{{ $sparepart->nama_barang }}" loading="lazy"
                                                             onerror="this.parentElement.classList.add('is-fallback'); this.remove();">
                                                    @else
                                                        <div class="ssis-match-img-fallback">
                                                            <i class="bi bi-gear-wide-connected"></i>
                                                            <span>{{ \Illuminate\Support\Str::limit($sparepart->brand->nama_brand ?? 'Sparepart', 14) }}</span>
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- Info --}}
                                                <div class="ssis-match-body">
                                                    <h6 class="ssis-match-name">{{ $sparepart->nama_barang }}</h6>
                                                    <ul class="ssis-match-meta">
                                                        <li>Kode: {{ $sparepart->kode_barang }}</li>
                                                        <li>Brand: {{ $sparepart->brand->nama_brand ?? 'N/A' }}</li>
                                                        <li>Kategori: {{ $sparepart->category->nama_kategori ?? 'N/A' }}</li>
                                                        <li>Lokasi: {{ $sparepart->lokasi_penyimpanan ?: 'N/A' }}</li>
                                                    </ul>
                                                </div>

                                                {{-- Skor kemiripan --}}
                                                @if($scoreDisplay !== null)
                                                <div class="ssis-match-score">
                                                    <span class="ssis-match-score-value">{{ $scoreDisplay }} %</span>
                                                    <div class="ssis-match-bar">
                                                        <div class="ssis-match-bar-fill" style="width: {{ max(0, min(100, (float) $score)) }}%;"></div>
                                                    </div>
                                                </div>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                    @else
                                        <div class="alert alert-warning text-center mt-4" role="alert">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            Tidak ada sparepart mirip yang ditemukan. Coba gambar lain.
                                        </div>
                                    @endif
                                @else
                                {{-- =========================================================
                                     HASIL PENCARIAN TEKS — kartu ringkas (ukuran lebih kecil)
                                     ========================================================= --}}
                                <h4 class="ssis-results-heading">
                                    <i class="bi bi-search"></i>
                                    Hasil Pencarian untuk:
                                    <span class="ssis-query-highlight">{{ $query }}</span>
                                    <span class="ssis-results-count">{{ $results['spareparts']->count() }} hasil</span>
                                </h4>

                                {{-- 1. Tampilkan Hasil Spareparts --}}
                                @if($results['spareparts']->count() > 0)
                                <div class="ssis-card-grid ssis-card-grid-sm">
                                    @foreach ($results['spareparts'] as $sparepart)
                                        @php
                                            $mainImage = $sparepart->images->first();

                                            // Hanya sertakan gambar yang file-nya benar-benar ada,
                                            // agar modal tidak menampilkan gambar rusak.
                                            $validImages = $sparepart->images
                                                ->filter(fn ($img) => \Illuminate\Support\Facades\Storage::disk('public')->exists($img->path))
                                                ->map(fn ($img) => route('storage.media', ltrim($img->path, '/')))
                                                ->values()
                                                ->all();

                                            // Cadangan: logo brand bila tidak ada gambar sparepart.
                                            if (empty($validImages)
                                                && $sparepart->brand
                                                && $sparepart->brand->logo_brand
                                                && \Illuminate\Support\Facades\Storage::disk('public')->exists($sparepart->brand->logo_brand)) {
                                                $validImages = [route('storage.media', ltrim($sparepart->brand->logo_brand, '/'))];
                                            }

                                            // Data untuk modal detail.
                                            $detailData = [
                                                'id'        => $sparepart->id,
                                                'nama'      => $sparepart->nama_barang,
                                                'kode'      => $sparepart->kode_barang,
                                                'brand'     => $sparepart->brand->nama_brand ?? null,
                                                'kategori'  => $sparepart->category->nama_kategori ?? null,
                                                'lokasi'    => $sparepart->lokasi_penyimpanan,
                                                'stok'      => $sparepart->jumlah_stok,
                                                'satuan'    => $sparepart->jumlah_satuan,
                                                'deskripsi' => $sparepart->deskripsi,
                                                'gambar'    => $validImages,
                                            ];
                                        @endphp
                                        <button type="button"
                                                class="ssis-match-card ssis-match-card-sm js-open-detail"
                                                data-sparepart-id="{{ $sparepart->id }}"
                                                data-json="{{ json_encode($detailData, JSON_HEX_APOS | JSON_HEX_QUOT) }}">
                                            {{-- Gambar --}}
                                            <div class="ssis-match-img">
                                                @php
                                                    // Cek ketersediaan file secara fisik agar tidak ada
                                                    // ikon "gambar rusak". Utamakan gambar sparepart,
                                                    // lalu logo brand; bila tidak ada -> tampilkan fallback.
                                                    $imgUrl = null;
                                                    if ($mainImage && \Illuminate\Support\Facades\Storage::disk('public')->exists($mainImage->path)) {
                                                        $imgUrl = route('storage.media', ltrim($mainImage->path, '/'));
                                                    } elseif ($sparepart->brand && $sparepart->brand->logo_brand
                                                        && \Illuminate\Support\Facades\Storage::disk('public')->exists($sparepart->brand->logo_brand)) {
                                                        $imgUrl = route('storage.media', ltrim($sparepart->brand->logo_brand, '/'));
                                                    }
                                                @endphp
                                                @if ($imgUrl)
                                                    <img src="{{ $imgUrl }}"
                                                         alt="{{ $sparepart->nama_barang }}" loading="lazy"
                                                         onerror="this.parentElement.classList.add('is-fallback'); this.remove();">
                                                @else
                                                    <div class="ssis-match-img-fallback">
                                                        <i class="bi bi-gear-wide-connected"></i>
                                                        <span>{{ \Illuminate\Support\Str::limit($sparepart->brand->nama_brand ?? 'Sparepart', 14) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            {{-- Info ringkas --}}
                                            <div class="ssis-match-body">
                                                <h6 class="ssis-match-name">{{ $sparepart->nama_barang }}</h6>
                                                <ul class="ssis-match-meta">
                                                    <li>Kode: {{ $sparepart->kode_barang }}</li>
                                                    <li>Brand: {{ $sparepart->brand->nama_brand ?? 'N/A' }}</li>
                                                    <li>Lokasi: {{ $sparepart->lokasi_penyimpanan ?: 'N/A' }}</li>
                                                </ul>
                                            </div>
                                            {{-- Stok (warna ungu sesuai tema) --}}
                                            <div class="ssis-match-stock">
                                                <span class="badge rounded-pill ssis-stock-badge">
                                                    Stok: {{ $sparepart->jumlah_stok }} {{ $sparepart->jumlah_satuan }}
                                                </span>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                                @endif
                                @endif

                                {{-- 2. Tampilkan Hasil Brands --}}
                                @if($results['brands']->count() > 0)
                                    <h5 class="mt-5 mb-3 ssis-brands-heading"><i class="bi bi-tags"></i> Brands ({{ $results['brands']->count() }})</h5>
                                    <ul class="list-group shadow-sm">
                                        @foreach ($results['brands'] as $brand)
                                            <li class="list-group-item ssis-result-item py-3">
                                                {{-- Logo Brand --}}
                                                <div class="ssis-result-thumb is-logo">
                                                    {{-- **MODIFIKASI BAGIAN INI UNTUK LOGO BRAND** --}}
                                                    @if ($brand->logo_brand)
                                                        <img src="{{ route('storage.media', ltrim($brand->logo_brand, '/')) }}" alt="{{ $brand->nama_brand }}" class="rounded-circle" style="border: 1px solid #ddd;">
                                                    @else
                                                        <i class="bi bi-tag-fill fs-3 text-warning"></i>
                                                    @endif
                                                </div>

                                                {{-- Nama Brand --}}
                                                <div class="ssis-result-main">
                                                    <p class="ssis-title mb-0">{{ $brand->nama_brand }}</p>
                                                    <small class="ssis-result-meta">Dibuat: {{ $brand->created_at->diffForHumans() }}</small>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                                {{-- 3. Tampilkan Pesan Jika Tidak Ada Hasil --}}
                                @if(isset($results) && !empty($query) && $results['spareparts']->count() === 0 && (!isset($results['brands']) || $results['brands']->count() === 0))
                                    <div class="alert alert-warning text-center mt-5" role="alert">
                                        <i class="bi bi-exclamation-triangle me-2"></i> Maaf, tidak ditemukan hasil untuk "<strong>{{ $query }}</strong>". Coba kata kunci lain atau unggah foto lainnya.
                                    </div>
                                @endif
                                                      {{-- Jika tidak ada query atau results (halaman awal) --}}
                                                      @else
                                                          {{-- Halaman awal: panduan singkat (History searching ditampilkan di atas) --}}
                                                          <div class="ssis-hint text-center">
                                                              <i class="bi bi-images"></i>
                                                              <span>Masukkan kata kunci, ambil foto, atau unggah gambar untuk mulai mencari.</span>
                                                          </div>
                                                      @endif
              </div>{{-- /ssis-results-area --}}
            </div>{{-- /row mt-4 --}}
          </div>{{-- /card-body --}}
        </div>{{-- /ssis-search-card --}}
      </div>{{-- /ssis-search-shell --}}
    </div>{{-- /col-12 --}}
  </div>{{-- /row --}}
</div>{{-- /container-fluid --}}
    </section>
  </main>

<a href="#" id="scroll-top" class="scroll-top" aria-label="Scroll to top"><i class="bi bi-arrow-up-short"></i></a>

  <!-- ===== SSIS Loader Overlay (animasi menunggu) ===== -->
  <div id="ssis-loader" class="ssis-loader-overlay" role="status" aria-live="polite">
    <div class="ssis-gear">
      <i class="bi bi-gear-fill gear-large"></i>
      <i class="bi bi-gear-fill gear-small"></i>
    </div>
    <div class="ssis-loader-text">Menyiapkan sistem…</div>
    <div class="ssis-loader-sub">Mohon tunggu sebentar</div>
    <div class="ssis-loader-bar"><span></span></div>
  </div>

  {{-- ===== MODAL DETAIL SPAREPART (muncul saat kartu diklik) ===== --}}
  <div class="ssis-modal" id="ssis-detail-modal" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="ssis-modal-title">
    <div class="ssis-modal-backdrop" data-close-modal></div>
    <div class="ssis-modal-panel" role="document">
      <button type="button" class="ssis-modal-close" data-close-modal aria-label="Tutup">
        <i class="bi bi-x-lg"></i>
      </button>

      <div class="ssis-modal-body">
        {{-- Kolom kiri: gambar besar --}}
        <div class="ssis-modal-media">
          <div class="ssis-modal-main-img">
            <img src="" alt="" id="ssis-modal-img">
          </div>
          <div class="ssis-modal-thumbs" id="ssis-modal-thumbs"></div>
        </div>

        {{-- Kolom kanan: penjelasan lengkap --}}
        <div class="ssis-modal-info">
          <div class="ssis-modal-score" id="ssis-modal-score" hidden>
            <span class="ssis-modal-score-label">Kemiripan</span>
            <span class="ssis-modal-score-value" id="ssis-modal-score-value">0 %</span>
            <div class="ssis-match-bar"><div class="ssis-match-bar-fill" id="ssis-modal-score-bar" style="width:0%"></div></div>
          </div>

          <h3 class="ssis-modal-title" id="ssis-modal-title">Nama Sparepart</h3>
          <span class="ssis-modal-code" id="ssis-modal-code">Kode</span>

          <dl class="ssis-modal-meta">
            <div><dt>Brand</dt><dd id="ssis-modal-brand">N/A</dd></div>
            <div><dt>Kategori</dt><dd id="ssis-modal-category">N/A</dd></div>
            <div><dt>Lokasi Penyimpanan</dt><dd id="ssis-modal-location">N/A</dd></div>
            <div><dt>Stok</dt><dd id="ssis-modal-stock">N/A</dd></div>
          </dl>

          <div class="ssis-modal-desc-wrap">
            <h4 class="ssis-modal-desc-title"><i class="bi bi-card-text"></i> Deskripsi</h4>
            <p class="ssis-modal-desc" id="ssis-modal-desc">Tidak ada deskripsi.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

  <!-- SSIS Theme Script: toggle dark mode + animasi loading -->
  <script src="{{ asset('assets/js/ssis-theme.js') }}"></script>

  <script>
    // Header shadow + tombol scroll-top — sama seperti welcome.
    const header = document.getElementById('ssis-header');
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
                    

{{-- **SCRIPT JAVASCRIPT: Auto-submit gambar + History searching** --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const uploadInput = document.getElementById('upload-image-input');
        const cameraInput = document.getElementById('camera-input');
        const form = document.getElementById('image-search-form');

        function handleFileSelection(event) {
            if (event.target.files.length > 0) {
                const selectedFile = event.target.files[0];
                const preview = document.getElementById('ssis-upload-preview');
                const previewImage = document.getElementById('ssis-upload-preview-image');
                const previewName = document.getElementById('ssis-upload-preview-name');
                if (preview && previewImage && previewName) {
                    if (previewImage.dataset.objectUrl) URL.revokeObjectURL(previewImage.dataset.objectUrl);
                    const objectUrl = URL.createObjectURL(selectedFile);
                    previewImage.src = objectUrl;
                    previewImage.dataset.objectUrl = objectUrl;
                    previewName.textContent = selectedFile.name;
                    preview.hidden = false;
                }

                // Tunda submit agar pratinjau pilihan benar-benar sempat terlihat;
                // setelah server merespons, tampilkan lagi foto yang dikirim.
                // Simpan salinan lokal agar thumbnail tetap ada setelah halaman hasil dimuat.
                if (event.target.dataset.submitPending === 'true') return;
                event.target.dataset.submitPending = 'true';
                const submitImageSearch = function () {
                    // Pastikan hanya input yang dipilih yang mengirim file bernama 'image'
                    uploadInput.name = '';
                    cameraInput.name = '';
                    event.target.name = 'image';

                    // Tampilkan animasi menunggu SEBELUM submit (form.submit() tidak
                    // memicu event 'submit', jadi overlay dipanggil eksplisit).
                    if (window.SSISLoader && typeof window.SSISLoader.imageSearch === 'function') {
                        window.SSISLoader.imageSearch();
                    }

                    form.submit();
                };

                // Simpan salinan lokal untuk mempertahankan pratinjau pada halaman hasil.
                const fileReader = new FileReader();
                fileReader.onload = function (loadEvent) {
                    try {
                        localStorage.setItem(lastImageKey, JSON.stringify({
                            name: selectedFile.name,
                            dataUrl: loadEvent.target.result
                        }));
                    } catch (e) {
                        // Kalau file terlalu besar, pratinjau pilihan tetap terlihat sebelum submit.
                    }
                    window.setTimeout(submitImageSearch, 350);
                };
                fileReader.onerror = function () {
                    window.setTimeout(submitImageSearch, 350);
                };
                fileReader.readAsDataURL(selectedFile);
            }
        }

        // Bila server mengembalikan hasil pencarian, pertahankan thumbnail foto
        // yang dicari di halaman hasil setelah navigasi/reload.
        const lastImageKey = 'ssis-last-image-search';
        const lastImagePreview = document.getElementById('ssis-upload-preview');
        const lastImageElement = document.getElementById('ssis-upload-preview-image');
        const lastImageName = document.getElementById('ssis-upload-preview-name');
        if (lastImagePreview && lastImageElement && lastImageName) {
            try {
                const previousImage = localStorage.getItem(lastImageKey);
                if (previousImage) {
                    const parsedImage = JSON.parse(previousImage);
                    lastImageElement.src = parsedImage.dataUrl;
                    lastImageName.textContent = parsedImage.name || 'Gambar pencarian';
                    lastImagePreview.hidden = false;
                }
            } catch (e) {
                localStorage.removeItem(lastImageKey);
            }
        }

        if (uploadInput) uploadInput.addEventListener('change', handleFileSelection);
        if (cameraInput) cameraInput.addEventListener('change', handleFileSelection);

        /* ==========================================================
           HISTORY SEARCHING (disimpan di localStorage)
           ========================================================== */
        const HISTORY_KEY = 'ssis-search-history';
        const MAX_HISTORY = 8;

        function getHistory() {
            try {
                return JSON.parse(localStorage.getItem(HISTORY_KEY)) || [];
            } catch (e) {
                return [];
            }
        }

        function saveHistory(list) {
            try { localStorage.setItem(HISTORY_KEY, JSON.stringify(list)); } catch (e) {}
        }

        function renderHistory() {
            const wrap = document.getElementById('ssis-history');
            const listEl = document.getElementById('ssis-history-list');
            if (!wrap || !listEl) return;

            const items = getHistory();
            if (!items.length) {
                wrap.hidden = true;
                return;
            }

            listEl.innerHTML = '';
            items.forEach(function (q) {
                const li = document.createElement('li');
                li.className = 'ssis-history-item';

                const icon = document.createElement('i');
                icon.className = 'bi bi-clock-history ssis-history-icon';

                const a = document.createElement('a');
                a.href = '{{ route('search.results') }}?search_query=' + encodeURIComponent(q);
                a.textContent = q;
                a.className = 'ssis-history-link';

                li.appendChild(icon);
                li.appendChild(a);
                listEl.appendChild(li);
            });
            wrap.hidden = false;
        }

        // Pencarian kata kunci menggantikan pencarian gambar sebelumnya.
        const textForm = document.querySelector('.ssis-search-form');
        if (textForm) {
            textForm.addEventListener('submit', function () {
                const input = textForm.querySelector('input[name="search_query"]');
                const q = input ? input.value.trim() : '';
                if (!q) return;
                try { localStorage.removeItem(lastImageKey); } catch (e) {}
                if (lastImagePreview) lastImagePreview.hidden = true;

                let items = getHistory().filter(function (x) {
                    return String(x).toLowerCase() !== q.toLowerCase();
                });
                items.unshift(q);
                saveHistory(items.slice(0, MAX_HISTORY));
            });
        }

        // Bila halaman hasil menampilkan query teks, bersihkan pratinjau gambar lama
        // (termasuk saat halaman hasil di-refresh).
        const currentQuery = @json($query ?? null);
        const isResultPage = @json(isset($results) && !empty($query));
        if (isResultPage && currentQuery) {
            try { localStorage.removeItem(lastImageKey); } catch (e) {}
            if (lastImagePreview) lastImagePreview.hidden = true;
        }
        if (isResultPage && currentQuery) {
            let items = getHistory().filter(function (x) {
                return String(x).toLowerCase() !== String(currentQuery).toLowerCase();
            });
            items.unshift(String(currentQuery));
            saveHistory(items.slice(0, MAX_HISTORY));
        }

        renderHistory();

        /* ==========================================================
           MODAL DETAIL SPAREPART
           Klik kartu hasil -> tampilkan gambar besar + info lengkap.
           ========================================================== */
        const modal = document.getElementById('ssis-detail-modal');

        // Decode entitas HTML (&quot; &amp; dll) sebelum JSON.parse.
        function decodeEntities(str) {
            const txt = document.createElement('textarea');
            txt.innerHTML = str;
            return txt.value;
        }

        function openDetail(data) {
            if (!modal) return;

            const imgEl    = document.getElementById('ssis-modal-img');
            const titleEl  = document.getElementById('ssis-modal-title');
            const codeEl   = document.getElementById('ssis-modal-code');
            const brandEl  = document.getElementById('ssis-modal-brand');
            const catEl    = document.getElementById('ssis-modal-category');
            const locEl    = document.getElementById('ssis-modal-location');
            const stockEl  = document.getElementById('ssis-modal-stock');
            const descEl   = document.getElementById('ssis-modal-desc');
            const thumbsEl = document.getElementById('ssis-modal-thumbs');
            const scoreWrap = document.getElementById('ssis-modal-score');
            const scoreVal  = document.getElementById('ssis-modal-score-value');
            const scoreBar  = document.getElementById('ssis-modal-score-bar');

            const images = Array.isArray(data.gambar) ? data.gambar.filter(Boolean) : [];

            // Gambar utama
            const mainWrap = imgEl.parentElement;
            if (images.length) {
                mainWrap.classList.remove('is-fallback');
                imgEl.style.display = '';
                imgEl.src = images[0];
                imgEl.alt = data.nama || 'Sparepart';
                imgEl.onerror = function () {
                    imgEl.style.display = 'none';
                    mainWrap.classList.add('is-fallback');
                };
            } else {
                // Tidak ada gambar -> tampilkan panel fallback yang rapi.
                imgEl.removeAttribute('src');
                imgEl.style.display = 'none';
                mainWrap.classList.add('is-fallback');

                let ph = mainWrap.querySelector('.ssis-modal-img-fallback');
                if (!ph) {
                    ph = document.createElement('div');
                    ph.className = 'ssis-modal-img-fallback';
                    mainWrap.appendChild(ph);
                }
                ph.innerHTML =
                    '<i class="bi bi-gear-wide-connected"></i>' +
                    '<span>' + (data.brand ? data.brand : 'Sparepart') + '</span>' +
                    '<small>Tidak ada gambar</small>';
            }

            // Thumbnail (bila > 1 gambar)
            thumbsEl.innerHTML = '';
            if (images.length > 1) {
                images.forEach(function (src, i) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'ssis-modal-thumb' + (i === 0 ? ' is-active' : '');
                    const t = document.createElement('img');
                    t.src = src;
                    t.alt = (data.nama || '') + ' ' + (i + 1);
                    btn.appendChild(t);
                    btn.addEventListener('click', function () {
                        imgEl.src = src;
                        thumbsEl.querySelectorAll('.ssis-modal-thumb').forEach(function (x) {
                            x.classList.remove('is-active');
                        });
                        btn.classList.add('is-active');
                    });
                    thumbsEl.appendChild(btn);
                });
            }

            // Info
            titleEl.textContent = data.nama || '-';
            codeEl.textContent = data.kode ? ('Kode: ' + data.kode) : '';
            brandEl.textContent = data.brand || 'N/A';
            catEl.textContent = data.kategori || 'N/A';
            locEl.textContent = data.lokasi || 'N/A';
            stockEl.textContent = (data.stok !== null && data.stok !== undefined)
                ? (data.stok + ' ' + (data.satuan || ''))
                : 'N/A';
            descEl.textContent = (data.deskripsi && String(data.deskripsi).trim())
                ? data.deskripsi
                : 'Tidak ada deskripsi untuk sparepart ini.';

            // Skor kemiripan (hanya pada pencarian gambar)
            if (data.score !== null && data.score !== undefined && data.score !== '') {
                const s = Math.max(0, Math.min(100, parseFloat(data.score)));
                scoreVal.textContent = s.toFixed(0) + ' %';
                scoreBar.style.width = s + '%';
                scoreWrap.hidden = false;
            } else {
                scoreWrap.hidden = true;
            }

            modal.hidden = false;
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('ssis-modal-open');
        }

        function closeDetail() {
            if (!modal) return;
            modal.hidden = true;
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('ssis-modal-open');
        }

        // Pasang pada semua kartu hasil.
        document.querySelectorAll('.js-open-detail').forEach(function (card) {
            card.addEventListener('click', function () {
                let data = {};
                try {
                    data = JSON.parse(decodeEntities(card.getAttribute('data-json') || '{}'));
                } catch (e) {
                    data = {};
                }
                openDetail(data);
            });
        });

        // Tutup modal: tombol X atau klik backdrop.
        document.querySelectorAll('[data-close-modal]').forEach(function (el) {
            el.addEventListener('click', closeDetail);
        });

        // Tutup dengan tombol Esc.
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeDetail();
        });
    });
</script>

</body>
</html>