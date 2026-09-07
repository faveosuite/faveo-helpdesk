@extends('themes.default1.admin.layout.admin')

@section('Emails')
active
@stop

@section('emails-bar')
active
@stop

@section('emails')
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
<div class="box-header">
	<h2 class="box-title">{{Lang::get('lang.templates')}}</h2><a href="{{route('template.create')}}" class="btn btn-primary pull-right">{{Lang::get('lang.create_template')}}</a></div>

<div class="box-body table-responsive no-padding">

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

				<table class="table table-bordered dataTable overflow-hidden">
	<tr>
		<th width="100px">{{Lang::get('lang.name')}}</th>
		<th width="100px">{{Lang::get('lang.status')}}</th>
		<th width="100px">{{Lang::get('lang.in_use')}}</th>
		<th width="100px">{{Lang::get('lang.created')}}</th>
		<th width="100px">{{Lang::get('lang.last_updated')}}</th>
		<th width="100px">{{Lang::get('lang.action')}}</th>
	</tr>
	<!-- Foreach @var templates as @var template -->
		@foreach($templates as $template)
	<tr>

		<!-- Template Name with Link to Edit page along Id -->
		<td><a href="{{route('template.edit',$template->id)}}">{!! $template->name !!}</a></td>
		<!-- template Status : if status==1 active -->
		<td>
			@if($template->status=='1')
				<p style="color:green">Active</p>
			@else
				<p style="color:red">Disable</p>
			@endif
		</td>
		<!-- To show Whether a template in use or not:: TODO -->
		<td></td>
		<!-- Date Added -->
		<td>{!! UTC::usertimezone($template->created_at) !!}</td>
		<!-- Last Updated -->
		<td> {!! UTC::usertimezone($template->updated_at) !!} </td>
		<!-- Deleting Fields -->
		<td>
			{!! html()->form('DELETE', route('template.destroy', [$template->id]))->open() !!}
			<a href="{{route('template.edit',$template->id)}}" class="btn btn-info btn-xs "><i class="fa-solid fa-pen-to-square" style="color:black;"> </i> Edit</a>
			<!-- To pop up a confirm Message -->
				{!! html()->button('<i class="fa-solid fa-trash" style="color:black;"> </i> Delete')->class('btn btn-warning btn-xs ')->attributes(['type' => 'submit', 'onclick' => 'return confirm("Are you sure?")']) !!}
			{!! html()->closeModelForm() !!}
		</td>
		@endforeach
	</tr>

	<!-- Set a link to Create Page -->




</table>

@stop
