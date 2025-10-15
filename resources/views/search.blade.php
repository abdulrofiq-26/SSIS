<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>SSIS</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="{{ asset('assets/img/favicon.png') }}" rel="icon">
  <link href="{{ asset('assets/img/apple-touch-icon.png') }}" rel="apple-touch-icon">
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/aos/aos.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet">

  <!-- =======================================================
  * Template Name: FlexStart
  * Template URL: https://bootstrapmade.com/flexstart-bootstrap-startup-template/
  * Updated: Nov 01 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">

      <a href="{{ route('index') }}" class="logo d-flex align-items-center me-auto">
        <!-- Uncomment the line below if you also wish to use an image logo -->
        <img src="{{ asset('assets/img/logo.png') }}" alt="">
        <h1 class="sitename">SSIS</h1>
      </a>

      <nav id="navmenu" class="navmenu">
      </nav>
    </div>
  </header>

  <main class="main"> 
    <!-- Features Section -->
    <section id="features" class="features section" style="padding-top: 100px;">
      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Features</h2>
        <p>Find Your Spare Part<br></p>
      </div><!-- End Section Title -->
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-8" data-aos="fade-up" data-aos-delay="100">
      
              <div class="card shadow-sm border-0">
                  <div class="card-body p-4 p-md-5">
 
                      <form action="{{ route('search.results') }}" method="GET">
                        <div class="input-group mb-3">
                            <input type="text" name="search_query" class="form-control form-control-lg" 
                                  placeholder="Enter part number or name..." aria-label="Part search input"
                                  value="{{ $query ?? '' }}"> <button class="btn btn-primary px-4" type="submit" id="button-text-search">
                                <i class="bi bi-search me-2"></i>Search
                            </button>
                        </div>
                      </form>
                      <div class="row mt-5" data-aos="fade-up" data-aos-delay="200">
                        <div class="col-lg-8 mx-auto">
                            @if(isset($results) && !empty($query))
                                <h4 class="mb-4 text-secondary">Hasil Pencarian untuk: <span class="text-primary fw-bold">{{ $query }}</span></h4>
                                <hr>
                    
                                {{-- 1. Tampilkan Hasil Spareparts --}}
                                @if($results['spareparts']->count() > 0)
                                <h5 class="mt-4 mb-3 text-dark">📦 Spareparts ({{ $results['spareparts']->count() }})</h5>

                                <div class="list-group shadow-sm">
                                    @foreach ($results['spareparts'] as $sparepart)
                                        <div class="list-group-item list-group-item-action d-flex align-items-center py-3">
                                            {{-- Kolom Kiri: Ikon/Gambar --}}
                                            <div class="me-3">
                                                @php
                                                    // ⭐ KOREKSI: Ambil gambar utama dari relasi images
                                                    $mainImage = $sparepart->images->first();
                                                @endphp
                                                
                                                @if ($mainImage)
                                                    {{-- 1. Gambar Sparepart ADA: Tampilkan gambar sparepart --}}
                                                    <img src="{{ asset('storage/' . $mainImage->path) }}" 
                                                        alt="{{ $sparepart->nama_barang }}" 
                                                        class="rounded" 
                                                        style="width: 50px; height: 50px; object-fit: cover;">
                                                
                                                @elseif ($sparepart->brand && $sparepart->brand->logo_brand)
                                                    {{-- 2. Gambar Sparepart TIDAK ADA, tapi Logo Brand ADA: Tampilkan logo brand --}}
                                                    <img src="{{ asset('storage/' . $sparepart->brand->logo_brand) }}" 
                                                        alt="{{ $sparepart->brand->nama_brand }}" 
                                                        class="rounded-circle" 
                                                        style="width: 50px; height: 50px; object-fit: contain; border: 1px solid #ddd;">
                                                        
                                                        @else
                                                            {{-- 3. Keduanya TIDAK ADA: Tampilkan ikon default Bootstrap --}}
                                                            <i class="bi bi-gear-fill fa-2x text-info"></i>
                                                        @endif
                                            </div>
                                            {{-- Kolom Tengah: Detail Barang --}}
                                            <div class="flex-grow-1">
                                                <p class="mb-1 fw-bold text-truncate" style="max-width: 90%;">
                                                    {{ $sparepart->nama_barang }} 
                                                </p>
                                                <small class="text-muted">
                                                    Kode: {{ $sparepart->kode_barang }} | 
                                                    Brand: {{ $sparepart->brand->nama_brand ?? 'N/A' }} | 
                                                    Kategori: {{ $sparepart->category->nama_category ?? 'N/A' }}
                                                </small>
                                            </div>
                                        
                                            {{-- Kolom Kanan: Status Stok dan Lokasi --}}
                                            <div class="text-end ms-3">
                                                <span class="badge rounded-pill {{ $sparepart->jumlah_stok > 0 ? 'bg-success' : 'bg-danger' }} mb-1">
                                                    Stok: {{ $sparepart->jumlah_stok }} {{ $sparepart->jumlah_satuan }}
                                                </span>
                                                <small class="d-block text-secondary">
                                                    Lokasi: {{ $sparepart->lokasi_penyimpanan }}
                                                </small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @endif

                                {{-- 2. Tampilkan Hasil Brands --}}
                                @if($results['brands']->count() > 0)
                                    <h5 class="mt-5 mb-3 text-dark">⭐ Brands ({{ $results['brands']->count() }})</h5>
                                    <ul class="list-group shadow-sm">
                                        @foreach ($results['brands'] as $brand)
                                            <li class="list-group-item d-flex align-items-center py-3">
                                                {{-- Logo Brand --}}
                                                <div class="me-3">
                                                    {{-- **MODIFIKASI BAGIAN INI UNTUK LOGO BRAND** --}}
                                                    @if ($brand->logo_brand)
                                                        <img src="{{ asset('storage/' . $brand->logo_brand) }}" alt="{{ $brand->nama_brand }}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: contain; border: 1px solid #ddd;">
                                                    @else
                                                        <i class="fas fa-tag fa-2x text-warning"></i>
                                                    @endif
                                                </div>
                                                
                                                {{-- Nama Brand --}}
                                                <div class="flex-grow-1">
                                                    <p class="mb-0 fw-bold">{{ $brand->nama_brand }}</p>
                                                </div>
                    
                                                {{-- Informasi Tambahan --}}
                                                <small class="text-muted">
                                                    Dibuat: {{ $brand->created_at->diffForHumans() }}
                                                </small>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                                {{-- 3. Tampilkan Pesan Jika Tidak Ada Hasil --}}
                                @if(isset($results) && !empty($query))
                                    <div class="alert alert-warning text-center mt-5" role="alert">
                                        <i class="fas fa-exclamation-triangle me-2"></i> Maaf, tidak ditemukan hasil untuk "**{{ $query }}**". Coba kata kunci lain.
                                    </div>
                                @endif
                            {{-- Jika tidak ada query atau results (halaman awal) --}}
                            @else
                                <div class="alert alert-info text-center mt-5" role="alert">
                                    Masukkan kata kunci pencarian Anda untuk menampilkan hasil.
                                </div>
                            @endif
                        </div>
                    </div>
                    </div>
                    <div class="d-flex align-items-center my-4">
                        <hr class="flex-grow-1">
                        <span class="mx-3 text-muted fw-light">OR</span>
                        <hr class="flex-grow-1">
                    </div>
                    {{-- Letakkan ini di atas form pencarian gambar --}}
                    @if ($errors->any())
                    <div class="alert alert-danger text-center mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        {{-- Kode ini akan menampilkan SEMUA pesan error yang ada --}}
                        @foreach ($errors->all() as $error)
                            <span>{{ $error }}</span>
                        @endforeach
                    </div>
                    @endif
                    {{-- **FORM PENCARIAN GAMBAR BARU** --}}
                    <form action="{{ route('search.image') }}" method="POST" enctype="multipart/form-data" id="image-search-form">
                        @csrf
                        
                        {{-- Tambahkan penanganan error validasi gambar di sini --}}
                        @if ($errors->has('image') || $errors->has('image_search'))
                            <div class="alert alert-danger text-center mb-3" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i> 
                                {{ $errors->first('image') ?? $errors->first('image_search') }}
                            </div>
                        @endif
                    
                        <div class="row g-3">
                            {{-- Tombol Upload File --}}
                            <div class="col-md-6">
                                <label for="upload-image-input" class="btn btn-outline-secondary w-100 p-3 d-flex flex-column align-items-center">
                                    <i class="bi bi-upload fs-2 mb-2"></i>
                                    <span class="fw-semibold">Upload Image</span>
                                </label>
                                {{-- Input File Utama. NAMA HARUS 'image' sesuai di Controller --}}
                                <input type="file" id="upload-image-input" class="d-none" accept="image/*">
                            </div>
                    
                            {{-- Tombol Gunakan Kamera --}}
                            <div class="col-md-6">
                                <label for="camera-input" class="btn btn-outline-secondary w-100 p-3 d-flex flex-column align-items-center">
                                    <i class="bi bi-camera-fill fs-2 mb-2"></i>
                                    <span class="fw-semibold">Use Camera</span>
                                </label>
                                {{-- Input file terpisah untuk kamera (bisa digabungkan, tapi ini lebih spesifik) --}}
                                <input type="file" id="camera-input" class="d-none" accept="image/*" capture="environment">
                            </div>
                        </div>
                    </form>
                    
<a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/aos/aos.js') }}"></script>
    <script src="{{ asset('assets/vendor/glightbox/js/glightbox.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/main-copy.js') }}"></script> {{-- Sesuaikan nama file ini --}}
                    

{{-- **SCRIPT JAVASCRIPT untuk Otomatis Submit Form (Disarankan di sini)** --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const uploadInput = document.getElementById('upload-image-input');
        const cameraInput = document.getElementById('camera-input');
        const form = document.getElementById('image-search-form');

        function handleFileSelection(event) {
            if (event.target.files.length > 0) {
                // KOREKSI UTAMA: Reset nama agar hanya input yang dipilih yang mengirim file 'image'
                uploadInput.name = '';
                cameraInput.name = '';
                
                // Beri nama 'image' pada input yang dipilih
                event.target.name = 'image'; 
                
                form.submit();
            }
        }

        uploadInput.addEventListener('change', handleFileSelection);
        cameraInput.addEventListener('change', handleFileSelection);
    });
</script>

</body>
</html>