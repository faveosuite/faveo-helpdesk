@extends('themes.default1.admin.layout.admin')

@section('Tickets')
class="nav-link active"
@stop

@section('ticket-menu-parent')
class="nav-item menu-open"
@stop

@section('ticket-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('auto-response')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{{ Lang::get('lang.settings') }}</h3>
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
{!! html()->modelForm($responders, 'PATCH', url('postresponder/'.$responders->id))->open() !!}
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
    <i class="fa-solid fa-ban"></i>
    <b>{!! lang::get('lang.alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {!!Session::get('fails')!!}
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{{Lang::get('lang.auto_responce-settings')}}</h3> 
    </div>
    <!-- New Ticket: CHECKBOX	 Ticket Owner   -->
    <div class="card-body">
        
        <div class="mb-3">
            {!! html()->checkbox('new_ticket', null, 1) !!} &nbsp;
            {!! html()->label(Lang::get('lang.new_ticket'), 'new_ticket') !!}
        </div>
        <!-- New Ticket by Agent: CHECKBOX	 Ticket Owner   -->
        <div>
            {!! html()->checkbox('agent_new_ticket', null, 1) !!}&nbsp;
            {!! html()->label(Lang::get('lang.new_ticket_by_agent'), 'agent_new_ticket') !!}
        </div>
    </div>
    <div class="card-footer">
        {!! html()->submit(Lang::get('lang.submit'))->class('btn btn-primary') !!}
    </div>
</div>
@stop
