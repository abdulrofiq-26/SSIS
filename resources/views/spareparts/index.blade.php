@extends('layouts.master')

{{-- ⭐ DIUBAH: Menyamakan breadcrumb dengan Brand --}}
@section('breadcrumb-items')
<span class="text-muted fw-light">Master Data /</span>
<span class="text-muted fw-light">Manage Product /</span>
@endsection
@section('title', 'Spareparts')

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
    /* Menggunakan style dari Sparepart (sudah bagus) */
    table.dataTable tbody td { vertical-align: middle; }
    table.dataTable td:nth-child(2) { max-width: 100px; } /* Kode Barang */
    table.dataTable td:nth-child(3) { max-width: 180px; } /* Nama Barang */
    table.dataTable td { white-space: nowrap; text-overflow: ellipsis; overflow: hidden; word-wrap: break-word; }
    .sparepart-img { max-height: 50px; max-width: 80px; object-fit: contain; }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-datatable table-responsive">
        <div class="card-header flex-column flex-md-row pb-0">
            <div class="row w-100">
                <div class="col-12 pt-3 pt-md-0">
                    <div class="col-12">
                        <div class="row">
                            {{-- ⭐ DIUBAH: Penempatan tombol disamakan dengan Brand --}}
                            <div class="offset-md-6 col-md-6 text-md-end text-center pt-3 pt-md-0">
                                <div class="d-flex justify-content-md-end justify-content-center gap-2">
                                    
                                    {{-- Tombol Import (Sama seperti Brand) --}}
                                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importModal">
                                        <i class="bx bx-upload me-sm-2"></i> <span>Import</span>
                                    </button>
                                    
                                    {{-- Tombol Tambah Sparepart (TIDAK PAKAI OFFCANVAS) --}}
                                    <a href="{{ route('spareparts.create') }}" class="btn btn-primary">
                                        <span><i class="bx bx-plus me-sm-2"></i>
                                        <span>Add Sparepart</span></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- ⭐ DIHAPUS: Offcanvas Add New Record tidak ada di sini --}}

        </div>

        {{-- ⭐ DIUBAH: Alert session (success/error) dihapus dari sini, akan ditangani JS (SweetAlert) --}}

        <table class="table table-hover table-sm" id="datatable" width="100%">
            <thead>
                {{-- ⭐ DIUBAH: Kolom disesuaikan untuk DataTables JS --}}
                <tr>
                    <th width="20px" data-priority="1">No</th>
                    <th data-priority="4">Kode</th>
                    <th data-priority="2">Nama Barang</th>
                    <th>Brand</th>
                    <th>Kategori</th>
                    <th>Stok</th>
                    <th>Lokasi</th>
                    <th width="40px">Gambar</th>
                    <th width="40px" data-priority="3">Aksi</th>
                </tr>
            </thead>
            {{-- ⭐ DIUBAH: tbody dikosongkan. DataTables yang akan mengisinya --}}
            <tbody>
                </tbody>
        </table>
        
        {{-- ⭐ DIHAPUS: Pagination Blade dihapus --}}

    </div>
</div>

