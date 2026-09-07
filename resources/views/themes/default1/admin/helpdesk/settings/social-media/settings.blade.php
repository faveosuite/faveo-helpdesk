@extends('themes.default1.admin.layout.admin')

@section('Settings')
class="nav-link active"
@stop

@section('settings-menu-parent')
class="nav-item menu-open"
@stop

@section('settings-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('social-login')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>Social media settings</h3>
@stop
<!-- /header -->
<!-- breadcrumbs -->
@section('breadcrumbs')
<ol class="breadcrumb">

</ol>
@stop
@section('content')
{!! html()->form('POST', url('social/media/'.$provider))->open() !!}
@if (count($errors) > 0)
<div class="alert alert-danger">
    <strong>{{Lang::get('lang.woops')}}</strong> {{Lang::get('lang.theirisproblem')}}<br><br>
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<!-- check whether success or not -->
@if(Session::has('warn'))
<div class="alert alert-warning alert-dismissible">
    <i class="fa-solid fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {!!Session::get('warn')!!}
</div>
@endif
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
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {!!Session::get('fails')!!}
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{{ucfirst($provider)}}</h3>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3 {{ $errors->has('client_id') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.client_id'), 'client_id') !!}<spam class="help-block"> *</spam>
                    {!! html()->text('client_id', $social->getvalueByKey($provider,'client_id'))->class('form-control') !!}
                    {!! $errors->first('client_id', '<spam class="help-block">:message</spam>') !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3 {{ $errors->has('client_secret') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.client_secret'), 'client_secret') !!}<spam class="help-block"> *</spam>
                    {!! html()->text('client_secret', $social->getvalueByKey($provider,'client_secret'))->class('form-control') !!}
                     {!! $errors->first('client_secret', '<spam class="help-block">:message</spam>') !!}
                </div>
            </div>
        </div> 
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3 {{ $errors->has('redirect') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.redirect'), 'redirect') !!}
                    {!! html()->text('redirect', $social->getvalueByKey($provider,'redirect'))->class('form-control') !!}
                    {!! $errors->first('redirect', '<spam class="help-block">:message</spam>') !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3 {{ $errors->has('status') ? 'has-error' : '' }}">
                    <div class="row">
                        <div class="col-md-12">
                            {!! html()->label(Lang::get('lang.status'), 'status') !!} 
                        </div>
                        <div class="col-md-6">
                            <p>{!! html()->radio('status', $social->checkActive($provider), 1) . Lang::get('lang.active') !!}</p>
                        </div>
                        <div class="col-md-6">
                            <p>{!! html()->radio('status', $social->checkInactive($provider), 0) . Lang::get('lang.inactive') !!}</p>
                        </div>
                        <div class="col-md-12">
                            <i>Activate login via {{ucfirst($provider)}}</i>
                        </div>
                         {!! $errors->first('status', '<spam class="help-block">:message</spam>') !!}
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="card-footer">
        {!! html()->submit(Lang::get('lang.submit'))->class('btn btn-primary') !!}
    </div>
</div>
{!! html()->closeModelForm() !!}
@stop
