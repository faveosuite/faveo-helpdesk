@extends('themes.default1.client.layout.logclient')

@section('home')
    class = "nav-item active"
@stop

@section('breadcrumb')
    <ol class="breadcrumb float-sm-right ">
        <li class="breadcrumb-item"> <i class="fa-solid fa-home"> </i> {!! Lang::get('lang.you_are_here') !!} : &nbsp;</li>
            <li><a href="{!! URL::route('post.login') !!}">{!! Lang::get('lang.login') !!}</a></li>
        </ol>
    </div>
@stop

@section('content')

    @if(Session::has('status'))
    <div class="alert alert-success alert-dismissible">
        <i class="fa-solid fa-circle-check"> </i> <b> {!! Lang::get('lang.success') !!} </b>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
        {{Session::get('status')}}
    </div>

    @endif

    @if(Session::has('error'))
    <div class="alert alert-danger alert-dismissible">
        <i class="fa-solid fa-circle-xmark"> </i> <b> {!! Lang::get('lang.alert') !!} </b>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
        {{Session::get('error')}}
    </div>
    @endif

    <div id="content" class="site-content col-md-12">

        <div id="corewidgetbox" class="wid">

            <div id="wbox" class="widgetrow text-center">

                @if(Auth::user())
                @else
                <span onclick="javascript: window.location.href='{{url('auth/register')}}';">
                    <a href="{{url('auth/register')}}"  class="widgetrowitem defaultwidget"  style="background-image:url({{ URL::asset('lb-faveo/media/images/register.png') }})"  >
                        <span class="widgetitemtitle"  style="color: rgb(0, 154, 186)">{!! Lang::get('lang.register') !!}</span>
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
                                    <div style="font-size: 13px ; color: rgb(0, 154, 186)"" class="widgetitemtitle">{!! Lang::get('lang.submit_a_ticket') !!}</div>
                                </a>
                            </span>
                        @endif
                    @endif
                @endif
{{--                <span onclick="javascript: window.location.href='{{url('mytickets')}}';">--}}
{{--                    <a href="{{url('mytickets')}}" class="widgetrowitem defaultwidget" style="background-image:url({{ URL::asset('lb-faveo/media/images/news.png') }})">--}}
{{--                        <span class="widgetitemtitle"  style="color: rgb(0, 154, 186)">{!! Lang::get('lang.my_tickets') !!}</span>--}}
{{--                    </a>--}}
{{--                </span>--}}
                <span onclick="javascript: window.location.href='{{url('/knowledgebase')}}';">
                    <a href="{{url('/knowledgebase')}}" class="widgetrowitem defaultwidget" style="background-image:url({{ URL::asset('lb-faveo/media/images/knowledgebase.png') }})">
                        <span class="widgetitemtitle"  style="color: rgb(0, 154, 186)">{!! Lang::get('lang.knowledge_base') !!}</span>
                    </a>
                </span>
            </div>
        </div>

        <script type="text/javascript"> $(function(){ $('.dialogerror, .dialoginfo, .dialogalert').fadeIn('slow');$("form").bind("submit", function(e){$(this).find("input:submit").attr("disabled", "disabled");});});</script>
        <script type="text/javascript" >try {if (top.location.hostname != self.location.hostname) { throw 1; }} catch (e) { top.location.href = self.location.href; }</script>

        <div class="d-flex justify-content-center">

            <div class="login-box login-box-fixed">

                <div class="form-border">

                    <div align="center">

                        <h4 style="background-color: #0084b4;"> <a href="http://www.faveohelpdesk.com" class="logo">
                            <img src="{{ asset('lb-faveo/media/images/logo.png')}}" width="100px;"></a>
                        </h4>
                    </div>

                    <div>

                        <h4 class="box-title" align="center">{{Lang::get('lang.login_to_start_your_session')}}</h4>
                    </div>

                    <!-- form open -->
                    {!! html()->form('POST', route('auth.post.login'))->open() !!}

                        <div class="mb-3">
                            <div class="input-group {{ $errors->has('email') ? 'is-invalid' : '' }}">
                                {!! html()->text('email', null)->placeholder(Lang::get("lang.email"))->class('form-control' . ($errors->has('email') ? ' is-invalid' : '')) !!}
                                <span class="input-group-text"><i class="fa-regular fa-envelope input-icon-muted"></i></span>
                            </div>
                            @if($errors->has('email'))
                                <div class="invalid-feedback d-block">{{ $errors->first('email') }}</div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <div class="input-group {{ $errors->has('password') ? 'is-invalid' : '' }}">
                                {!! html()->password('password')->placeholder(Lang::get("lang.password"))->class('form-control' . ($errors->has('password') ? ' is-invalid' : ''))->id('login-password') !!}
                                <button class="input-group-text" type="button" onclick="togglePwd('login-password', this)" tabindex="-1">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </button>
                            </div>
                            @if($errors->has('password'))
                                <div class="invalid-feedback d-block">{{ $errors->first('password') }}</div>
                            @endif
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary w-100">{!! Lang::get("lang.login") !!}</button>
                        </div>

                        <div class="row mt-2">

                            <div class="col-sm-5">

                                <div>

                                    <label>

                                        <input type="checkbox" name="remember"> {!! Lang::get("lang.remember") !!}
                                    </label>
                                </div>
                            </div>

                            <div class="col-sm-5">

                                <a href="{{url('password/email')}}">{!! Lang::get("lang.iforgot") !!}</a><br>
                            </div>

                            <div class="col-sm-2">

                                <a href="{{url('auth/register')}}" class="text-center">{!! Lang::get("lang.register") !!}</a>
                            </div>
                        </div>

                        <div>
                            @include('themes.default1.client.layout.social-login')
                        </div>

                    {!! html()->closeModelForm() !!}
                    <script>
                    function togglePwd(id, btn) {
                        var input = document.getElementById(id);
                        var icon = btn.querySelector('i');
                        if (input.type === 'password') {
                            input.type = 'text';
                            icon.className = 'fa-solid fa-eye';
                        } else {
                            input.type = 'password';
                            icon.className = 'fa-solid fa-eye-slash';
                        }
                    }
                    </script>
                </div>
            </div>
        </div>
    </div>
@stop
