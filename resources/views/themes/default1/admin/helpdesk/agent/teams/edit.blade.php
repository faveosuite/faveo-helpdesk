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

@section('teams')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{{Lang::get('lang.teams')}}</h3>
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
{!! html()->modelForm($teams, 'PATCH', url('teams/'.$teams->id))->open() !!}

@if(Session::has('errors'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>Alert!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <br/>
    @if($errors->first('name'))
    <li class="error-message-padding">{!! $errors->first('name', ':message') !!}</li>
    @endif
    @if($errors->first('team_lead'))
    <li class="error-message-padding">{!! $errors->first('team_lead', ':message') !!}</li>
    @endif
    @if($errors->first('status'))
    <li class="error-message-padding">{!! $errors->first('status', ':message') !!}</li>
    @endif
</div>
@endif

<div class="card card-light">

    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.edit_a_team') !!}</h3>	
    </div>
    
    <div class="card-body">
       
        <div class="row">
            <!-- name -->
            <div class="col-sm-5 mb-3 {{ $errors->has('name') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.name'), 'name') !!} <span class="text-red"> *</span>
                {!! html()->text('name', null)->class('form-control') !!}
            </div>
            <!-- team lead -->
            <div class="col-sm-4 mb-3 {{ $errors->has('team_lead') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.team_lead'), 'team_lead') !!}
                {!! html()->select('team_lead', [''=>Lang::get('lang.select_a_team_lead'), Lang::get('lang.members')=>$user->pluck('full_name','id')->toArray()], null)->class('form-control') !!}	
            </div>

            <div class="col-sm-3">
                <!-- status -->
                <div class="mb-3 {{ $errors->has('status') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.status'), 'status') !!}
                    <div class="row">
                        <div class="col-sm-6">
                            {!! html()->radio('status', true, '1') !!} {{Lang::get('lang.active')}}
                        </div>
                        <div class="col-sm-6">
                            {!! html()->radio('status', null, '0') !!} {{Lang::get('lang.inactive')}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- admin notes -->
        <div>
            {!! html()->label(Lang::get('lang.admin_notes'), 'admin_notes') !!}
            {!! html()->textarea('admin_notes', null)->class('form-control')->attributes(['size' => '30x5']) !!}
        </div>
    </div>
    <div class="card-footer">
        {!! html()->submit(Lang::get('lang.update'))->class('btn btn-primary') !!}
    </div>
    {!! html()->closeModelForm() !!}
</div>
@stop