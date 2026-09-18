@extends('themes.default1.admin.layout.admin')

@section('Emails')
class="nav-link active"
@stop

@section('email-menu-parent')
class="nav-item menu-open"
@stop

@section('email-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('email')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{{Lang::get('lang.emails')}}</h3>
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
{!! html()->modelForm($emails, 'PATCH', url('postemail/'.$emails->id))->open() !!}
<!-- check whether success or not -->
@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa-solid  fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {!!Session::get('success')!!}
</div>
@endif
<!-- failure message -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! lang::get('lang.success') !!} !</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {!!Session::get('fails')!!}
</div>
@endif
@if(Session::has('errors'))
<?php //dd($errors); ?>
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <br/>
    @if($errors->first('sys_email'))
    <li class="error-message-padding">{!! $errors->first('sys_email', ':message') !!}</li>
    @endif
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{{Lang::get('lang.email-settings')}}</h3>             
    </div>
    <div class="card-body">
        <!-- Accept All Emails:	CHECKBOX: Accept email from unknown Users  -->
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    {!! html()->checkbox('all_emails', true, 1) !!}&nbsp;{{Lang::get('lang.accept_all_email')}}
                </div>
            </div>
        </div>
        <!-- Admin's Email Address:	  Text : Required  -->
        <div class="row">
        </div>
        <!-- Accept Email Collaborators: CHECKBOX : Automatically add collaborators from email fields   -->
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    {!! html()->checkbox('email_collaborator', null, 1) !!}&nbsp;{{Lang::get('lang.accept_email_collab')}}
                </div>
            </div>
        </div>
        <!-- Attachments: CHECKBOX	: Email attachments to the user  -->
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    {!! html()->checkbox('attachment', null, 1) !!}&nbsp;{{Lang::get('lang.attachments')}}
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer">
        {!! html()->submit(Lang::get('lang.submit'))->class('btn btn-primary') !!}
    </div>
</div>
@stop