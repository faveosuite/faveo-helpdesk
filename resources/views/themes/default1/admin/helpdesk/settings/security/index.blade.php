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

@section('security')
class="nav-link active"
@stop

@section('PageHeader')
<h3>{!! Lang::get('lang.settings') !!}</h3>
@stop

@section('header')
@stop

@section('content')
@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa-solid fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {!! Session::get('success') !!}
</div>
@endif
@if(Session::has('failed'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang/alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <p>{{Session::get('failed')}}</p>                
</div>
@endif
@if(Session::has('errors'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <br/>
    @if($errors->first('lockout_message'))
    <li class="error-message-padding">{!! $errors->first('lockout_message', ':message') !!}</li>
    @endif
    @if($errors->first('backlist_threshold'))
    <li class="error-message-padding">{!! $errors->first('backlist_threshold', ':message') !!}</li>
    @endif
    @if($errors->first('lockout_period'))
    <li class="error-message-padding">{!! $errors->first('lockout_period', ':message') !!}</li>
    @endif
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.security_settings') !!}</h3>
    </div><!-- /.card-header -->
    <div class="card-body">
        {!! html()->modelForm($security, 'PATCH', route('securitys.update', [$security->id]))->acceptsFiles()->open() !!}
        <div class="mb-3 {{ $errors->has('lockout_message') ? 'has-error' : '' }}">
            <div class="row">
                <div class="col-md-3">
                    <label for="title">{{trans('lang.Lockout_Message:')}}<span class="text-red"> *</span></label>
                </div>
                <div  class="col-md-9">
                    <div class="callout callout-default font-oblique">{!! Lang::get('lang.security_msg1') !!}</div>
                    {!! html()->textarea('lockout_message', null)->class('form-control') !!}
                </div>
            </div>
        </div>
        <div class="mb-3 {{ $errors->has('backlist_threshold') ? 'has-error' : '' }}">
            <div class="row">
                <div class="col-md-3">
                    <label for="title">{!! Lang::get('lang.max_attempt') !!}: <span class="text-red"> *</span></label>
                </div>
                <div class="col-md-9">
                    <div class="callout callout-default font-oblique">{!! Lang::get('lang.security_msg2') !!}</div>
                    <span>{!! html()->text('backlist_threshold', null)->class('form-control') !!} {!! Lang::get('lang.lockouts') !!}</span>
                </div>     
            </div>
        </div>
        <div class="mb-3 {{ $errors->has('lockout_period') ? 'has-error' : '' }}"> 
            <div class="row">
                <div class="col-md-3">
                    <label for="title">{{trans('lang.lockout_period:')}}<span class="text-red"> *</span></label>
                </div>
                <div class="col-md-8">
                    <div class="callout callout-default font-oblique">{!! Lang::get('lang.security_msg3') !!}</div>
                    <span> {!! html()->text('lockout_period', null)->class('form-control') !!} {!! Lang::get('lang.minutes') !!}</span>
                </div>
            </div>
        </div>
    </div><!-- /.card-body -->
    <div class="card-footer">
        <button type="submit" class="btn btn-primary">{!! lang::get('lang.submit') !!}</button>
    </div>
    {!! html()->closeModelForm() !!}
</div>
@stop
