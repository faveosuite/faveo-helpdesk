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

@section('ban')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{!! Lang::get('lang.ban_email') !!}</h3>
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
{!! html()->form('POST', route('banlist.store'))->open() !!}

@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa  fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('success')}}
</div>
@endif
@if(Session::has('errors'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <br/>
    @if($errors->first('ban'))
    <li class="error-message-padding">{!! $errors->first('ban', ':message') !!}</li>
    @endif
    @if($errors->first('email'))
    <li class="error-message-padding">{!! $errors->first('email', ':message') !!}</li>
    @endif
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{{Lang::get('lang.create_a_banned_email')}}</h3>
    </div>
    <!-- Ban Status : Radio form : Required -->
    <div class="card-body">
        
        <div class="row">
            <!-- email Address : Text form : Required -->
            <div class="mb-3 col-sm-6 {{ $errors->has('email') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.email_address'), 'email') !!} <span class="text-red"> *</span>
                {!! html()->text('email', null)->class('form-control') !!}

            </div>

            <div class="mb-3 col-sm-6 {{ $errors->has('ban') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.ban_status'), 'ban') !!} <span class="text-red"> *</span>
                <div class="row">
                    <div class="col-sm-3">
                        {!! html()->radio('ban', null, 1) !!} {{Lang::get('lang.active')}}
                    </div>
                    <div class="col-sm-3">
                        {!! html()->radio('ban', null, 0) !!} {{Lang::get('lang.inactive')}}
                    </div>
                </div>
            </div>
        </div>
        <!-- intrnal Notes : Textarea :  -->
        <div class="mb-3">
            {!! html()->label(Lang::get('lang.internal_notes'), 'internal_note') !!}
            {!! html()->textarea('internal_note', null)->class('form-control') !!}
        </div>
    </div>
    <div class="card-footer">
        {!! html()->submit(Lang::get('lang.submit'))->class('btn btn-primary') !!}
    </div>
</div>
@stop
