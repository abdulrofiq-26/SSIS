@extends('layouts.master')

@section('title', 'Edit Brand: ' . $brand->nama_brand)

@section('breadcrumb-items')
<span class="text-muted fw-light">Master Data / Brands /</span>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Edit Brand: {{ $brand->nama_brand }}</h4>
    </div>
    <div class="card-body">
        {{-- Form ini akan menggunakan action ke route brands.update dengan ID Brand --}}
        <form action="{{ route('brands.update', $brand->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            {{-- 1. Input Nama Brand --}}
            <div class="mb-4"> 
                <label for="nama_brand_edit" class="form-label">Nama Brand</label>
                {{-- Gunakan old() untuk nilai yang gagal validasi, jika tidak gunakan data dari database --}}
                <input type="text" 
                       class="form-control @error('nama_brand') is-invalid @enderror" 
                       id="nama_brand_edit" 
                       name="nama_brand" 
                       value="{{ old('nama_brand', $brand->nama_brand) }}" 
                       required>
                @error('nama_brand')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            {{-- 2. Preview dan Input Logo --}}
            <div class="mb-3">
                <label class="form-label d-block">Logo Saat Ini</label>
                @if ($brand->logo_brand)
                    <img src="{{ route('storage.media', ltrim($brand->logo_brand, '/')) }}" 
                         alt="{{ $brand->nama_brand }} Logo" 
                         class="brand-logo-img border p-1 mb-2" 
                         style="max-height: 80px;">
                @else
                    <p class="text-muted">Tidak ada logo terlampir saat ini.</p>
                @endif
            </div>

            <div class="mb-4">
                <label for="logo_brand_edit" class="form-label">Ganti Logo (Maks 2MB)</label>
                <input type="file" 
                       class="form-control @error('logo_brand') is-invalid @enderror" 
                       id="logo_brand_edit" 
                       name="logo_brand">
                @error('logo_brand')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Biarkan kosong jika tidak ingin mengganti logo.</small>
            </div>
            
            <a href="{{ route('brands.index') }}" class="btn btn-outline-secondary me-2">Batal</a>
            <button type="submit" class="btn btn-warning">Update Brand</button>
        </form>
    </div>
</div>
@endsection

@section('style')
<style>
    /* Definisikan CSS untuk logo di sini jika belum ada di master blade */
    .brand-logo-img {
        max-height: 40px;
        max-width: 100px;
        object-fit: contain;
    }
</style>
@endsection