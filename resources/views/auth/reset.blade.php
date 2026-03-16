@extends('themes.default1.layouts.login')

@section('body')
@if(Session::has('status'))
<div class="alert alert-success alert-dismissible">
    <i class="fa-solid fa-circle-check"> </i> <b> {!! Lang::get('lang.success') !!} </b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('status')}}
</div>
@endif
<!-- failure message -->
@if (count($errors) > 0)
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!} !</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    @foreach ($errors->all() as $error)
    <li class="error-message-padding">{{ $error }}</li>
    @endforeach
</div>
@endif

<div id="corewidgetbox" class="wid">
            
    <div id="wbox" class="widgetrow text-center">
        
        @if(Auth::user())
        @else
        <span onclick="javascript: window.location.href='{{url('auth/register')}}';">
            <a href="{{url('auth/register')}}" class="widgetrowitem defaultwidget widget-bg-register">
                <span class="widgetitemtitle">{!! Lang::get('lang.register') !!}</span>
            </a>
        </span>
        @endif
        <?php $system = App\Model\helpdesk\Settings\System::where('id', '=', '1')->first();            
        ?>
        @if($system != null) 
            @if($system->status) 
                @if($system->status == 1)
                    <span onclick="javascript: window.location.href='{!! URL::route('form') !!}';">
                        <a href="{!! URL::route('form') !!}" class="widgetrowitem defaultwidget widget-bg-submit-ticket">
                            <span class="widgetitemtitle">{!! Lang::get('lang.submit_a_ticket') !!}</span>
                        </a>
                    </span>
                @endif
            @endif
        @endif
        <span onclick="javascript: window.location.href='{{url('mytickets')}}';">
            <a href="{{url('mytickets')}}" class="widgetrowitem defaultwidget widget-bg-my-tickets">
                <span class="widgetitemtitle">{!! Lang::get('lang.my_tickets') !!}</span>
            </a>
        </span>
        <span onclick="javascript: window.location.href='{{url('/knowledgebase')}}';">
            <a href="{{url('/knowledgebase')}}" class="widgetrowitem defaultwidget widget-bg-knowledge-base">
                <span class="widgetitemtitle">{!! Lang::get('lang.knowledge_base') !!}</span>
            </a>
        </span>
    </div>
</div>

        <script type="text/javascript"> $(function(){ $('.dialogerror, .dialoginfo, .dialogalert').fadeIn('slow');$("form").bind("submit", function(e){$(this).find("input:submit").attr("disabled", "disabled");});});</script>
        <script type="text/javascript" >try {if (top.location.hostname != self.location.hostname) { throw 1; }} catch (e) { top.location.href = self.location.href; }</script>
        
<div class="col-md-6 offset-md-3 form-helper">

    <div valign="center" class="login-box login-box-auto">

        <div class="form-border">

            <h3 class="text-center-inline">{!! Lang::get('lang.reset_password') !!}</h3>

            <form class="form-horizontal" role="form" method="POST" action="{{ url('/password/reset') }}">
                
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                
                <input type="hidden" name="token" value="{{ $token }}">
                <!-- Email -->
                <div class="input-group mb-3 {{ $errors->has('email') ? 'has-error' : '' }}">
                    <input type="email" class="form-control" name="email" placeholder="{!! Lang::get('lang.e-mail') !!}" value="{{ $email }}" readonly>
                    <span class="input-group-text"><i class="fa-regular fa-envelope input-icon-muted"></i></span>
                </div>

                <!-- password -->
                <div class="input-group mb-3 {{ $errors->has('password') ? 'has-error' : '' }}">
                    <input type="password" class="form-control" name="password" id="reset-password" placeholder="{!! Lang::get('lang.password') !!}">
                    <button class="input-group-text" type="button" onclick="togglePwd('reset-password', this)" tabindex="-1">
                        <i class="fa-solid fa-eye-slash"></i>
                    </button>
                </div>

                <!-- confirm password -->
                <div class="input-group mb-3 {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
                    <input type="password" class="form-control" name="password_confirmation" id="reset-password-confirm" placeholder="{!! Lang::get('lang.confirm_password') !!}">
                    <button class="input-group-text" type="button" onclick="togglePwd('reset-password-confirm', this)" tabindex="-1">
                        <i class="fa-solid fa-eye-slash"></i>
                    </button>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa-solid fa-arrows-rotate"></i>
                        {!! Lang::get('lang.reset_password') !!}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
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
@stop
