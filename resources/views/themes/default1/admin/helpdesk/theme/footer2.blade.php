@extends('themes.default1.admin.layout.admin')

@section('Themes')
class="active"
@stop

@section('theme-bar')
active
@stop

@section('footer2')
class="active"
@stop

@section('content')
<!-- open a form -->

	{!! html()->modelForm($footer2, 'PATCH', url('post-create-footer2/'.$footer2->id))->acceptsFiles()->open() !!}

<!-- <div class="mb-3 {{ $errors->has('company_name') ? 'has-error' : '' }}"> -->
	<!-- table  -->

<div class="box box-primary">
	<div class="box-header">
        	<h4 class="box-title">{!! Lang::get('lang.footer2') !!}</h4> {!! html()->submit(Lang::get('lang.save'))->class('mb-3 btn btn-primary pull-right') !!}
    </div>
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
		<!-- Name text form Required -->
 		<div class="box-body table-responsive"style="overflow:hidden;">
            <div class="mb-3 {{ $errors->has('title') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.title'), 'title') !!}
                {!! $errors->first('title', '<spam class="help-block">:message</spam>') !!}
                {!! html()->text('title', null)->class('form-control') !!}
            </div>
            <div class="mb-3 {{ $errors->has('footer') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.footer'), 'footer') !!}
                {!! $errors->first('footer', '<spam class="help-block">:message</spam>') !!}
                {!! html()->textarea('footer', null)->class('form-control')->id('footer')->attributes(['size' => '30x5']) !!}
            </div>
        </div>
        <div class="box-footer">
        </div>
    </div>
</div>
@stop
