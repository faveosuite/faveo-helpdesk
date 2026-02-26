@extends('themes.default1.agent.layout.agent')

@section('Users')
class="nav-link active"
@stop

@section('user-bar')
class="nav-link active"
@stop

@section('user')
class="active"
@stop

@section('user-directory')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h1>{{Lang::get('lang.user_directory')}}</h1>
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

<!-- check whether success or not -->
@if(Session::has('success'))
<div class="alert alert-success alert-dismissable">
    <i class="fa  fa-check-circle"></i>
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    {{Session::get('success')}}
</div>
@endif
<!-- failure message -->
@if(Session::has('warning'))
<div class="alert alert-warning alert-dismissable">
    <i class="fa fa-ban"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <b>{!! Lang::get('lang.alert') !!} !</b>            
    {{Session::get('warning')}}
</div>
@endif
<!-- failure message -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissable">
    <i class="fa fa-ban"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <b>{!! Lang::get('lang.alert') !!} !</b>            
    {{Session::get('fails')}}
</div>
@endif
<div class="card card-light">

    <div class="card-header">
        
        <h3 class="card-title">{{Lang::get('lang.user')}}</h3>

        <div class="card-tools">
            
            <div class="has-feedback" style="display: inline-block;">
                <input type="text" class="form-control input-sm m-0" id="search-text" name="search" placeholder="{{Lang::get('lang.search')}}">
            </div>

            <div class="btn-group">
        
                <button type="button" class="btn btn-tool btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                  <i class="fas fa-eye"> </i> {{Lang::get('lang.view-option')}}
                </button>
        
                <div class="dropdown-menu dropdown-menu-right" role="menu" style="">
                    <a href="#" class="dropdown-item all active">{{Lang::get('lang.all-users')}}</a>
                    <a href="#" class="dropdown-item agents">{{Lang::get('lang.only-agents')}}</a>
                    <a href="#" class="dropdown-item users">{{Lang::get('lang.only-users')}}</a>
                    <a href="#" class="dropdown-item active-users">{{Lang::get('lang.active-users')}}</a>
                    <a href="#" class="dropdown-item inactive">{{Lang::get('lang.inactive-users')}}</a>
                    <a href="#" class="dropdown-item deleted">{{Lang::get('lang.deleted-users')}}</a>
                    <a href="#" class="dropdown-item banned">{{Lang::get('lang.banned-users')}}</a>
                </div>
            </div>
              
            <a href="{{url('user-export')}}" class="btn btn-tool btn-default">Export</a>
            
            <a href="{{route('user.create')}}" class="btn btn-tool btn-default">{{Lang::get('lang.create_user')}}</a>
        </div>
    </div>
    
    <div class="card-body">

        <table id="chumper" class="table table-bordered" style="width:100%;display:table;">
            <thead>
                <tr>
                    <th>{{Lang::get('lang.name')}}</th>
                    <th>{{Lang::get('lang.email')}}</th>
                    <th>{{Lang::get('lang.phone')}}</th>
                    <th>{{Lang::get('lang.status')}}</th>
                    <th>{{Lang::get('lang.last_login')}}</th>
                    <th>{{Lang::get('lang.role')}}</th>
                    <th>{{Lang::get('lang.action')}}</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        @include('vendor.Chumper.user-javascript')
    </div>
</div>
@stop
<!-- /content -->