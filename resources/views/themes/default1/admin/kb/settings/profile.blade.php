@extends('themes.default1.admin.layout.kb')
@section('content')

    <div class="row">
    <div class="col-md-6">

{!! html()->modelForm($user, 'PATCH', url('post-profile'))->acceptsFiles()->open() !!}

<div class="box box-primary">

	<div class="app-content-header">

	 	<h4>Profile	{!! html()->submit('Save')->class('mb-3 btn btn-primary pull-right') !!}</h4>

	</div>

<div class="box-body">

@if(Session::has('success1'))
                    <div class="alert alert-success alert-dismissible">
                        <i class="fa-solid fa-ban"></i>
                        <b>Alert!</b> Success.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                        {{Session::get('success1')}}
                    </div>
                    @endif
                    <!-- fail message -->
                    @if(Session::has('fails1'))
                    <div class="alert alert-danger alert-dismissible">
                        <i class="fa-solid fa-ban"></i>
                        <b>Alert!</b> Failed.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                        {{Session::get('fails1')}}
                    </div>
                    @endif

    <div class="row">
		<div class="mb-3 col-md-6 {{ $errors->has('firstname') ? 'has-error' : '' }}">

			{!! html()->label(Lang::get('lang.firstname'), 'firstname') !!}
			{!! $errors->first('firstname', '<spam class="help-block">:message</spam>') !!}
			{!! html()->text('firstname', null)->class('form-control') !!}

		</div>
		<div class="mb-3 col-md-6 {{ $errors->has('lastname') ? 'has-error' : '' }}">

			{!! html()->label(Lang::get('lang.lastname'), 'lastname') !!}
			{!! $errors->first('lastname', '<spam class="help-block">:message</spam>') !!}
			{!! html()->text('lastname', null)->class('form-control') !!}

		</div>
	</div>

		<div class="row">

		<div class="mb-3 col-md-6">
			{!! html()->label(Lang::get('lang.gender'), 'gender') !!}
			<div class="row">
				<div class="col-6">
					{!! html()->radio('gender', true, '1') !!}{{Lang::get('lang.male')}}
				</div>
				<div class="col-6">
					{!! html()->radio('gender', null, '0') !!}{{Lang::get('lang.female')}}
				</div>
			</div>
		</div>

		<div class="col-md-6 mb-3">

                        {!! html()->label(Lang::get('lang.timezone'), 'timezone') !!}
                        {!! html()->select('timezone', $time->pluck('location','name'), null)->class('form-control select') !!}

                    </div>

		</div>



		<div class="mb-3">

			{!! html()->label(Lang::get('lang.email'), 'email') !!}
			<div>
				{{$user->email}}
			</div>
		</div>

		<div class="mb-3 {{ $errors->has('company') ? 'has-error' : '' }}">

			{!! html()->label(Lang::get('lang.company'), 'company') !!}
			{!! $errors->first('company', '<spam class="help-block">:message</spam>') !!}
			{!! html()->text('company', null)->class('form-control') !!}

		</div>

		<div class="row">
			<div class="col-3 mb-3 {{ $errors->has('ext') ? 'has-error' : '' }}">

				{!! html()->label(Lang::get('lang.ext'), 'ext') !!}
				{!! $errors->first('ext', '<spam class="help-block">:message</spam>') !!}
				{!! html()->text('ext', null)->class('form-control') !!}

			</div>

			<div class="col-9 mb-3 {{ $errors->has('phone_number') ? 'has-error' : '' }}">

				{!! html()->label(Lang::get('lang.phone'), 'phone_number') !!}
				{!! $errors->first('phone_number', '<spam class="help-block">:message</spam>') !!}
				{!! html()->text('phone_number', null)->class('form-control') !!}

			</div>
		</div>

			<div class="mb-3 {{ $errors->has('mobile') ? 'has-error' : '' }}">

				{!! html()->label(Lang::get('lang.mobile'), 'mobile') !!}
				{!! $errors->first('mobile', '<spam class="help-block">:message</spam>') !!}
				{!! html()->text('mobile', null)->class('form-control') !!}

			</div>


	<div class="mb-3 {{ $errors->has('profile_pic') ? 'has-error' : '' }}">
	<div class="btn btn-secondary btn-file">
		{!! html()->label(Lang::get('lang.profilepicture'), 'profile_pic') !!}
		{!! $errors->first('profile_pic', '<spam class="help-block">:message</spam>') !!}
		{!! html()->file('profile_pic') !!}
	</div>
	</div>

	{!! html()->token() !!}
	{!! html()->closeModelForm() !!}
</div>
</div>
</div>
<div class="col-md-6">

    {!! html()->modelForm($user, 'PATCH', url('post-profile-password/'.$user->id))->open() !!}

<div class="box box-primary">

	<div class="app-content-header">

	 	<h4>Change Password	{!! html()->submit('Save')->class('mb-3 btn btn-primary pull-right') !!}</h4>

	</div>

<div class="box-body">
					@if(Session::has('success2'))
                    <div class="alert alert-success alert-dismissible">
                        <i class="fa-solid fa-ban"></i>
                        <b>Alert!</b> Success.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                        {{Session::get('success2')}}
                    </div>
                    @endif
                    <!-- fail message -->
                    @if(Session::has('fails2'))
                    <div class="alert alert-danger alert-dismissible">
                        <i class="fa-solid fa-ban"></i>
                        <b>Alert!</b> Failed.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                        {{Session::get('fails2')}}
                    </div>
                    @endif

	<div class="mb-3 has-feedback {{ $errors->has('old_password') ? 'has-error' : '' }}">
			{!! html()->label(Lang::get('lang.oldpassword'), 'old_password') !!}
            {!! html()->password('old_password')->placeholder('Password')->class('form-control') !!}
			{!! $errors->first('old_password', '<spam class="help-block">:message</spam>') !!}
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
    </div>

    <div class="mb-3 has-feedback {{ $errors->has('new_password') ? 'has-error' : '' }}">
    		{!! html()->label(Lang::get('lang.newpassword'), 'new_password') !!}
            {!! html()->password('new_password')->placeholder('New Password')->class('form-control') !!}
			{!! $errors->first('new_password', '<spam class="help-block">:message</spam>') !!}
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
    </div>

    <div class="mb-3 has-feedback {{ $errors->has('confirm_password') ? 'has-error' : '' }}">
    		{!! html()->label(Lang::get('lang.confirmpassword'), 'confirm_password') !!}
            {!! html()->password('confirm_password')->placeholder('Confirm Password')->class('form-control') !!}
			{!! $errors->first('confirm_password', '<spam class="help-block">:message</spam>') !!}
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
    </div>




</div>
</div>
</div>
</div>


{!! html()->closeModelForm() !!}
@stop