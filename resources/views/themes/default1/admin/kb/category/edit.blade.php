@extends('themes.default1.admin.layout.kb')

@section('category')
    active
@stop

<script type="text/javascript" src="{{asset('dist/js/EditnicEdit.js')}}"></script>
<script type="text/javascript">
	bkLib.onDomLoaded(function() { nicEditors.allTextAreas() });
</script>

@section('content')
{!! html()->modelForm($category, 'PATCH', url('category/'.$category->slug))->open() !!}


<div class="box box-primary">
	<div class="app-content-header">

	 	<h4>Edit	{!! html()->submit('save')->class('mb-3 btn btn-primary pull-right') !!}</h4>

	</div>

	<div class="box-body">
	@if(Session::has('success'))
    <div class="alert alert-success alert-dismissible">
        <i class="fa  fa-circle-check"></i>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
        {{Session::get('success')}}
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

	<div class="row">

		<div class="col-3 mb-3 {{ $errors->has('name') ? 'has-error' : '' }}">

			{!! html()->label(Lang::get('lang.name'), 'name') !!}
			{!! $errors->first('name', '<spam class="help-block">:message</spam>') !!}
			{!! html()->text('name', null)->class('form-control') !!}

		</div>

		<div class="col-3 mb-3 {{ $errors->has('slug') ? 'has-error' : '' }}">

			{!! html()->label(Lang::get('lang.slug'), 'slug') !!}
			{!! $errors->first('slug', '<spam class="help-block">:message</spam>') !!}
			{!! html()->text('slug', null)->class('form-control') !!}

		</div>

		<div class="col-3 mb-3 {{ $errors->has('parent') ? 'has-error' : '' }}">

			{!! html()->label(Lang::get('lang.parent'), 'parent') !!}
			{!! $errors->first('parent', '<spam class="help-block">:message</spam>') !!}
			{!! html()->select('parent', [''=>'Select a Group','Categorys'=>$category->pluck('name','name')], null)->class('form-control select') !!}

		</div>


		<div class="col-3 mb-3 {{ $errors->has('status') ? 'has-error' : '' }}">

			{!! html()->label(Lang::get('lang.status'), 'status') !!}
			{!! $errors->first('status', '<spam class="help-block">:message</spam>') !!}
			<div class="row">
				<div class="col-3">
					{!! html()->radio('status', true, '1') !!}{{Lang::get('lang.active')}}
				</div>
				<div class="col-3">
					{!! html()->radio('status', null, '0') !!}{{Lang::get('lang.inactive')}}
				</div>
			</div>
		</div>

		<div class="col-md-12 mb-3 {{ $errors->has('description') ? 'has-error' : '' }}">
			{!! html()->label(Lang::get('lang.description'), 'description') !!}
			{!! $errors->first('description', '<spam class="help-block">:message</spam>') !!}
			{!! html()->textarea('description', null)->class('form-control')->id('description')->placeholder('Enter the description')->attributes(['size' => '128x10']) !!}
		</div>

	</div>


</div>
@stop
@section('FooterInclude')

@stop

<!-- /content -->
