@extends('themes.default1.admin.layout.admin')
@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{!! Lang::get('lang.admin_panel') !!}</h3>
@stop
<!-- /header -->
<!-- breadcrumbs -->
@section('breadcrumbs')
@stop

<style type="text/css">
    
    .content-wrapper { min-height: auto !important; }

    .settingiconblue {
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
    }

     .settingdivblue {
        width: 80px;
        height: 80px;
        margin: 0 auto;
        text-align: center;
        border: 5px solid #C4D8E4;
        border-radius: 100%;
        padding-top: 5px;
    }

     .settingdivblue span { margin-top: 3px; }

     .fw_600 { font-weight: 600; }
    .settingiconblue p{
        text-align: center;
        font-size: 16px;
        word-wrap: break-word;
        font-variant: small-caps;
        font-weight: 500;
        line-height: 30px;
    }
</style>
<!-- /breadcrumbs -->
<!-- content -->
@section('content')
<!-- failure message -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"> </i> <b> {!! Lang::get('lang.alert') !!}! </b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('fails')}}
</div>
@endif

<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.staffs') !!}</h3>
    </div>
    <!-- /.box-header -->
    <div class="card-body">
        <div class="row">

            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{ url('agents') }}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-user fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.agents') !!}</div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a href="{{ url('departments') }}">
                                <span class="fa-stack fa-2x">
                                    <i class="fa-solid fa-sitemap fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm">{!! Lang::get('lang.departments') !!}</div>
                    </div>
                </div>

                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a href="{{ url('teams') }}">
                                <span class="fa-stack fa-2x">
                                    <i class="fa-solid fa-users fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm">{!! Lang::get('lang.teams') !!}</div>
                    </div>
                </div>

                <div class="col-md-2 col-sm-6">
                    <div class="settingiconblue">
                        <div class="settingdivblue">
                            <a href="{{ url('groups') }}">
                                <span class="fa-stack fa-2x">
                                    <i class="fa-solid fa-object-group fa-stack-1x"></i>
                                </span>
                            </a>
                        </div>
                        <div class="text-center text-sm">{!! Lang::get('lang.groups') !!}</div>
                    </div>
                </div>
        </div>
    </div>
</div>

<div class="card card-light">
    
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.email') !!}</h3>
    </div>
    
    <div class="card-body">
        
        <div class="row">
            <!--col-md-2-->
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{ url('emails') }}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-envelope fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.emails') !!}</div>
                </div>
            </div>
            <!--/.col-md-2-->
            <!--col-md-2-->
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{ url('banlist') }}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-ban fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.ban_lists') !!}</div>
                </div>
            </div>
            <!--/.col-md-2-->
            <!--col-md-2-->
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{ url('template-sets') }}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-reply fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.templates') !!}</div>
                </div>
            </div>
            <!--/.col-md-2-->
            <!--/.col-md-2-->
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{url('getemail')}}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-at fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.email-settings') !!}</div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{url('queue')}}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-upload fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.queues') !!}</div>
                </div>
            </div>

            <!--col-md-2-->
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{ url('getdiagno') }}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-plus fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.diagnostics') !!}</div>
                </div>
            </div>
        </div>
        <!-- /.row -->
    </div>
    <!-- ./box-body -->
</div>

<div class="card card-light">
    
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.manage') !!}</h3>
    </div>
    
    <!-- /.box-header -->
    <div class="card-body">
        <div class="row">
            <!--/.col-md-2-->
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{url('helptopic')}}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-file-lines fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.help_topics') !!}</div>
                </div>
            </div>
            <!--/.col-md-2-->
            <!--/.col-md-2-->
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{url('sla')}}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-clock fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.sla_plans') !!}</div>
                </div>
            </div>
            <!--/.col-md-2-->

            <!--/.col-md-2-->
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{url('forms')}}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-file-lines fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.forms') !!}</div>
                </div>
            </div>
            <!--/.col-md-2-->
            <!--/.col-md-2-->
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{url('workflow')}}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-sitemap fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.workflow') !!}</div>
                </div>
            </div>
                <!-- priority -->
             <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{url('ticket/priority')}}">
                            <span class="fa-stack fa-2x">
                                
                                <i class="fa-solid fa-asterisk fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.priority') !!}</div>
                </div>
            </div>
            <!--/.col-md-2-->
            <!--/.col-md-2-->
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{url('url/settings')}}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-server fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">Url</div>
                </div>
            </div>
        </div>
        <!-- /.row -->
    </div>
    <!-- ./box-body -->
</div>

