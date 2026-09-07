@extends('themes.default1.client.layout.logclient')

@section('home')
    class = "nav-item active"
@stop

@section('breadcrumb')
    <ol class="breadcrumb float-sm-right ">
        <li class="breadcrumb-item"> <i class="fa-solid fa-home"> </i> {!! Lang::get('lang.you_are_here') !!} :&nbsp; </li>
            <li><a href="{!! URL::route('/') !!}">{!! Lang::get('lang.forgot_password') !!}</a></li>
        </ol>
@stop

@section('content')

    @if(Session::has('status'))
    <div class="col-sm-12">
        <div class="alert alert-success alert-dismissible">
            <i class="fa-solid fa-circle-check"> </i> <b> {!! Lang::get('lang.success') !!} </b>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
            {{Session::get('status')}}
        </div>
    </div>
    @endif

<div id="content" class="site-content col-md-12">

    <div id="corewidgetbox" class="wid">

        <div id="wbox" class="widgetrow text-center">

            @if(Auth::user())
            @else
            <span onclick="javascript: window.location.href='{{url('auth/login')}}';">
                <a href="{{url('auth/login')}}" class="widgetrowitem defaultwidget" style="background-image:url({{ URL::asset('lb-faveo/media/images/register.png') }})">
                    <span class="widgetitemtitle" style="color: rgb(0, 154, 186)">{!! Lang::get('lang.login') !!}</span>
                </a>
            </span>
            @endif
            <?php $system = App\Model\helpdesk\Settings\System::where('id', '=', '1')->first(); ?>
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

    <script type="text/javascript"> $(function(){ $('.dialogerror, .dialoginfo, .dialogalert').fadeIn('slow');$("form").bind("submit", function(e){$(this).find("input:submit").attr("disabled", "disabled");});});</script>
    <script type="text/javascript" >try {if (top.location.hostname != self.location.hostname) { throw 1; }} catch (e) { top.location.href = self.location.href; }
    </script>
    <div class="d-flex justify-content-center">

        <div class="login-box login-box-fixed">

            <div class="form-border">

                <div align="center">

                    <h4 class="login-brand-strip"> <a href="http://www.faveohelpdesk.com" class="logo">
                        <img src="{{ asset('lb-faveo/media/images/logo.png')}}" width="100px;"></a>
                    </h4>
                </div>

                <div>
                    <h3 class="box-title" align="center">{{trans('lang.forgot_password')}}</h3>
                </div>

                <!-- form open -->
                <form role="form" method="POST" action="{{ url('/password/email') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="mb-3">
                        <div class="input-group">
                            <input type="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" name="email" placeholder="{!! Lang::get('lang.email') !!}" value="{{ old('email') }}">
                            <span class="input-group-text"><i class="fa-regular fa-envelope input-icon-muted"></i></span>
                        </div>
                        @if($errors->has('email'))
                            <div class="invalid-feedback d-block">{{ $errors->first('email') }}</div>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <a href="{{url('auth/login')}}" class="text-center">{!! Lang::get('lang.i_know_my_password') !!}</a>
                        </div>
                        <div class="col-sm-6">
                            <button type="submit" class="btn btn-primary w-100">{!! Lang::get('lang.send') !!}</button>
                            <br/>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- /.login-page -->
@stop