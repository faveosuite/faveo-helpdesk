@extends('themes.default1.admin.layout.kb')

@section('widget')
    active
@stop
@section('side1')
    class="active"
@stop
<script type="text/javascript" src="{{asset('dist/js/SetnicEdit.js')}}"></script>
<script type="text/javascript">
    bkLib.onDomLoaded(function() { nicEditors.allTextAreas() });
</script>

@section('content')

	{!! html()->modelForm($side, 'PATCH', url('side1/'.$side->id))->acceptsFiles()->open() !!}

<!-- <div class="form-group {{ $errors->has('company_name') ? 'has-error' : '' }}"> -->
	<!-- table  -->

<div class="box box-primary">
@if(Session::has('success'))
    <div class="alert alert-success alert-dismissable">
        <i class="fa  fa-check-circle"></i>
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {{Session::get('success')}}
    </div>
    @endif
    <!-- failure message -->
    @if(Session::has('fails'))
    <div class="alert alert-danger alert-dismissable">
        <i class="fa fa-ban"></i>
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {{Session::get('fails')}}
    </div>
    @endif
    <div class="box-header">
        <h3 class="box-title">{{Lang::get('lang.sidewidget1')}}</h3>  {!! html()->submit(Lang::get('lang.save'))->class('form-group btn btn-primary pull-right') !!}
    </div>

    <div class="box-body">

    <div class="row">


    <div class="col-md-10">

        <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">

            {!! html()->label(Lang::get('lang.title'), 'title') !!}
            {!! $errors->first('title', '<spam class="help-block">:message</spam>') !!}
            {!! html()->text('title', null)->class('form-control') !!}

        </div>

        <div class="form-group {{ $errors->has('content') ? 'has-error' : '' }}">
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
