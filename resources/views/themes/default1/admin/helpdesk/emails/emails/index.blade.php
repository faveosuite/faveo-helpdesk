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

@section('emails')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h1>{{Lang::get('lang.emails')}}</h1>
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
<div class="alert alert-success alert-dismissable">
  <i class="fa  fa-check-circle"></i>
  <b>Success!</b>
  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
  {{Session::get('success')}}
</div>
@endif
<!-- failure message -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissable">
  <i class="fa fa-ban"></i>
  <b>Fail!</b>
  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
  {{Session::get('fails')}}
</div>
@endif

<div class="card card-light">

	<div class="card-header">
		
		<h3 class="card-title">{!! Lang::get('lang.emails') !!}</h3>

		<div class="card-tools">
			
			<a href="{{route('emails.create')}}" class="btn btn-default btn-tool">
				<span class="fas fa-plus"></span>&nbsp;{{Lang::get('lang.create_email')}}
			</a>
		</div>	
	</div>

	<div class="card-body">
	   <?php
		   $default_system_email = App\Model\helpdesk\Settings\Email::where('id', '=', '1')->first();
		   if($default_system_email->sys_email) {
		    	$default_email = $default_system_email->sys_email;
		   } else {
		    	$default_email = null;
		   }
	   ?>
    	<!-- table -->
		<table class="table table-bordered dataTable" style="overflow:hidden;">
			<tr>
				<th width="100px">{{Lang::get('lang.email')}}</th>
				<th width="100px">{{Lang::get('lang.priority')}}</th>
				<th width="100px">{{Lang::get('lang.department')}}</th>
				<th width="100px">{{Lang::get('lang.created')}}</th>
				<th width="100px">{{Lang::get('lang.last_updated')}}</th>
				<th width="100px">{{Lang::get('lang.action')}}</th>
			</tr>

			@foreach($emails as $email)
			<tr>
				<td><a href="{{route('emails.edit', $email->id)}}"> {{$email -> email_address }}</a>
				@if($default_email == $email->id) 
					( Default )
					<?php $disabled = 'disabled'; ?>
				@else
					<?php $disabled = ''; ?>
				@endif
				</td>
				<?php $priority = App\Model\helpdesk\Ticket\Ticket_Priority::where('priority_id','=',$email->priority)->first(); ?>
				@if($email->priority == null)
					<?php $priority = "<a href=". url('getticket') .">System Default</a>"; ?>
				@else 
					<?php $priority = ucfirst($priority->priority_desc); ?>
				@endif
				<td>{!! $priority !!}</td>
				@if($email->department !== null)
					<?php  $department = App\Model\helpdesk\Agent\Department::where('id','=',$email->department)->first(); 
					$dept = $department->name; ?>
				@elseif($email->department == null)
					<?php  $dept = "<a href=". url('getsystem') .">System Default</a>"; ?>
				@endif

				<td>{!! $dept !!}</td>
				<td>{!! UTC::usertimezone($email->created_at) !!}</td>
				<td>{!! UTC::usertimezone($email->updated_at) !!}</td>
				<td>
				{!! html()->form('DELETE', route('emails.destroy', [$email->id]))->open() !!}
				<a href="{{route('emails.edit', $email->id)}}" class="btn btn-primary btn-xs"><i class="fas fa-edit"> </i>{{Lang::get('lang.edit')}}</a>
				<!-- To pop up a confirm Message -->

				@if($default_email == $email->id) 
					{!! html()->button('<i class="fas fa-trash"> </i>'.Lang::get('lang.delete'))->class('btn btn-danger btn-xs '. $disabled) !!}
				@else
					{!! html()->button('<i class="fas fa-trash"> </i>'.Lang::get('lang.delete'))->class('btn btn-danger btn-xs')->attributes(['type' => 'submit', 'onclick' => 'return confirm("Are you sure?")']) !!}
				@endif

					
				{!! html()->closeModelForm() !!}
				</td>
			</tr>
			@endforeach
		</table>
	</div>
</div>
@stop
