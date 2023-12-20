@extends('themes.default1.client.layout.client')

@section('profile')
class="nav-item active"
@stop
@section('breadcrumb')
    {{--<div class="site-hero clearfix">--}}
    <ol class="breadcrumb float-sm-right ">
        <li class="breadcrumb-item"> <i class="fa-solid fa-home"> </i> {!! Lang::get('lang.you_are_here') !!} : &nbsp;</li>
        <li><a href="{!! URL::route('client.profile') !!}">{!! Lang::get('lang.my_profile') !!}</a></li>
    </ol>

@stop
@section('content')

<div id="content" class="site-content col-md-12">

    <article class="henry">

        <header class="entry-header">

            <h2 class="entry-title">{!! Lang::get('lang.profile_settings') !!}</h2>
        </header>

        <div class="entry-content clearfix">

            @if(Session::has('success1'))
            <div class="alert alert-success alert-dismissible">
                <i class="fa-solid fa-circle-check"></i>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                {{Session::get('success1')}}
            </div>
            @endif
            @if (count($errors) > 0)
            <div class="alert alert-danger alert-dismissible">
                <i class="fa-solid fa-ban"></i>
                <b>{!! Lang::get('lang.alert') !!} !</b>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
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

             @if(Session::has('success2'))
            <div class="alert alert-success alert-dismissible">
                <i class="fa-solid fa-circle-check"></i>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                {{Session::get('success2')}}
            </div>
            @endif
            <!-- fail message -->
            @if(Session::has('fails2'))
            <div class="alert alert-danger alert-dismissible">
                <i class="fa-solid fa-ban"></i>
                <b>{!! Lang::get('lang.alert') !!} !</b>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                {{Session::get('fails2')}}
            </div>
            @endif

            <div class="row">

                <div class="col-md-6">

                     {!! html()->modelForm($user, 'PATCH', url('client-profile-edit'))->acceptsFiles()->attributes(['id' => 'client-profile'])->open() !!}

                    <div id="form-border" class="comment-respond form-border" style="background : #fff">

                        <section id="section-categories" class="section">

                            <h2 class="section-title h4 clearfix">

                                <i class="line" style="border-color: rgb(0, 154, 186);"></i>{!! Lang::get('lang.profile') !!}
                            </h2>

                            <div>

                                <div class="mb-3 {{ $errors->has('first_name') ? 'has-error' : '' }}">
                                    <!-- first name -->
                                    {!! html()->label(Lang::get('lang.first_name'), 'first_name') !!}<span class="text-red"> *</span>

                                    {!! html()->text('first_name', null)->class('form-control') !!}
                                </div>
                                <div class="mb-3 {{ $errors->has('last_name') ? 'has-error' : '' }}">
                                    <!-- last name -->
                                    {!! html()->label(Lang::get('lang.last_name'), 'last_name') !!}

                                    {!! html()->text('last_name', null)->class('form-control') !!}
                                </div>
                                <div class="mb-3">
                                    <!-- gender -->
                                    {!! html()->label(Lang::get('lang.gender'), 'gender') !!}
                                    <div class="row">
                                        <div class="col-sm-3">
                                            {!! html()->radio('gender', true, '1') !!}&nbsp;&nbsp;{{Lang::get('lang.male')}}
                                        </div>
                                        <div class="col-sm-3">
                                            {!! html()->radio('gender', null, '0') !!}&nbsp;&nbsp;{{Lang::get('lang.female')}}
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <!-- email -->
                                    {!! html()->label(Lang::get('lang.email_address'), 'email') !!}
                                    <div>
                                        {{$user->email}}
                                    </div>
                                </div>
                                <div class="mb-3 {{ $errors->has('company') ? 'has-error' : '' }}">
                                    <!-- company -->
                                    {!! html()->label(Lang::get('lang.company'), 'company') !!}

                                    {!! html()->text('company', null)->class('form-control') !!}
                                </div>
                                <div class="row">
                                    <div class="col-sm-2 mb-3 {{ $errors->has('country_code') ? 'has-error' : '' }}">
                                        <!-- phone extensionn -->
                                        {!! html()->label(Lang::get('lang.country-code'), 'country_code') !!}
                                        {!! html()->text('country_code', null)->class('form-control')->placeholder($phonecode)->id('code')->attributes(['title' => Lang::get('lang.enter-country-phone-code')]) !!}

                                    </div>
                                    <div class="col-sm-2 mb-3 {{ $errors->has('ext') ? 'has-error' : '' }}">
                                        <!-- phone extensionn -->
                                        {!! html()->label(Lang::get('lang.ext'), 'ext') !!}

                                        {!! html()->text('ext', null)->class('form-control') !!}
                                    </div>
                                    <div class="col-sm-8 mb-3 {{ $errors->has('phone_number') ? 'has-error' : '' }}">
                                        <!-- phone number -->
                                        {!! html()->label(Lang::get('lang.phone'), 'phone_number') !!}

                                        {!! html()->text('phone_number', null)->class('form-control') !!}
                                    </div>
                                </div>
                                <div class="mb-3 {{ $errors->has('mobile') ? 'has-error' : '' }}">
                                    <!-- mobile -->
                                    {!! html()->label(Lang::get('lang.mobile_number'), 'mobile') !!}

                                    {!! html()->number('mobile', null)->class('form-control')->id('mobile') !!}
                                </div>
                                <div class="mb-3 {{ $errors->has('profile_pic') ? 'has-error' : '' }}">
                                    <!-- profile pic -->
                                    {!! html()->label(Lang::get('lang.profile_pic'), 'profile_pic') !!}

                                    {!! html()->file('profile_pic') !!}
                                </div>

                                {!! html()->token() !!}
                                {!! html()->closeModelForm() !!}

                                <div class="mb-3" style="padding-bottom: 10px;">


                                    <button type="submit" class="btn btn-primary float-end" style="background-color: #337ab7 !important; border-color: #337ab7 !important; color: white;">
                                        <i class="fa-solid fa-arrows-rotate"></i> {{ Lang::get('lang.update') }}
                                    </button>                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <div class="col-md-6">

                    {!! html()->modelForm($user, 'PATCH', url('client-profile-password'))->open() !!}

                    <div id="form-border" class="comment-respond form-border" style="background : #fff">

                        <section id="section-categories" class="section">

                            <h2 class="section-title h4 clearfix">

                                <i class="line"></i>{!! Lang::get('lang.change_password') !!}
                            </h2>

                            <div>
                                 {!! html()->label(Lang::get('lang.old_password'), 'old_password') !!}<span class="text-red"> *</span>
                                <div class="mb-3 has-feedback {{ $errors->has('old_password') ? 'has-error' : '' }}" style="display: -webkit-box;">
                                    {!! html()->password('old_password')->class('form-control') !!}
                                    <span class="fa-solid fa-lock form-control-feedback" style="top: 9px;left: -25px;color: #6c757d !important;"></span> <!--change the "glyphicon glyphicon-lock form-control-feedback" to "fa-solid fa-lock form-control-feedback" bcoz bs5 has removed the Glyphicons icon font that was included in earlier versions of Bootstrap-->
                                </div>
                                <!-- new password -->
                                  {!! html()->label(Lang::get('lang.new_password'), 'new_password') !!}<span class="text-red"> *</span>
                                <div class="mb-3 has-feedback {{ $errors->has('new_password') ? 'has-error' : '' }}" style="display: -webkit-box;">
                                    {!! html()->password('new_password')->class('form-control') !!}
                                    <span class="fa-solid fa-lock form-control-feedback" style="top: 9px;left: -25px;color: #6c757d !important;"></span>
                                </div>
                                <!-- cofirm password -->
                                 {!! html()->label(Lang::get('lang.confirm_password'), 'confirm_password') !!}<span class="text-red"> *</span>
                                <div class="mb-3 has-feedback {{ $errors->has('confirm_password') ? 'has-error' : '' }}" style="display: -webkit-box;">
                                    {!! html()->password('confirm_password')->class('form-control') !!}
                                    <span class="fa-solid fa-lock form-control-feedback" style="top: 9px;left: -25px;color: #6c757d !important;"></span>
                                </div>

                                {!! html()->closeModelForm() !!}

                                <div class="mb-3" style="padding-bottom: 10px;">

                                    <button type="submit" class="btn btn-primary float-end" style="background-color: #337ab7 !important; border-color: #337ab7 !important; color: white;">
                                        <i class="fa-solid fa-arrows-rotate"></i> {{ Lang::get('lang.update') }}
                                    </button>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </article>
<!-- Modal for last step of setting -->
<div class="modal" id="last-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none; padding-right: 15px;background-color: rgba(0, 0, 0, 0.7);">
    <div class="modal-dialog" role="document">
        <div class="col-md-2"></div>
        <div class="col-md-12" style="height:40%">
            <div class="modal-content">
                <div class="modal-header">
                    <span style="font-size:1.2em">{{Lang::get('lang.verify-number')}}</span>
                    <button type="button" class="btn-close closemodal" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div id="custom-alert-body2">
                        <div class="row">
                            <div class="col-md-12">
                            <div id="loader2" style="display:none">
                                <center><img src="{{asset('lb-faveo/media/images/gifloader.gif')}}"></center>
                            </div>
                            <div id="verify-success" style="display:none" class="alert alert-success alert-dismissible">
                                <i class="fa  fa-circle-check"> </i>
                                <span id = "success_message"></span>
                            </div>
                            <div id="verify-fail" style="display:none" class="alert alert-danger alert-dismissible">
                                <i class="fa-solid fa-ban"> </i> <b> {!! Lang::get('lang.alert') !!}! </b>
                                <span id = "error_message"></span>
                            </div>
                            </div>
                        </div>
                    </div>
                    <div id="verify-number-form">
                    {!! html()->form('POST', url()->current())->attributes(['id' => 'verify-otp'])->open() !!}
                        <div class="row">
                            <div class="col-md-8">
                                {{ Lang::get('lang.get-verify-message') }}
                            </div>
                            <div class="col-md-4">
                                {!! html()->text('token', '')->class('form-control')->required()->placeholder(Lang::get('lang.enter-otp'))->id('otp') !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="justify-content: space-between;">
                    <button type="button" id="close-last" class="btn btn-secondary closemodal float-start">{{Lang::get('lang.close')}}</button>
                    <div id="last-submit"><input  type="submit" id="merge-btn" class="btn btn-primary float-end" value="{!! Lang::get('lang.verify') !!}"></input></div>
                </div>
                {!! html()->closeModelForm() !!}
            </div>
        </div>
    </div>
</div>
<!-- modal end -->
@if($verify == 1 || $verify == '1')
    <script type="text/javascript">
    $('#client-profile').on('submit', function(e){
        var old_mobile = "<?php echo $user->mobile;?>";
        var email = "<?php echo $user->email;?>";
        var full_name = "<?php echo $user->first_name; ?>";
        var mobile = document.getElementById('mobile').value;
        var code = document.getElementById('code').value;
        var id = "<?php echo $user->id; ?>";
        if (code == '' || code == null) {
            //do nothing
        } else {
        if (mobile !== old_mobile) {
            e.preventDefault();
            $('#last-modal').css('display', 'block');
            $.ajax({
                url: '{{URL::route("client-verify-number")}}',
                type: 'POST', // performing a POST request
                data : {
                    mobile : mobile,
                    full_name: full_name,
                    email: email,
                    code: code// will be accessible in $_POST['data1']
                },
                dataType: 'json',
                beforeSend: function() {
                    $('#loader2').css('display', 'block');
                    $('#verify-number-form').css('display', 'none');
                    $('#verify-fail').css('display', 'none');
                    $('verify-success').css('display', 'none');
                },
                success: function(response) {
                    $('#loader2').css('display', 'none');
                    $('#verify-number-form').css('display', 'block');
                    $('#verify-otp').on('submit', function(e){
                        e.preventDefault();
                        var otp = document.getElementById('otp').value;
                        $.ajax({
                            url: '{{URL::route("post-client-verify-number")}}',
                            type: 'POST',
                            data: {
                                otp: otp,
                                u_id: id,
                            },
                            dataType: 'html',
                            beforeSend: function(){
                                $('#loader2').css('display', 'block');
                                $('#verify-number-form').css('display', 'none');
                                $('#verify-fail').css('display', 'none');
                                $('verify-success').css('display', 'none');
                            },
                            success: function(response){
                                if( response == 1) {
                                    $('#loader2').css('display', 'none');
                                    var message = "{{Lang::get('lang.number-verification-sussessfull')}}";
                                    $('#success_message').html(message);
                                    $('#verify-success').css('display', 'block');
                                    $('#client-profile').unbind('submit').submit();
                                } else {
                                    $('#loader2').css('display', 'none');
                                    $("#error_message").html(response);
                                    $('#verify-fail').css('display', 'block');
                                    $('#verify-number-form').css('display', 'block');
                                }
                            }
                        });
                    });
                },
                complete: function( jqXHR, textStatus) {
                    if (textStatus === "parsererror" || textStatus === "timeout" || textStatus === "abort" || textStatus === "error") {
                        var message = "{{Lang::get('lang.otp-not-sent')}}";
                        $('#loader2').css('display', 'none');
                        $("#error_message").html(message);
                        $("#merge-btn").css('display', 'none');
                        $('#verify-fail').css('display', 'block');
                    }
                }
            });
        }
        }
    });
    $('.closemodal').on('click', function(){
        $('#last-modal').css('display', 'none');
    });
    </script>
@endif
@stop