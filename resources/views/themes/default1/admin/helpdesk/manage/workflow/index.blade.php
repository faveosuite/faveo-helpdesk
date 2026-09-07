@extends('themes.default1.admin.layout.admin')

@section('Manage')
class="nav-link active"
@stop

@section('manage-menu-parent')
class="nav-item menu-open"
@stop

@section('manage-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('workflow')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{{Lang::get('lang.manage')}}</h3>
@stop
<!-- /header -->
<!-- breadcrumbs -->
@section('breadcrumbs')
@stop
<!-- /breadcrumbs -->
<!-- content -->
@section('content')
<!-- check whether success or not -->
@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa  fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {!! Session::get('success') !!}
</div>
@endif
<!-- failure message -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!} !</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {!! Session::get('fails') !!}
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.ticket_workflow') !!}</h3>
        <div class="card-tools d-flex">
            <a href="{!! URL::route('workflow.create') !!}" class="btn btn-secondary btn-tool">
                <span class="fa-solid fa-plus"></span>&nbsp;{!! Lang::get('lang.create') !!}
            </a>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="card-body">
        <table id="workflowTable" class="table table-bordered w-100 d-table">
            <thead>
                <tr>
                    <th>{{Lang::get('lang.name')}}</th>
                    <th>{{Lang::get('lang.status')}}</th>
                    <th>{{Lang::get('lang.order')}}</th>
                    <th>{{Lang::get('lang.rules')}}</th>
                    <th>{{Lang::get('lang.target_channel')}}</th>
                    <th>{{Lang::get('lang.created')}}</th>
                    <th>{{Lang::get('lang.updated')}}</th>
                    <th>{{Lang::get('lang.action')}}</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery('#workflowTable').dataTable({
                    "sPaginationType": "full_numbers",
                    "bProcessing": true,
                    "bServerSide": true,
                    "ajax": {
                        url: "{{route('workflow.list')}}"
                    },
                    "columns": [
                        {data: "name"},
                        {data: "status"},
                        {data: "order"},
                        {data: "rules"},
                        {data: "target"},
                        {data: "Created"},
                        {data: "Updated"},
                        {data: "Actions"}
                    ]
                });
            });
        </script>
    </div>
    <!-- </div> -->
</div>
<!-- /.box -->

<script>
    $(function() {
        $("#example1").DataTable();
        $('#example2').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": false,
            "ordering": true,
            "info": true,
            "autoWidth": false
        });
    });
</script>
@stop
