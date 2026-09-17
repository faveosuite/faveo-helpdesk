@extends('themes.default1.admin.layout.admin')

@section('Staffs')
class="nav-link active"
@stop

@section('staff-menu-parent')
class="nav-item menu-open"
@stop

@section('staff-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('departments')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{{Lang::get('lang.departments')}}</h3>
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
{!! html()->modelForm($departments, 'PATCH', url('departments/'.$departments->id))->open() !!}
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
    @if($errors->first('account_status'))
    <li class="error-message-padding">{!! $errors->first('account_status', ':message') !!}</li>
    @endif
    @if($errors->first('sla'))
    <li class="error-message-padding">{!! $errors->first('sla', ':message') !!}</li>
    @endif
    @if($errors->first('manager'))
    <li class="error-message-padding">{!! $errors->first('manager', ':message') !!}</li>
    @endif
    @if($errors->first('outgoing_email'))
    <li class="error-message-padding">{!! $errors->first('outgoing_email', ':message') !!}</li>
    @endif
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.edit_department') !!}</h3>
    </div>
    <div class="card-body">
        
        <div class="row">
            <!-- name -->
            <div class="col-sm-6 mb-3 {{ $errors->has('name') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.name'), 'name') !!} <span class="text-red"> *</span>
                {!! html()->text('name', null)->class('form-control') !!}
            </div>
            <!-- account status -->
            <div class="col-sm-6 mb-3 {{ $errors->has('account_status') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.type'), 'type') !!}
                <div class="row">
                    <div class="col-sm-2">
                        {!! html()->radio('type', true, '1') !!} {{Lang::get('lang.public')}}
                    </div>
                    <div class="col-sm-3">
                        {!! html()->radio('type', null, '0') !!} {{Lang::get('lang.private')}}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- sla -->
            <div class="col-sm-6 mb-3 {{ $errors->has('sla') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.SLA_plan'), 'sla') !!}
                {!! html()->select('sla', [''=>Lang::get('lang.select_a_sla'), Lang::get('lang.sla_plans')=>$slas->pluck('grace_period','id')->toArray()], null)->class('form-control select') !!}
            </div>
            <!-- manager -->
            <div class="col-sm-6 mb-3 {{ $errors->has('manager') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.manager'), 'manager') !!}
                {!! html()->select('manager', [null=>Lang::get('lang.select_a_manager'),Lang::get('lang.manager')=>$user->pluck('full_name','id')->toArray()], null)->class('form-control select') !!}
            </div>
        </div>

        <div class="row">
            <!-- sla -->
            <div class="col-sm-6 mb-3 {{ $errors->has('outgoing_email') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.outgoing_email'), 'outgoing_email') !!}
                {!! html()->select('outgoing_email', ['' => Lang::get('lang.system_default'), Lang::get('lang.emails')=>$emails->pluck('email_name','id')->toArray()], null)->class('form-control select') !!}
            </div>
        </div>
        <div>
            <input type="checkbox" name="sys_department" @if($sys_department->department == $departments->id) checked disabled @endif> {{ Lang::get('lang.make-default-department')}}
        </div>
    </div>

    <div class="card-footer">

        {!! html()->submit(Lang::get('lang.update'))->class('btn btn-primary') !!}    
    </div>
    {!! html()->closeModelForm() !!}
</div>
@stop