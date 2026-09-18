@extends('themes.default1.admin.layout.admin')

@section('Staffs')
class="nav-link active"
@stop

@section('staff-menu-parent')
class="nav-item menu-open"
@stop

@section('staff-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('groups')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{{Lang::get('lang.staffs')}}</h3>
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
{!! html()->form('POST', route('groups.store'))->open() !!}
@if(Session::has('errors'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b> {!! Lang::get('lang.alert') !!}</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <br/>
    @if($errors->first('name'))
    <li class="error-message-padding">{!! $errors->first('name', ':message') !!}</li>
    @endif
    @if($errors->first('group_status'))
    <li class="error-message-padding">{!! $errors->first('group_status', ':message') !!}</li>
    @endif
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title"> {{Lang::get('lang.create_a_group')}} </h3> 
    </div>
    <div class="card-body">
        
        <div class="row">
            <!-- name -->
            <div class="col-sm-4 mb-3 {{ $errors->has('name') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.name'), 'name') !!} <span class="text-red"> *</span>
                {!! html()->text('name', null)->class('form-control') !!}
            </div>
            <!-- group status -->
            <div class="col-sm-6 mb-3 {{ $errors->has('group_status') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.status'), 'group_status') !!}
                <div class="row">
                    <div class="col-sm-2">
                        {!! html()->radio('group_status', true, '1') !!} {{Lang::get('lang.active')}}
                    </div>
                    <div class="col-sm-3">
                        {!! html()->radio('group_status', null, '0') !!} {{Lang::get('lang.inactive')}}
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-light">
            
            <div class="card-header">
                
                <h3 class="card-title">Permissions</h3>
            </div>

            <div class="card-body">
                <!-- can create ticket -->
                <div class="row">
                    {!! html()->checkbox('can_create_ticket', null, 1)->class('checkbox') !!}
                    &nbsp;{!! html()->label(Lang::get('lang.can_create_ticket'), 'can_create_ticket')->attributes(['style' => 'line-height:1;']) !!}
                </div>
                <!-- can_edit_ticket -->
                <div class="row">
                    {!! html()->checkbox('can_edit_ticket', null, 1)->class('checkbox') !!}
                    &nbsp;{!! html()->label(Lang::get('lang.can_edit_ticket'), 'can_edit_ticket')->attributes(['style' => 'line-height:1;']) !!}
                </div>
                <!-- can post ticket -->
                <div class="row">
                    {!! html()->checkbox('can_post_ticket', null, 1)->class('checkbox') !!}
                    &nbsp;{!! html()->label(Lang::get('lang.can_post_ticket'), 'can_post_ticket')->attributes(['style' => 'line-height:1;']) !!}
                </div>
                <!-- can_close_ticket -->
                <div class="row">
                    {!! html()->checkbox('can_close_ticket', null, 1)->class('checkbox') !!}
                    &nbsp;{!! html()->label(Lang::get('lang.can_close_ticket'), 'can_close_ticket')->attributes(['style' => 'line-height:1;']) !!}
                </div>
                <!-- can delete ticket -->
                <div class="row">
                    {!! html()->checkbox('can_delete_ticket', null, 1)->class('checkbox') !!}
                    &nbsp;{!! html()->label(Lang::get('lang.can_delete_ticket'), 'can_delete_ticket')->attributes(['style' => 'line-height:1;']) !!}
                </div>
                <!-- can assign ticket -->
                <div class="row">
                    {!! html()->checkbox('can_assign_ticket', null, 1)->class('checkbox') !!}
                    &nbsp;{!! html()->label(Lang::get('lang.can_assign_ticket'), 'can_assign_ticket')->attributes(['style' => 'line-height:1;']) !!}
                </div>
                <!-- can ban email -->
                <div class="row">
                    {!! html()->checkbox('can_ban_email', null, 1)->class('checkbox') !!}
                    &nbsp;{!! html()->label(Lang::get('lang.can_ban_emails'), 'can_ban_email')->attributes(['style' => 'line-height:1;']) !!}
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer">
        {!! html()->submit(Lang::get('lang.submit'))->class('btn btn-primary') !!}
    </div>
</div>
{!! html()->closeModelForm() !!}
@stop