{{-- Modal Import Excel (Sama seperti Brand, ID diganti) --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Data Sparepart dari Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body pb-0">
                <p class="text-muted">
                    Unduh template untuk memastikan format kolom Anda benar.
                </p>
                <div class="mb-3 d-grid">
                    <a href="{{ route('spareparts.template') }}" class="btn btn-outline-success">
                        <i class="bx bx-download me-2"></i> Download Template Format (.xlsx)
                    </a>
                </div>
                <hr>
                
                <form action="{{ route('spareparts.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="file" class="form-label">Pilih File Excel (.xlsx, .xls)</label>
                        <input class="form-control" type="file" name="file" id="file" required accept=".xlsx, .xls">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Import Data</button>
                </div>
                </form> 
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
{{-- ⭐ DIUBAH: Menggunakan script lengkap dari Brand --}}
<script src="{{asset('assets/vendor/libs/datatables/jquery.dataTables.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables/datatables.responsive.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables/responsive.bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables/datatables.checkboxes.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables/datatables-buttons.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables/buttons.bootstrap5.js')}}"></script>
<script src="{{asset('assets/js/sweetalert.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>

{{-- ⭐ DIGANTI: Notifikasi Session menggunakan SweetAlert (Meniru Brand) --}}
@if(session('success'))
<script type="text/javascript">
    $(document).ready(function () {
        swal(
            'Berhasil!',
            "{{ session('success') }}",
            'success'
        );
    });
</script>
@endif

{{-- ⭐ BLOK UNTUK MENANGANI ERROR (TERMASUK VALIDASI IMPORT) --}}
@if(session('error'))
<script type="text/javascript">
    $(document).ready(function () {
        
        // 1. Ambil pesan error utama
        let errorMessage = "{{ session('error') }}\n\n"; // \n\n = baris baru

        // 2. Cek jika ada detail error validasi dari controller
        @if (session('import_errors'))
            
            // 3. Ubah array PHP 'import_errors' menjadi array JavaScript
            var importErrors = @json(session('import_errors'));
            
            // 4. Tambahkan setiap detail error ke pesan utama
            errorMessage += "Detail Kesalahan:\n";
            importErrors.forEach(function(error) {
                errorMessage += "• " + error + "\n"; // Tambahkan sebagai poin baru
            });
        @endif

        // 5. Tampilkan SweetAlert dengan semua pesan error sebagai TEKS BIASA
        swal({
            title: "Gagal Import!",
            text: errorMessage, // 'text' akan menampilkan string 'errorMessage' apa adanya
            icon: "error"
        });
    });
</script>
@endif


{{-- ⭐ INI BAGIAN UTAMA: Inisialisasi DataTables untuk Sparepart --}}
<script type="text/javascript">
    $(document).ready(function () {
        
        // 1. Inisialisasi DataTables
        var table = $('#datatable').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ordering: true, // Izinkan ordering
            searching: true,
            language: {
                searchPlaceholder: 'Cari Kode, Nama, Brand...',
            },
            
            // URL AJAX DataTables
            ajax: {
                url: "{{ route('spareparts.data') }}", 
                data: function (d) {
                    d.search = $('#datatable_filter input[type="search"]').val()
                },
            },
            
            columnDefs: [{
                "defaultContent": "-",
                "targets": "_all"
            }],
            columns: [
                {
                    // 0. Kolom No
                    render: function (data, type, row, meta) {
                        var no = (meta.row + meta.settings._iDisplayStart + 1);
                        return no;
                    },
                    className: "text-center",
                    orderable: false,
                    searchable: false
                },
                {
                    // 1. Kolom Kode
                    data: 'kode_barang',
                    name: 'kode_barang',
                },
                {
                    // 2. Kolom Nama Barang (Link ke Edit)
                    data: 'nama_barang',
                    name: 'nama_barang',
                    render: function (data, type, row, meta) {
                        var editUrl = "{{ route('spareparts.edit', ':id') }}";
                        editUrl = editUrl.replace(':id', row.idd); // 'idd' dari Controller

                        var html = `<a class="text-primary" title="` + data +
                            `" href="` + editUrl + `">` + data + `</a>`;
                        return html;
                    },
                },
                {
                    // 3. Kolom Brand
                    data: 'nama_brand',
                    name: 'brands.nama_brand', // Sesuaikan dengan 'name' di join controller
                },
                {
                    // 4. Kolom Kategori
                    data: 'nama_kategori',
                    name: 'categories.nama_kategori', // Sesuaikan dengan 'name' di join controller
                },
                {
                    // 5. Kolom Stok (custom)
                    data: 'stok',
                    name: 'jumlah_stok', // Kolom DB untuk sorting
                    className: "text-center"
                },
                {
                    // 6. Kolom Lokasi
                    data: 'lokasi_penyimpanan',
                    name: 'lokasi_penyimpanan',
                },
                {
                    // 7. Kolom Gambar (custom)
                    data: 'gambar', // 'gambar' dari Controller
                    name: 'gambar',
                    render: function (data, type, row, meta) {
                        if (data) {
                            return `<img src="{{ asset('storage') }}/` + data + `" alt="` + row.nama_barang + `" class="sparepart-img">`;
                        } else {
                            return '<span class="text-muted">No Img</span>';
                        }
                    },
                    className: "text-center",
                    orderable: false,
                    searchable: false
                },
                {
                    // 8. Kolom Aksi
                    data: 'action', 
                    name: 'action',
                    render: function (data, type, row, meta) {
                        var html = '<div class="d-flex gap-1">';
                        
                        // Tombol Edit (Sama seperti Brand)
                        var editUrl = "{{ route('spareparts.edit', ':id') }}";
                        editUrl = editUrl.replace(':id', row.idd);
                        html +=
                            `<a href="` + editUrl + `" class="btn btn-icon btn-sm btn-label-warning" title="Edit">` +
                            `<i class="bx bxs-edit"></i></a>`;
                        
                        // Tombol Hapus (Memanggil JS 'DeleteId')
                        html +=
                            `<a href="#" class="btn btn-icon btn-sm btn-label-danger" title="Delete" style="cursor:pointer" onclick="DeleteId(` +
                            row.id + `)" >` + // 'id' (primary key) dari Controller
                            `<i class="bx bx-trash"></i></a>`;
                        
                        html += '</div>';
                        return html;
                    },
                    className: "text-center",
                    orderable: false,
                    searchable: false
                }
            ]
        });

        // 3. Logika untuk error validasi (jika Anda punya modal 'create' di sini, tapi Anda tidak)
        // Dikosongkan karena Anda tidak pakai Offcanvas
    });

    // 4. Fungsi Hapus dengan SweetAlert (Meniru Brand)
    function DeleteId(id) {
        swal({
                title: "Apakah Anda yakin?",
                text: "Setelah dihapus, data sparepart tidak dapat dipulihkan!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: "{{ route('spareparts.delete') }}", // Route baru
                        type: "DELETE",
                        data: {
                            "id": id,
                            "_token": $("meta[name='csrf-token']").attr("content"),
                        },
                        success: function (data) {
                            if (data['success']) {
                                swal(data['message'], {
                                    icon: "success",
                                });
                                $('#datatable').DataTable().ajax.reload(); // Refresh tabel
                            } else {
                                swal(data['message'], { // Tampilkan pesan error (misal: data terkait)
                                    icon: "error",
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            var json = xhr.responseJSON;
                             if(json && json.message) {
                                swal(json.message, { // Tampilkan pesan error dari server
                                    icon: "error",
                                });
                            } else {
                                swal("Gagal menghapus data.", {
                                    icon: "error",
                                });
                            }
                        }
                    })
                }
            })
    }
</script>
@endsection