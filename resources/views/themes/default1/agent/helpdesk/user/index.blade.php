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
<h3>{{Lang::get('lang.user_directory')}}</h3>
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
<div class="alert alert-success alert-dismissible">
    <i class="fa  fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('success')}}
</div>
@endif
<!-- failure message -->
@if(Session::has('warning'))
<div class="alert alert-warning alert-dismissible">
    <i class="fa-solid fa-ban"></i><button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <b>{!! Lang::get('lang.alert') !!} !</b>            
    {{Session::get('warning')}}
</div>
@endif
<!-- failure message -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i><button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <b>{!! Lang::get('lang.alert') !!} !</b>            
    {{Session::get('fails')}}
</div>
@endif
<div class="card card-light">

    <div class="card-header">

        <h3 class="card-title">{{ Lang::get('lang.user') }}</h3>

        <div class="card-tools d-flex align-items-center gap-2">

            <div class="has-feedback">
                <input type="text"
                       class="form-control form-control-sm m-0"
                       id="search-text"
                       name="search"
                       placeholder="{{ Lang::get('lang.search') }}">
            </div>

            <div class="btn-group">
                <button type="button"
                        class="btn btn-secondary btn-sm dropdown-toggle"
                        data-bs-toggle="dropdown">
                    <i class="fa-solid fa-eye"></i> {{ Lang::get('lang.view-option') }}
                </button>

                <div class="dropdown-menu dropdown-menu-end">
                    <a href="#" class="dropdown-item all active">{{ Lang::get('lang.all-users') }}</a>
                    <a href="#" class="dropdown-item agents">{{ Lang::get('lang.only-agents') }}</a>
                    <a href="#" class="dropdown-item users">{{ Lang::get('lang.only-users') }}</a>
                    <a href="#" class="dropdown-item active-users">{{ Lang::get('lang.active-users') }}</a>
                    <a href="#" class="dropdown-item inactive">{{ Lang::get('lang.inactive-users') }}</a>
                    <a href="#" class="dropdown-item deleted">{{ Lang::get('lang.deleted-users') }}</a>
                    <a href="#" class="dropdown-item banned">{{ Lang::get('lang.banned-users') }}</a>
                </div>
            </div>

            <a href="{{ url('user-export') }}" class="btn btn-secondary btn-sm text-white">
                Export
            </a>

            <a href="{{ route('user.create') }}" class="btn btn-secondary btn-sm text-white">
                {{ Lang::get('lang.create_user') }}
            </a>

        </div>
    </div>
    
    <div class="card-body">

        <table id="chumper" class="table table-bordered w-100 d-table">
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