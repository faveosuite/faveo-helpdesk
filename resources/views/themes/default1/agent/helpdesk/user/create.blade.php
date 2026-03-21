@extends('themes.default1.agent.layout.agent')

@section('Users')
class="nav-link active"
@stop

@section('user-bar')
class="nav-link active"
@stop

@section('user')
class="active"
@stop

@section('user-directory')
class="nav-link active"
@stop

<!-- header -->
@section('PageHeader')
<h3>{!! Lang::get('lang.create_user') !!}</h3>
@stop
<!-- /header -->
<!-- content -->
@section('content')
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('fails')}}
</div>
@endif

@if(Session::has('errors'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <br/>
    @if($errors->first('first_name'))
    <li class="error-message-padding">{!! $errors->first('first_name', ':message') !!}</li>
    @endif
    @if($errors->first('last_name'))
    <li class="error-message-padding">{!! $errors->first('last_name', ':message') !!}</li>
    @endif
    @if($errors->first('user_name'))
    <li class="error-message-padding">{!! $errors->first('user_name', ':message') !!}</li>
    @endif
    @if($errors->first('email'))
    <li class="error-message-padding">{!! $errors->first('email', ':message') !!}</li>
    @endif
    @if($errors->first('country_code'))
    <li class="error-message-padding">{!! $errors->first('country_code', ':message') !!}</li>
    @endif
    @if($errors->first('mobile'))
    <li class="error-message-padding">{!! $errors->first('mobile', ':message') !!}</li>
    @endif
    @if($errors->first('ext'))
    <li class="error-message-padding">{!! $errors->first('ext', ':message') !!}</li>
    @endif
    @if($errors->first('phone_number'))
    <li class="error-message-padding">{!! $errors->first('phone_number', ':message') !!}</li>
    @endif
    @if($errors->first('active'))
    <li class="error-message-padding">{!! $errors->first('active', ':message') !!}</li>
    @endif
</div>
@endif    
<!-- open a form -->
{!! html()->form('POST', route('user.store'))->open() !!}
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">
            User Credentials
        </h3>
    </div>
    <div class="card-body">
            
        <div class="row">
            <!-- First name : first name : Required -->
            <div class="col-sm-6 mb-3 {{ $errors->has('first_name') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.first_name'), 'first_name') !!}<span class="text-red"> *</span>
                {!! html()->text('first_name', null)->class('form-control') !!}
            </div>
            <!-- Last name : last name : Required -->
            <div class="col-sm-6 mb-3 {{ $errors->has('last_name') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.last_name'), 'last_name') !!}
                {!! html()->text('last_name', null)->class('form-control') !!}
            </div>
            <!-- User Name : Text : Required-->
        </div>
        <div class="row">
            <!-- Email Address : Email : Required -->
            <div class="col-sm-6 mb-3 {{ $errors->has('email') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.email'), 'email') !!}
                @if ($email_mandatory->status == 1 || $email_mandatory->status == '1')
                <span class="text-red"> *</span>
                @endif
                {!! html()->email('email', null)->class('form-control') !!}
            </div>
            
            <div class="col-sm-6 mb-3 {{ $errors->has('user_name') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.user_name'), 'user_name') !!}<span class="text-red"> *</span>
                {!! html()->text('user_name', null)->class('form-control') !!}
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 mb-3 {{ $errors->has('organization') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.organization'), 'organization') !!}
                {!! html()->select('org_id', [''=>'Select','Organization'=>$org], null)->class('form-control')->id('org') !!}
                
            </div>
        </div>
        <div class="row">
            <div class="col-sm-1 mb-3 {{ $errors->has('country_code') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.country-code'), 'country_code') !!}
                @if ($email_mandatory->status == 0 || $settings->status == 1)
                     <span class="text-red"> *</span>
                @endif
                <!-- {!! $errors->first('country_code', '<spam class="help-block">:message</spam>') !!} -->
                {!! html()->text('country_code', null)->class('form-control')->placeholder($phonecode)->attributes(['title' => Lang::get('lang.enter-country-phone-code')]) !!}
            </div>
            <!-- mobile Number : Text :  -->
            <div class="col-md-3 mb-3 {{ $errors->has('mobile') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.mobile'), 'mobile') !!}
                @if ($email_mandatory->status == 0 || $settings->status == 1)
                     <span class="text-red"> *</span>
                @endif
                {!! html()->number('mobile', null)->class('form-control') !!}
            </div>
            <div class="col-sm-1 mb-3 {{ $errors->has('ext') ? 'has-error' : '' }}">
                <label for="ext">{!! Lang::get('lang.ext') !!}</label>  
                {!! html()->text('ext', null)->class('form-control') !!}
            </div>
            <div class="col-sm-3 mb-3 {{ $errors->has('phone_number') ? 'has-error' : '' }}">
                <label for="phone_number">{!! Lang::get('lang.phone') !!}</label>
                {!! html()->text('phone_number', null)->class('form-control') !!}
            </div>
            <div class="col-md-3 mb-3 {{ $errors->has('active') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.status'), 'active') !!}
                <div class="row">
                    <div class="col-sm-4">
                        {!! html()->radio('active', true, '1') !!} {{Lang::get('lang.active')}}
                    </div>
                    <div class="col-sm-6">
                        {!! html()->radio('active', null, '0') !!} {{Lang::get('lang.inactive')}}
                    </div>
                </div>
            </div>
        </div>
        <!-- Internal Notes : Textarea -->
        <div class="mb-3">
            {!! html()->label(Lang::get('lang.internal_notes'), 'internal_note') !!}
            {!! html()->textarea('internal_note', null)->class('form-control')->attributes(['size' => '30x5']) !!}
        </div>
        <!-- Send email to user about registration password -->
        <div>
            <input type="checkbox" name="send_email" checked> &nbsp;<label> {{ Lang::get('lang.send_password_via_email')}}</label>
        </div>
    </div>
    <div class="card-footer">
        {!! html()->submit(Lang::get('lang.submit'))->class('btn btn-primary') !!}
    </div>
</div>
<script>
    $(function () {
         $("textarea").summernote({
            height: 300,
            tabsize: 2,
            toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']]
          ]
          });
    });
</script>
@stop
@section('FooterInclude')
<!--<script>
    $('#org').autocomplete({
        minLength: 1,
        source: function (request, response) {
            $.getJSON("{{url('get-organization')}}", {
                term: request.term
            }, function (data) {
                var array = data.error ? [] : $.map(data, function (m) {
                    return {
                        label: m.label,
                        value: m.value
                    };
                });
                response(array);
            });
        },
        select: function (event, ui) {
            $("#org").val(ui.item.label); // display the selected text
            $("#field_id").val(ui.item.value); // save selected id to hidden input
            return false;
        }
    });

</script>-->

@stop
