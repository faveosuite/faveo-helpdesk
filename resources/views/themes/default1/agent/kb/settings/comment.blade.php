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
        <div class="table-responsive">
            <table id="comments-table" class="table table-bordered table-striped align-middle w-100">
                <thead>
                    <tr>
                        <th style="width:22%">{!! Lang::get('lang.author') !!}</th>
                        <th style="width:18%">{!! Lang::get('lang.article') !!}</th>
                        <th>{!! Lang::get('lang.comment') !!}</th>
                        <th style="width:10%">{!! Lang::get('lang.status') !!}</th>
                        <th style="width:1%">{!! Lang::get('lang.action') !!}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<script>
$(function() {
    $('#comments-table').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        ajax: '{{ route("api.comment") }}',
        // Newest comments first (sorted on the hidden created_at column)
        order: [[5, 'desc']],
        columns: [
            {data: 'details',    name: 'kb_comment.name'},
            {data: 'article',    name: 'kb_article.name'},
            {data: 'comment',    name: 'kb_comment.comment'},
            {data: 'status',     name: 'kb_comment.status', searchable: false},
            {data: 'Actions',    name: 'Actions', orderable: false, searchable: false},
            {data: 'created_at', name: 'kb_comment.created_at', visible: false, searchable: false},
        ]
    });
});
</script>

@stop