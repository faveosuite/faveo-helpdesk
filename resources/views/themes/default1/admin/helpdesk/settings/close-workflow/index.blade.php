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

@section('close-workflow')
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
    {{Session::get('success')}}
</div>
@endif
@if(Session::has('failed'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <p>{{Session::get('failed')}}</p>                
</div>
@endif
@if(Session::has('errors'))
<?php //dd($errors); ?>
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <br/>
    @if($errors->first('days'))
    <li class="error-message-padding">{!! $errors->first('days', ':message') !!}</li>
    @endif
    @if($errors->first('condition'))
    <li class="error-message-padding">{!! $errors->first('condition', ':message') !!}</li>
    @endif
    @if($errors->first('send_email'))
    <li class="error-message-padding">{!! $errors->first('send_email', ':message') !!}</li>
    @endif
    @if($errors->first('status'))
    <li class="error-message-padding">{!! $errors->first('status', ':message') !!}</li>
    @endif
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.close_ticket_workflow_settings') !!}</h3>
    </div><!-- /.box-header -->
    <div class="card-body">
        {!! html()->modelForm($security, 'PATCH', route('close-workflow.update', [$security->id]))->acceptsFiles()->open() !!}
        <div class="mb-3 {{ $errors->has('days') ? 'has-error' : '' }}">
            <div class="row">
                <div class="col-md-3">
                    <label for="title">{!! Lang::get('lang.no_of_days') !!}: <span class="text-red"> *</span></label>
                </div>
                <div  class="col-md-9">
                    <div class="callout callout-default font-oblique">{!! Lang::get('lang.close-msg1') !!}</div>
                    {!! html()->text('days', null)->class('form-control') !!}
                </div>
            </div>
        </div>
        <div class="mb-3 {{ $errors->has('send_email') ? 'has-error' : '' }}"> 
            <div class="row">
                <div class="col-md-3">
                    <label for="title">{!! Lang::get('lang.send_email_to_user') !!}:</label>
                </div>
                <div class="col-md-6">
                    <div class="callout callout-default font-oblique">{!! Lang::get('lang.close-msg4') !!}</div>
                    <div class="row">
                        <div class="col-sm-3">
                            {!! html()->radio('send_email', null, '1') !!} {{Lang::get('lang.yes')}}
                        </div>
                        <div class="col-sm-3">
                            {!! html()->radio('send_email', null, '0') !!} {{Lang::get('lang.no')}}
                        </div>
                    </div>       
                </div>
            </div>
        </div>
        <div class="mb-3 {{ $errors->has('status') ? 'has-error' : '' }}"> 
            <div class="row">
                <div class="col-md-3">
                    <label for="title">{!! Lang::get('lang.ticket_status') !!}:</label>
                </div>
                <div class="col-md-6">
                    <div class="callout callout-default font-oblique">{!! Lang::get('lang.close-msg3') !!}</div>
                    <?php $user = \App\Model\helpdesk\Ticket\Ticket_Status::where('state', '=', 'closed')->get(); ?>
                    {!! html()->select('status', [ Lang::get('lang.status')=>$user->pluck('name','id')->toArray()], null)->class('form-control') !!}	
                </div>
            </div>
        </div>
    </div><!-- /.box-body -->
    <div class="card-footer">
        <button type="submit" class="btn btn-primary">{!! Lang::get('lang.submit') !!}</button>
    </div>
    {!! html()->closeModelForm() !!}
</div>
@stop
