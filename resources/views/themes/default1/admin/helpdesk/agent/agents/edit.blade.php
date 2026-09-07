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

@section('agents')
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
<?php //dd($user->agent_tzone); ?>
{!! html()->modelForm($user, 'PATCH', url('agents/'.$user->id))->open() !!}

@if(Session::has('errors'))
<?php //dd($errors); ?>
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>Alert!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <br/>
    @if($errors->first('user_name'))
    <li class="error-message-padding">{!! $errors->first('user_name', ':message') !!}</li>
    @endif
    @if($errors->first('first_name'))
    <li class="error-message-padding">{!! $errors->first('first_name', ':message') !!}</li>
    @endif
    @if($errors->first('last_name'))
    <li class="error-message-padding">{!! $errors->first('last_name', ':message') !!}</li>
    @endif
    @if($errors->first('email'))
    <li class="error-message-padding">{!! $errors->first('email', ':message') !!}</li>
    @endif
    @if($errors->first('ext'))
    <li class="error-message-padding">{!! $errors->first('ext', ':message') !!}</li>
    @endif
    @if($errors->first('phone_number'))
    <li class="error-message-padding">{!! $errors->first('phone_number', ':message') !!}</li>
    @endif
    @if($errors->first('mobile'))
    <li class="error-message-padding">{!! $errors->first('mobile', ':message') !!}</li>
    @endif
    @if($errors->first('active'))
    <li class="error-message-padding">{!! $errors->first('active', ':message') !!}</li>
    @endif
    @if($errors->first('role'))
    <li class="error-message-padding">{!! $errors->first('role', ':message') !!}</li>
    @endif
    @if($errors->first('group'))
    <li class="error-message-padding">{!! $errors->first('group', ':message') !!}</li>
    @endif
    @if($errors->first('primary_department'))
    <li class="error-message-padding">{!! $errors->first('primary_department', ':message') !!}</li>
    @endif
    @if($errors->first('agent_time_zone'))
    <li class="error-message-padding">{!! $errors->first('agent_time_zone', ':message') !!}</li>
    @endif
    @if($errors->first('team'))
    <li class="error-message-padding">{!! $errors->first('team', ':message') !!}</li>
    @endif 
</div>
@endif
@if(Session::has('fails2'))
    <div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>Alert!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <br/>
        <li class="error-message-padding">{!! Session::get('fails2') !!}</li>
    </div>
