@extends('themes.default1.admin.layout.admin')

@section('Manage')
class="nav-link active"
@stop

@section('manage-menu-parent')
class="nav-item menu-open"
@stop

@section('manage-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('sla')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{!! Lang::get('lang.sla_plan') !!}</h3>
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
{!! html()->form('POST', route('sla.store'))->open() !!}
@if(Session::has('errors'))
<?php //dd($errors); ?>
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>Alert!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <br/>
    @if($errors->first('name'))
    <li class="error-message-padding">{!! $errors->first('name', ':message') !!}</li>
    @endif
    @if($errors->first('grace_period'))
    <li class="error-message-padding">{!! $errors->first('grace_period', ':message') !!}</li>
    @endif
    @if($errors->first('status'))
    <li class="error-message-padding">{!! $errors->first('status', ':message') !!}</li>
    @endif
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{{Lang::get('lang.create')}}</h3>
    </div>
    <div class="card-body">
        <!-- <table class="table table-hover overflow-hidden"> -->
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3 {{ $errors->has('name') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.name'), 'name') !!} <span class="text-red"> *</span>
                    {!! html()->text('name', null)->class('form-control') !!}
                </div>
            </div>
            <!-- Grace Period text form Required -->
            <div class="col-md-4">
                <div class="mb-3 {{ $errors->has('grace_period') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.grace_period'), 'grace_period') !!}
                    {!! html()->select('grace_period', ['6 Hours'=>'6 Hours', '12 Hours'=>'12 Hours', '18 Hours'=>'18 Hours', '24 Hours'=>'24 Hours', '36 Hours'=>'36 Hours', '48 Hours'=>'48 Hours'], null)->class('form-control') !!}
                </div>
            </div>
            <!-- status radio: required: Active|Dissable -->
            <div class="col-md-4">
                <div class="mb-3 {{ $errors->has('status') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.status'), 'status') !!}&nbsp;<br/>
                    {!! html()->radio('status', true, '1') !!} {{Lang::get('lang.active')}}&nbsp;&nbsp;
                    {!! html()->radio('status', null, '0') !!} {{Lang::get('lang.inactive')}}
                </div>
            </div>
        </div>
        <!-- Admin Note  : Textarea :  -->
        <div class="row">
            <div class="col-md-12">
                <div class="mb-3">
                    {!! html()->label(Lang::get('lang.admin_notes'), 'admin_note') !!}
                    {!! html()->textarea('admin_note', null)->class('form-control')->attributes(['size' => '30x5']) !!}
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer">
        {!! html()->submit(Lang::get('lang.submit'))->class('btn btn-primary') !!}
    </div>
</div>
<!-- close form -->
{!! html()->closeModelForm() !!}
@stop