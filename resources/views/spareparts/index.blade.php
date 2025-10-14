@extends('layouts.master')

@section('breadcrumb-items')
<span class="text-muted fw-light">Master Data /</span>
@endsection
@section('title', 'Daftar Sparepart')

@section('css')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/sweetalert2.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
@endsection

@section('style')
<style>
    table.dataTable tbody td {
        vertical-align: middle;
    }

    /* Sesuaikan lebar kolom agar lebih proporsional untuk sparepart */
    table.dataTable td:nth-child(2) {
        max-width: 100px; /* Kode Barang */
    }

    table.dataTable td:nth-child(3) {
        max-width: 180px; /* Nama Barang */
    }

    table.dataTable td {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
        word-wrap: break-word;
    }

    .sparepart-img {
        max-height: 50px;
        max-width: 80px;
        object-fit: contain;
    }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-datatable table-responsive">
        <div class="card-header flex-column flex-md-row pb-0">
            <div class="row">
                <div class="col-12 pt-3 pt-md-0">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-12 text-md-end text-center pt-3 pt-md-0">
                                
                                {{-- ⭐ PERBAIKAN: GABUNGKAN DUA TOMBOL DALAM SATU DIV FLEKSIBEL ⭐ --}}
                                <div class="d-flex justify-content-end gap-2">
                                    
                                    {{-- Tombol Import Excel --}}
                                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importModal">
                                        <i class="bx bx-upload me-sm-2"></i> <span>Import</span>
                                    </button>
                                    
                                    {{-- Tombol Tambah Sparepart --}}
                                    <a href="{{ route('spareparts.create') }}" class="btn btn-primary">
                                        <span>
                                            <i class="bx bx-plus me-sm-2"></i>
                                            <span>Tambah Sparepart</span>
                                        </span>
                                    </a>
                                </div>
                                {{-- ⭐ AKHIR PERBAIKAN ⭐ --}}

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
        {{-- Modal/Offcanvas Form Import (DIPOSISIKAN DI SINI UNTUK KETERBACAAN) --}}
        <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">Import Data Sparepart dari Excel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body pb-0">
                        <p class="text-muted">
                            Untuk menghindari kesalahan format, silakan unduh template Excel resmi.
                        </p>
                        {{-- Tombol Download Template --}}
                        <div class="mb-3 d-grid">
                            <a href="{{ route('spareparts.template') }}" class="btn btn-outline-success">
                                <i class="bx bx-download me-2"></i>Template Add Sparepart (.xlsx)
                            </a>
                        </div>
                        <hr>
                        
                        <form action="{{ route('spareparts.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="file" class="form-label">Pilih File Excel (.xlsx, .xls)</label>
                                <input class="form-control" type="file" name="file" id="file" required accept=".xlsx, .xls">
                            </div>
                            <p class="text-muted small">Pastikan kolom header Excel Anda **cocok** dengan template yang diunduh.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success">Import Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{-- Akhir Modal Import --}}
        
        {{-- Tampilkan pesan ERROR dari Import --}}
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible mx-3 mt-3" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                
                {{-- Tampilkan detail error dari Import Class --}}
                @if (session('import_errors'))
                    <ul class="mt-2 mb-0">
                        @foreach (session('import_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif
        
        {{-- Tampilkan pesan SUKSES --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible mx-3 mt-3" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        <table class="table table-hover table-sm mt-2" id="datatable" width="100%">
            <thead>
                <tr>
                    <th width="20px">No</th>
                    <th>Kode</th>
                    <th>Nama Barang</th>
                    <th>Brand</th>
                    <th>Kategori</th>
                    <th>Stok</th>
                    <th>Lokasi</th>
                    <th>Gambar</th>
                    <th width="40px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($spareparts as $sparepart)
                <tr>
                    {{-- Menghitung nomor urut dengan memperhitungkan pagination --}}
                    <td>{{ $loop->iteration + (($spareparts->currentPage() - 1) * $spareparts->perPage()) }}</td>
                    <td>{{ $sparepart->kode_barang }}</td>
                    <td>{{ $sparepart->nama_barang }}</td>
                    <td>{{ $sparepart->brand->nama_brand ?? 'N/A' }}</td>
                    <td>{{ $sparepart->category->nama_kategori ?? 'N/A' }}</td>
                    <td>{{ $sparepart->jumlah_stok }} {{ $sparepart->jumlah_satuan }}</td>
                    <td>{{ $sparepart->lokasi_penyimpanan ?? '-' }}</td>
                    <td>
                        @php
                            // Ambil gambar pertama dari relasi images
                            $mainImage = $sparepart->images->first();
                        @endphp

                        @if ($mainImage)
                            <img src="{{ asset('storage/' . $mainImage->path) }}" alt="{{ $sparepart->nama_barang }} Gambar" class="sparepart-img">
                        @else
                            <span class="text-muted">Tidak ada Gambar</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            
                            {{-- Tombol Edit --}}
                            <a href="{{ route('spareparts.edit', $sparepart->id) }}" class="btn btn-icon btn-sm btn-label-warning" title="Edit">
                                <i class="bx bx-edit"></i>
                            </a>
                            
                            {{-- Tombol Hapus (Konfirmasi Browser) --}}
                            <a href="#" class="btn btn-icon btn-sm btn-label-danger" title="Hapus" 
                                onclick="event.preventDefault(); 
                                         if(confirm('Apakah Anda yakin ingin menghapus sparepart ini?')) {
                                             document.getElementById('delete-sparepart-{{ $sparepart->id }}').submit();
                                         }">
                                <i class="bx bx-trash"></i>
                            </a>
                            {{-- Form Tersembunyi untuk DELETE Method --}}
                            <form id="delete-sparepart-{{ $sparepart->id }}" 
                                action="{{ route('spareparts.destroy', $sparepart->id) }}" 
                                method="POST" 
                                style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                            {{-- Akhir Form --}}
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center">Belum ada data Sparepart.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="mx-3 mt-3">
            {{ $spareparts->links() }}
        </div>
        
    </div>
</div>
@endsection

@section('js')
<script src="{{asset('assets/vendor/libs/datatables/jquery.dataTables.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables/datatables.responsive.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables/responsive.bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables/datatables.checkboxes.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables/datatables-buttons.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables/buttons.bootstrap5.js')}}"></script>
<script src="{{asset('assets/js/sweetalert.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>

<script>
    // Hanya sisakan logika non-hapus yang Anda butuhkan
    document.addEventListener('DOMContentLoaded', function() {
        
        // Logika untuk menampilkan modal create jika ada error validation (dibiarkan)
        @if($errors->any() && (old('kode_barang') || old('nama_barang')))
            var addModal = new bootstrap.Modal(document.getElementById('addSparepartModal'));
            addModal.show();
        @endif
        
        // Inisialisasi Select2 jika digunakan di modal
        $('.select2').select2({
            dropdownParent: $('#addSparepartModal')
        });
    });
</script>
@endsection