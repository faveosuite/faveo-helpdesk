@extends('themes.default1.admin.layout.admin')

@section('error-bugs')
class="nav-link active"
@stop

@section('error-menu-parent')
class="nav-item menu-open"
@stop

@section('error-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('debugging-option')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{{Lang::get('lang.error-debug')}}</h3>
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
    <i class="fa-solid fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {!!Session::get('success')!!}
</div>
@endif
<!-- failure message -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i><button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <b>{!! Lang::get('lang.alert') !!}!</b><br/>
    <li class="error-message-padding">{!!Session::get('fails')!!}</li>
</div>
@endif

<div class="card card-light">

    <div class="card-header">
        <h3 class="card-title">{{Lang::get('lang.debug-options')}}</h3> 
    </div>

    <!-- Helpdesk Status: radio Online Offline -->
    <div class="card-body">
        {!! html()->form('POST', route('post.error.debug.settings'))->open() !!}
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    {!! html()->label(Lang::get('lang.debugging'), 'debug') !!}
                    <div class="row">
                        <div class="col-sm-3">
                            <input type="radio" name="debug" value="true" @if($debug == true) checked="true" @endif> {{Lang::get('lang.enable')}}
                        </div>
                        <div class="col-sm-3">
                            <input type="radio" name="debug" value="false" @if($debug == false) checked="true" @endif> {{Lang::get('lang.disable')}}
                        </div>
                    </div>
                </div> 
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    {!! html()->label(Lang::get('lang.bugsnag-debugging'), 'bugsnag') !!}
                    <div class="row">
                        <div class="col-sm-3">
                            <input type="radio" name="bugsnag" value="true" @if($bugsnag == true) checked="true" @endif> {{Lang::get('lang.yes')}}
                        </div>
                        <div class="col-sm-3">
                            <input type="radio" name="bugsnag" value="false" @if($bugsnag == false) checked="true" @endif> {{Lang::get('lang.no')}}
                        </div>
                    </div>
                </div> 
            </div>
        </div>
    </div>
    <div class="card-footer">
        {!! html()->submit(Lang::get('lang.save'))->class('btn btn-primary')->attributes(['onclick' => 'sendForm()']) !!}
    </div>
    {!! html()->closeModelForm() !!}  
</div>
@stop