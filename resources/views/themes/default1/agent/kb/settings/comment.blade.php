@extends('themes.default1.agent.layout.agent')

@extends('themes.default1.agent.layout.sidebar')    
@section('PageHeader')
<h3>{!! Lang::get('lang.comments') !!}</h3>
@stop
@section('comment')
class="nav-link active"
@stop

@section('Tools')
class="nav-link active"
@stop

@section('tool')
class="active"
@stop

@section('kb')
class="nav-link active"
@stop

@section('content')

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
    <b>{!! Lang::get('lang.alert') !!} !</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('fails')}}
</div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{Lang::get('lang.comments-list')}}</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-sm-12">
                <table id="comments-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>{!! Lang::get('lang.details') !!}</th>
                            <th>{!! Lang::get('lang.comment') !!}</th>
                            <th>{!! Lang::get('lang.status') !!}</th>
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
    $('#comments-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("api.comment") }}',
        columns: [
            {data: 'details',  name: 'details'},
            {data: 'comment',  name: 'comment'},
            {data: 'status',   name: 'status'},
            {data: 'Actions',  name: 'Actions', orderable: false, searchable: false},
        ]
    });
});
</script>

@stop