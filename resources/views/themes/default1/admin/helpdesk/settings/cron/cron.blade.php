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

@section('cron')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{{ Lang::get('lang.settings') }}</h3>
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
<style type="text/css">
    pre{
        background-color: #f5f5f5;
    border: 1px solid #ccc;
    border-radius: 4px;
    color: #333;
    display: block;
    font-size: 13px;
    line-height: 1.42857;
    margin: 0 0 10px;
    padding: 9.5px;
    word-break: break-all;
    }
</style>
<!-- open a form -->

@if($warn!=="")
@include('themes.default1.admin.helpdesk.settings.cron.cron-new')
@else 
@include('themes.default1.admin.helpdesk.settings.cron.cron-new')
@endif
@stop
