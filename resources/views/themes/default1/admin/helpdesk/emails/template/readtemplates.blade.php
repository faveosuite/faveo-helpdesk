@extends('themes.default1.admin.layout.admin')

@section('Emails')
active
@stop

@section('emails-bar')
active
@stop

@section('template')
class="active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')

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
	<div class="row">
<div class="col-md-12">
<div class="box box-primary">
    {!! html()->modelForm($contents, 'PATCH', route('template.write', [$template, $path]))->acceptsFiles()->open() !!}
<div class="box-header">

	<h2 class="box-title">{{Lang::get('lang.edit_template')}}: <b><?php $parts = explode('.',$template); $names  = $parts[0]; $name = str_replace('-', ' ', $names); $cname = ucfirst($name); echo $cname?></b></h2><button type="submit" class="btn btn-primary pull-right">Save changes</button>
</div>
<div class="box-body table-responsive">

<!-- check whether success or not -->

@if(Session::has('success'))
    <div class="alert alert-success alert-dismissible">
        <i class="fa  fa-circle-check"></i>
        <b>Success!</b>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
        {{Session::get('success')}}
    </div>
    @endif
    <!-- failure message -->
    @if(Session::has('fails'))
    <div class="alert alert-danger alert-dismissible">
        <i class="fa-solid fa-ban"></i>
        <b>Fail!</b>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
        {{Session::get('fails')}}
    </div>
    @endif

				<!-- <div class="box-body"> -->

                  {!! html()->textarea('templatedata', $contents)->class('form-control') !!}

<!-- </div> -->
  {!! html()->closeModelForm() !!}
@stop
</div><!-- /.box -->
@section('FooterInclude')

@stop
@stop
<!-- /content -->