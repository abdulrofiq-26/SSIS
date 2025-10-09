@extends('layouts.master')

@section('title', 'Edit Sparepart: ' . $sparepart->nama_barang)

{{-- Breadcrumb Navigation --}}
@section('breadcrumb-items')
<span class="text-muted fw-light">Inventaris / Spareparts /</span>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Edit Sparepart: {{ $sparepart->nama_barang }}</h4>
    </div>
    <div class="card-body">
        
        {{-- Form: Menggunakan POST dan @method('PUT') untuk UPDATE --}}
        <form action="{{ route('spareparts.update', $sparepart->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            {{-- Notifikasi Error --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Mohon periksa input Anda:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- 1. Kode Barang --}}
            <div class="mb-4">
                <label for="kode_barang" class="form-label">Kode Barang <span class="text-danger">*</span></label>
                <input type="text" 
                       class="form-control @error('kode_barang') is-invalid @enderror" 
                       id="kode_barang" 
                       name="kode_barang" 
                       value="{{ old('kode_barang', $sparepart->kode_barang) }}" 
                       required>
                @error('kode_barang')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            {{-- 2. Nama Barang --}}
            <div class="mb-4">
                <label for="nama_barang" class="form-label">Nama Barang <span class="text-danger">*</span></label>
                <input type="text" 
                       class="form-control @error('nama_barang') is-invalid @enderror" 
                       id="nama_barang" 
                       name="nama_barang" 
                       value="{{ old('nama_barang', $sparepart->nama_barang) }}" 
                       required>
                @error('nama_barang')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            {{-- 3. Brand (Dropdown) --}}
            <div class="mb-4">
                <label for="brand_id" class="form-label">Brand <span class="text-danger">*</span></label>
                <select class="form-select @error('brand_id') is-invalid @enderror" id="brand_id" name="brand_id" required>
                    <option value="">-- Pilih Brand --</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}" 
                            {{ old('brand_id', $sparepart->brand_id) == $brand->id ? 'selected' : '' }}>
                            {{ $brand->nama_brand }}
                        </option>
                    @endforeach
                </select>
                @error('brand_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            {{-- 4. Kategori (Dropdown) --}}
            <div class="mb-4">
                <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                    <option value="">-- Pilih Kategori --</option>
                    @foreach ($categorys as $category) 
                        <option value="{{ $category->id }}" 
                            {{ old('category_id', $sparepart->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->nama_kategori }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- 5. Deskripsi (Textarea) --}}
            <div class="mb-4">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi', $sparepart->deskripsi) }}</textarea>
            </div>
            
            {{-- 6. Jumlah Stok dan Satuan (Inline) --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-4">
                        <label for="jumlah_stok" class="form-label">Jumlah Stok</label>
                        <input type="number" class="form-control @error('jumlah_stok') is-invalid @enderror" id="jumlah_stok" name="jumlah_stok" value="{{ old('jumlah_stok', $sparepart->jumlah_stok) }}" min="0">
                        @error('jumlah_stok')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-4">
                        <label for="jumlah_satuan" class="form-label">Satuan</label>
                        <input type="text" class="form-control @error('jumlah_satuan') is-invalid @enderror" id="jumlah_satuan" name="jumlah_satuan" value="{{ old('jumlah_satuan', $sparepart->jumlah_satuan) }}">
                        @error('jumlah_satuan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- 7. Lokasi Penyimpanan --}}
            <div class="mb-4">
                <label for="lokasi_penyimpanan" class="form-label">Lokasi Penyimpanan</label>
                <input type="text" class="form-control @error('lokasi_penyimpanan') is-invalid @enderror" id="lokasi_penyimpanan" name="lokasi_penyimpanan" value="{{ old('lokasi_penyimpanan', $sparepart->lokasi_penyimpanan) }}">
                @error('lokasi_penyimpanan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            
            {{-- 8. Preview Gambar Saat Ini --}}
            <div class="mb-3">
                <label class="form-label d-block">Gambar Saat Ini</label>
                @if ($sparepart->gambar_utama)
                    <img src="{{ asset('storage/' . $sparepart->gambar_utama) }}" 
                            alt="{{ $sparepart->nama_barang }} Gambar" 
                            class="img-thumbnail border p-1 mb-2" 
                            style="max-height: 100px; max-width: 150px; object-fit: contain;">
                @else
                    <p class="text-muted">Tidak ada gambar terlampir saat ini.</p>
                @endif
            </div>

            {{-- 9. Input Ganti Gambar --}}
            <div class="mb-4">
                <label for="gambar_utama" class="form-label">Ganti Gambar (Maks 20MB)</label>
                <input type="file" 
                       class="form-control @error('gambar_utama') is-invalid @enderror" 
                       id="gambar_utama" 
                       name="gambar_utama"
                       accept="image/*">
                @error('gambar_utama')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Biarkan kosong jika tidak ingin mengganti gambar.</small>
            </div>
            
            <hr class="mt-5 mb-3">

            {{-- Tombol Aksi --}}
            <a href="{{ route('spareparts.index') }}" class="btn btn-outline-secondary me-2">Batal</a>
            <button type="submit" class="btn btn-warning">Update Sparepart</button>
        </form>
    </div>
</div>
@endsection
