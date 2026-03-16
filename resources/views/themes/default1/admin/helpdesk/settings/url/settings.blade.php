@extends('themes.default1.admin.layout.admin')

@section('Manage')
class="nav-link active"
@stop

@section('manage-menu-parent')
class="nav-item menu-open"
@stop

@section('manage-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('url')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{!! Lang::get('lang.url') !!}</h3>
@stop
<!-- /header -->
<!-- breadcrumbs -->
@section('breadcrumbs')
<ol class="breadcrumb">
</ol>
@stop

@section('content')
{!! html()->form('PATCH', url('url/settings'))->open() !!}

@if (count($errors) > 0)
<div class="alert alert-danger">
    <strong>{{Lang::get('lang.woops')}}</strong> {{Lang::get('lang.theirisproblem')}} <br><br>
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
    <i class="fa  fa-circle-check"></i>
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
        <h3 class="card-title">URL {{trans('lang.settings')}}</h3>
    </div>

    <div class="card-body">
        
        
        <div class="row">

            <div class="col-md-3">
                {!! html()->label('WWW/non-WWW', 'www') !!}<br/>
                {!! html()->radio('www', $www['www'], 'yes')->class('option') !!} WWW&nbsp;&nbsp;
                {!! html()->radio('www', $www['nonwww'], 'no')->class('option') !!} Non WWW
            </div>
 
            <div class="col-md-3">
                
                {!! html()->label('SSl', 'option') !!}<br/>
                {!! html()->radio('ssl', $https['https'], 'yes')->class('option') !!} HTTPS&nbsp;&nbsp;
                {!! html()->radio('ssl', $https['http'], 'no')->class('option') !!} HTTP
            </div>
        </div>
    </div>
    
    <div class="card-footer">
        {!! html()->submit(Lang::get('lang.submit'))->class('btn btn-primary') !!}
    </div>
</div>
{!! html()->closeModelForm() !!}
@stop