@endif
<!-- <section class="app-content"> -->
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.edit_an_agent') !!}</h3>	
    </div>
    <div class="card-body">

        <div class="row">
            <!-- username -->
            <div class="col-sm-4 mb-3 {{ $errors->has('user_name') ? 'has-error' : '' }}">

                {!! html()->label(Lang::get('lang.user_name'), 'user_name') !!} <span class="text-red"> *</span>

                {!! html()->text('user_name', null)->class('form-control') !!}

            </div>

            <!-- firstname -->
            <div class="col-sm-4 mb-3 {{ $errors->has('first_name') ? 'has-error' : '' }}">

                {!! html()->label(Lang::get('lang.first_name'), 'first_name') !!} <span class="text-red"> *</span>

                {!! html()->text('first_name', null)->class('form-control') !!}

            </div>

            <!-- Lastname -->
            <div class="col-sm-4 mb-3 {{ $errors->has('last_name') ? 'has-error' : '' }}">

                {!! html()->label(Lang::get('lang.last_name'), 'last_name') !!} <span class="text-red"> *</span>

                {!! html()->text('last_name', null)->class('form-control') !!}

            </div>

        </div>

        <div class="row">
            <!-- Email -->
            <div class="col-sm-4 mb-3 {{ $errors->has('email') ? 'has-error' : '' }}">

                {!! html()->label(Lang::get('lang.email_address'), 'email') !!} <span class="text-red"> *</span>

                {!! html()->email('email', null)->class('form-control') !!}

            </div>

            <div class="col-sm-1 mb-3 {{ $errors->has('ext') ? 'has-error' : '' }}">

                <label for="ext">EXT</label>	

                {!! html()->text('ext', null)->class('form-control') !!}

            </div>
            <!--country code-->
            <div class="col-sm-1 mb-3 {{ Session::has('country_code') ? 'has-error' : '' }}">

                {!! html()->label(Lang::get('lang.country-code'), 'country_code') !!}
                {!! html()->text('country_code', null)->class('form-control')->placeholder($phonecode)->attributes(['title' => Lang::get('lang.enter-country-phone-code')]) !!}

            </div>
            <!-- phone -->
            <div class="col-sm-3 mb-3 {{ $errors->has('phone_number') ? 'has-error' : '' }}">

                {!! html()->label(Lang::get('lang.phone'), 'phone_number') !!}

                {!! html()->text('phone_number', null)->class('form-control') !!}

            </div>

            <!-- Mobile -->
            <div class="col-sm-3 mb-3 {{ $errors->has('mobile') ? 'has-error' : '' }}">

                {!! html()->label(Lang::get('lang.mobile_number'), 'mobile') !!}

                {!! html()->number('mobile', null)->class('form-control') !!}

            </div>

        </div>

        <div class="row">
            <!-- assigned group -->
            <div class="col-sm-4 mb-3 {{ $errors->has('group') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.assigned_group'), 'assign_group') !!} <span class="text-red"> *</span>

                {!! html()->select('group', [''=>Lang::get('lang.select_a_group'), Lang::get('lang.groups')=>$groups->pluck('name','id')->toArray()], $user->assign_group)->class('form-control select') !!}
            </div>

            <!-- primary department -->
            <div class="col-sm-4 mb-3 {{ $errors->has('primary_department') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.primary_department'), 'primary_dpt') !!} <span class="text-red"> *</span>

                {!! html()->select('primary_department', [''=>Lang::get('lang.select_a_department'), Lang::get('lang.departments')=>$departments->pluck('name','id')->toArray()], $user->primary_dpt)->class('form-control select') !!}
            </div>

            <!-- agent timezone -->
            <div class="col-sm-4 mb-3 {{ $errors->has('agent_time_zone') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.agent_time_zone'), 'agent_tzone') !!} <span class="text-red"> *</span>

                {!! html()->select('agent_time_zone', [''=>Lang::get('lang.select_a_time_zone'), Lang::get('lang.time_zones')=>$timezones->pluck('name','id')->toArray()], $user->agent_tzone)->class('form-control select') !!}
            </div>
        </div>

        <div class="row">
            <div class="col-sm-4">
                <!-- acccount type -->
                <div class="mb-3 {{ $errors->has('active') ? 'has-error' : '' }}">

                    {!! html()->label(Lang::get('lang.status'), 'active') !!}

                    <div class="row">
                        <div class="col-sm-3">
                            {!! html()->radio('active', true, '1') !!} {{ Lang::get('lang.active') }}
                        </div>
                        <div class="col-sm-3">
                            {!! html()->radio('active', null, '0') !!} {{Lang::get('lang.inactive')}}
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-sm-4">
                  <!-- role -->
                <div class="mb-3 {{ $errors->has('role') ? 'has-error' : '' }}">

                    {!! html()->label(Lang::get('lang.role'), 'role') !!}

                    <div class="row">
                        <div class="col-sm-3">
                            {!! html()->radio('role', true, 'admin') !!} {{Lang::get('lang.admin')}}
                        </div>
                        <div class="col-sm-3">
                            {!! html()->radio('role', null, 'agent') !!} {{Lang::get('lang.agent')}}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-4">
                <!-- team -->
                <div class="mb-3 {{ $errors->has('team') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.assigned_team'), 'agent_tzone') !!} <span class="text-red"> *</span>
                </div>
                @foreach($teams as $key => $val)
                <div class="mb-3 ">
                    <input type="checkbox" name="team[]" value="<?php echo $val; ?> " <?php
                    if (in_array($val, $assign)) {
                        echo ('checked');
                    }
                    ?> > &nbsp;<?php echo "  " . $key; ?><br/>
                </div>
                @endforeach
            </div>
        </div>

         <div>
            {!! html()->label(Lang::get('lang.agent_signature'), 'agent_signature') !!}
            {!! html()->textarea('agent_sign', null)->class('form-control')->attributes(['size' => '30x5']) !!}
        </div>
    </div>
    <div class="card-footer">
        {!! html()->submit(Lang::get('lang.update'))->class('btn btn-primary') !!}
    </div>
</div>
{!! html()->closeModelForm() !!}
@stop