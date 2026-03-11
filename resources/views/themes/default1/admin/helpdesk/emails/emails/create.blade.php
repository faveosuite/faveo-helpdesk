@extends('themes.default1.admin.layout.admin')

@section('Emails')
class="nav-link active"
@stop

@section('email-menu-parent')
class="nav-item menu-open"
@stop

@section('email-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('emails')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h1>{{Lang::get('lang.add_an_email')}}</h1> 
@stop
<!-- /header -->
<!-- breadcrumbs -->
@section('breadcrumbs')
<ol class="breadcrumb">
</ol>
@stop
<!-- /breadcrumbs -->
<!-- content -->
@section('content')<!-- open a form -->

<form id="form">
    <div id="head"></div>    
    <div id="alert" style="display:none;">
        <div class="alert alert-danger alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <div id="alert-message"></div>
        </div>
    </div>
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div class="card card-light">
        <div class="card-header">
            <h3 class="card-title">{!! Lang::get('lang.email_information_and_settings') !!}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- email address -->
                <div class="col-sm-6 form-group {!! $errors->has('email_address') ? 'has-error' : '' !!}" id = "email_address_error">
                    {!! html()->label(Lang::get('lang.email_address'), 'email_address') !!} <span class="text-red"> *</span>
                    {!! $errors->first('email_address', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->text('email_address', null)->class('form-control')->id('email_address') !!}
                </div>
                <!-- user name -->
                <div class="col-sm-6 form-group {{ $errors->has('user_name') ? 'has-error' : '' }}" id="user_name_error">
                    {!! html()->label(Lang::get('lang.user_name'), 'user_name') !!}
                    {!! $errors->first('user_name', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->text('user_name', null)->class('form-control') !!}
                </div>
                <!-- Email name -->
                <div class="col-sm-6 form-group {!! $errors->has('email_name') ? 'has-error' : ''!!}" id="email_name_error">
                    {!! html()->label(Lang::get('lang.from_name'), 'email_name') !!} <span class="text-red"> *</span>
                    {!! $errors->first('email_name', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->text('email_name', null)->class('form-control')->id('email_name') !!}
                </div>
                <!-- password -->
                <div class="col-sm-6 form-group {!! $errors->has('password') ? 'has-error' : ''!!}" id="password_error">
                    {!! html()->label(Lang::get('lang.password'), 'password') !!} <span class="text-red"> *</span>
                    {!! $errors->first('password', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->password('password')->class('form-control')->id('password') !!}
                </div>
            </div>
        
            <div class="card card-light">
                
                <div class="card-header">
                    <h3 class="card-title">{!! Lang::get('lang.new_ticket_settings') !!}</h3>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- department -->
                        <div class="col-sm-4 form-group {!! $errors->has('department') ? 'has-error' : ''!!}" id="department_error">
                            {!! html()->label(Lang::get('lang.department'), 'department') !!}
                            {!! $errors->first('department', '<spam class="help-block">:message</spam>') !!}
                            {!! html()->select('department', [''=>'--System Default--','departments'=>$departments->pluck('name','id')->toArray()], null)->class('form-control select')->id('department') !!}
                        </div>
                        <!-- Priority -->
                        <div class="col-sm-4 form-group {!! $errors->has('priority') ? 'has-error' : ''!!}" id="priority_error">
                            {!! html()->label(Lang::get('lang.priority'), 'priority') !!}
                            {!! $errors->first('priority', '<spam class="help-block">:message</spam>') !!}
                            {!! html()->select('priority', [''=>'--System Default--','Priorities'=>$priority->pluck('priority_desc','priority_id')->toArray()], null)->class('form-control select')->id('priority') !!}
                        </div>
                        <!-- Help topic -->
                        <div class="col-sm-4 form-group {!! $errors->has('help_topic') ? 'has-error' : ''!!}" id="help_topic_error">
                            {!! html()->label(Lang::get('lang.help_topic'), 'help_topic') !!}
                            {!! $errors->first('help_topic', '<spam class="help-block">:message</spam>') !!}
                            {!! html()->select('help_topic', [''=>'--System Default--','Help Topics'=>$helps->pluck('topic','id')->toArray()], null)->class('form-control select')->id('help_topic') !!}
                        </div>
                        <!-- status -->
                        <div class="col-sm-2 form-group">
                            {!! html()->label(Lang::get('lang.auto_response'), 'auto_response') !!}
                        </div>
                        <div class="col-sm-3 form-group">

                            <input type="checkbox" name="auto_response" id="auto_response"> {{Lang::get('lang.disable_for_this_email_address')}}
                        </div>
                    </div>
                </div>    
            </div>
            
            <div class="card card-light">
                
                <div class="card-header">
                    <h3 class="card-title">{!! Lang::get('lang.incoming_email_information') !!}</h3>
                </div>

                <div class="card-body">
                    <div>
                        <div class="form-group">
                            <!-- status -->
                            {!! html()->label(Lang::get('lang.status'), 'fetching_status') !!}
                            <input type="checkbox" name="fetching_status" id="fetching_status"> {{Lang::get('lang.enable')}}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-2 form-group {!! $errors->has('fetching_protocol') ? 'has-error' : ''!!}" id="fetching_protocol_error">
                            {!! html()->label(Lang::get('lang.protocol'), 'fetching_protocol') !!}
                            {!! $errors->first('fetching_protocol', '<spam class="help-block">:message</spam>') !!}
                            {!! html()->select('fetching_protocol', ['imap' => 'IMAP', 'pop' => 'POP3'], null)->class('form-control select')->id('fetching_protocol') !!}
                        </div>
                        <div class="col-sm-2 form-group  {!! $errors->has('fetching_host') ? 'has-error' : ''!!}" id="fetching_host_error">
                            {!! html()->label(Lang::get('lang.host_name'), 'fetching_host') !!}
                            {!! $errors->first('fetching_host', '<spam class="help-block">:message</spam>') !!}
                            {!! html()->text('fetching_host', null)->class('form-control')->id('fetching_host') !!}
                        </div>
                        <div class="col-sm-2 form-group {!! $errors->has('fetching_port') ? 'has-error' : ''!!}" id="fetching_port_error">
                            {!! html()->label(Lang::get('lang.port_number'), 'fetching_port') !!}
                            {!! $errors->first('fetching_port', '<spam class="help-block">:message</spam>') !!}
                            {!! html()->text('fetching_port', null)->class('form-control')->id('fetching_port') !!}
                        </div>
                        <div class="col-sm-2 form-group {!! $errors->has('fetching_encryption') ? 'has-error' : ''!!}" id="fetching_encryption_error">
                            {!! html()->label(Lang::get('lang.encryption'), 'fetching_encryption') !!}
                            {!! $errors->first('fetching_encryption', '<spam class="help-block">:message</spam>') !!}
                            {!! html()->select('fetching_encryption', [''=>'-----Select-----','ssl' => 'SSL', 'tls' => 'TLS', 'starttls' => 'STARTTLS'], null)->class('form-control select')->id('fetching_encryption') !!}
                        </div>
                        <div class="col-sm-2 form-group {!! $errors->has('imap_authentication') ? 'has-error' : ''!!}" id="imap_authentication_error">
                            {!! html()->label(Lang::get('lang.authentication'), 'fetching_authentication') !!}
                            {!! html()->select('imap_authentication', ['normal' => 'Normal Password'], null)->class('form-control select')->id('imap_authentication') !!}
                        </div>
                        <div class="col-sm-2 form-group">
                            <br>
                            <input type="checkbox" name="imap_validate" id="imap_validate">&nbsp; {!! Lang::get('lang.validate_certificates_from_tls_or_ssl_server') !!}
                        </div>
                    </div>
                </div>    
            </div>
            
            <div class="card card-light">
                
                <div class="card-header">
                    <h3 class="card-title">{!! Lang::get('lang.outgoing_email_information') !!}</h3>
                </div>
                <div class="card-body">
                    <div>
                        <!-- status -->
                        <div class="form-group">
                            {!! html()->label(Lang::get('lang.status'), 'sending_status') !!} 
                            <input type="checkbox" name="sending_status" id="sending_status"> {!! Lang::get('lang.enable') !!} 
                        </div>
                    </div>
                    <div class="row">
                        <!-- Encryption -->
                        <div class="col-sm-2 form-group {!! $errors->has('sending_protocol') ? 'has-error' : ''!!}" id="sending_protocol_error">
                            {!! html()->label(Lang::get('lang.transfer_protocol'), 'sending_protocol') !!}
                            {!! $errors->first('sending_protocol', '<spam class="help-block">:message</spam>') !!} 
                            {!! html()->select('sending_protocol', [''=>'Select','Drives'=>$services], null)->class('form-control select')->id('service') !!}
                        </div> 
                        <!-- sending hoost -->
                        <div class="col-sm-2 form-group {!! $errors->has('sending_host') ? 'has-error' : ''!!}" id="sending_host_error">
                            {!! html()->label(Lang::get('lang.host_name'), 'sending_host') !!}
                            {!! $errors->first('sending_host', '<spam class="help-block">:message</spam>') !!} 
                            {!! html()->text('sending_host', null)->class('form-control') !!}
                        </div> 
                        <!-- sending port -->
                        <div class="col-sm-2 form-group {!! $errors->has('sending_port') ? 'has-error' : ''!!}" id="sending_port_error">
                            {!! html()->label(Lang::get('lang.port_number'), 'sending_port') !!}
                            {!! $errors->first('sending_port', '<spam class="help-block">:message</spam>') !!}
                            {!! html()->text('sending_port', null)->class('form-control') !!}
                        </div>
                        <!-- Encryption -->
                        <div class="col-sm-2 form-group {!! $errors->has('sending_encryption') ? 'has-error' : ''!!}" id="sending_encryption_error">
                            {!! html()->label(Lang::get('lang.encryption'), 'sending_encryption') !!}
                            {!! $errors->first('sending_encryption', '<spam class="help-block">:message</spam>') !!} 
                            {!! html()->select('sending_encryption', [''=>'-----Select-----','ssl' => 'SSL', 'tls' => 'TLS', 'starttls' => 'STARTTLS'], null)->class('form-control select') !!}
                        </div> 
                        <div class="col-sm-2 form-group {!! $errors->has('smtp_authentication') ? 'has-error' : ''!!}" id="smtp_authentication_error">
                            {!! html()->label(Lang::get('lang.authentication'), 'sending_authentication') !!}
                            {!! html()->select('smtp_authentication', ['normal' => 'Normal Password'], null)->class('form-control select')->id('smtp_authentication') !!}
                        </div>
                        <div class="col-sm-2 form-group">
                            <br>
                            <input type="checkbox" name="smtp_validate" id="smtp_validate">&nbsp; {!! Lang::get('lang.validate_certificates_from_tls_or_ssl_server') !!}
                        </div>
                    </div>
                    <div id="response"></div>
                    <!-- Internal notes -->
                    <div class="form-group">
                        {!! html()->label(Lang::get('lang.internal_notes'), 'internal_notes') !!}
                        {!! html()->textarea('internal_notes', null)->class('form-control')->attributes(['size' => '30x10']) !!}
                    </div>
                </div>    
            </div>
        </div> 
        
        <div class="card-footer">
            {!! html()->button('<i id="spin" class="fas fa-spinner initially-hidden"></i>' . Lang::get("lang.create").'')->class('btn btn-primary')->attributes(['type' => 'submit']) !!}
        </div>
    </div>
</form>

<div class="modal fade" id="loadingpopup">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div id="head" class="text-center">
                    <button type="button" class="close" id="close" data-dismiss="modal" aria-label="Close" style="display:none;"><span aria-hidden="true">×</span></button>
                    <img src="{{asset("lb-faveo/media/images/gifloader.gif")}}" >
                    <br/>
                    <br/>
                    <br/>
                    <center><h3 style="color:#80DE02;">Testing incoming & outgoing mail server</h3></center>
                    <br/>
                    <center><h6>Please wait while testing is in progress ...</h6></center>
                    <center><h6>(Please do not use "Refresh" or "Back" button)</h6></center>
                    <br/>
                </div>
            </div>
        </div>
    </div>
</div>

<button style="display:none" data-toggle="modal" data-target="#loadingpopup" id="click"></button>

<script type="text/javascript">
    //submit form
    $('#form').on('submit', function () {
        var form_data = $(this).serialize();
        $.ajax({
            type: "POST",
            url: "{!! route('validating.email.settings') !!}",
            dataType: "json",
            data: form_data,
            headers: {
                'X-CSRF-Token': $('meta[name="_token"]').attr('content')
            },
            beforeSend: function () {
                $('#alert').empty();
                $("#click").trigger("click");
            },
            success: function (json) {
                console.log(json);
                $("#close").trigger("click");
                var res = "";
                $.each(json.result, function (idx, topic) {
                    if (idx === "success") {
                        res = "<div class='alert alert-success'>" + topic + "</div>";
                    }
                    if (idx === "fails") {
                        res = "<div class='alert alert-danger'>" + topic + "</div>";
                    }
                });

                $("#head").html(res);
                $('html, body').animate({scrollTop: $("#form").offset().top}, 500);
            },
            error: function (json) {
                console.log(json);
                $("#close").trigger("click");
                var res = "";
                $.each(json.responseJSON.errors, function (idx, topic) {
                    res += "<li>" + topic + "</li>";
                });
                $("#head").html("<div class='alert alert-danger'><strong>Whoops!</strong> There were some problems with your input.<br><br><ul>" + res + "</ul></div>");
                $('html, body').animate({scrollTop: $("#form").offset().top}, 500);
            }
        });
        return false;
    });

    $(document).ready(function () {
        var serviceid = $("#service").val();
        send(serviceid);
        $("#service").on('change', function () {
            serviceid = $("#service").val();
            send(serviceid);
        });
        function send(serviceid) {
            $.ajax({
                url: "{{url('mail/config/service')}}",
                dataType: "html",
                data: {'service': serviceid},
                success: function (response) {
                    $("#response").html(response);
                },
                error: function (response) {
                    $("#response").html(response);
                }
            });
        }
    });
</script>
@stop