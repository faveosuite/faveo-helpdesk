@extends('themes.default1.admin.layout.admin')

@section('Plugins')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{!! Lang::get('lang.plugins') !!}</h3>
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
<div class="alert alert-info alert-dismissible">
    <i class="fa-solid fa-circle-info"></i>
    <span>{!! Lang::get('lang.plugin-info') !!}</span><br/>
    <a href="http://www.faveohelpdesk.com/plugins/" target="_blank">{!!Lang::get('lang.click-here')!!}</a>&nbsp;{!!Lang::get('lang.plugin-info-pro')!!}
</div>
@if (count($errors) > 0)
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b><br/>
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa-solid fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {!!Session::get('success')!!}
</div>
@endif
<!-- failure message -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {!!Session::get('fails')!!}
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.plugins-list') !!}</h3>
        <div class="card-tools d-flex">
            <button type="button" class="btn btn-secondary btn-tool" id="Edit_Ticket" data-bs-toggle="modal" data-bs-target="#Edit">
                <i class="fa-solid fa-plus"></i> {!! Lang::get('lang.add_plugin') !!}
            </button> 

            <div class="modal fade" id="Edit">
                <div class="modal-dialog">
                    <div class="modal-content">  
                        <div class="modal-header">
                            <h5 class="modal-title">{!! Lang::get('lang.add_plugin') !!}</h4>
                        </div>
                        <div class="modal-body">
                            {!! html()->form('POST', url('post-plugin'))->acceptsFiles()->open() !!}
                            <label>{!! Lang::get('lang.plugin') !!} :</label> 
                            <div class="btn bg-olive btn-file" style="color:blue">
                                {!! Lang::get('lang.upload_file') !!}<input type="file" name="plugin">
                            </div>
                        </div><!-- /.modal-content -->   
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="dismis">{!! Lang::get('lang.close') !!}</button>
                            <input type="submit" class="btn btn-primary" value="{!! Lang::get('lang.upload') !!}">
                        </div>
                        {!! html()->closeModelForm() !!}
                    </div>
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->           
        </div>
    </div>
    <div class="card-body">
        <table id="pluginsTable" class="table table-bordered w-100 d-table">
            <thead>
                <tr>
                    <th>{{trans('lang.name')}}</th>
                    <th>{{trans('lang.description')}}</th>
                    <th>{{trans('lang.author')}}</th>
                    <th>{{trans('lang.website')}}</th>
                    <th>{{trans('lang.version')}}</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery('#pluginsTable').dataTable({
                    "sPaginationType": "full_numbers",
                    "bProcessing": true,
                    "bServerSide": true,
                    "ajax": {
                        url: "{{url('getplugin')}}"
                    },
                    "columns": [
                        {data: "name"},
                        {data: "description"},
                        {data: "author"},
                        {data: "website"},
                        {data: "version"}
                    ]
                });
            });
        </script>
    </div>
</div>
@stop
