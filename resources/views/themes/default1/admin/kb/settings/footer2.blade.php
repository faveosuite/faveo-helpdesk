@extends('themes.default1.admin.layout.kb')

@section('widget')
    active
@stop
@section('footer2')
    class="active"
@stop
<script type="text/javascript" src="{{asset('dist/js/SetnicEdit.js')}}"></script>
<script type="text/javascript">
    bkLib.onDomLoaded(function() { nicEditors.allTextAreas() });
</script>

@section('content')


	{!! html()->modelForm($footer2, 'PATCH', url('post-create-footer2/'.$footer2->id))->acceptsFiles()->open() !!}

<!-- <div class="mb-3 {{ $errors->has('company_name') ? 'has-error' : '' }}"> -->
	<!-- table  -->

<div class="box box-primary">
    <div class="box-header">
        <h3 class="box-title">{{Lang::get('lang.footer2')}}</h3>  {!! html()->submit(Lang::get('lang.save'))->class('mb-3 btn btn-primary pull-right') !!}
    </div>

    <div class="box-body">

    <div class="row">


    <div class="col-md-10">

        <div class="mb-3 {{ $errors->has('title') ? 'has-error' : '' }}">

            {!! html()->label(Lang::get('lang.title'), 'title') !!}
            {!! $errors->first('title', '<spam class="help-block">:message</spam>') !!}
            {!! html()->text('title', null)->class('form-control') !!}

        </div>

        <div class="mb-3 {{ $errors->has('footer') ? 'has-error' : '' }}">
            {!! html()->label(Lang::get('lang.footer'), 'footer') !!}
            {!! $errors->first('footer', '<spam class="help-block">:message</spam>') !!}
            {!! html()->textarea('footer', null)->class('form-control')->id('footer')->placeholder('Enter the description')->attributes(['size' => '128x10']) !!}
        </div>

    </div>

    </div>

    </div>

@stop
@section('FooterInclude')

@stop

<!-- /content -->
