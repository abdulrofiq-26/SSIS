@extends('layouts.master')

@section('breadcrumb-items')
<span class="text-muted fw-light">Master Data / Sparepart /</span>
@endsection
@section('title', 'Tambah Sparepart Baru')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title">Form Tambah Sparepart Baru</h5>
    </div>
    <div class="card-body">
        
        {{-- Form akan POST ke route('spareparts.store') --}}
        <form method="POST" action="{{ route('spareparts.store') }}" enctype="multipart/form-data">
            @csrf
            
            <div class="row">
                {{-- Baris 1: Kode dan Nama Barang --}}
                <div class="col-md-6 mb-3">
                    <label for="kode_barang" class="form-label">Kode Barang</label>
                    <input type="text" 
                        class="form-control @error('kode_barang') is-invalid @enderror" 
                        id="kode_barang" 
                        name="kode_barang" 
                        value="{{ old('kode_barang') }}" 
                        placeholder="Contoh: SP-001"
                        required>
                    @error('kode_barang')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="nama_barang" class="form-label">Nama Barang</label>
                    <input type="text" 
                        class="form-control @error('nama_barang') is-invalid @enderror" 
                        id="nama_barang" 
                        name="nama_barang" 
                        value="{{ old('nama_barang') }}" 
                        placeholder="Contoh: Busi Motor"
                        required>
                    @error('nama_barang')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Baris 2: Brand dan Kategori --}}
                <div class="col-md-6 mb-3">
                    <label for="brand_id" class="form-label">Brand</label>
                    {{-- Gunakan kelas select2 untuk styling --}}
                    <select name="brand_id" id="brand_id" class="form-select select2 @error('brand_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Brand --</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                {{ $brand->nama_brand }}
                            </option>
                        @endforeach
                    </select>
                    @error('brand_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="category_id" class="form-label">Kategori</label>
                    <select name="category_id" id="category_id" class="form-select select2 @error('category_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                {{-- Baris 3: Stok, Satuan, Lokasi --}}
                <div class="col-md-4 mb-3">
                    <label for="jumlah_stok" class="form-label">Jumlah Stok</label>
                    <input type="number" 
                        class="form-control @error('jumlah_stok') is-invalid @enderror" 
                        id="jumlah_stok" 
                        name="jumlah_stok" 
                        value="{{ old('jumlah_stok', 0) }}" 
                        placeholder="0">
                    @error('jumlah_stok')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="jumlah_satuan" class="form-label">Satuan</label>
                    <input type="text" 
                        class="form-control @error('jumlah_satuan') is-invalid @enderror" 
                        id="jumlah_satuan" 
                        name="jumlah_satuan" 
                        value="{{ old('jumlah_satuan') }}" 
                        placeholder="Contoh: pcs, unit">
                    @error('jumlah_satuan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="lokasi_penyimpanan" class="form-label">Lokasi Penyimpanan</label>
                    <input type="text" 
                        class="form-control @error('lokasi_penyimpanan') is-invalid @enderror" 
                        id="lokasi_penyimpanan" 
                        name="lokasi_penyimpanan" 
                        value="{{ old('lokasi_penyimpanan') }}" 
                        placeholder="Contoh: Rak A1">
                    @error('lokasi_penyimpanan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Baris 4: Deskripsi --}}
                <div class="col-12 mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                        id="deskripsi" 
                        name="deskripsi" 
                        rows="3">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                {{-- Baris 5: Gambar Utama --}}
                <div class="col-12 mb-3">
                    <label class="form-label">Galeri Gambar Sparepart</label>
                    {{-- Input File Tersembunyi --}}
                    <input type="file" 
                        class="form-control d-none @error('gambar_tambahan') is-invalid @enderror" 
                        id="gambar_tambahan" 
                        name="gambar_tambahan[]" 
                        multiple 
                        accept="image/*"> {{-- Batasi hanya file gambar --}}
                    
                    @error('gambar_tambahan.*')
                        <div class="invalid-feedback d-block">Error upload gambar: {{ $message }}</div>
                    @enderror
                
                    {{-- Wadah Pratinjau Gambar --}}
                    <div class="mt-3 p-3 border rounded" id="image-preview-container" 
                         style="min-height: 50px; display: flex; flex-wrap: wrap; gap: 10px; {{ old('gambar_tambahan') ? '' : 'display: none;' }}">
                        {{-- Pratinjau akan dimasukkan di sini oleh JavaScript --}}
                    </div>
                    {{-- Tombol Custom untuk Membuka File Dialog --}}
                    <button type="button" class="btn btn-outline-primary d-block w-100" id="btn-select-images">
                        <i class="bx bx-image-add me-2"></i> Pilih Gambar Tambahan (Max 2MB per gambar)
                    </button>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary me-2">Simpan Sparepart</button>
                <a href="{{ route('spareparts.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>

    </div>
</div>
@endsection

@section('js')
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script>
// Gunakan jQuery(document).ready untuk memastikan semua resources siap
// Ini akan mencakup inisialisasi Select2 dan logika tombol Anda
jQuery(document).ready(function($) { // '$' di sini adalah alias untuk jQuery
    
    // Inisialisasi Select2
    $('.select2').select2();

    const btnSelectImages = document.getElementById('btn-select-images');
    const inputGambarTambahan = document.getElementById('gambar_tambahan');
    const previewContainer = document.getElementById('image-preview-container');

    // Cek keamanan: Pastikan kedua elemen ada
    if (!btnSelectImages || !inputGambarTambahan) {
        console.error('PENTING: Elemen tombol atau input file tidak ditemukan. Cek ID di HTML.');
        return; // Hentikan script jika elemen tidak ditemukan
    }

    // 1. Logika Klik Tombol Kustom
    btnSelectImages.addEventListener('click', function() {
        inputGambarTambahan.click(); // Memicu klik pada input file tersembunyi
    });

    // 2. Logika Menghasilkan Pratinjau
    inputGambarTambahan.addEventListener('change', function() {
        renderPreviews();
    });
    
    // 3. Fungsi untuk Render Pratinjau
    function renderPreviews() {
        previewContainer.innerHTML = ''; 
        const files = inputGambarTambahan.files;

        if (files.length > 0) {
            previewContainer.style.display = 'flex'; 
        } else {
            previewContainer.style.display = 'none'; 
            return;
        }

        Array.from(files).forEach((file, index) => { 
            const reader = new FileReader();

            reader.onload = (e) => {
                const previewWrapper = document.createElement('div');
                previewWrapper.classList.add('position-relative');
                
                const img = document.createElement('img');
                img.src = e.target.result;
                img.classList.add('img-thumbnail');
                img.style.width = '80px';
                img.style.height = '80px';
                img.style.objectFit = 'cover';

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.setAttribute('data-index', index); 
                
                removeBtn.classList.add('btn-close', 'btn-close-white', 'p-1'); 
                removeBtn.style.position = 'absolute';
                removeBtn.style.top = '2px';
                removeBtn.style.right = '2px';
                removeBtn.style.backgroundColor = 'rgba(255, 0, 0, 0.7)';
                removeBtn.style.borderRadius = '3px';
                removeBtn.setAttribute('title', 'Hapus Gambar');
                
                removeBtn.addEventListener('click', function() {
                    const indexToRemove = parseInt(this.getAttribute('data-index'));
                    removeFileFromInput(indexToRemove);
                });

                previewWrapper.appendChild(img);
                previewWrapper.appendChild(removeBtn);
                previewContainer.appendChild(previewWrapper);
            };

            reader.readAsDataURL(file);
        });
    }
    
    // 4. Fungsi untuk Menghapus File dari Input File
    function removeFileFromInput(indexToRemove) {
        const dt = new DataTransfer();
        const files = inputGambarTambahan.files;

        for (let i = 0; i < files.length; i++) {
            if (i !== indexToRemove) { 
                dt.items.add(files[i]);
            }
        }

        inputGambarTambahan.files = dt.files; 
        renderPreviews(); // Render ulang pratinjau
    }
});
</script>
@endsection