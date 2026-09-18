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
<ol class="breadcrumb">
</ol>
@stop
<!-- /breadcrumbs -->
<!-- content -->
@section('content')
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">{!! Lang::get('lang.staffs') !!}</h3>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <!--/.col-md-2-->
                                        <div class="col-md-2 col-sm-6">
                                            <div class="settingiconblue">
                                                <div class="settingdivblue">
                                                    <a href="{{ url('agents') }}"><span class="fa-stack fa-2x">
                                                        <i class="fa-solid fa-user fa-stack-1x"></i>
                                                    </span></a>
                                                </div>
                                                <center class="box-title" >{!! Lang::get('lang.agents') !!}</center>
                                            </div>
                                        </div>
                                        <!--/.col-md-2-->
                                        <!--/.col-md-2-->
                                        <div class="col-md-2 col-sm-6">
                                            <div class="settingiconblue">
                                                <div class="settingdivblue">
                                                    <a href="{{ url('departments') }}"><span class="fa-stack fa-2x">
                                                        <i class="fa-solid fa-sitemap fa-stack-1x"></i>
                                                        </span></a>
                                                </div>
                                                <center class="box-title" >{!! Lang::get('lang.departments') !!}</center>
                                            </div>
                                        </div>
                                        <!--/.col-md-2-->

                                        <!--/.col-md-2-->
                                        <div class="col-md-2 col-sm-6">
                                            <div class="settingiconblue">
                                                <div class="settingdivblue">
                                                    <a href="{{ url('teams') }}"><span class="fa-stack fa-2x">
                                                        <i class="fa-solid fa-users fa-stack-1x"></i>
                                                        </span></a>
                                                </div>
                                                <center class="box-title" >{!! Lang::get('lang.teams') !!}</center>
                                            </div>
                                        </div>
                                        <!--/.col-md-2-->
                                        <!--/.col-md-2-->
                                        <div class="col-md-2 col-sm-6">
                                            <div class="settingiconblue">
                                                <div class="settingdivblue">
                                                    <a href="{{ url('groups') }}"><span class="fa-stack fa-2x">
                                                        
                                                        <i class="fa-solid fa-group fa-stack-1x"></i>
                                                        </span></a>
                                                </div>
                                                <center class="box-title" >{!! Lang::get('lang.groups') !!}</center>
                                            </div>
                                        </div>
                                        <!--/.col-md-2-->
                                    </div>
                                </div>
                                <!-- /.row -->
                            </div>
                            <!-- ./box-body -->
                        </div>
                        <!-- /.box -->



                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">{!! Lang::get('lang.email') !!}</h3>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <!--/.col-md-2-->
                                        <div class="col-md-2 col-sm-6">
                                            <div class="settingiconblue">
                                                <div class="settingdivblue">
                                                    <a href="{{ url('emails') }}"><span class="fa-stack fa-2x">
                                                    <i class="fa-solid fa-envelope-o fa-stack-1x"></i>
                                                    </span></a>
                                                </div>
                                                <center class="box-title" >{!! Lang::get('lang.emails') !!}</center>
                                            </div>
                                        </div>
                                        <!--/.col-md-2-->

                                        <!--/.col-md-2-->
                                        <div class="col-md-2 col-sm-6">
                                            <div class="settingiconblue">
                                                <div class="settingdivblue">
                                                    <a href="{{ url('banlist') }}"><span class="fa-stack fa-2x">
                                                        <i class="fa-solid fa-ban fa-stack-1x"></i>
                                                        </span></a>
                                                </div>
                                                <center class="box-title" >{!! Lang::get('lang.ban_lists') !!}</center>
                                            </div>
                                        </div>
                                        <!--/.col-md-2-->
                                       
                                        <!--/.col-md-2-->
                                        <div class="col-md-2 col-sm-6">
                                            <div class="settingiconblue">
                                                <div class="settingdivblue">
                                                    <a href="{{ url('list-directories') }}"><span class="fa-stack fa-2x">
                                                        
                                                        <i class="fa-solid fa-mail-forward fa-stack-1x"></i>
                                                        </span></a>
                                                </div>
                                                <center class="box-title" >{!! Lang::get('lang.templates') !!}</center>
                                            </div>
                                        </div>
                                        <!--/.col-md-2-->
                                       
                                        <!--/.col-md-2-->
                                        <div class="col-md-2 col-sm-6">
                                            <div class="settingiconblue">
                                                <div class="settingdivblue">
                                                    <a href="{{ url('getdiagno') }}"><span class="fa-stack fa-2x">
                                                        
                                                        <i class="fa-solid fa-plus fa-stack-1x"></i>
                                                        </span></a>
                                                </div>
                                                <center class="box-title" >{!! Lang::get('lang.diagnostics') !!}</center>
                                            </div>
                                        </div>
                                        <!--/.col-md-2-->

                                    </div>
                                </div>
                                <!-- /.row -->
                            </div>
                            <!-- ./box-body -->
                        </div>

                        


                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">{!! Lang::get('lang.manage') !!}</h3>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <!--/.col-md-2-->
                                        <div class="col-md-2 col-sm-6">
                                            <div class="settingiconblue">
                                                <div class="settingdivblue">
                                                    <a href="{{url('helptopic')}}"><span class="fa-stack fa-2x">
                                                        
                                                        <i class="fa-solid fa-file-text-o fa-stack-1x"></i>
                                                    </span></a>
                                                </div>
                                                <center class="box-title" >{!! Lang::get('lang.help_topics') !!}</center>
                                            </div>
                                        </div>
                                        <!--/.col-md-2-->
                                        <!--/.col-md-2-->
                                        <div class="col-md-2 col-sm-6">
                                            <div class="settingiconblue">
                                                <div class="settingdivblue">
                                                    <a href="{{url('sla')}}"><span class="fa-stack fa-2x">
                                                        
                                                        <i class="fa-solid fa-clock-o fa-stack-1x"></i>
                                                        </span></a>
                                                </div>
                                                <center class="box-title" >{!! Lang::get('lang.sla_plans') !!}</center>
                                            </div>
                                        </div>
                                        <!--/.col-md-2-->

                                        <!--/.col-md-2-->
                                        <div class="col-md-2 col-sm-6">
                                            <div class="settingiconblue">
                                                <div class="settingdivblue">
                                                    <a href="{{url('forms')}}"><span class="fa-stack fa-2x">
                                                        
                                                        <i class="fa-solid fa-file-text fa-stack-1x"></i>
                                                        </span></a>
                                                </div>
                                                <center class="box-title" >{!! Lang::get('lang.forms') !!}</center>
                                            </div>
                                        </div>
                                        <!--/.col-md-2-->
                                        <!--/.col-md-2-->
                                        <div class="col-md-2 col-sm-6">
                                            <div class="settingiconblue">
                                                <div class="settingdivblue">
                                                    <a href="{{url('workflow')}}"><span class="fa-stack fa-2x">
                                                        
                                                        <i class="fa-solid fa-sitemap fa-stack-1x"></i>
                                                        </span></a>
                                                </div>
                                                <center class="box-title" >{!! Lang::get('lang.workflow') !!}</center>
                                            </div>
                                        </div>
                                        <!--/.col-md-2-->
                                        
                                    </div>
                                </div>
                                <!-- /.row -->
                            </div>
                            <!-- ./box-body -->
                        </div>



                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">{!! Lang::get('lang.settings') !!}</h3>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <!--/.col-md-2-->
                                        <div class="col-md-2 col-sm-6">
                                            <div class="settingiconblue">
                                                <div class="settingdivblue">
                                                    <a href="{{url('getcompany')}}"><span class="fa-stack fa-2x">
                                                        
                                                        <i class="fa-solid fa-building-o fa-stack-1x"></i>
                                                    </span></a>
                                                </div>
                                                <center class="box-title" >{!! Lang::get('lang.company') !!}</center>
                                            </div>
                                        </div>
                                        <!--/.col-md-2-->
                                        <!--/.col-md-2-->
                                        <div class="col-md-2 col-sm-6">
                                            <div class="settingiconblue">
                                                <div class="settingdivblue">
                                                    <a href="{{url('getsystem')}}"><span class="fa-stack fa-2x">
                                                        
                                                        <i class="fa-solid fa-laptop fa-stack-1x"></i>
                                                        </span></a>
                                                </div>
                                                <center class="box-title" >{!! Lang::get('lang.system') !!}</center>
                                            </div>
                                        </div>
                                        <!--/.col-md-2-->

                                        <!--/.col-md-2-->
                                        <div class="col-md-2 col-sm-6">
                                            <div class="settingiconblue">
                                                <div class="settingdivblue">
                                                    <a href="{{url('getemail')}}"><span class="fa-stack fa-2x">
                                                        
                                                        <i class="fa-solid fa-at fa-stack-1x"></i>
                                                        </span></a>
                                                </div>
                                                <center class="box-title" >{!! Lang::get('lang.email') !!}</center>
                                            </div>
                                        </div>
                                        <!--/.col-md-2-->

                                        <!--/.col-md-2-->
                                        <div class="col-md-2 col-sm-6">
                                            <div class="settingiconblue">
                                                <div class="settingdivblue">
                                                    <a href="{{url('getticket')}}"><span class="fa-stack fa-2x">
                                                        
                                                        <i class="fa-solid fa-file-text-o fa-stack-1x"></i>
                                                        </span></a>
                                                </div>
                                                <center class="box-title" >{!! Lang::get('lang.ticket') !!}</center>
                                            </div>
                                        </div>
                                        <!--/.col-md-2-->
                                        
                                        <!--/.col-md-2-->
                                        <div class="col-md-2 col-sm-6">
                                            <div class="settingiconblue">
                                                <div class="settingdivblue">
                                                    <a href="{{url('getresponder')}}"><span class="fa-stack fa-2x">
                                                        
                                                        <i class="fa-solid fa-reply-all fa-stack-1x"></i>
                                                        </span></a>
                                                </div>
                                                <center class="box-title" >{!! Lang::get('lang.auto_response') !!}</center>
                                            </div>
                                        </div>
                                        <!--/.col-md-2-->
      
                                        <!--/.col-md-2-->
                                        <div class="col-md-2 col-sm-6">
                                            <div class="settingiconblue">
                                                <div class="settingdivblue">
                                                    <a href="{{url('getalert')}}"><span class="fa-stack fa-2x">
                                                        <i class="fa-solid fa-bell-o fa-stack-1x"></i>
                                                        </span></a>
                                                </div>
                                                <center class="box-title" >{!! Lang::get('lang.alert_notices') !!}</center>
                                            </div>
                                        </div>
                                        <!--/.col-md-2-->

                                        <!--/.col-md-2-->
                                        <div class="col-md-2 col-sm-6">
                                            <div class="settingiconblue">
                                                <div class="settingdivblue">
                                                    <a href="{{url('languages')}}"><span class="fa-stack fa-2x">
                                                        <i class="fa-solid fa-language fa-stack-1x"></i>
                                                        </span></a>
                                                </div>
                                                <center class="box-title">{!! Lang::get('lang.language') !!}</center>
                                            </div>
                                        </div>
                                        <!--/.col-md-2-->
                                        <div class="col-md-2 col-sm-6">
                                            <div class="settingiconblue">
                                                <div class="settingdivblue">
                                                    <a href="{{url('job-scheduler')}}"><span class="fa-stack fa-2x">
                                                        
                                                        <i class="fa  fa-hourglass-o fa-stack-1x"></i>
                                                        </span></a>
                                                </div>
                                                <center class="box-title" >{!! Lang::get('lang.cron') !!}</center>
                                            </div>
                                        </div>
                                        <!--/.col-md-2-->

                                    </div>
                                </div>
                                <!-- /.row -->
                            </div>
                            <!-- ./box-body -->
                        </div>




                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">{!! Lang::get('lang.widgets') !!}</h3>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <!--/.col-md-2-->
                                        <div class="col-md-2 col-sm-6">
                                            <div class="settingiconblue">
                                                <div class="settingdivblue">
                                                    <a href="{{ url('widgets') }}"><span class="fa-stack fa-2x">
                                                        <i class="fa-solid fa-list-alt fa-stack-1x"></i>
                                                    </span></a>
                                                </div>
                                                <center class="box-title" >{!! Lang::get('lang.widgets') !!}</center>
                                            </div>
                                        </div>
                                        <!--/.col-md-2-->                                        


                                        <!--/.col-md-2-->
                                        <div class="col-md-2 col-sm-6">
                                            <div class="settingiconblue">
                                                <div class="settingdivblue">
                                                    <a href="{{ url('social-buttons') }}"><span class="fa-stack fa-2x">
                                                        <i class="fa-solid fa-cubes fa-stack-1x"></i>
                                                    </span></a>
                                                </div>
                                                <center class="box-title" >{!! Lang::get('lang.social') !!}</center>
                                            </div>
                                        </div>
                                        <!--/.col-md-2-->                                        


                                    </div>
                                </div>
                                <!-- /.row -->
                            </div>
                            <!-- ./box-body -->
                        </div>
                    

                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">{!! Lang::get('lang.plugin') !!}</h3>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <!--/.col-md-2-->
                                        <div class="col-md-2 col-sm-6">
                                            <div class="settingiconblue">
                                                <div class="settingdivblue">
                                                    <a href="{{ url('plugins') }}"><span class="fa-stack fa-2x">
                                                        
                                                        <i class="fa-solid fa-plug fa-stack-1x"></i>
                                                    </span></a>
                                                </div>
                                                <center class="box-title" >{!! Lang::get('lang.plugin') !!}</center>
                                            </div>
                                        </div>
                                        <!--/.col-md-2-->
                                        
                                    </div>
                                </div>
                                <!-- /.row -->
                            </div>
                            <!-- ./box-body -->
                        </div>


@stop
