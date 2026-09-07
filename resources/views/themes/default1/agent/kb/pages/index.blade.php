@extends('themes.default1.agent.layout.agent')

@extends('themes.default1.agent.layout.sidebar')    

@section('Tools')
class="nav-link active"
@stop

@section('tool')
class="active"
@stop

@section('kb')
class="nav-link active"
@stop

@section('all-pages')
class="nav-link active"
@stop

@section('pages')
class="nav-link active"
@stop

@section('page-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('page-menu-parent')
class="nav-item menu-open"
@stop

@section('PageHeader')
<h1>{{Lang::get('lang.pages')}}</h1>
@stop

@section('content')

<!-- check whether success or not -->
@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa-solid fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('success')}}
</div>
@endif
<!-- failure message -->
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
        <h3 class="card-title">{{Lang::get('lang.pages')}}</h3>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-sm-12">
                <table id="pages-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>{!! Lang::get('lang.name') !!}</th>
                            <th>{!! Lang::get('lang.created') !!}</th>
                            <th>{!! Lang::get('lang.action') !!}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
$(function() {
    $('#pages-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("api.page") }}',
        columns: [
            {data: 'name',    name: 'name'},
            {data: 'Created', name: 'Created'},
            {data: 'Actions', name: 'Actions', orderable: false, searchable: false},
        ]
    });
});
</script>
@stop