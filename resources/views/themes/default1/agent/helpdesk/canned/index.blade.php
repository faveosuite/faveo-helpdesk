@extends('themes.default1.agent.layout.agent')

@section('Tools')
class="nav-link active"
@stop

@section('tools-bar')
active
@stop

@section('tool')
class="active"
@stop

@section('tools')
class="nav-link active"
@stop

@section('PageHeader')
<h3>{{Lang::get('lang.tools')}}</h3>
@stop

<!-- content -->
@section('content')
    {{-- Success message --}}
    @if(Session::has('success'))
    <div class="alert alert-success alert-dismissible">
        <i class="fa-solid fa-circle-check"></i>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
        {{Session::get('success')}}
    </div>
    @endif
    {{-- failure message --}}
    @if(Session::has('fails'))
    <div class="alert alert-danger alert-dismissible">
        <i class="fa-solid fa-ban"></i>
        <b>{!! Lang::get('lang.alert') !!}!</b>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
        {{Session::get('fails')}}
    </div>
    @endif
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{!! Lang::get('lang.canned_response') !!}</h3>
            <div class="card-tools d-flex">
                <a href="{{route('canned.create')}}" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus me-1"></i>{!! Lang::get('lang.create_canned_response') !!}
                </a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="canned-table">
                <thead>
                    <tr>
                        <th>{{Lang::get('lang.name')}}</th>
                        <th>{{Lang::get('lang.action')}}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

<script>
    function updateModelTitle(title) {
        $('.modal-title').html(title);
    }
    $(function() {
        $('#canned-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("canned.datatable") }}',
            columns: [
                {data: 'title',   name: 'title'},
                {data: 'Actions', name: 'Actions', orderable: false, searchable: false},
            ]
        });
    });
</script>
@stop
<!-- /content -->
