@extends('themes.default1.admin.layout.admin')
@section('content')


    <div class="row">
    <div class="col-md-6">

{!! html()->modelForm($user, 'PATCH', url('admin-profile'))->acceptsFiles()->open() !!}

<div class="box box-primary">

	<div class="content-header">

	 	<h4>Profile	{!! html()->submit(Lang::get('lang.save'))->class('form-group btn btn-primary pull-right') !!}</h4>

	</div>

<div class="box-body">

@if(Session::has('success'))
                    <div class="alert alert-success alert-dismissable">
                        <i class="fa fa-ban"></i>
                        <b>Alert!</b> Success.
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        {{Session::get('success')}}
                    </div>
                    @endif
                    <!-- fail message -->
                    @if(Session::has('fails'))
                    <div class="alert alert-danger alert-dismissable">
                        <i class="fa fa-ban"></i>
                        <b>Alert!</b> Failed.
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        {{Session::get('fails')}}
                    </div>
                    @endif

        <!-- first name -->
		<div class="form-group {{ $errors->has('first_name') ? 'has-error' : '' }}">

			{!! html()->label(Lang::get('lang.first_name'), 'first_name') !!}
			{!! $errors->first('first_name', '<spam class="help-block">:message</spam>') !!}
			{!! html()->text('first_name', null)->class('form-control') !!}

		</div>
		<!-- last name -->
		<div class="form-group {{ $errors->has('last_name') ? 'has-error' : '' }}">

			{!! html()->label(Lang::get('lang.last_name'), 'last_name') !!}
			{!! $errors->first('last_name', '<spam class="help-block">:message</spam>') !!}
			{!! html()->text('last_name', null)->class('form-control') !!}

		</div>
		<!-- gender -->
		<div class="form-group">
			{!! html()->label(Lang::get('lang.gender'), 'gender') !!}
			<div class="row">
				<div class="col-xs-3">
					{!! html()->radio('gender', true, '1') !!}{{Lang::get('lang.male')}}
				</div>
				<div class="col-xs-3">
					{!! html()->radio('gender', null, '0') !!}{{Lang::get('lang.female')}}
				</div>
			</div>
		</div>



		<div class="form-group">

			{!! html()->label(Lang::get('lang.email_address'), 'email') !!}
			<div>
				{{$user->email}}
			</div>
		</div>
		<!-- company -->
		<div class="form-group {{ $errors->has('company') ? 'has-error' : '' }}">

			{!! html()->label(Lang::get('lang.company'), 'company') !!}
			{!! $errors->first('company', '<spam class="help-block">:message</spam>') !!}
			{!! html()->text('company', null)->class('form-control') !!}

		</div>

		<div class="row">
			<!-- phone extension -->
			<div class="col-xs-3 form-group {{ $errors->has('ext') ? 'has-error' : '' }}">

				{!! html()->label(Lang::get('lang.ext'), 'ext') !!}
				{!! $errors->first('ext', '<spam class="help-block">:message</spam>') !!}
				{!! html()->text('ext', null)->class('form-control') !!}

			</div>
			<!-- phone number -->
			<div class="col-xs-9 form-group {{ $errors->has('phone_number') ? 'has-error' : '' }}">

				{!! html()->label(Lang::get('lang.phone'), 'phone_number') !!}
				{!! $errors->first('phone_number', '<spam class="help-block">:message</spam>') !!}
				{!! html()->text('phone_number', null)->class('form-control') !!}

			</div>
		</div>
			<!-- mobile -->
			<div class="form-group {{ $errors->has('mobile') ? 'has-error' : '' }}">

				{!! html()->label(Lang::get('lang.mobile_number'), 'mobile') !!}
				{!! $errors->first('mobile', '<spam class="help-block">:message</spam>') !!}
				{!! html()->number('mobile', null)->class('form-control') !!}

			</div>

	<!-- profile pic -->
	<div class="form-group {{ $errors->has('profile_pic') ? 'has-error' : '' }}">

		{!! html()->label(Lang::get('lang.profile_pic'), 'profile_pic') !!}
		{!! $errors->first('profile_pic', '<spam class="help-block">:message</spam>') !!}
		{!! html()->file('profile_pic') !!}

	</div>

	{!! html()->token() !!}
	{!! html()->closeModelForm() !!}
</div>
</div>
</div>
<div class="col-md-6">

    {!! html()->modelForm($user, 'PATCH', url('admin-profile-password/'.$user->id))->open() !!}

<div class="box box-primary">

	<div class="content-header">

	 	<h4>Change Password	{!! html()->submit(Lang::get('lang.save'))->class('form-group btn btn-primary pull-right') !!}</h4>

	</div>

<div class="box-body">
					@if(Session::has('success'))
                    <div class="alert alert-success alert-dismissable">
                        <i class="fa fa-ban"></i>
                        <b>Alert!</b> Success.
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        {{Session::get('success')}}
                    </div>
                    @endif
                    <!-- fail message -->
                    @if(Session::has('fails'))
                    <div class="alert alert-danger alert-dismissable">
                        <i class="fa fa-ban"></i>
                        <b>Alert!</b> Failed.
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        {{Session::get('fails')}}
                    </div>
                    @endif
	<!-- old password -->
	<div class="mb-3 {{ $errors->has('old_password') ? 'has-error' : '' }}">
			{!! html()->label(Lang::get('lang.old_password'), 'old_password') !!}
			{!! $errors->first('old_password', '<span class="help-block">:message</span>') !!}
            <div class="input-group">
                {!! html()->password('old_password')->placeholder('Password')->class('form-control') !!}
                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
            </div>
    </div>
    <!-- new password -->
    <div class="mb-3 {{ $errors->has('new_password') ? 'has-error' : '' }}">
    		{!! html()->label(Lang::get('lang.new_password'), 'new_password') !!}
			{!! $errors->first('new_password', '<span class="help-block">:message</span>') !!}
            <div class="input-group">
                {!! html()->password('new_password')->placeholder('New Password')->class('form-control') !!}
                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
            </div>
    </div>
    <!-- confirm password -->
    <div class="mb-3 {{ $errors->has('confirm_password') ? 'has-error' : '' }}">
    		{!! html()->label(Lang::get('lang.confirm_password'), 'confirm_password') !!}
			{!! $errors->first('confirm_password', '<span class="help-block">:message</span>') !!}
            <div class="input-group">
                {!! html()->password('confirm_password')->placeholder('Confirm Password')->class('form-control') !!}
                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
            </div>
    </div>




</div>
</div>
</div>
</div>


{!! html()->closeModelForm() !!}
@stop