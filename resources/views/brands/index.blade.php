@extends('layouts.master')

@section('breadcrumb-items')
<span class="text-muted fw-light">Master Data /</span>
<span class="text-muted fw-light">Manage Product /</span>
@endsection
@section('title', 'Brands')

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
    table.dataTable tbody td { vertical-align: middle; }
    table.dataTable td:nth-child(2) { max-width: 150px; }
    table.dataTable td:nth-child(3) { max-width: 100px; }
    table.dataTable td { white-space: nowrap; text-overflow: ellipsis; overflow: hidden; word-wrap: break-word; }
    .brand-logo-img { max-height: 40px; max-width: 80px; object-fit: contain; }
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
                            <div class="offset-md-6 col-md-6 text-md-end text-center pt-3 pt-md-0">
                                <div class="d-flex justify-content-md-end justify-content-center gap-2">
                                    
                                    {{-- Tombol Import (TANPA CAN CHECK) --}}
                                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importBrandModal">
                                        <i class="bx bx-upload me-sm-2"></i> <span>Import</span>
                                    </button>
                                    
                                    {{-- Tombol Tambah Brand (Offcanvas) (TANPA CAN CHECK) --}}
                                    <button class="btn btn-primary" type="button" 
                                        data-bs-toggle="offcanvas"
                                        data-bs-target="#newrecord" 
                                        aria-controls="offcanvasEnd" tabindex="0">
                                        <span><i class="bx bx-plus me-sm-2"></i>
                                        <span>Add Brand</span></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Offcanvas Tambah Brand (TANPA CAN CHECK) --}}
            <div class="offcanvas offcanvas-end @if($errors->any() && old('nama_brand')) show @endif" tabindex="-1" id="newrecord"
                aria-labelledby="offcanvasEndLabel">
                 <div class="offcanvas-header">
                    <h5 id="offcanvasEndLabel" class="offcanvas-title">Add Brand</h5>
                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                        aria-label="Close"></button>
                </div>
                <div class="offcanvas-body my-auto mx-0 flex-grow-1">
                    <form class="add-new-record pt-0 row g-2 fv-plugins-bootstrap5 fv-plugins-framework"
                        id="form-add-new-record" method="POST" action="{{ route('brands.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="col-sm-12 fv-plugins-icon-container">
                            <label class="form-label" for="nama_brand_create">Nama Brand</label>
                            <div class="input-group input-group-merge has-validation">
                                <input type="text" 
                                    class="form-control @error('nama_brand') is-invalid @enderror" 
                                    name="nama_brand" 
                                    id="nama_brand_create" 
                                    placeholder="Nama Brand" 
                                    value="{{ old('nama_brand') }}">
                                @error('nama_brand')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-sm-12 fv-plugins-icon-container">
                            <label class="form-label" for="logo_brand_create">Logo Brand (Maks 2MB)</label>
                            <div class="input-group input-group-merge has-validation">
                                <input type="file" 
                                    class="form-control @error('logo_brand') is-invalid @enderror" 
                                    name="logo_brand" 
                                    id="logo_brand_create">
                                @error('logo_brand')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-sm-12 mt-4">
                            <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1">Submit</button>
                            <button type="reset" class="btn btn-outline-secondary"
                                data-bs-dismiss="offcanvas">Cancel</button>
                        </div>
                        <div></div><input type="hidden">
                    </form>
                </div>
            </div>
        </div>

        <table class="table table-hover table-sm" id="datatable" width="100%">
            <thead>
                <tr>
                    <th width="20px" data-priority="1">No</th>
                    <th data-priority="2">Nama Brand</th>
                    <th width="40px">Logo</th>
                    <th width="40px" data-priority="3">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

{{-- Modal Import Excel --}}
<div class="modal fade" id="importBrandModal" tabindex="-1" aria-labelledby="importBrandModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importBrandModalLabel">Import Data Brand dari Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body pb-0">
                <p class="text-muted">
                    Unduh template untuk memastikan format kolom Anda benar.
                </p>
                <div class="mb-3 d-grid">
                    <a href="{{ route('brands.template') }}" class="btn btn-outline-success">
                        <i class="bx bx-download me-2"></i> Download Template Format (.xlsx)
                    </a>
                </div>
                <hr>
                
                <form action="{{ route('brands.import') }}" method="POST" enctype="multipart/form-data">
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
<script src="{{asset('assets/vendor/libs/datatables/jquery.dataTables.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables/datatables.responsive.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables/responsive.bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables/datatables.checkboxes.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables/datatables-buttons.js')}}"></script>
<script src="{{asset('assets/vendor/libs/datatables/buttons.bootstrap5.js')}}"></script>
<script src="{{asset('assets/js/sweetalert.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>

