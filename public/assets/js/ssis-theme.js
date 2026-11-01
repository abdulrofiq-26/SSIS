/* ==========================================================================
   SSIS — Script Tema & Animasi Loading
   --------------------------------------------------------------------------
   - Toggle Dark/Light mode (tersimpan di localStorage).
   - Menampilkan overlay animasi "menunggu" saat submit form pencarian
     (teks maupun gambar) dan saat berpindah halaman.
   ========================================================================== */
(function () {
  'use strict';

  var STORAGE_KEY = 'ssis-theme';

  /* ------------------------------------------------------------------
     1. TOGGLE DARK MODE
     ------------------------------------------------------------------ */
  function applyTheme(theme) {
    var isDark = theme === 'dark';
    document.documentElement.classList.toggle('dark-mode', isDark);

    // Perbarui ikon semua tombol toggle yang ada di halaman.
    document.querySelectorAll('#themeToggle i').forEach(function (icon) {
      icon.className = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
    });
  }

  function currentTheme() {
    return document.documentElement.classList.contains('dark-mode') ? 'dark' : 'light';
  }

  function initThemeToggle() {
    var toggle = document.getElementById('themeToggle');
    // Set ikon sesuai kondisi awal.
    applyTheme(currentTheme());

    if (!toggle) return;
    toggle.addEventListener('click', function () {
      var next = currentTheme() === 'dark' ? 'light' : 'dark';
      applyTheme(next);
      try { localStorage.setItem(STORAGE_KEY, next); } catch (e) { }
    });
  }

  /* ------------------------------------------------------------------
     2. OVERLAY ANIMASI MENUNGGU
     ------------------------------------------------------------------ */
  var loader;

  function showLoader(title, subtitle) {
    if (!loader) loader = document.getElementById('ssis-loader');
    if (!loader) return;

    var titleEl = loader.querySelector('.ssis-loader-text');
    var subEl = loader.querySelector('.ssis-loader-sub');
    if (titleEl && title) titleEl.textContent = title;
    if (subEl && subtitle) subEl.textContent = subtitle;

    loader.classList.add('is-active');
    document.body.setAttribute('aria-busy', 'true');
  }

  function hideLoader() {
    if (!loader) loader = document.getElementById('ssis-loader');
    if (!loader) return;
    loader.classList.remove('is-active');
    document.body.removeAttribute('aria-busy');
  }

  /* ------------------------------------------------------------------
     3. PASANG PEMICU LOADER
     ------------------------------------------------------------------ */
  function initLoaders() {
    // --- Form pencarian teks & gambar ---
    document.querySelectorAll('form').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        // Jangan tampilkan loader bila form dihentikan oleh listener lain.
        if (e.defaultPrevented) return;

        var isImageForm = form.id === 'image-search-form';
        var isTextForm = form.querySelector('input[name="search_query"]') !== null;

        if (isImageForm) {
          showLoader('Menganalisis gambar…', 'Mencocokkan dengan basis data sparepart');
          // Beri tahu pengguna bahwa proses bisa memakan waktu.
          setTimeout(function () {
            var sub = loader && loader.querySelector('.ssis-loader-sub');
            if (sub) sub.textContent = 'Mengekstrak fitur visual & mencari kemiripan…';
          }, 2500);
        } else if (isTextForm) {
          showLoader('Mencari sparepart…', 'Mencari berdasarkan kata kunci');
        } else {
          showLoader('Memproses…', 'Mohon tunggu sebentar');
        }

        // Tombol submit: ubah jadi status loading.
        var submitter = form.querySelector('button[type="submit"]');
        if (submitter) {
          submitter.classList.add('is-loading');
          submitter.dataset._origHtml = submitter.innerHTML;
          submitter.innerHTML = '<i class="bi bi-arrow-repeat me-2"></i>' +
            (isImageForm ? 'Mencari…' : 'Mencari…');
        }
      });
    });

    // --- Tautan navigasi internal (berpindah halaman) ---
    document.querySelectorAll('a[href]').forEach(function (link) {
      var href = link.getAttribute('href') || '';
      if (
        href.startsWith('#') ||
        href.startsWith('javascript:') ||
        link.target === '_blank' ||
        link.hasAttribute('download') ||
        href.startsWith('mailto:') ||
        href.startsWith('tel:')
      ) {
        return;
      }
      link.addEventListener('click', function () {
        showLoader('Memuat halaman…', 'Mohon tunggu sebentar');
      });
    });

    // --- Sembunyikan loader bila halaman dipulihkan dari cache (tombol back) ---
    window.addEventListener('pageshow', function (e) {
      if (e.persisted) hideLoader();
    });
  }

  /* ------------------------------------------------------------------
     4. INISIALISASI
     ------------------------------------------------------------------ */
  document.addEventListener('DOMContentLoaded', function () {
    initThemeToggle();
    initLoaders();
  });

  // Sinkronkan tema antar tab yang terbuka.
  window.addEventListener('storage', function (e) {
    if (e.key === STORAGE_KEY && e.newValue) applyTheme(e.newValue);
  });

  /* ------------------------------------------------------------------
     5. API PUBLIK
     Agar skrip inline di halaman (mis. auto-submit form gambar) bisa
     memunculkan overlay loader secara eksplisit.
     ------------------------------------------------------------------ */
  window.SSISLoader = {
    show: showLoader,
    hide: hideLoader,
    textSearch: function () {
      showLoader('Mencari sparepart…', 'Mencari berdasarkan kata kunci');
    },
    imageSearch: function () {
      showLoader('Menganalisis gambar…', 'Mencocokkan dengan basis data sparepart');
      setTimeout(function () {
        var sub = loader && loader.querySelector('.ssis-loader-sub');
        if (sub) sub.textContent = 'Mengekstrak fitur visual & mencari kemiripan…';
      }, 2500);
    }
  };
})();
