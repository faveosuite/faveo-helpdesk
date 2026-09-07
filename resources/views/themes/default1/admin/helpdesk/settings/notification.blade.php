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

@section('notification')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{!! Lang::get('lang.settings') !!}</h3>
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
<!-- open a form -->
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
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <b> {!! Lang::get('lang.alert') !!} ! </b>
    <li class="error-message-padding">{!!Session::get('fails')!!}</li>
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{{Lang::get('lang.notification_settings')}}</h3>
    </div>
    <!-- check whether success or not -->
    <div class="card-body">

        <div class="row">
            <div class="col-md-3 no-padding">
                <div class="mb-3">
                    {!! html()->label(Lang::get('lang.delete_noti'), 'del_noti') !!}
                </div>
            </div>
            <div class="col-md-6">
                <a href="{{ url('delete-read-notification') }}" class="btn btn-danger">{!! Lang::get('lang.del_all_read') !!}</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 no-padding">
                <div class="mb-3">
                    {!! html()->label(Lang::get('lang.noti_msg1'), 'del_noti') !!}<span class="text-red"> *</span>
                </div>
            </div>
            <div class="col-md-6">
                <form action="{{ url('delete-notification-log') }}" method="post">
                {{ csrf_field() }}
                    <div class="callout callout-default font-oblique">{!! Lang::get('lang.noti_msg2') !!}</div>
                    <input type="number" class="form-control" name='no_of_days' placeholder="{!! lang::get('lang.enter_no_of_days') !!}" min='1'>
                    <button type="submit" class="btn btn-primary">{!! Lang::get('lang.submit') !!}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@stop