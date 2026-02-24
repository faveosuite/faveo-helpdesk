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
<h1>{!! Lang::get('lang.ban_email') !!}</h1>
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
<div class="alert alert-success alert-dismissable">
    <i class="fa  fa-check-circle"></i>
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    {{Session::get('success')}}
</div>
@endif
@if(Session::has('errors'))
<div class="alert alert-danger alert-dismissable">
    <i class="fa fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
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
            <div class="form-group col-sm-6 {{ $errors->has('email') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.email_address'), 'email') !!} <span class="text-red"> *</span>
                {!! html()->text('email', null)->class('form-control') !!}

            </div>

            <div class="form-group col-sm-6 {{ $errors->has('ban') ? 'has-error' : '' }}">
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
        <div class="form-group">
            {!! html()->label(Lang::get('lang.internal_notes'), 'internal_note') !!}
            {!! html()->textarea('internal_note', null)->class('form-control') !!}
        </div>
    </div>
    <div class="card-footer">
        {!! html()->submit(Lang::get('lang.submit'))->class('btn btn-primary') !!}
    </div>
</div>
@stop
