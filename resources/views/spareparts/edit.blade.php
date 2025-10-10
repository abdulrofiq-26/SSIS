@extends('layouts.master')

@section('breadcrumb-items')
<span class="text-muted fw-light">Master Data /Sparepart /</span>
@endsection
@section('title', 'Edit Sparepart: ' . $sparepart->kode_barang)

@section('css')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Edit Sparepart: {{ $sparepart->nama_barang }}</h4>
    </div>
    <div class="card-body">
        
        <form method="POST" action="{{ route('spareparts.update', $sparepart->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row">
                {{-- Detail Produk --}}
                <div class="col-md-6 mb-3">
                    <label for="kode_barang" class="form-label">Kode Barang</label>
                    <input type="text" class="form-control @error('kode_barang') is-invalid @enderror" 
                        id="kode_barang" name="kode_barang" value="{{ old('kode_barang', $sparepart->kode_barang) }}" required>
                    @error('kode_barang') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="nama_barang" class="form-label">Nama Barang</label>
                    <input type="text" class="form-control @error('nama_barang') is-invalid @enderror" 
                        id="nama_barang" name="nama_barang" value="{{ old('nama_barang', $sparepart->nama_barang) }}" required>
                    @error('nama_barang') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Brand dan Kategori --}}
                <div class="col-md-6 mb-3">
                    <label for="brand_id" class="form-label">Brand</label>
                    <select name="brand_id" id="brand_id" class="form-select select2 @error('brand_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Brand --</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ old('brand_id', $sparepart->brand_id) == $brand->id ? 'selected' : '' }}>
                                {{ $brand->nama_brand }}
                            </option>
                        @endforeach
                    </select>
                    @error('brand_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="category_id" class="form-label">Kategori</label>
                    <select name="category_id" id="category_id" class="form-select select2 @error('category_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" 
                                {{ old('category_id', $sparepart->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                {{-- Stok, Satuan, Lokasi --}}
                <div class="col-md-4 mb-3">
                    <label for="jumlah_stok" class="form-label">Jumlah Stok</label>
                    <input type="number" class="form-control @error('jumlah_stok') is-invalid @enderror" 
                        id="jumlah_stok" name="jumlah_stok" value="{{ old('jumlah_stok', $sparepart->jumlah_stok) }}" placeholder="0">
                    @error('jumlah_stok') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="jumlah_satuan" class="form-label">Satuan</label>
                    <input type="text" class="form-control @error('jumlah_satuan') is-invalid @enderror" 
                        id="jumlah_satuan" name="jumlah_satuan" value="{{ old('jumlah_satuan', $sparepart->jumlah_satuan) }}" placeholder="Contoh: pcs, unit">
                    @error('jumlah_satuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="lokasi_penyimpanan" class="form-label">Lokasi Penyimpanan</label>
                    <input type="text" class="form-control @error('lokasi_penyimpanan') is-invalid @enderror" 
                        id="lokasi_penyimpanan" name="lokasi_penyimpanan" value="{{ old('lokasi_penyimpanan', $sparepart->lokasi_penyimpanan) }}" placeholder="Contoh: Rak A1">
                    @error('lokasi_penyimpanan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Deskripsi --}}
                <div class="col-12 mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                        id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi', $sparepart->deskripsi) }}</textarea>
                    @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Bagian Galeri Gambar (Tampilan Gambar Lama & Input Gambar Baru) --}}
                <div class="col-12 mb-4 border rounded p-3 bg-light">
                    <h6 class="mb-3">Galeri Gambar Saat Ini</h6>

                    <div class="d-flex flex-wrap gap-2 mb-3" id="existing-images-container">
                        @forelse ($sparepart->images as $image)
                            <div class="position-relative existing-image-wrapper" data-image-id="{{ $image->id }}">
                                <img src="{{ asset('storage/' . $image->path) }}" alt="Gambar" 
                                    class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                
                                {{-- Tombol Hapus Gambar Lama --}}
                                <button type="button" class="btn-close btn-close-white p-1 remove-existing-btn" 
                                    data-id="{{ $image->id }}"
                                    style="position: absolute; top: 2px; right: 2px; background-color: rgba(255, 0, 0, 0.7); border-radius: 3px;"
                                    title="Hapus Gambar Lama">
                                </button>
                            </div>
                        @empty
                            <p class="text-muted mb-0">Belum ada gambar tambahan.</p>
                        @endforelse
                    </div>

                    <h6 class="mb-2 mt-4">Tambah Gambar Baru</h6>
                    
                    {{-- Tombol Custom untuk Membuka File Dialog --}}
                    <button type="button" class="btn btn-outline-secondary d-block w-100" id="btn-select-images">
                        <i class="bx bx-image-add me-2"></i> Tambah Gambar Baru (Max 2MB per gambar)
                    </button>

                    {{-- Input File Tersembunyi --}}
                    <input type="file" class="form-control d-none @error('gambar_tambahan') is-invalid @enderror" 
                        id="gambar_tambahan" name="gambar_tambahan[]" multiple accept="image/*">
                    
                    <input type="hidden" name="images_to_delete" id="images-to-delete-input" value="">

                    @error('gambar_tambahan.*')
                        <div class="invalid-feedback d-block">Error upload gambar: {{ $message }}</div>
                    @enderror
                
                    {{-- Wadah Pratinjau Gambar BARU --}}
                    <div class="mt-3 p-3 border rounded" id="image-preview-container" 
                         style="min-height: 50px; display: flex; flex-wrap: wrap; gap: 10px; display: none;">
                        {{-- Pratinjau akan dimasukkan di sini oleh JavaScript --}}
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary me-2">Update Sparepart</button>
                <a href="{{ route('spareparts.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>

    </div>
</div>
@endsection

@section('js')
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script>
jQuery(document).ready(function($) {
    
    // Inisialisasi Select2
    $('.select2').select2();

    const btnSelectImages = document.getElementById('btn-select-images');
    const inputGambarTambahan = document.getElementById('gambar_tambahan');
    const previewContainer = document.getElementById('image-preview-container');
    const imagesToDeleteInput = document.getElementById('images-to-delete-input');
    const existingImagesContainer = document.getElementById('existing-images-container');
    let imagesToDelete = []; // Array untuk menyimpan ID gambar yang akan dihapus

    // ===============================================
    // LOGIKA GAMBAR BARU (ADD & PREVIEW)
    // ===============================================

    // 1. Logika Klik Tombol Kustom
    if(btnSelectImages) {
        btnSelectImages.addEventListener('click', function() {
            inputGambarTambahan.click();
        });
    }

    // 2. Logika Menghasilkan Pratinjau (untuk gambar baru)
    if(inputGambarTambahan) {
        inputGambarTambahan.addEventListener('change', function() {
            renderPreviews();
        });
    }
    
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
                removeBtn.setAttribute('title', 'Hapus Gambar Baru');
                
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
    
    // 4. Fungsi untuk Menghapus File dari Input File (gambar baru)
    function removeFileFromInput(indexToRemove) {
        const dt = new DataTransfer();
        const files = inputGambarTambahan.files;

        for (let i = 0; i < files.length; i++) {
            if (i !== indexToRemove) { 
                dt.items.add(files[i]);
            }
        }

        inputGambarTambahan.files = dt.files; 
        renderPreviews(); 
    }


    // ===============================================
    // ⭐ LOGIKA PENGHAPUSAN GAMBAR LAMA (EXISTING) ⭐
    // ===============================================
    
    document.querySelectorAll('.remove-existing-btn').forEach(button => {
        button.addEventListener('click', function() {
            const imageId = this.getAttribute('data-id');
            // ⭐ KOREKSI DI SINI: Gunakan jQuery untuk penghapusan visual
            const wrapper = $(this).closest('.existing-image-wrapper'); // Cari parent terdekat dengan class ini

            if (imageId && wrapper.length) {
                if (confirm('Apakah Anda yakin ingin menghapus gambar ini? Tindakan ini akan diterapkan saat Anda menekan tombol Update.')) {
                    
                    // 1. Tambahkan ID ke array penghapusan
                    imagesToDelete.push(imageId);
                    
                    // 2. Update input tersembunyi
                    imagesToDeleteInput.value = imagesToDelete.join(',');
                    
                    // 3. Hilangkan gambar dari tampilan secara visual
                    wrapper.fadeOut(300, function() { // Gunakan fadeOut untuk efek yang lebih halus
                        $(this).remove(); // Hapus elemen setelah fade out
                        
                        // Cek jika tidak ada gambar lama tersisa
                        if (existingImagesContainer.querySelectorAll('.existing-image-wrapper').length === 0) {
                             if (!document.getElementById('no-existing-images-msg')) {
                                 existingImagesContainer.innerHTML = '<p class="text-muted mb-0" id="no-existing-images-msg">Semua gambar telah dihapus atau ditandai untuk dihapus.</p>';
                             }
                        }
                    });
                }
            }
        });
    });
});
</script>
@endsection
