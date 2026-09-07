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

{!! html()->form('POST', action('Admin\helpdesk\TemplateController@store'))->open() !!}
	<div class="row">
<div class="col-md-12">
<div class="box box-primary">
<div class="box-body">
<div class="box-header">
<h2 class="box-title">{{Lang::get('lang.create')}}</h2>
<div class="pull-right">
   {!! html()->submit(Lang::get('lang.save'))->class('btn btn-primary') !!}</div>
   </div>

	 <div class="box-body table-responsive no-padding"style="overflow:hidden">
	    <div class="row">

		<!--  Status : Radio form : Required -->
		<div class="col-md-6 mb-3 {{ $errors->has('ban_status') ? 'has-error' : ''}}">
			<div class="row col-3">
			{!! html()->label(Lang::get('lang.status'), 'status') !!}
			</div>
			<div class="row">
				<div class="col-3">
					{!! html()->radio('ban_status', true, 'active') !!}{{Lang::get('lang.active')}}
				</div>
				<div class="col-3">
					{!! html()->radio('ban_status', null, 'disabled') !!}{{Lang::get('lang.disabled')}}
				</div>
			</div>
			</div>
		</div>

		<!-- Name : Text form : Required -->
		<div class="row">
           <div class="col-md-4">
		        <div class="mb-3 {{ $errors->has('name') ? 'has-error' : '' }}">
			      {!! html()->label(Lang::get('lang.name'), 'name') !!}
			      {!! $errors->first('name', '<spam class="help-block">:message</spam>') !!}
			       {!! html()->text('name', null)->class('form-control') !!}
			</div>
		</div>

		<!-- Form for template set to clone From template table : Drop down : required -->
             <div class="col-md-4">
		<div class="mb-3 {{ $errors->has('template_set_to_clone') ? 'has-error' : '' }}">
			{!! html()->label(Lang::get('lang.template_set_to_clone'), 'template_set_to_clone') !!}
			{!! $errors->first('template_set_to_clone', '<spam class="help-block">:message</spam>') !!}
			{!! html()->select('template_set_to_clone', [''=>'Select a Template','Templates'=>$templates->pluck('name','name')], 1)->class('form-control') !!}
			</div>
		</div>

		<!-- Language field to Set the language in the template -->
           <div class="col-md-4">
		<div class="mb-3 {{ $errors->has('language') ? 'has-error' : '' }}">
			{!! html()->label(Lang::get('lang.language'), 'language') !!}
			{!! $errors->first('language', '<spam class="help-block">:message</spam>') !!}
			{!! html()->select('language', [''=>'Select a Language','Languages'=>$languages->pluck('name','name')], null)->class('form-control') !!}
			</div>
		</div>

		<!-- intrnal Notes : Textarea :  -->

             <div class="col-md-12">
		      <div class="mb-3">
			     {!! html()->label(Lang::get('lang.internal_notes'), 'internal_note') !!}
			     {!! html()->textarea('internal_note', null)->class('form-control') !!}
		     </div>
           </div>



	</div>
	</div>
	</div>
	</div>


@stop
