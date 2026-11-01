@extends('layouts.master')

@section('breadcrumb-items')
<span class="text-muted fw-light">Master Data / <a href="{{ route('spareparts.index') }}">Sparepart</a> /</span>
@endsection
@section('title', 'Edit Sparepart: ' . $sparepart->kode_barang)

@section('css')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title">Form Edit Sparepart: **{{ $sparepart->nama_barang }}**</h5>
    </div>
    <div class="card-body">
        
        <form method="POST" action="{{ route('spareparts.update', $sparepart->id    ) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row">
                {{-- Field input data sparepart lainnya --}}
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

                <div class="col-12 mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                        id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi', $sparepart->deskripsi) }}</textarea>
                    @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                {{-- Bagian Galeri Gambar (Tampilan Gambar Lama & Input Gambar Baru) --}}
                <div class="col-12 mb-4 border rounded p-3 bg-light">
                    <h6 class="mb-3">Galeri Gambar Saat Ini (Centang untuk Hapus)</h6>
                    <div class="d-flex flex-wrap gap-3 mb-3" id="existing-images-container">
                        @forelse ($sparepart->images as $image)
                            {{-- Ganti Wrapper: Tambahkan ID unik untuk referensi JS --}}
                            <div class="existing-image-wrapper position-relative" id="image-wrapper-{{ $image->id }}">
                                <img src="{{ route('storage.media', ltrim($image->path, '/')) }}" alt="Gambar" 
                                    class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                
                                {{-- Tombol Hapus 'X' (Button) --}}
                                <button type="button" class="btn btn-sm btn-danger p-0 delete-image-btn" 
                                        data-id="{{ $image->id }}"
                                        data-path="{{ route('sparepart.image.delete', $image->id) }}"
                                        title="Hapus Gambar Ini"
                                        style="position: absolute; top: -5px; right: -5px; width: 20px; height: 20px; border-radius: 50%; font-size: 0.8rem; line-height: 1;">
                                    &times;
                                </button>
                            </div>
                        @empty
                            <p class="text-muted mb-0" id="no-existing-images-msg">Belum ada gambar tambahan.</p>
                        @endforelse
                    </div>
                    <h6 class="mb-2 mt-4">Tambah Gambar Baru</h6>
                    {{-- Tombol untuk Membuka File Dialog --}}
                    <button type="button" class="btn btn-outline-secondary d-block w-100" id="btn-select-images">
                        <i class="bx bx-image-add me-2"></i> Pilih Gambar Tambahan (Max 2MB per gambar)
                    </button>

                    <input type="file" class="form-control d-none @error('gambar_tambahan') is-invalid @enderror" 
                        id="gambar_tambahan" name="gambar_tambahan[]" multiple accept="image/*">
                    
                    @error('gambar_tambahan.*')
                        <div class="invalid-feedback d-block">Error upload gambar: {{ $message }}</div>
                    @enderror
                
                    <div class="mt-3 p-3 border rounded" id="image-preview-container" 
                        style="min-height: 50px; display: flex; flex-wrap: wrap; gap: 10px; display: none;">
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                {{-- TOMBOL UPDATE/SIMPAN ADALAH TOMBOL PENGHAPUSAN TERPUSAT --}}
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

    // 1. Logika Klik Tombol Kustom (Gambar Baru)
    if(btnSelectImages) {
        btnSelectImages.addEventListener('click', function() {
            inputGambarTambahan.click();
        });
    }

    // 2. Logika Menghasilkan Pratinjau (Gambar Baru)
    if(inputGambarTambahan) {
        inputGambarTambahan.addEventListener('change', function() {
            renderPreviews();
        });
    }
    
    // =========================================================
    // 5. LOGIKA HAPUS GAMBAR LAMA VIA AJAX (Tombol X)
    // =========================================================
    $(document).on('click', '.delete-image-btn', function(e) {
        e.preventDefault();
        
        const btn = $(this);
        const imageId = btn.data('id');
        const deleteUrl = btn.data('path');
        const wrapper = $('#image-wrapper-' + imageId); // Selector yang merujuk ke elemen div gambar lama
        
        if (confirm('Anda yakin ingin menghapus gambar ini secara permanen?')) {
            $.ajax({
                url: deleteUrl,
                type: 'DELETE', // Menggunakan metode DELETE
                data: {
                    _token: $('input[name="_token"]').val() // Ambil CSRF token dari form utama
                },
                beforeSend: function() {
                    // Beri feedback visual saat loading
                    btn.html('<i class="bx bx-loader bx-spin"></i>').prop('disabled', true);
                },
                success: function(response) {
                    if (response.success) {
                        // Hapus elemen gambar dari DOM dengan animasi fade
                        wrapper.fadeOut(300, function() {
                            $(this).remove();
                            // Periksa dan tampilkan pesan 'Belum ada gambar' jika semua sudah dihapus
                            if ($('#existing-images-container').children('.existing-image-wrapper').length === 0) {
                                $('#existing-images-container').append('<p class="text-muted mb-0" id="no-existing-images-msg">Belum ada gambar tambahan.</p>');
                            }
                        });
                        alert('Gambar berhasil dihapus.'); 
                    } else {
                        alert('Gagal menghapus gambar: ' + response.message);
                        btn.prop('disabled', false).html('&times;');
                    }
                },
                error: function(xhr) {
                    alert('Terjadi kesalahan saat menghubungi server.');
                    btn.prop('disabled', false).html('&times;');
                }
            });
        }
    });
    
    // 3. Fungsi untuk Render Pratinjau (Gambar BARU)
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

                // Tombol Hapus Pratinjau Gambar Baru
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
});
</script>
@endsection