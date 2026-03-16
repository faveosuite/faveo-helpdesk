@extends('themes.default1.admin.layout.admin')

@section('Themes')
class="nav-link active"
@stop

@section('widget-menu-parent')
class="nav-item menu-open"
@stop

@section('widget-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('widget')
class="nav-link active"
@stop

@section('PageHeader')
<h3>{!! Lang::get('lang.widgets') !!}</h3>
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
    <b>{!! Lang::get('lang.alert') !!} !</b> 
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('fails')}}
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.widget-settings') !!} </h3>
    </div>
    <div class="card-body">
        <table id="widgetsTable" class="table table-bordered w-100 d-table">
            <thead>
                <tr>
                    <th>{{Lang::get('lang.name')}}</th>
                    <th>{{Lang::get('lang.title')}}</th>
                    <th>{{Lang::get('lang.content')}}</th>
                    <th>{{Lang::get('lang.action')}}</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery('#widgetsTable').dataTable({
                    "sPaginationType": "full_numbers",
                    "bProcessing": true,
                    "bServerSide": true,
                    "ajax": {
                        url: "{{url('list-widget')}}"
                    },
                    "columns": [
                        {data: "name"},
                        {data: "title"},
                        {data: "body"},
                        {data: "Actions"}
                    ]
                });
            });
        </script>
    </div>
</div>

@stop
