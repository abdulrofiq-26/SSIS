<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('Log in') }} - {{ config('app.name') }}</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />

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
        /* ====== Variabel Warna Selaras Halaman Welcome ====== */
        :root {
            --font-main: 'Inter', 'Poppins', system-ui, sans-serif;
            --accent: #4154f1;
            --accent-dark: #3040d0;
            --heading: #0d1c6b;
            --text: #4a5568;
            --bg: #f0f4ff;
            --white: #ffffff;
            --radius-btn: 10px;
            --shadow-sm: 0 4px 16px rgba(65, 84, 241, 0.12);
            --shadow-card: 0 20px 60px rgba(65, 84, 241, 0.16), 0 4px 12px rgba(0, 0, 0, 0.04);
            --transition: 0.25s ease;
        }

        html.dark-mode {
            --bg: #0f1629;
            --white: #1e2d4a;
            --heading: #e8eeff;
            --text: #a0aec0;
            --shadow-card: 0 20px 60px rgba(0, 0, 0, 0.45);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(16px, 3vw, 32px);
            font-family: var(--font-main);
            background-color: var(--bg);
            color: var(--text);
            position: relative;
            overflow-x: hidden;
            transition: background-color var(--transition), color var(--transition);
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
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(193,210,255,0.55) 0%, transparent 70%);
            top: -120px; right: -80px;
        }
        .bg-blob-2 {
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(180,200,255,0.4) 0%, transparent 70%);
            bottom: -80px; left: -80px;
        }
        html.dark-mode .bg-blob-1 {
            background: radial-gradient(circle, rgba(65,84,241,0.2) 0%, transparent 70%);
        }
        html.dark-mode .bg-blob-2 {
            background: radial-gradient(circle, rgba(65,84,241,0.15) 0%, transparent 70%);
        }

        /* ====== Tombol Theme Toggle Pojok Atas ====== */
        .theme-toggle-fixed {
            position: fixed;
            top: 20px;
            right: 24px;
            z-index: 20;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--accent);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1rem;
            transition: background var(--transition), transform var(--transition), box-shadow var(--transition);
            box-shadow: 0 2px 10px rgba(65,84,241,0.3);
        }
        .theme-toggle-fixed:hover {
            background: var(--accent-dark);
            transform: translateY(-2px) rotate(-20deg);
            box-shadow: 0 4px 14px rgba(65,84,241,0.4);
        }

        /* ====== Kartu utama (2 kolom) ====== */
        .auth-card {
            width: 100%;
            max-width: 980px;
            background-color: var(--white);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-card);
            position: relative;
            z-index: 1;
            border: 1px solid rgba(65, 84, 241, 0.1);
            transition: background-color var(--transition), border-color var(--transition);
        }

        html.dark-mode .auth-card {
            border-color: rgba(65, 84, 241, 0.25);
        }

        .auth-card .row { min-height: 580px; }

        /* ====== Kolom kiri: form ====== */
        .auth-form-col {
            padding: clamp(28px, 4vw, 52px);
            background: var(--white);
            transition: background-color var(--transition);
        }

        .auth-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
            text-decoration: none;
        }
        .auth-brand .brand-icon svg {
            width: 32px;
            height: 32px;
        }
        .auth-brand .brand-name {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--heading);
            letter-spacing: -0.01em;
        }

        .auth-title {
            font-size: clamp(1.4rem, 2.2vw, 1.85rem);
            font-weight: 800;
            color: var(--heading);
            margin-bottom: 6px;
            letter-spacing: -0.02em;
        }
        .auth-subtitle {
            color: var(--text);
            font-size: 0.92rem;
            margin-bottom: 24px;
            opacity: 0.85;
        }

        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--heading);
            margin-bottom: 6px;
        }

        .form-control.auth-input {
            border-radius: var(--radius-btn);
            border: 1.5px solid rgba(65, 84, 241, 0.18);
            padding: 11px 14px;
            font-size: 0.92rem;
            font-family: var(--font-main);
            background-color: #fff;
            color: var(--heading);
            transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
        }
        .form-control.auth-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(65, 84, 241, 0.18);
            background-color: #fff;
        }

        html.dark-mode .form-control.auth-input {
            background-color: #152238;
            border-color: rgba(65, 84, 241, 0.35);
            color: #e8eeff;
        }
        html.dark-mode .form-control.auth-input:focus {
            background-color: #121c2e;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(65, 84, 241, 0.3);
        }

        .btn-app-primary {
            background: var(--accent);
            border: none;
            color: #fff;
            border-radius: var(--radius-btn);
            padding: 12px 18px;
            font-weight: 700;
            font-size: 0.95rem;
            font-family: var(--font-main);
            width: 100%;
            transition: background var(--transition), transform var(--transition), box-shadow var(--transition);
            box-shadow: 0 2px 10px rgba(65,84,241,0.25);
            letter-spacing: 0.01em;
        }
        .btn-app-primary:hover,
        .btn-app-primary:focus {
            background: var(--accent-dark);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(65,84,241,0.35);
        }

        .link-app {
            color: var(--accent);
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
        }
        .link-app:hover { color: var(--accent-dark); text-decoration: underline; }

        .form-check-input:checked {
            background-color: var(--accent);
            border-color: var(--accent);
        }

        /* ====== Kolom kanan: panel ilustrasi bertema SSIS ====== */
        .auth-info-col {
            background: linear-gradient(145deg, #0d1c6b 0%, #2035a8 50%, #4154f1 100%);
            color: #fff;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: clamp(24px, 3.5vw, 42px);
            overflow: hidden;
        }
        .auth-info-col::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 80% 20%, rgba(255,255,255,0.12) 0%, transparent 60%);
            pointer-events: none;
        }
        .auth-info-col::after {
            content: "";
            position: absolute;
            width: 340px;
            height: 340px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(193,210,255,0.25) 0%, transparent 70%);
            bottom: -80px;
            right: -80px;
            pointer-events: none;
            animation: pulseGlow 4s ease-in-out infinite alternate;
        }
        @keyframes pulseGlow {
            0%   { opacity: 0.4; transform: scale(0.95); }
            100% { opacity: 0.9; transform: scale(1.08); }
        }

        .auth-info-col .info-top {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .auth-info-col .info-top .info-text {
            text-align: left;
        }
        .auth-info-col .info-top .info-title {
            font-weight: 800;
            font-size: 1.1rem;
            margin-bottom: 2px;
            letter-spacing: -0.01em;
        }
        .auth-info-col .info-top .info-desc {
            font-size: 0.78rem;
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.3;
        }
        .btn-back-home {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #fff;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(8px);
            text-decoration: none;
            transition: background var(--transition), transform var(--transition);
            white-space: nowrap;
        }
        .btn-back-home:hover {
            background: rgba(255, 255, 255, 0.25);
            color: #fff;
            transform: translateX(-2px);
        }

        .auth-info-col .info-illustration {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1;
            padding: 24px 0 12px;
        }
        .auth-info-col .info-illustration img {
            max-width: 95%;
            max-height: 320px;
            object-fit: contain;
            filter: drop-shadow(0 15px 35px rgba(0, 0, 0, 0.25));
            animation: floatImg 5s ease-in-out infinite;
        }
        @keyframes floatImg {
            0%, 100% { transform: translateY(0px); }
            50%       { transform: translateY(-10px); }
        }

        .auth-info-col .info-bottom {
            position: relative;
            z-index: 1;
            text-align: center;
            font-size: 0.82rem;
            color: rgba(255, 255, 255, 0.85);
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            padding-top: 14px;
        }

        @media (max-width: 767.98px) {
            body { padding: 12px; }
            .auth-form-col { padding: 30px 20px; }
            .auth-card .row { min-height: auto; }
            .auth-info-col { display: none; }
            .theme-toggle-fixed { top: 12px; right: 12px; }
        }
    </style>
</head>
<body>
    <!-- Decorative background blobs -->
    <div class="bg-blob bg-blob-1" aria-hidden="true"></div>
    <div class="bg-blob bg-blob-2" aria-hidden="true"></div>

    <!-- Theme Toggle -->
    <button type="button" id="themeToggle" class="theme-toggle-fixed" aria-label="Ubah tema" title="Ubah tema terang/gelap">
        <i class="bi bi-moon-stars-fill"></i>
    </button>

    <div class="auth-card">
        <div class="row g-0">
            <!-- ===== Kolom Kiri: Form Login ===== -->
            <div class="col-md-6 auth-form-col d-flex flex-column">
                <a href="/" class="auth-brand">
                    <span class="brand-icon">
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
                    <span class="brand-name">{{ config('app.name', 'SSIS') }}</span>
                </a>

                <h4 class="auth-title">{{ __('Welcome Back') }} 👋</h4>
                <p class="auth-subtitle">{{ __('Silakan masuk ke akun Anda') }}</p>

                @if ($errors->any())
                    <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:13px; border-radius:10px;">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="flex-grow-1 d-flex flex-column">
                    @csrf

                    <!-- Email / Username -->
                    <div class="mb-3">
                        <label class="form-label" for="login_account">{{ __('Email/Username') }}</label>
                        <input id="login_account" type="text" name="login_account"
                               class="form-control auth-input"
                               value="{{ old('login_account') }}" autocomplete="username" autofocus placeholder="Masukkan email atau username">
                    </div>

                    <!-- Password -->
                    <div class="mb-2">
                        <label class="form-label" for="password">{{ __('Password') }}</label>
                        <div class="position-relative">
                            <input id="password" type="password" name="password"
                                   class="form-control auth-input" autocomplete="current-password" placeholder="Masukkan password">
                            <span class="position-absolute top-50 end-0 translate-middle-y me-3"
                                  style="cursor:pointer;color:var(--accent);" onclick="togglePassword()">
                                <i class="bx bx-show" id="togglePasswordIcon"></i>
                            </span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                            <label class="form-check-label" for="remember_me"
                                   style="font-size:13px;color:var(--text);">
                                {{ __('Remember me') }}
                            </label>
                        </div>
                        @if (Route::has('password.request'))
                            <a class="link-app" href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-app-primary mt-auto">
                        {{ __('Log in') }}
                    </button>
                </form>
            </div>

            <!-- ===== Kolom Kanan: Panel Ilustrasi ===== -->
            <div class="col-md-6 auth-info-col">
                <div class="info-top">
                    <div class="info-text">
                        <div class="info-title">{{ config('app.name', 'SSIS') }}</div>
                        <div class="info-desc">{{ __('Smart Sparepart Identification System') }}</div>
                    </div>
                    <a href="/" class="btn-back-home">
                        <i class="bi bi-arrow-left"></i> {{ __('Beranda') }}
                    </a>
                </div>
                <div class="info-illustration">
                    <img src="{{ asset('assets/img/hero-img.png') }}"
                         alt="{{ __('Smart Sparepart Identification') }}">
                </div>
                <div class="info-bottom">
                    <span>Identifikasi suku cadang lebih cepat & cerdas</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Script Tema Dark/Light Sync -->
    <script src="{{ asset('assets/js/ssis-theme.js') }}"></script>

    <script>
        function togglePassword() {
            var input = document.getElementById('password');
            var icon = document.getElementById('togglePasswordIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bx-show');
                icon.classList.add('bx-hide');
            } else {
                input.type = 'password';
                icon.classList.remove('bx-hide');
                icon.classList.add('bx-show');
            }
        }
    </script>
</body>
</html>
