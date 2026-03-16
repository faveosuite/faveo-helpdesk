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

@section('sla')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{{Lang::get('lang.manage')}}</h1>
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
  <b>Success!</b>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
  {!! Session::get('success') !!}
</div>
@endif
<!-- failure message -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissible">
  <i class="fa-solid fa-ban"></i>
  <b>Fail!</b>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
  {!! Session::get('fails') !!}
</div>
@endif

<div class="card card-light">
	
	<div class="card-header">
		<h3 class="card-title">{{Lang::get('lang.SLA_plan')}}</h3>
		<div class="card-tools d-flex">
			<a href="{{route('sla.create')}}" class="btn btn-secondary btn-tool">
				<span class="fa-solid fa-plus"></span>&nbsp;{{Lang::get('lang.create_SLA')}}
			</a>
		</div>
	</div>
	
	<div class="card-body ">

		<table class="table table-bordered dataTable" style="overflow:scroll;">

			<tr>
				<th width="100px">{{Lang::get('lang.name')}}</th>
				<th width="100px">{{Lang::get('lang.status')}}</th>
				<th width="100px">{{Lang::get('lang.grace_period')}}</th>
				<th width="100px">{{Lang::get('lang.created')}}</th>
				<th width="100px">{{Lang::get('lang.last_updated')}}</th>
				<th width="100px">{{Lang::get('lang.action')}}</th>
			</tr>

			<?php
			$default_sla = App\Model\helpdesk\Settings\Ticket::where('id','=','1')->first();
			$default_sla = $default_sla->sla;
			?>

	<!-- Foreach @var$slas as @var sla -->
		@foreach($slas as $sla)
	<tr>
		<!-- sla Name with Link to Edit page along Id -->
		<td><a href="{{route('sla.edit',$sla->id)}}">{!! $sla->name !!}
		@if($sla->id == $default_sla)
			( Default )
		<?php  
			$disable = 'disabled';
		?>
		@else
		<?php  
			$disable = '';
		?>
		@endif
		</a> </td>
		<!-- sla Status : if status==1 active -->
		<td>
			@if($sla->status=='1')
				<span style="color:green">Active</span>
			@else
				<span style="color:red">Disable</span>
			@endif
		</td>
		<!-- To show the sla's Time Period -->
		<td>{!! $sla->grace_period !!}</td>
		<!-- Created Date -->
		<td>{!! UTC::usertimezone($sla->created_at) !!}</td>
		<!-- Last Updated -->
		<td> {!! UTC::usertimezone($sla->updated_at) !!} </td>
		<!-- Deleting Fields -->
		<td>
			{!! html()->form('DELETE', route('sla.destroy', [$sla->id]))->open() !!}
			<a href="{{route('sla.edit',$sla->id)}}" class="btn btn-primary btn-xs"><i class="fa-solid fa-pen-to-square"> </i> {!! Lang::get('lang.edit') !!}</a>
			<!-- To pop up a confirm Message -->
			@if($sla->id == $default_sla)
				{!! html()->button('<i class="fa-solid fa-trash"> </i> '.Lang::get('lang.delete'))->class('btn btn-danger btn-xs '.$disable) !!}
			@else
			{!! html()->button('<i class="fa-solid fa-trash"> </i> '.Lang::get('lang.delete'))->class('btn btn-danger btn-xs')->attributes(['type' => 'submit', 'onclick' => 'return confirm("Are you sure?")']) !!}
			@endif
				
			{!! html()->closeModelForm() !!}
		</td>
		@endforeach
	</tr>
	<!-- Set a link to Create Page -->

</table>

</div>
</div>
@stop
