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

@section('help')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{!! Lang::get('lang.help_topic') !!}</h3>
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
{!! html()->form('POST', route('helptopic.store'))->open() !!}
@if(Session::has('errors'))
<?php //dd($errors); ?>
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>Alert!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <br/>
    @if($errors->first('status'))
    <li class="error-message-padding">{!! $errors->first('status', ':message') !!}</li>
    @endif
    @if($errors->first('type'))
    <li class="error-message-padding">{!! $errors->first('type', ':message') !!}</li>
    @endif
    @if($errors->first('topic'))
    <li class="error-message-padding">{!! $errors->first('topic', ':message') !!}</li>
    @endif
    @if($errors->first('parent_topic'))
    <li class="error-message-padding">{!! $errors->first('parent_topic', ':message') !!}</li>
    @endif
    @if($errors->first('custom_form'))
    <li class="error-message-padding">{!! $errors->first('custom_form', ':message') !!}</li>
    @endif
    @if($errors->first('department'))
    <li class="error-message-padding">{!! $errors->first('department', ':message') !!}</li>
    @endif
    @if($errors->first('priority'))
    <li class="error-message-padding">{!! $errors->first('priority', ':message') !!}</li>
    @endif
    @if($errors->first('sla_plan'))
    <li class="error-message-padding">{!! $errors->first('sla_plan', ':message') !!}</li>
    @endif
    @if($errors->first('auto_assign'))
    <li class="error-message-padding">{!! $errors->first('auto_assign', ':message') !!}</li>
    @endif
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{{Lang::get('lang.create')}}</h3>
    </div>
    <div class="card-body">

        <div class="row">
            <!-- Topic text form Required -->
            <div class="col-md-6">
                <div class="mb-3 {{ $errors->has('topic') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.topic'), 'topic') !!} <span class="text-red"> *</span>
                    {!! html()->text('topic', null)->class('form-control') !!}
                </div>
            </div> 
            <!-- status radio: required: Active|Dissable -->
            <div class="col-md-3">
                <div class="mb-3 {{ $errors->has('ticket_status') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.status'), 'ticket_status') !!}&nbsp;&nbsp;<br/>
                    {!! html()->radio('status', true, '1') !!} {{Lang::get('lang.active')}}&nbsp;&nbsp;&nbsp;
                    {!! html()->radio('status', null, '0') !!} {{Lang::get('lang.inactive')}}
                </div>
            </div>
            <!-- Type : Radio : required : Public|private -->
            <div class="col-md-3">
                <div class="mb-3 {{ $errors->has('type') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.type'), 'type') !!}&nbsp;&nbsp;<br/>
                    {!! html()->radio('type', true, '1') !!} {{Lang::get('lang.public')}}&nbsp;&nbsp;&nbsp;
                    {!! html()->radio('type', null, '0') !!} {{Lang::get('lang.private')}}
                </div>
            </div>   
        </div>

        <div class="row">
            <!-- Parent Topic: Drop down: value from helptopic table -->
            <div class="col-md-4">
                <div class="mb-3 {{ $errors->has('parent_topic') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.parent_topic'), 'parent_topic') !!}
                    {!! html()->select('parent_topic', [''=>Lang::get('lang.select_a_parent_topic'),Lang::get('lang.help_topic')=>$topics->pluck('topic','topic')->toArray()], 1)->class('form-control') !!}
                </div>
            </div>
            <!-- Custom Form: Drop down: value from form table -->
            <div class="col-md-4">
                <div class="mb-3 {{ $errors->has('custom_form') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.Custom_form'), 'custom_form') !!}
                    {!! html()->select('custom_form', [''=>Lang::get('lang.select_a_form'),Lang::get('lang.custom_form')=>$forms->pluck('formname','id')->toArray()], 1)->class('form-control') !!}
                </div>
            </div>
            <!-- Department:    Drop down: value Department form table -->
            <div class="col-md-4">
                <div class="mb-3 {{ $errors->has('department') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.department'), 'department') !!}
                    {!! html()->select('department', [''=>Lang::get('lang.select_a_department'),Lang::get('lang.departments')=>$departments->pluck('name','id')->toArray()], 1)->class('form-control') !!}
                </div>
            </div>
        </div>
        <!-- Priority:	Drop down: value from Priority  table -->
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3 {{ $errors->has('priority') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.priority'), 'priority') !!} <span class="text-red"> *</span>
                    {!! html()->select('priority', [''=>Lang::get('lang.select_a_priority'),Lang::get('lang.priorities')=>$priority->pluck('priority_desc','priority_id')->toArray()], null)->class('form-control') !!}
                </div>
            </div>
            <!-- SLA Plan:	 Drop down: value SLA Plan  table-->
            <div class="col-md-4">
                <div class="mb-3 {{ $errors->has('sla_plan') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.SLA_plan'), 'sla_plan') !!}
                    {!! html()->select('sla_plan', [''=>Lang::get('lang.select_a_sla_plan'),Lang::get('lang.sla_plans')=>$slas->pluck('name','id')->toArray()], 1)->class('form-control') !!}
                </div>
            </div>
            <!-- Auto-assign To:	Drop Down: value  from Agent table   -->
            <div class="col-md-4">
                <div class="mb-3 {{ $errors->has('auto_assign') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.auto_assign'), 'auto_assign') !!}
                    {!! html()->select('auto_assign', [''=>Lang::get('lang.select_an_agent'),Lang::get('lang.agents')=>$agents->pluck('full_name','id')->toArray()], null)->class('form-control') !!}
                </div>
            </div>
        </div>
        <!-- Auto-response:	checkbox : Disable new ticket auto-response  -->
        <div class="row">
            <!-- intrnal Notes : Textarea :  -->
            <div class="col-md-12">
                <div class="mb-3">
                    {!! html()->label(Lang::get('lang.internal_notes'), 'internal_notes') !!}
                    {!! html()->textarea('internal_notes', null)->class('form-control')->attributes(['size' => '10x5']) !!}
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