@if(session('msg'))
<script type="text/javascript">
    $(document).ready(function () {
        swal(`{!! session('msg') !!}`, {
            icon: "info",
        });
    });
</script>
@endif

<script>
    "use strict";
    setTimeout(function () {
        (function ($) {
            "use strict";
            $(".select2-modal").select2({ 
                dropdownParent: $('#newrecord'),
                allowClear: true,
                minimumResultsForSearch: 5
            });
        })(jQuery);
    }, 350);
</script>

<script type="text/javascript">
    $(document).ready(function () {
        // 1. Inisialisasi DataTables
        var table = $('#datatable').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            language: {
                searchPlaceholder: 'Search Brand..',
            },
            
            // URL AJAX DataTables sudah benar
            ajax: {
                url: "{{ route('brands.data') }}", 
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
                    className: "text-center"
                },
                {
                    // 1. Kolom Nama Brand
                    data: 'nama_brand',
                    name: 'nama_brand',
                    render: function (data, type, row, meta) {
                        // FIX UTAMA: Menggunakan route() helper dengan placeholder untuk URL Edit
                        var editUrl = "{{ route('brands.edit', ':id') }}";
                        editUrl = editUrl.replace(':id', row.idd);

                        var html = `<a class="text-primary" title="` + data +
                            `" href="` + editUrl + `">` + data + `</a>`;
                        return html;
                    },
                },
                {
                    // 2. Kolom Logo
                    data: 'logo_brand',
                    name: 'logo_brand',
                    render: function (data, type, row, meta) {
                        if (data) {
                            return `<img src="{{ asset('storage') }}/` + data + `" alt="` + row.nama_brand + ` Logo" class="brand-logo-img">`;
                        } else {
                            return '<span class="text-muted">No Logo</span>';
                        }
                    },
                    className: "text-center"
                },
                {
                    // 3. Kolom Aksi (TAMPILKAN SEMUA UNTUK DEBUGGING)
                    data: 'action', 
                    name: 'action',
                    render: function (data, type, row, meta) {
                        var html = '<div class="d-flex gap-1">';
                        
                        // 1. Tombol Edit (Menggunakan kelas button ikon dari Sparepart Blade)
                        var editUrl = "{{ route('brands.edit', ':id') }}";
                        editUrl = editUrl.replace(':id', row.idd);
                        html +=
                            `<a href="` + editUrl + `" class="btn btn-icon btn-sm btn-label-warning" title="Edit">` +
                            `<i class="bx bxs-edit"></i></a>`; // bx bxs-edit atau bx bx-edit
                        
                        // 2. Tombol Hapus (Menggunakan kelas button ikon dari Sparepart Blade)
                        html +=
                            `<a href="#" class="btn btn-icon btn-sm btn-label-danger" title="Delete" style="cursor:pointer" onclick="DeleteId(` +
                            row.id + `)" >` +
                            `<i class="bx bx-trash"></i></a>`; // bx bx-trash
                        
                        html += '</div>';
                        return html;
                    },
                    className: "text-center"
                }
            ]
        });

        // 3. Logika untuk menampilkan offcanvas jika ada error validasi saat submit
        @if($errors->any() && old('nama_brand'))
            var offcanvas = new bootstrap.Offcanvas(document.getElementById('newrecord'));
            offcanvas.show();
        @endif
    });

    // 4. Fungsi Hapus dengan SweetAlert
    function DeleteId(id) {
        swal({
                title: "Are you sure?",
                text: "Once deleted, brand data can't be recovered!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: "{{ route('brands.delete') }}",
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
                                $('#datatable').DataTable().ajax.reload();
                            } else {
                                swal(data['message'], {
                                    icon: "error",
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                             swal("Gagal menghapus data.", {
                                icon: "error",
                            });
                        }
                    })
                }
            })
    }
</script>
@endsection