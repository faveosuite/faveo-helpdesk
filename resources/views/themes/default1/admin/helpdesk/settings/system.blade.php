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

@section('system')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{{Lang::get('lang.settings')}}</h3>
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
{!! html()->modelForm($systems, 'PATCH', url('postsystem/'.$systems->id))->attributes(['id' => 'formID'])->open() !!}
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
    <i class="fa-solid fa-ban"></i><button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <b>{!! Lang::get('lang.alert') !!}!</b><br/>
    <li class="error-message-padding">{!!Session::get('fails')!!}</li>
</div>
@endif
@if(Session::has('errors'))
<?php //dd($errors); ?>
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <br/>
    @if($errors->first('user_name'))
    <li class="error-message-padding">{!! $errors->first('user_name', ':message') !!}</li>
    @endif
    @if($errors->first('first_name'))
    <li class="error-message-padding">{!! $errors->first('first_name', ':message') !!}</li>
    @endif
    @if($errors->first('last_name'))
    <li class="error-message-padding">{!! $errors->first('last_name', ':message') !!}</li>
    @endif
    @if($errors->first('email'))
    <li class="error-message-padding">{!! $errors->first('email', ':message') !!}</li>
    @endif
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{{Lang::get('lang.system-settings')}}</h3> 
    </div>
    <!-- Helpdesk Status: radio Online Offline -->
    <div class="card-body">
        <div class="row">
           
            <!-- Helpdesk Name/Title: text Required   -->
            <div class="col-md-4">
                <div class="mb-3 {{ $errors->has('name') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.name/title'), 'name') !!}
                    {!! $errors->first('name', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->text('name', $systems->name)->class('form-control') !!}
                </div>
            </div>
             <!-- Helpdesk URL:      text   Required -->
             <div class="col-md-4">
                <div class="mb-3 {{ $errors->has('url') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.url'), 'url') !!}
                    {!! $errors->first('url', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->text('url', $systems->url)->class('form-control') !!}
                </div>
            </div>
            <!-- Default Time Zone: Drop down: timezones table : Required -->
            <div class="col-md-4">
                <div class="mb-3 {{ $errors->has('time_zone') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.timezone'), 'time_zone') !!}
                    {!! $errors->first('time_zone', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->select('time_zone', ['Time Zones'=>$timezones->pluck('name','id')->toArray()], null)->class('form-control') !!}
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Date and Time Format: text: required: eg - 03/25/2015 7:14 am -->
            <div class="col-md-4">
                <div class="mb-3 {{ $errors->has('date_time_format') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.date_time'), 'date_time_format') !!}
                    {!! $errors->first('date_time_format', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->select('date_time_format', ['Date Time Formats'=>$date_time->pluck('format','id')->toArray()], null)->class('form-control') !!}
                </div>
            </div>
           
            <div class="col-md-4">
                <div class="mb-3">
                    {!! html()->label(Lang::get('lang.status'), 'status') !!}
                    <div class="row">
                        <div class="col-sm-5">
                            {!! html()->radio('status', true, '1') !!} {{Lang::get('lang.online')}}
                        </div>
                        <div class="col-sm-6">
                            {!! html()->radio('status', null, '0') !!} {{Lang::get('lang.offline')}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    {!! html()->label(Lang::get('lang.user_set_ticket_status'), 'user_set_ticket_status') !!}
                    <div class="row">
                        <div class="col-sm-5">
                            <input type="radio" name="user_set_ticket_status" value="1" @if($common_setting->status == '1')checked="true" @endif>&nbsp;{{Lang::get('lang.yes')}}
                        </div>
                        <div class="col-sm-5">
                            <input type="radio" name="user_set_ticket_status" value="0" @if($common_setting->status == '0')checked="true" @endif>&nbsp;{{Lang::get('lang.no')}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">    
            <div class="col-md-4" data-bs-toggle="tooltip" title="{!! Lang::get('lang.the_rtl_support_is_only_applicable_to_the_outgoing_mails') !!}">
                <div class="mb-3">
                    {!! html()->label(Lang::get('lang.rtl'), 'status') !!}
                    <div class="row">
                        <div class="col-sm-12">
                            <?php
                            $rtl = App\Model\helpdesk\Settings\CommonSettings::where('option_name', '=', 'enable_rtl')->first();
                            ?>
                            <input type="checkbox" name="enable_rtl" @if($rtl->option_value == 1) checked @endif> {{Lang::get('lang.enable')}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4" data-bs-toggle="tooltip" title="{!! Lang::get('lang.otp_usage_info') !!}">
                <div class="mb-3">
                    {!! html()->label(Lang::get('lang.allow_unverified_users_to_create_ticket'), 'send_otp') !!}
                    <div class="row">
                        <div class="col-sm-5">
                            <input type="radio" name="send_otp" value="0" @if($send_otp->status == '0')checked="true" @endif>&nbsp;{{Lang::get('lang.yes')}}
                        </div>
                        <div class="col-sm-6">
                            <input type="radio" name="send_otp" value="1" @if($send_otp->status == '1')checked="true" @endif>&nbsp;{{Lang::get('lang.no')}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4" data-bs-toggle="tooltip" title="{!! Lang::get('lang.email_man_info') !!}">
                <div class="mb-3">
                    {!! html()->label(Lang::get('lang.make-email-mandatroy'), 'email_mandatory') !!}
                    <div class="row">
                        <div class="col-sm-5">
                            <input type="radio" name="email_mandatory" value="1" @if($email_mandatory->status == '1')checked="true" @endif>&nbsp;{{Lang::get('lang.yes')}}
                        </div>
                        <div class="col-sm-6">
                            <input type="radio" name="email_mandatory" value="0" @if($email_mandatory->status == '0')checked="true" @endif>&nbsp;{{Lang::get('lang.no')}}
                        </div>
                    </div>
                </div>
            </div>
            
            
        </div>
    </div>
    <div class="card-footer">
        {!! html()->submit(Lang::get('lang.submit'))->class('btn btn-primary')->attributes(['onclick' => 'sendForm()']) !!}
    </div>
</div>

@stop
