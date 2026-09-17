@extends('themes.default1.agent.layout.agent')

@section('Dashboard')
    class="nav-link active"
@stop

@section('dashboard-bar')
    active
@stop

@section('profile')
    class="nav-link active"
@stop

@section('PageHeader')
    <h3>{{Lang::get('lang.edit-profile')}}</h3>
@stop

@section('content')


    @if(Session::has('success1'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i><b>Success</b> {{Session::get('success1')}}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(Session::has('fails1'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-ban me-2"></i><b>Fail!</b> {{Session::get('fails1')}}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i><b>Success</b> {{Session::get('success')}}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(Session::has('fails'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-ban me-2"></i><b>Fail!</b> {{Session::get('fails')}}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(Session::has('errors'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-ban me-2"></i><b>{!! Lang::get('lang.alert') !!}!</b>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <br/>
            @if($errors->first('first_name'))
                <li class="error-message-padding">{!! $errors->first('first_name', ':message') !!}</li>
            @endif
            @if($errors->first('mobile'))
                <li class="error-message-padding">{!! $errors->first('mobile', ':message') !!}</li>
            @endif
        </div>
    @endif

    <div class="row">
        {{-- ── Profile Form ── --}}
        <div class="col-md-6">
            {!! html()->modelForm($user, 'PATCH', url('agent-profile'))->acceptsFiles()->attributes(['id' => 'agent-profile'])->open() !!}
            <div class="card card-light">
                <div class="card-header">
                    <h3 class="card-title">{!! Lang::get('lang.profile') !!}</h3>
                </div>
                <div class="card-body">

                    <div class="mb-3 mb-3 {{ $errors->has('first_name') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('lang.first_name'), 'first_name') !!} <span class="text-danger">*</span>
                        {!! html()->text('first_name', null)->class('form-control') !!}
                    </div>

                    <div class="mb-3 mb-3 {{ $errors->has('last_name') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('lang.last_name'), 'last_name') !!}
                        {!! html()->text('last_name', null)->class('form-control') !!}
                    </div>

                    <div class="mb-3 mb-3">
                        {!! html()->label(Lang::get('lang.gender'), 'gender') !!}
                        <div class="row">
                            <div class="col-sm-3">
                                {!! html()->radio('gender', true, '1') !!} {{Lang::get('lang.male')}}
                            </div>
                            <div class="col-sm-3">
                                {!! html()->radio('gender', null, '0') !!} {{Lang::get('lang.female')}}
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 mb-3">
                        {!! html()->label(Lang::get('lang.email_address'), 'email') !!}
                        <p class="form-control-plaintext border rounded px-3 py-2 bg-body-secondary text-body-secondary small mb-0">
                            <i class="fa-solid fa-envelope me-2"></i>{{ $user->email }}
                        </p>
                    </div>

                    <div class="mb-3 mb-3 {{ $errors->has('company') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('lang.company'), 'company') !!}
                        {!! html()->text('company', null)->class('form-control') !!}
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-2 mb-3 {{ Session::has('country_code_error') ? 'has-error' : '' }}">
                            {!! html()->label(Lang::get('lang.country-code'), 'country_code') !!}
                            {!! html()->text('country_code', null)->class('form-control')->placeholder($phonecode)->id('code')->attributes(['title' => Lang::get('lang.enter-country-phone-code')]) !!}
                        </div>
                        <div class="col-sm-8 mb-3 {{ $errors->has('phone_number') ? 'has-error' : '' }}">
                            {!! html()->label(Lang::get('lang.phone'), 'phone_number') !!}
                            {!! html()->text('phone_number', null)->class('form-control') !!}
                        </div>
                        <div class="col-sm-2 mb-3 {{ $errors->has('ext') ? 'has-error' : '' }}">
                            {!! html()->label(Lang::get('lang.ext'), 'ext') !!}
                            {!! html()->text('ext', null)->class('form-control') !!}
                        </div>
                    </div>

                    <div class="mb-3 mb-3 {{ $errors->has('mobile') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('lang.mobile_number'), 'mobile') !!}
                        {!! html()->number('mobile', null)->class('form-control')->id('mobile') !!}
                    </div>

                    <div class="mb-3 mb-3 {{ $errors->has('agent_sign') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('lang.agent_sign'), 'agent_sign') !!}
                        {!! html()->textarea('agent_sign', null)->class('form-control') !!}
                    </div>

                    <div class="mb-3 mb-3 {{ $errors->has('profile_pic') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('lang.profile_pic'), 'profile_pic') !!}
                        {!! html()->file('profile_pic')->class('form-control')->id('profile_pic_input') !!}
                        <small class="text-muted">JPG, PNG or GIF. Max 2MB.</small>
                    </div>

                    {!! html()->token() !!}
                    {!! html()->closeModelForm() !!}
                </div>
                <div class="card-footer">
                    {!! html()->submit(Lang::get('lang.update'))->class('btn btn-primary') !!}
                </div>
            </div>
        </div>

        {{-- ── Change Password Form ── --}}
        <div class="col-md-6">
            {!! html()->modelForm($user, 'PATCH', url('agent-profile-password/'.$user->id))->open() !!}
            <div class="card card-light">
                <div class="card-header">
                    <h3 class="card-title">{!! Lang::get('lang.change_password') !!}</h3>
                </div>
                <div class="card-body pb-0">

                    <div class="mb-3 mb-3 {{ $errors->has('old_password') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('lang.old_password'), 'old_password') !!} <span class="text-danger">*</span>
                        <div class="input-group">
                            {!! html()->password('old_password')->class('form-control')->id('old_password') !!}
                            <button class="input-group-text" type="button" onclick="togglePwd('old_password', this)" tabindex="-1">
                                <i class="fa-solid fa-eye-slash"></i>
                            </button>
                        </div>
                        {!! $errors->first('old_password', '<span class="text-danger small">:message</span>') !!}
                    </div>

                    <div class="mb-3 mb-3 {{ $errors->has('new_password') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('lang.new_password'), 'new_password') !!} <span class="text-danger">*</span>
                        <div class="input-group">
                            {!! html()->password('new_password')->class('form-control')->id('new_password') !!}
                            <button class="input-group-text" type="button" onclick="togglePwd('new_password', this)" tabindex="-1">
                                <i class="fa-solid fa-eye-slash"></i>
                            </button>
                        </div>
                        {!! $errors->first('new_password', '<span class="text-danger small">:message</span>') !!}
                    </div>

                    <div class="mb-3 mb-3 {{ $errors->has('confirm_password') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('lang.confirm_password'), 'confirm_password') !!} <span class="text-danger">*</span>
                        <div class="input-group">
                            {!! html()->password('confirm_password')->class('form-control')->id('confirm_password') !!}
                            <button class="input-group-text" type="button" onclick="togglePwd('confirm_password', this)" tabindex="-1">
                                <i class="fa-solid fa-eye-slash"></i>
                            </button>
                        </div>
                        {!! $errors->first('confirm_password', '<span class="text-danger small">:message</span>') !!}
                    </div>

                </div>
                <div class="card-footer">
                    {!! html()->submit(Lang::get('lang.update'))->class('btn btn-primary') !!}
                </div>
            </div>
            {!! html()->closeModelForm() !!}
        </div>
    </div>

    {{-- ── Verify Number Modal ── --}}
    <div class="modal fade" id="last-modal" tabindex="-1" aria-labelledby="lastModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="lastModalLabel">{{Lang::get('lang.verify-number')}}</h5>
                    <button type="button" class="btn-close closemodal" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="loader2" class="text-center d-none">
                        <img src="{{asset('lb-faveo/media/images/gifloader.gif')}}">
                    </div>
                    <div id="verify-success" class="alert alert-success d-none" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i>
                        <span id="success_message"></span>
                    </div>
                    <div id="verify-fail" class="alert alert-danger d-none" role="alert">
                        <i class="fa-solid fa-ban me-2"></i>
                        <b>{!! Lang::get('lang.alert') !!}!</b>
                        <span id="error_message"></span>
                    </div>
                    <div id="verify-number-form">
                        {!! html()->form('POST', url()->current())->attributes(['id' => 'verify-otp'])->open() !!}
                        <div class="row">
                            <div class="col-md-8">{{ Lang::get('lang.get-verify-message') }}</div>
                            <div class="col-md-4">
                                {!! html()->text('token', '')->class('form-control')->required()->placeholder(Lang::get('lang.enter-otp'))->id('otp') !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" id="close-last" class="btn btn-outline-secondary closemodal">{{Lang::get('lang.close')}}</button>
                    <div id="last-submit">
                        <input type="submit" id="merge-btn" class="btn btn-primary" value="{!! Lang::get('lang.verify') !!}">
                    </div>
                </div>
                {!! html()->closeModelForm() !!}
            </div>
        </div>
    </div>

    <script>
        function togglePwd(id, btn) {
            var input = document.getElementById(id);
            var icon  = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fa-solid fa-eye';
            } else {
                input.type = 'password';
                icon.className = 'fa-solid fa-eye-slash';
            }
        }

        $(function () {
            $("textarea").wysihtml5();


        });
    </script>

    @if($verify == 1 || $verify == '1')
        <script type="text/javascript">
            $('#agent-profile').on('submit', function (e) {
                var old_mobile = "<?php echo $user->mobile; ?>";
                var email      = "<?php echo $user->email; ?>";
                var full_name  = "<?php echo $user->first_name; ?>";
                var mobile     = document.getElementById('mobile').value;
                var code       = document.getElementById('code').value;

                if (code !== '' && code !== null) {
                    var id = "<?php echo $user->id; ?>";
                    if (mobile !== old_mobile) {
                        e.preventDefault();
                        var modal = new bootstrap.Modal(document.getElementById('last-modal'));
                        modal.show();
                        $.ajax({
                            url: '{{URL::route("agent-verify-number")}}',
                            type: 'post',
                            data: { mobile: mobile, full_name: full_name, email: email, code: code },
                            dataType: 'json',
                            beforeSend: function () {
                                $('#loader2').removeClass('d-none');
                                $('#verify-number-form').addClass('d-none');
                                $('#verify-fail, #verify-success').addClass('d-none');
                            },
                            success: function (response) {
                                $('#loader2').addClass('d-none');
                                $('#verify-number-form').removeClass('d-none');
                                $('#verify-otp').on('submit', function (e) {
                                    e.preventDefault();
                                    var otp = document.getElementById('otp').value;
                                    $.ajax({
                                        url: '{{URL::route("post-agent-verify-number")}}',
                                        type: 'POST',
                                        data: { otp: otp, u_id: id },
                                        dataType: 'html',
                                        beforeSend: function () {
                                            $('#loader2').removeClass('d-none');
                                            $('#verify-number-form').addClass('d-none');
                                            $('#verify-fail, #verify-success').addClass('d-none');
                                        },
                                        success: function (response) {
                                            if (response == 1) {
                                                $('#loader2').addClass('d-none');
                                                $('#success_message').html("{{Lang::get('lang.number-verification-sussessfull')}}");
                                                $('#verify-success').removeClass('d-none');
                                                $('#agent-profile').unbind('submit').submit();
                                            } else {
                                                $('#loader2').addClass('d-none');
                                                $('#error_message').html(response);
                                                $('#verify-fail').removeClass('d-none');
                                                $('#verify-number-form').removeClass('d-none');
                                            }
                                        }
                                    });
                                });
                            },
                            complete: function (jqXHR, textStatus) {
                                if (['parsererror','timeout','abort','error'].includes(textStatus)) {
                                    $('#loader2').addClass('d-none');
                                    $('#error_message').html("{{Lang::get('lang.otp-not-sent')}}");
                                    $('#merge-btn').addClass('d-none');
                                    $('#verify-fail').removeClass('d-none');
                                }
                            }
                        });
                    }
                }
            });

            $('.closemodal').on('click', function () {
                var modal = bootstrap.Modal.getInstance(document.getElementById('last-modal'));
                if (modal) modal.hide();
            });
        </script>
    @endif

@stop