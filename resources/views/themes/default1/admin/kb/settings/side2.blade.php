@extends('themes.default1.admin.layout.kb')

@section('widget')
    active
@stop
@section('side2')
    class="active"
@stop
<script type="text/javascript" src="{{asset('dist/js/SetnicEdit.js')}}"></script>
<script type="text/javascript">
    bkLib.onDomLoaded(function() { nicEditors.allTextAreas() });
</script>

@section('content')

	{!! html()->modelForm($side, 'PATCH', url('side2/'.$side->id))->acceptsFiles()->open() !!}

<!-- <div class="mb-3 {{ $errors->has('company_name') ? 'has-error' : '' }}"> -->
	<!-- table  -->

<div class="box box-primary">
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
    <div class="box-header">
        <h3 class="box-title">{{Lang::get('lang.sidewidget2')}}</h3>  {!! html()->submit(Lang::get('lang.save'))->class('mb-3 btn btn-primary pull-right') !!}
    </div>
    <div class="box-body">

    <div class="row">


    <div class="col-md-10">

        <div class="mb-3 {{ $errors->has('title') ? 'has-error' : '' }}">

            {!! html()->label(Lang::get('lang.title'), 'title') !!}
            {!! $errors->first('title', '<spam class="help-block">:message</spam>') !!}
            {!! html()->text('title', null)->class('form-control') !!}

        </div>

        <div class="mb-3 {{ $errors->has('content') ? 'has-error' : '' }}">
            {!! html()->label(Lang::get('lang.content'), 'content') !!}
            {!! $errors->first('content', '<spam class="help-block">:message</spam>') !!}
            {!! html()->textarea('content', null)->class('form-control')->id('footer')->placeholder('Enter the description')->attributes(['size' => '128x10']) !!}
        </div>

    </div>

    </div>

    </div>

@stop
@section('FooterInclude')

@stop

<!-- /content -->
