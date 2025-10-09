@extends('layouts.master')

@section('breadcrumb-items')
<span class="text-muted fw-light">Master Data /</span>
@endsection
@section('title', 'Daftar Sparepart')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/sweetalert2.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
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
                                 <a href="{{ route('spareparts.create') }}" class="btn btn-primary">
                                            <span>
                                                <i class="bx bx-plus me-sm-2"></i>
                                                <span>Tambah Sparepart</span>
                                            </span>
                                        </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
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
                        @if ($sparepart->gambar_utama)
                            {{-- Menggunakan asset('storage/') --}}
                            <img src="{{ asset('storage/' . $sparepart->gambar_utama) }}" alt="{{ $sparepart->nama_barang }} Gambar" class="sparepart-img">
                        @else
                            <span class="text-muted">Tidak ada Gambar</span>
                        @endif
                    </td>
                    <td>
                        {{-- Tambahkan class d-flex dan gap-1 di sini --}}
                        <div class="d-flex gap-1">
                            
                            {{-- Tombol Edit (Link ke halaman edit baru) --}}
                            <a href="{{ route('spareparts.edit', $sparepart->id) }}" class="btn btn-icon btn-sm btn-label-warning" title="Edit">
                                <i class="bx bx-edit"></i>
                            </a>
                            {{-- Tombol Hapus (Konfirmasi via SweetAlert) --}}
                            <form action="{{ route('spareparts.destroy', $sparepart->id) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-sm btn-label-danger delete-btn" title="Hapus">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </form>
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
    document.addEventListener('DOMContentLoaded', function() {
        // Logika untuk menampilkan modal create jika ada error validation
        @if($errors->any())
            // Periksa apakah error berasal dari form create (bisa diidentifikasi dengan adanya old input)
            @if(old('kode_barang') || old('nama_barang')) // Contoh cek sederhana
                var addModal = new bootstrap.Modal(document.getElementById('addSparepartModal'));
                addModal.show();
            @endif
        @endif
        
        // ===================================
        // Logika Konfirmasi Hapus (SweetAlert)
        // ===================================
        document.querySelectorAll('.delete-form').forEach(form => {
            
            form.addEventListener('submit', function(e) {
                e.preventDefault(); 
                
                const currentForm = e.target;
                
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data sparepart ini akan dihapus secara permanen!", 
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        currentForm.submit();
                    }
                });
            });
        });
        
        // Inisialisasi Select2 jika digunakan di modal
        $('.select2').select2({
            dropdownParent: $('#addSparepartModal')
        });
    });
</script>
@endsection