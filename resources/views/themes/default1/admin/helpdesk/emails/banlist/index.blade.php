@extends('themes.default1.admin.layout.admin')

@section('Emails')
class="nav-link active"
@stop

@section('email-menu-parent')
class="nav-item menu-open"
@stop

@section('email-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('ban')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{!! Lang::get('lang.ban_email') !!}</h3>
@stop
<!-- /header -->
<!-- breadcrumbs -->
@section('breadcrumbs')
<ol class="breadcrumb">

</ol>
@stop
<!-- /breadcrumbs -->
<!-- content -->
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
    <b>{!! Lang::get('lang.fails') !!} ! </b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('fails')}}
</div>
@endif

<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{{Lang::get('lang.list_of_banned_emails')}}</h3>
        <div class="card-tools d-flex">
            <a href="{{route('banlist.create')}}" class="btn btn-secondary btn-tool"><i class="fa-solid fa-ban"></i> {{Lang::get('lang.ban_email')}}</a>
        </div>
    </div>
    <div class="card-body">
        <table id="banlistTable" class="table table-bordered w-100 d-table">
            <thead>
                <tr>
                    <th>{{Lang::get('lang.email_address')}}</th>
                    <th>{{Lang::get('lang.last_updated')}}</th>
                    <th>{{Lang::get('lang.action')}}</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@stop

@section('FooterInclude')
<script>
    jQuery(document).ready(function () {
        jQuery('#banlistTable').dataTable({
            "sPaginationType": "full_numbers",
            "bProcessing": true,
            "bServerSide": true,
            "ajax": {
                url: "{{ route('banlist.list') }}"
            },
            "columns": [
                { data: "email" },
                { data: "updated_at" },
                { data: "action", orderable: false, searchable: false }
            ]
        });
    });
</script>
@stop