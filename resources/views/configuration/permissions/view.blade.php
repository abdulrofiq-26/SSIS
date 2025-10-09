@extends('layouts.master')
@section('breadcrumb-items')
<span class="text-muted fw-light">Setting /</span>
<span class="text-muted fw-light">Manage Account /</span>
<span class="text-muted fw-light">Permissions /</span>
@endsection
@section('title',  $data->name)

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

    table.dataTable tbody td {
        vertical-align: middle;
    }

    table.dataTable td {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
        word-wrap: break-word;
    }

</style>
@endsection

@section('content')
<div class="card">
    <h5 class="card-header">Users have direct permissions </h5>
    <hr class="my-0">
    <div class="card-datatable table-responsive">
        <table class="table table-hover table-sm" id="datatable" width="100%">
            <thead>
                <tr>
                    <th width="20px" data-priority="1">No</th>
                    <th width="100px">Username</th>
                    <th data-priority="2">Full Name</th>
                    <th width="120px">Email</th>
                    <th width="120px">Gender</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<div class="card mt-4">
    <h5 class="card-header">Roles have permissions </h5>
    <hr class="my-0">
    <div class="card-datatable table-responsive">
        <table class="table table-hover table-sm" id="datatable2" width="100%">
            <thead>
                <tr>
                    <th width="20px" data-priority="1">No</th>
                    <th width="100px" data-priority="3">Color</th>
                    <th data-priority="2">Role name</th>
                    <th >Description</th>
                    <th width="120px">Guard Name</th>
                </tr>
            </thead>
        </table>
    </div>
    <div class="card-footer pt-0">
        <a class="btn btn-outline-secondary" href="{{ route('permissions.index') }}">Back</a>
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
    //swall message notification
    $(document).ready(function () {
        swal(`{!! session('msg') !!}`, {
            icon: "info",
        });
    });
</script>
@endif
<script type="text/javascript">
    $(document).ready(function () {
        var table = $('#datatable').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            language: {
                searchPlaceholder: 'Search..',
                // url: "{{asset('assets/vendor/libs/datatables/id.json')}}"
            },
            ajax: {
                url: "{{ route('permissions.view_users_data', ['id' => $data->id]) }}",
                data: function (d) {
                    d.search = $('#datatable_filter input[type="search"]').val()
                },
            },
            columnDefs: [{
                "defaultContent": "-",
                "targets": "_all"
            }],
            columns: [{
                    render: function (data, type, row, meta) {
                        var no = (meta.row + meta.settings._iDisplayStart + 1);
                        return no;
                    },
                    className: "text-center"
                },
                {
                    render: function (data, type, row, meta) {
                        var html = "<code><span title='" + row.username + "'>" + row.username +
                            "</span></code>";
                        return html;
                    },
                },
                {
                    render: function (data, type, row, meta) {
                        var html = "<span title='" + row.name + "'>" + row.name +
                            "</span>";
                        return html;
                    },
                },
                {
                    render: function (data, type, row, meta) {
                        var html = "<span title='" + row.email + "'>" + row.email +
                            "</span>";
                        return html;
                    },
                },
                {
                    render: function (data, type, row, meta) {
                        if (row.gender != null) {
                            return "<span title='" + row.gender + "'>" + row.gender + "</span>";
                        }

                    },
                }
            ]
        });
    });
    $(document).ready(function () {
        var table = $('#datatable2').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ordering: false,
            searching: false,
            language: {
                searchPlaceholder: 'Search..',
                // url: "{{asset('assets/vendor/libs/datatables/id.json')}}"
            },
            ajax: {
                url: "{{ route('permissions.view_roles_data', ['id' => $data->id]) }}",
                data: function (d) {
                },
            },
            columnDefs: [{
                "defaultContent": "-",
                "targets": "_all"
            }],
            columns: [{
                    render: function (data, type, row, meta) {
                        var no = (meta.row + meta.settings._iDisplayStart + 1);
                        return no;
                    },
                    className: "text-center"
                },
                {
                    render: function (data, type, row, meta) {
                        return '<code class="badge" style="font-size:8pt;background-color:'+row.color+'">' + row.color + '</code>';
                    },
                    className: "text-md-center"
                },
                {
                    render: function (data, type, row, meta) {
                        var html = "<span title='" + row.name + "'>" + row.name +
                            "</span>";
                        return html;
                    },
                },
                {
                    render: function (data, type, row, meta) {
                        return row.description;
                    },
                },
                {
                    render: function (data, type, row, meta) {
                        return row.guard_name;
                    },
                    className: "text-md-center"
                }
            ]
        });
    });
</script>
@endsection
