    @extends('layouts.master')

    @section('breadcrumb-items')
    <span class="text-muted fw-light">Master Data /</span>
    @endsection
    @section('title', 'Brands')

    @section('css')
    <!-- Pastikan Anda menggunakan helper asset() untuk jalur yang benar -->
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

        table.dataTable td:nth-child(2) {
            max-width: 150px;
        }

        table.dataTable td:nth-child(3) {
            max-width: 150px;
        }

        table.dataTable td {
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
            word-wrap: break-word;
        }

        .brand-logo-img {
            max-height: 40px;
            max-width: 100px;
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
                                <!-- Tombol Tambah Brand -->
                                <div class="col-12 text-md-end text-center pt-3 pt-md-0">
                                    <button class="btn btn-primary" type="button" 
                                        data-bs-toggle="modal"
                                        data-bs-target="#addBrandModal">
                                        <span>
                                            <i class="bx bx-plus me-sm-2"></i>
                                            <span>Add Brand</span>
                                        </span>
                                    </button>
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
            
            <!-- Tabel Data Brand -->
            <table class="table table-hover table-sm mt-2" id="datatable" width="100%">
                <thead>
                    <tr>
                        <th width="20px">No</th>
                        <th>Nama Brand</th>
                        <th>Logo</th>
                        <th width="40px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($brands as $brand)
                    <tr>
                        {{-- Menghitung nomor urut dengan memperhitungkan pagination --}}
                        <td>{{ $loop->iteration + (($brands->currentPage() - 1) * $brands->perPage()) }}</td>
                        <td>{{ $brand->nama_brand }}</td>
                        <td>
                            @if ($brand->logo_brand)
                                {{-- Menggunakan asset('storage/') untuk mengambil gambar yang di-upload --}}
                                <img src="{{ asset('storage/' . $brand->logo_brand) }}" alt="{{ $brand->nama_brand }} Logo" class="brand-logo-img">
                            @else
                                <span class="text-muted">No Logo</span>
                            @endif
                        </td>
                        <td>
                            {{-- Tambahkan class d-flex dan gap-1 di sini --}}
                            <div class="d-flex gap-1">
                                
                                {{-- Tombol Edit (Link ke halaman edit baru) --}}
                                <a href="{{ route('brands.edit', $brand->id) }}" class="btn btn-icon btn-sm btn-label-warning" title="Edit">
                                    <i class="bx bx-edit"></i>
                                </a>
                                {{-- Tombol Hapus (Konfirmasi via SweetAlert) --}}
                                <form action="{{ route('brands.destroy', $brand->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    {{-- HAPUS onclick="return false" di sini --}}
                                    <button type="submit" class="btn btn-icon btn-sm btn-label-danger delete-btn" title="Hapus">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">Belum ada data Brand.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div class="mx-3 mt-3">
                {{ $brands->links() }}
            </div>
            
        </div>
    </div>

    <!-- ============================================= -->
    <!-- MODAL TAMBAH BRAND (CREATE) -->
    <!-- ============================================= -->
    <div class="modal fade @if($errors->any() && old('nama_brand')) show @endif" 
        id="addBrandModal" tabindex="-1" aria-labelledby="addBrandModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBrandModalLabel">Tambah Brand Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form method="POST" action="{{ route('brands.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        
                        {{-- Input Nama Brand --}}
                        {{-- Gunakan mb-4 untuk memberi jarak yang lebih lega sebelum elemen berikutnya --}}
                        <div class="mb-4"> 
                            <label for="nama_brand_create" class="form-label">Nama Brand</label>
                            <input type="text" 
                                    class="form-control @error('nama_brand') is-invalid @enderror" 
                                    id="nama_brand_create" 
                                    name="nama_brand" 
                                    value="{{ old('nama_brand') }}" 
                                    placeholder="Contoh: Honda"
                                    required>
                            @error('nama_brand')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Input Logo Brand --}}
                        <div class="mb-3">
                            <label for="logo_brand_create" class="form-label">Logo Brand (Maks 2MB)</label>
                            <input type="file" 
                                    class="form-control @error('logo_brand') is-invalid @enderror" 
                                    id="logo_brand_create" 
                                    name="logo_brand">
                            @error('logo_brand')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
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
        
        // ... (Logika ADD Modal yang sudah ada) ...

        // ===================================
        // 2. Logika Konfirmasi Hapus (SweetAlert diadaptasi dari contoh Anda)
        // ===================================
        document.querySelectorAll('.delete-form').forEach(form => {
            
            form.addEventListener('submit', function(e) {
                // Hentikan submit bawaan browser
                e.preventDefault(); 
                
                const currentForm = e.target;
                
                // Ambil Nama Brand (Jika ingin ditambahkan di pesan konfirmasi)
                // Karena kita hanya punya brand ID di form, kita bisa improvisasi pesan.
                const brandId = currentForm.action.split('/').pop(); // Mengambil ID dari URL action
                
                // --- Menggunakan Syntax SweetAlert2 (Swal.fire) karena Anda memuatnya ---
                // Jika Anda benar-benar ingin menggunakan syntax swal(...) lama, 
                // Anda harus mengganti library SweetAlert2 dengan swal.js
                
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data brand ini akan dihapus secara permanen!", // Pesan disederhanakan
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Jika dikonfirmasi, kirim form secara manual
                        currentForm.submit();
                    }
                });
                
                // KETERANGAN: Metode ini TIDAK menggunakan AJAX seperti contoh Anda
                // karena kita ingin form melakukan full submit ke controller 
                // agar redirect/pesan sukses dari Laravel bisa dimuat di halaman.
            });
        });
    });
</script>
@endsection

