@extends('themes.default1.admin.layout.admin')

@section('Settings')
class="nav-link active"
@stop

@section('settings-menu-parent')
class="nav-item menu-open"
@stop

@section('settings-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('languages')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{{ Lang::get('lang.settings') }}</h3>
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
    <i class="fa  fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('success')}} @if(Session::has('link'))<a href="{{url(Session::get('link'))}}">{{Lang::get('lang.enable_lang')}}</a> @endif
</div>
@endif
<!-- failure message -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('fails')}}
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{{ Lang::get('lang.language-settings') }}</h3>
        <div class="card-tools d-flex">
            <a href="{{route('download')}}" title="click here to download template file" class="btn btn-secondary btn-tool">
                <i class="fa-solid fa-download"></i> {{Lang::get('lang.download')}} 
            </a> 
            <a href="{{route('add-language')}}" class="btn btn-secondary btn-tool"><i class="fa-solid fa-plus"></i> {{Lang::get('lang.add')}}</a>
        </div>
    </div>
    <div class="card-body">
        <table id="lang" class="table table-bordered w-100 d-table">
            <thead>
                <tr>
                    <th>{{Lang::get('lang.language')}}</th>
                    <th>{{Lang::get('lang.native-name')}}</th>
                    <th>{{Lang::get('lang.iso-code')}}</th>
                    <th>{{Lang::get('lang.system-language')}}</th>
                    <th>{{Lang::get('lang.Action')}}</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery('#lang').dataTable({
                    "sPaginationType": "full_numbers",
                    "bProcessing": true,
                    "bServerSide": true,
                    "ajax": {
                        url: "{{route('getAllLanguages')}}"
                    },
                    "columns": [
                        {data: "language"},
                        {data: "name"},
                        {data: "id"},
                        {data: "status"},
                        {data: "Action"}
                    ]
                });
            });
        </script>
    </div>
</div>
@stop