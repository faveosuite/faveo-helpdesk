@extends('themes.default1.admin.layout.admin')

@section('Tickets')
active
@stop

@section('manage-bar')
active
@stop

@section('labels')
class="active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>Labels</h3>
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

@if(Session::has('success'))
<div class="alert alert-success alert-dismissable">
    <i class="fa fa-check-circle"></i>
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    {{Session::get('success')}}
</div>
@endif
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    {{Session::get('fails')}}
</div>
@endif
@if(Session::has('warn'))
<div class="alert alert-warning alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    {{Session::get('warn')}}
</div>
@endif
<div class="box">
    <div class="box-header">
        <div class="box-title">
            Labels


        </div>
        <a href="{{url('labels/create')}}" class="btn btn-success pull-right"><span class="glyphicon glyphicon-plus"></span> &nbsp; New Label</a>

    </div>
    <div class="box-body">
        {!! 
        Datatable::table()
        ->addColumn('Title','Order','Status','Action')
        ->setUrl(url('labels-ajax'))   
        ->render()
        !!}
    </div>
</div>
@stop