<div class="card card-light">
    
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.ticket') !!}</h3>
    </div>
    <!-- /.box-header -->
    <div class="card-body">
        <div class="row">
                
            <!--/.col-md-2-->
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{url('getticket')}}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-file-lines fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.ticket') !!}</div>
                </div>
            </div>
                <!--/.col-md-2-->
            
             <!--/.col-md-2-->
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{url('getresponder')}}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-reply-all fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.auto_response') !!}</div>
                </div>
            </div>
            <!--/.col-md-2-->
            
            <!--/.col-md-2-->
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{url('getalert')}}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-bell fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.alert_notices') !!}</div>
                </div>
            </div>
            <!--/.col-md-2-->
            
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{url('setting-status')}}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-plus-square fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">Statuses</div>
                </div>
            </div>
                            
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{url('getratings')}}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-star fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.ratings') !!}</div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{url('close-workflow')}}">
                            <span class="fa-stack fa-2x">    
                                <i class="fa-solid fa-sitemap fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.close_ticket_workflow') !!}</div>
                </div>
            </div>
           <?php \Illuminate\Support\Facades\Event::dispatch('settings.ticket.view',[]); ?>

        </div>
        <!-- /.row -->
    </div>
    <!-- ./box-body -->
</div>
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.settings') !!}</h3>
    </div>
    <!-- /.box-header -->
    <div class="card-body">
        <div class="row">
            <!--/.col-md-2-->
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{!! url('getcompany') !!}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-building fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.company') !!}</div>
                </div>
            </div>
            <!--/.col-md-2-->
            <!--/.col-md-2-->
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{url('getsystem')}}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-laptop fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.system') !!}</div>
                </div>
            </div>
            <!--/.col-md-2-->

            
            <!--/.col-md-2-->
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{ url('social/media') }}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-globe fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{{Lang::get('lang.social-login')}}</div>
                </div>
            </div>
            <!--/.col-md-2-->
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{url('languages')}}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-language fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.language') !!}</div>
                </div>
            </div>
            <!--/.col-md-2-->
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{url('job-scheduler')}}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid  fa-hourglass fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.cron') !!}</div>
                </div>
            </div>
            <!--/.col-md-2-->
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{url('security')}}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-lock fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.security') !!}</div>
                </div>
            </div>
            <!--/.col-md-2-->
            
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{url('settings-notification')}}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-bell fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.notifications') !!}</div>
                </div>
            </div>
            
            <?php \Illuminate\Support\Facades\Event::dispatch('settings.system',[]); ?>
     
        </div>
        <!-- /.row -->
    </div>
    <!-- ./box-body -->
</div>

<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.error-debug') !!}</h3>
    </div>
    <!-- /.box-header -->
    <div class="card-body">
        <div class="row">
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{ route('err.debug.settings') }}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-bug fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.debug-options') !!}</div>
                </div></div>
                    @if(Config::get('app.debug'))
                        <div class="col-md-2 col-sm-6">
                            <div class="settingiconblue">
                                <div class="settingdivblue">
                                    <a href="{{ url('clockwork/app') }}">
                    <span class="fa-stack fa-2x">
                        <i class="fa-solid fa-server fa-stack-1x"></i>
                    </span>
                                    </a>
                                </div>

                                <div class="text-center text-sm">{!!Lang::get('lang.clock-work')!!}</div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
            </div>



<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.widgets') !!}</h3>
    </div>
    <!-- /.box-header -->
    <div class="card-body">
        <div class="row">
            <!--/.col-md-2-->
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{ url('widgets') }}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-list-alt fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.widgets') !!}</div>
                </div>
            </div>
            <!--/.col-md-2-->                                        
            <!--col-md-2-->
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{ url('social-buttons') }}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-cubes fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.social') !!}</div>
                </div>
            </div>
            
        </div>
        <!-- /.row -->
    </div>
    <!-- ./box-body -->
</div>

<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.plugin') !!}</h3>
    </div>
    <!-- /.box-header -->
    <div class="card-body">
        <div class="row">
            <!--/.col-md-2-->
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{ url('plugins') }}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-plug fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.plugin') !!}</div>
                </div>
            </div>
        </div>
        <!-- /.row -->
    </div>
    <!-- ./box-body -->
</div>
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.api') !!}</h3>
    </div>
    <!-- /.box-header -->
    <div class="card-body">
        <div class="row">
            <!--/.col-md-2-->
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{ url('api') }}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-gears fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{!! Lang::get('lang.api') !!}</div>
                </div>
            </div>
        </div>
        <!-- /.row -->
    </div>
    <!-- ./box-body -->
</div>
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{{Lang::get('log::lang.logs')}}</h3>
    </div>
    <!-- /.box-header -->
    <div class="card-body">
        <div class="row">
            <!--/.col-md-2-->
            <div class="col-md-2 col-sm-6">
                <div class="settingiconblue">
                    <div class="settingdivblue">
                        <a href="{{ url('logs') }}">
                            <span class="fa-stack fa-2x">
                                <i class="fa-solid fa-lock fa-stack-1x"></i>
                            </span>
                        </a>
                    </div>
                    <div class="text-center text-sm">{{Lang::get('log::lang.logs')}}</div>
                </div>
            </div>
        </div>
        <!-- /.row -->
    </div>
    <!-- ./box-body -->
</div>
<?php \Illuminate\Support\Facades\Event::dispatch('service.desk.admin.settings', []); ?>


@stop
