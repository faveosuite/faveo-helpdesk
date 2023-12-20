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

@section('social-login')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
    <h3>{{\Illuminate\Support\Facades\Lang::get('lang.social-media')}}</h3>
@stop
<!-- /header -->
<!-- breadcrumbs -->
@section('breadcrumbs')
<ol class="breadcrumb">

</ol>
@stop
@section('content')
@if (count($errors) > 0)
<div class="alert alert-danger">
    <strong>{{Lang::get('lang.woops')}}</strong> {{Lang::get('lang.theirisproblem')}}<br><br>
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
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
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {!!Session::get('fails')!!}
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{{trans('lang.social-media')}}</h3>
    </div>

    <div class="card-body">
        <div class="row">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>{{trans('lang.provider')}}</th>
                        <th>{{trans('lang.status')}}</th>
                        <th>{{trans('lang.action')}}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{\Illuminate\Support\Facades\Lang::get('lang.facebook')}}</td>
                        <td>
                            @if($social->checkActive('facebook')===true)
                                <span style="color: green">{{trans('lang.active')}}</span>
                            @else
                                <span style="color: red">{{trans('lang.inactive')}}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{url('social/media/facebook')}}" class="btn btn-primary btn-sm">{{trans('lang.settings')}}</a>                        </td>
                    </tr>
                    <tr>
                        <td>{{\Illuminate\Support\Facades\Lang::get('lang.google')}}</td>
                        <td>
                            @if($social->checkActive('google')===true)
                                <span style="color: green">{{trans('lang.active')}}</span>
                            @else
                                <span style="color: red">{{trans('lang.inactive')}}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{url('social/media/google')}}" class="btn btn-primary btn-sm">{{trans('lang.settings')}}</a>                        </td>
                    </tr>
                    <tr>
                        <td>{{\Illuminate\Support\Facades\Lang::get('lang.github')}}</td>
                        <td>
                            @if($social->checkActive('github')===true)
                                <span style="color: green">{{trans('lang.active')}}</span>
                            @else
                                <span style="color: red">{{trans('lang.inactive')}}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{url('social/media/github')}}" class="btn btn-primary btn-sm">{{trans('lang.settings')}}</a>                        </td>
                    </tr>
                    <tr>
                        <td>{{\Illuminate\Support\Facades\Lang::get('lang.twitter')}}</td>
                        <td>
                            @if($social->checkActive('twitter')===true)
                                <span style="color: green">{{trans('lang.active')}}</span>
                            @else
                                <span style="color: red">{{trans('lang.inactive')}}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{url('social/media/twitter')}}" class="btn btn-primary btn-sm">{{trans('lang.settings')}}</a>                        </td>
                    </tr>
                    <tr>
                        <td>{{\Illuminate\Support\Facades\Lang::get('lang.linkedin')}}</td>
                        <td>
                            @if($social->checkActive('linkedin')===true)
                                <span style="color: green">{{trans('lang.active')}}</span>
                            @else
                                <span style="color: red">{{trans('lang.inactive')}}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{url('social/media/linkedin')}}" class="btn btn-primary btn-sm">{{trans('lang.settings')}}</a>                        </td>
                    </tr>
                    <tr>
                        <td>{{\Illuminate\Support\Facades\Lang::get('lang.bitbucket')}}</td>
                        <td>
                            @if($social->checkActive('bitbucket')===true)
                                <span style="color: green">{{trans('lang.active')}}</span>
                            @else
                                <span style="color: red">{{trans('lang.inactive')}}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{url('social/media/bitbucket')}}" class="btn btn-primary btn-sm">{{trans('lang.settings')}}</a>                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop
