@extends('themes.default1.layouts.login')

@section('body')
<h4  id="otp-screen" class="login-box-msg">
<p>Hello
@if (Session::has('name'))
    {{ Session::get('name')}}
@endif!</p><br/>
<span style='font-size: .8em'>
{!!Lang::get('lang.verify-screen-msg')!!}
    <?php
        $value = Session::get('values');
        if (array_key_exists('referer', $value)) {
            $referer = $value['referer'];
        } else {
            $referer = '/';
        }
        $email = $value['email'];
        $password = $value['password'];
        $number =  Session::get('number');
        $sub_number = substr($number, 1,6);
        $show_number = str_replace($sub_number, '******', $number);
    ?>
    {{ $show_number }}
    </span>
</h4>
<h4 id="loading-screen" class="login-box-msg d-none">

<center>Wait we are sending a new OTP code to your number.<br/><img src="{{asset('lb-faveo/media/images/gifloader.gif')}}"></center>
</h4>

<div id="success" style="display:none" class="alert alert-success alert-dismissible">
    <i class="fa-solid fa-circle-check"> </i>
    <span id = "success_message"></span>
</div>
<div id="ere_msg" style="display:none" class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"> </i> <b> {!! Lang::get('lang.alert') !!}! </b>
    <span id = "error_message"></span>
</div>

<!-- failure message -->
@if(Session::has('errors'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"> </i> <b> {!! Lang::get('lang.alert') !!}! </b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    @if(Session::has('error'))    
    <li>{!! Session::get('error') !!}</li>
    @else
    <li>{!! Lang::get('lang.please_fill_all_required_feilds') !!}</li>
    @endif
</div>
@endif
<!-- failure message -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"> </i> <b> {!! Lang::get('lang.alert') !!}! </b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <li>{!! Session::get('fails') !!}</li>
</div>
@endif

<!-- form open -->
{!! html()->form('POST', route('otp-verification'))->open() !!}
<!-- Email -->
<div class="mb-3 has-feedback {{ $errors->has('email') ? 'has-error' : '' }}">
    {!! html()->hidden('email', null)->placeholder(Lang::get("lang.email"))->class('form-control') !!}
    <!-- {!! $errors->first('email', '<spam class="help-block">:message</spam>') !!} -->
</div>

<!-- Password -->
<div class="mb-3 has-feedback {{ $errors->has('password') ? 'has-error' : '' }}">
    {!! html()->hidden('password', ['placeholder'=>Lang::get("lang.password"),'class' => 'form-control']) !!}
    <!-- {!! $errors->first('password', '<spam class="help-block">:message</spam>') !!} -->
</div>
<div class="mb-3 {{ $errors->has('otp') ? 'has-error' : '' }}">
    {!! html()->text('otp', null)->placeholder(Lang::get("lang.enter-otp"))->class('form-control')->required()->attributes(['pattern' => "[0-9]{6}", 'title' => Lang::get('lang.otp-input-title')]) !!}
    <!-- {!! $errors->first('email', '<spam class="help-block">:message</spam>') !!} -->
</div>
@if (Session::has('referer'))
    <input type='hidden' name="referer" value="{!! Session::get('referer') !!}">
@elseif(Session::has('errors'))
    <input type='hidden' name="referer" value="form">
@endif
<div class="row">
    <div class="col-8">
        {!! Lang::get('lang.did-not-recive-code') !!}<br/>
        <a id="resend" onclick="resendOTP();" href="#" title="{!!Lang::get('lang.resend-otp-title') !!}">{!! Lang::get("lang.resend_otp") !!}</a><br>
    </div><!-- /.col -->
    <div class="col-4">
        <button type="submit" class="btn btn-primary w-100">{!! Lang::get("lang.verify") !!}</button>
    </div><!-- /.col -->
</div>
</form>
<!-- /.login-page -->
<style>
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  margin: 0; 
}
</style>
<script type="text/javascript">
    function resendOTP() {
        var mobile = " <?php echo $number ?> ";
        var full_name = "<?php echo Session::get('name');?>";
        var email = document.getElementsByName("email")[0].value;
        $.ajax({                    
            url: '{{URL::route("resend-otp")}}',     
            type: 'post', // performing a POST request
            data : {
                mobile : mobile,
                full_name: full_name,
                email: email,
                code: 0// will be accessible in $_POST['data1']
            },
            dataType: 'json', 
            beforeSend: function() {
                $('#success').css('display', 'none');
                $('#ere_msg').css('display','none');
                $('#otp-screen').css('display','none');
                $(".btn-close").trigger("click");
                $('#loading-screen').css('display','block');
            },
            success: function(response) {
                var message = "{{Lang::get('lang.otp-sent')}}";
                $("#success_message").html(message);
                $('#otp-screen').css('display','block');
                $('#success').css('display','block');
                $('#loading-screen').css('display','none');
            },
            complete: function( jqXHR, textStatus) {
                if (textStatus === "parsererror" || textStatus === "timeout" || textStatus === "abort" || textStatus === "error") {
                    var message = "{{Lang::get('lang.otp-not-sent')}}";
                    $("#error_message").html(message);
                    $('#otp-screen').css('display','block');
                    $('#ere_msg').css('display','block');
                    $('#loading-screen').css('display','none');
                }
            }
        });
    }
</script>
@stop
