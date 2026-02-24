@extends('themes.default1.client.layout.logclient')

@section('home')
    class = "nav-item active"
@stop

@section('breadcrumb')
{{--    <div class="site-hero clearfix">--}}
        <ol class="breadcrumb float-sm-right ">
            <li class="breadcrumb-item"> <i class="fas fa-home"> </i> {!! Lang::get('lang.you_are_here') !!} : &nbsp;</li>

            <li><a href="{!! URL::route('post.register') !!}">{!! Lang::get('lang.register') !!}</a></li>
        </ol>
{{--    </div>--}}
@stop
{{--    <div class="site-hero clearfix">--}}
{{--        <ol class="breadcrumb breadcrumb-custom">--}}
{{--            <li class="text">{!! Lang::get('lang.you_are_here') !!}: </li>--}}
{{--            <li><a href="{!! URL::route('/') !!}">{!! Lang::get('lang.home') !!}</a></li>--}}
{{--        </ol>--}}
{{--    </div>--}}

@section('content')

    @if(Session::has('status'))
    <div class="alert alert-success alert-dismissable">
        <i class="fas fa-check-circle"> </i> <b> {!! Lang::get('lang.success') !!} </b>
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {{Session::get('status')}}
    </div>
    @endif

    @if (count($errors) > 0)
    <div class="alert alert-danger alert-dismissable">
        <i class="fa fa-ban"></i>
        <b>{!! Lang::get('lang.alert') !!} !</b>
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </div>
    @endif

