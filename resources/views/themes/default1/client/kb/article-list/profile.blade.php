@extends('themes.default1.client.layout.client')
@section('HeadInclude')
<link href="{{asset("lb-faveo/css/AdminLTE.css")}}" rel="stylesheet" type="text/css" />
@stop

@section('content')
<div id="content" class="site-content col-md-12">
    <section class="section-title">
        <h2>
            {!! Lang::get('lang.profile_settings') !!} </h2>
    </section>
    <div class="row">
        <div class="col-md-6">
            {!! html()->modelForm($user, 'PATCH', url('client-profile-edit'))->acceptsFiles()->open() !!}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h4>{!! Lang::get('lang.pofile') !!} </h4>
                </div>
                <div class="box-body">
                    @if(Session::has('success1'))
                    <div class="alert alert-success alert-dismissible">
                        <i class="fa-solid fa-ban"></i>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                        {{Session::get('success1')}}
                    </div>
                    @endif
                    <!-- fail message -->
                    @if(Session::has('fails1'))
                    <div class="alert alert-danger alert-dismissible">
                        <i class="fa-solid fa-ban"></i>
                        <b>{!! Lang::get('lang.alert') !!}!</b>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                        {{Session::get('fails1')}}
                    </div>
                    @endif
                    <div class="mb-3 {{ $errors->has('firstname') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! html()->label(Lang::get('lang.firstname'), 'firstname') !!}
                        {!! $errors->first('firstname', '<spam class="help-block">:message</spam>') !!}
                        {!! html()->text('firstname', null)->class('form-control') !!}
                    </div>
                    <div class="mb-3 {{ $errors->has('lastname') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! html()->label(Lang::get('lang.lastname'), 'lastname') !!}
                        {!! $errors->first('lastname', '<spam class="help-block">:message</spam>') !!}
                        {!! html()->text('lastname', null)->class('form-control') !!}
                    </div>
                    <div class="mb-3">
                        <!-- gender -->
                        {!! html()->label(Lang::get('lang.gender'), 'gender') !!}
                        <div class="row">
                            <div class="col-3">
                                {!! html()->radio('gender', true, '1') !!}{{Lang::get('lang.male')}}
                            </div>
                            <div class="col-3">
                                {!! html()->radio('gender', null, '0') !!}{{Lang::get('lang.female')}}
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <!-- email -->
                        {!! html()->label(Lang::get('lang.email'), 'email') !!}
                        <div>
                            {{$user->email}}
                        </div>
                    </div>
                    <div class="mb-3 {{ $errors->has('company') ? 'has-error' : '' }}">
                        <!-- company -->
                        {!! html()->label(Lang::get('lang.company'), 'company') !!}
                        {!! $errors->first('company', '<spam class="help-block">:message</spam>') !!}
                        {!! html()->text('company', null)->class('form-control') !!}
                    </div>
                    <div class="row">
                        <div class="col-3 mb-3 {{ $errors->has('ext') ? 'has-error' : '' }}">
                            <!-- phone extensionn -->
                            {!! html()->label(Lang::get('lang.ext'), 'ext') !!}
                            {!! $errors->first('ext', '<spam class="help-block">:message</spam>') !!}
                            {!! html()->text('ext', null)->class('form-control') !!}
                        </div>
                        <div class="col-9 mb-3 {{ $errors->has('phone') ? 'has-error' : '' }}">
                            <!-- phone number -->
                            {!! html()->label(Lang::get('lang.phone'), 'phone_number') !!}
                            {!! $errors->first('phone_number', '<spam class="help-block">:message</spam>') !!}
                            {!! html()->text('phone_number', null)->class('form-control') !!}
                        </div>
                    </div>
                    <div class="mb-3 {{ $errors->has('mobile') ? 'has-error' : '' }}">
                        <!-- mobile -->
                        {!! html()->label(Lang::get('lang.mobile'), 'mobile') !!}
                        {!! $errors->first('mobile', '<spam class="help-block">:message</spam>') !!}
                        {!! html()->text('mobile', null)->class('form-control') !!}
                    </div>
                    <div class="mb-3 {{ $errors->has('profile_pic') ? 'has-error' : '' }}" >
                        <!-- profile pic -->
                        <div class="btn btn-secondary btn-file">
                            {!! html()->label(Lang::get('lang.profilepicture'), 'profile_pic') !!}
                            {!! $errors->first('profile_pic', '<spam class="help-block">:message</spam>') !!}
                            {!! html()->file('profile_pic') !!}
                        </div>
                    </div>
                    {!! html()->token() !!}
                    {!! html()->closeModelForm() !!}
                </div>
                <div class="box-footer">
                    {!! html()->submit(Lang::get('lang.update'))->class('btn btn-primary') !!}
                </div>
            </div>
        </div>
        <div class="col-md-6">
            {!! html()->modelForm($user, 'PATCH', url('client-profile-password'))->open() !!}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h4>{!! Lang::get('lang.change_password') !!}	{!! html()->submit(Lang::get('lang.update'))->class('mb-3 btn btn-primary pull-right') !!}</h4>
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
                    <!-- old password -->
                    <div class="mb-3 has-feedback {{ $errors->has('old_password') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('lang.oldpassword'), 'old_password') !!}
                        {!! html()->password('old_password')->placeholder('Password')->class('form-control') !!}
                        {!! $errors->first('old_password', '<spam class="help-block">:message</spam>') !!}
                        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                    </div>
                    <!-- new password -->
                    <div class="mb-3 has-feedback {{ $errors->has('new_password') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('lang.newpassword'), 'new_password') !!}
                        {!! html()->password('new_password')->placeholder('New Password')->class('form-control') !!}
                        {!! $errors->first('new_password', '<spam class="help-block">:message</spam>') !!}
                        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                    </div>
                    <!-- cofirm password -->
                    <div class="mb-3 has-feedback {{ $errors->has('confirmpassword') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('lang.confirm_password'), 'confirm_password') !!}
                        {!! html()->password('confirm_password')->placeholder('Confirm Password')->class('form-control') !!}
                        {!! $errors->first('confirm_password', '<spam class="help-block">:message</spam>') !!}
                        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                    </div>

                </div>
            </div>
        </div>
    </div>


    {!! html()->closeModelForm() !!}
</div>
@stop