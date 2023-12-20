@extends('themes.default1.admin.layout.admin')

@section('Staffs')
active
@stop

@section('staffs-bar')
active
@stop

@section('agents')
class="active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')


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

<div class="row">
    <div class="col-md-3">
        <!-- Profile Image -->
        <div class="box box-primary">
            <div class="box-body box-profile">

                @if(Auth::user()->profile_pic)
                <img src="{{asset('lb-faveo/profilepic')}}{{'/'}}{{ $agent->profile_pic }}"class="profile-user-img img-responsive img-circle" alt="User Image"/>
                @else
                <img src="{{ Gravatar::src($agent->email) }}" class="profile-user-img img-responsive img-circle" alt="User Image">
                @endif
                <h3 class="profile-username text-center">{!! $agent->user_name !!}</h3>
                <p class="text-muted text-center">Software Engineer</p>
                <ul class="list-group list-group-unbordered">
                    <li class="list-group-item">
                        <b>Followers</b> <a class="pull-right">1,322</a>
                    </li>
                    <li class="list-group-item">
                        <b>Following</b> <a class="pull-right">543</a>
                    </li>
                    <li class="list-group-item">
                        <b>Friends</b> <a class="pull-right">13,287</a>
                    </li>
                </ul>
                <a href="#" class="btn btn-primary btn-block"><b>Follow</b></a>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
        <!-- About Me Box -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">About Me</h3>
            </div><!-- /.box-header -->
            <div class="box-body">
                <strong><i class="fa-solid fa-book margin-r-5"></i>  Education</strong>
                <p class="text-muted">
                    B.S. in Computer Science from the University of Tennessee at Knoxville
                </p>
                <hr>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div><!-- /.col -->
    <div class="col-md-9">
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#activity" data-bs-toggle="tab">Activity</a></li>
                <li><a href="#timeline" data-bs-toggle="tab">Timeline</a></li>
                <li><a href="#settings" data-bs-toggle="tab">Settings</a></li>
            </ul>
            <div class="tab-content">
                <div class="active tab-pane" id="activity">
                    <!-- Post -->
                </div><!-- /.tab-pane -->
                <div class="tab-pane" id="timeline">
                </div><!-- /.tab-pane -->
                <div class="tab-pane" id="settings">
                </div><!-- /.tab-pane -->
            </div><!-- /.tab-content -->
        </div><!-- /.nav-tabs-custom -->
    </div><!-- /.col -->
</div>

@stop