<div id="content" class="site-content col-md-12">
   
    <div id="corewidgetbox" class="wid">
   
        <div id="wbox" class="widgetrow text-center">
   
        @if(Auth::user())
        @else
            <span onclick="javascript: window.location.href='{{url('auth/login')}}';">
                <a href="{{url('auth/login')}}" class="widgetrowitem defaultwidget"  style="background-image:url({{ URL::asset('lb-faveo/media/images/register.png') }})">
                    <span class="widgetitemtitle" style="color: rgb(0, 154, 186)">{!! Lang::get('lang.login') !!}</span>
                </a>
            </span>
        @endif
        <?php $system = App\Model\helpdesk\Settings\System::where('id', '=', '1')->first();            
        ?>
        @if($system != null) 
            @if($system->status) 
                @if($system->status == 1)
                    <span onclick="javascript: window.location.href='{!! URL::route('form') !!}';">
                        <a href="{!! URL::route('form') !!}" class="widgetrowitem defaultwidget" style="background-image:url({{ URL::asset('lb-faveo/media/images/submitticket.png') }})">
                            <span class="widgetitemtitle" style="color: rgb(0, 154, 186)">{!! Lang::get('lang.submit_a_ticket') !!}</span>
                        </a>
                    </span>
                @endif
            @endif
        @endif
            <span onclick="javascript: window.location.href='{{url('mytickets')}}';">
                <a href="{{url('mytickets')}}" class="widgetrowitem defaultwidget" style="background-image:url({{ URL::asset('lb-faveo/media/images/news.png') }})">
                    <span class="widgetitemtitle" style="color: rgb(0, 154, 186)">{!! Lang::get('lang.my_tickets') !!}</span>
                </a>
            </span>
            <span onclick="javascript: window.location.href='{{url('/knowledgebase')}}';">
                <a href="{{url('/knowledgebase')}}" class="widgetrowitem defaultwidget" style="background-image:url({{ URL::asset('lb-faveo/media/images/knowledgebase.png') }})">
                    <span class="widgetitemtitle" style="color: rgb(0, 154, 186)">{!! Lang::get('lang.knowledge_base') !!}</span>
                </a>
            </span>
        </div>
    </div>

    <div class="d-flex justify-content-center">
        <div class="login-box" style=" width: 490px;">
            
            <div class="form-border">
     
                <div align="center">
                    
                    <h4 style="background-color: #0084b4;">
                        <a href="http://www.faveohelpdesk.com" class="logo"><img src="{{ asset('lb-faveo/media/images/logo.png')}}" width="100px;" ></a>
                    </h4>    
                </div>
               
                <div>
                    <div class="text-center">
                        <h3 class="box-title" >{{Lang::get('lang.registration')}}</h3>
                    </div>   </div>

                <div>

                    <placeholder ="Let’s set up your account in just a couple of steps.">
                </div>      

                <!-- form open -->
                {!! html()->form('POST', url('auth/register'))->open() !!}

                <!-- fullname -->
                <div class="form-group has-feedback {{ $errors->has('full_name') ? 'has-error' : '' }}" style="display: -webkit-box;">
            
                    {!! html()->text('full_name', null)->placeholder(Lang::get('lang.full_name'))->class('form-control') !!}
                    <span class="fas fa-user   form-control-feedback" style="top: 9px;left: -25px;color: #6c757d;"></span>
                </div>

                <!-- Email -->
                @if (($email_mandatory->status == 1 || $email_mandatory->status == '1'))
                <div class="form-group has-feedback {{ $errors->has('email') ? 'has-error' : '' }}" style="display: -webkit-box;">
                    {!! html()->text('email', null)->placeholder(Lang::get('lang.email'))->class('form-control') !!}
                    <span class="far fa-envelope text-muted form-control-feedback" style="top: 9px;left: -25px;color: #6c757d;"></span>
                </div>
                @elseif (($settings->status == 0 || $settings->status == '0') && ($email_mandatory->status == 0 || $email_mandatory->status == '0'))
                <div class="form-group has-feedback {{ $errors->has('email') ? 'has-error' : '' }}" style="display: -webkit-box;">
                    {!! html()->text('email', null)->placeholder(Lang::get('lang.email'))->class('form-control') !!}
                    <span class="far fa-envelope text-muted form-control-feedback" style="top: 9px;left: -25px;color: #6c757d;"></span>
                </div>
                @else
                    {!! html()->hidden('email', null) !!}
                @endif

                @if($settings->status == '1' || $settings->status == 1)
                <div class='row'>
                    <div class="col-md-3">
                        <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                        {!! html()->text('code', null)->placeholder(91)->class('form-control') !!}
                        </div>    
                    </div>
                    <div class="col-md-9">
                        <div class="form-group has-feedback {{ $errors->has('mobile') ? 'has-error' : '' }}" style="display: -webkit-box;">
                        {!! html()->text('mobile', null)->placeholder(Lang::get('lang.mobile'))->class('form-control') !!}
                        <span class="fas fa-phone  form-control-feedback" style="top: 9px;left: -25px;color: #6c757d;"></span>
                        </div>
                    </div>
                </div>
                @else
                    {!! html()->hidden('mobile', null) !!}
                    {!! html()->hidden('code', null) !!}

                @endif
                <!-- Password -->
                <div class="form-group has-feedback {{ $errors->has('password') ? 'has-error' : '' }}" style="display: -webkit-box;">
                           
                    {!! html()->password('password')->placeholder(Lang::get('lang.password'))->class('form-control') !!}
                    <span class="fa fa-lock  form-control-feedback" style="top: 9px;left: -25px;color: #6c757d;"></span>

                </div>
                <!-- Confirm password -->
                <div class="form-group has-feedback {{ $errors->has('password_confirmation') ? 'has-error' : '' }}" style="display: -webkit-box;">
                           
                    {!! html()->password('password_confirmation')->placeholder(Lang::get('lang.retype_password'))->class('form-control') !!}
                    <span class="fas fa-sign-in-alt form-control-feedback" style="top: 9px;left: -25px;color: #6c757d;"></span>

                </div>
                
                <div >
                    
                    <button type="submit" class="btn btn-primary btn-block btn-flat" style="width: 100%; hov: #00c0ef; color: #fff">{!! Lang::get('lang.register') !!}</button>
                </div>

                <div>
                  
                    <div class="checkbox icheck" align="center">
                        <label class="mb-0">
                           {{trans('lang.already_got_an_account?')}} <a href="{{url('auth/login')}}" class="text-center">{!! Lang::get('lang.login') !!}</a>
                        </label>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            @include('themes.default1.client.layout.social-login')
                        </div>
                    </div>
                </div><!-- /.col --> 
            </div>
        </div>
    </div>
</div>
{!! html()->closeModelForm() !!}  

@stop
