@extends('themes.default1.admin.layout.admin')

@section('API')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h1>{{ Lang::get('lang.api')}}</h1>
@stop
<!-- /header -->
<!-- breadcrumbs -->
@section('breadcrumbs')
<ol class="breadcrumb">
</ol>
@stop
<!-- /breadcrumbs -->
<!-- content -->
@section('content')
@if (count($errors) > 0)
<div class="alert alert-danger">
    <strong>Whoops!</strong> There were some problems with your input.<br><br>
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('success')}}
</div>
@endif
<!-- fail message -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('fails')}}
</div>
@endif

{!! html()->form('POST', url('api'))->acceptsFiles()->open() !!}
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{{Lang::get('lang.api_settings')}}</h3>     
    </div>
    <div class="card-body">

        <div class="card card-light">
            
            <div class="card-header">
                <h3 class="card-title">{!! Lang::get('lang.api_configurations') !!}</h3>
            </div>

            <div class="card-body">        
                <!-- Guest user page Content -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3 {{ $errors->has('api_enable') ? 'has-error' : '' }}">
                            {!! html()->label(Lang::get('lang.api'), 'api') !!}
                            {!! $errors->first('api_enable', '<spam class="help-block">:message</spam>') !!}
                            <div class="row">
                                <div class="col-sm-5">
                                    <input type="radio" name="api_enable" value="1" @if($systems->api_enable ==1) checked @endif>&nbsp;{{Lang::get('lang.enable')}}
                                    <!-- {!! html()->radio('api_enable', true, '1') !!} {{Lang::get('lang.enable')}} -->
                                </div>
                                <div class="col-sm-5">
                                    <input type="radio" name="api_enable" value="0" @if($systems->api_enable == 0) checked @endif>&nbsp;{{Lang::get('lang.disable')}}
                                    <!-- {!! html()->radio('api_enable', null, '0') !!} {{Lang::get('lang.disable')}} -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3 {{ $errors->has('api_key_mandatory') ? 'has-error' : '' }}">
                            {!! html()->label(Lang::get('lang.api_key_mandatory'), 'api_key_mandatory') !!}
                            {!! $errors->first('api_key_mandatory', '<spam class="help-block">:message</spam>') !!}
                            <div class="row">
                                <div class="col-sm-5">
                                    <input type="radio" name="api_key_mandatory" value="1" @if($systems->api_key_mandatory == 1) checked @endif>&nbsp;{{Lang::get('lang.enable')}}
                                    <!-- {!! html()->radio('api_key_mandatory', true, '1') !!} {{Lang::get('lang.enable')}} -->
                                </div>
                                <div class="col-sm-5">
                                     <input type="radio" name="api_key_mandatory" value="0" @if($systems->api_key_mandatory == 0) checked @endif>&nbsp;{{Lang::get('lang.disable')}}
                                    <!-- {!! html()->radio('api_key_mandatory', null, '0') !!} {{Lang::get('lang.disable')}} -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Date and Time Format: text: required: eg - 03/25/2015 7:14 am -->
                    <div class="col-md-3">
                        <div class="mb-3 {{ $errors->has('api_key') ? 'has-error' : '' }}">
                            {!! html()->label(Lang::get('lang.api_key'), 'api_key') !!}
                            {!! $errors->first('api_key', '<spam class="help-block">:message</spam>') !!}
                            {!! html()->text('api_key', $systems->api_key)->class('form-control') !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <br/>
                        <a class="btn btn-primary" id="generate" href="javascript:;" style="margin-top: 8px;"> <i class="fa-solid fa-arrows-rotate"> </i> {!! Lang::get('lang.generate_key') !!}</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-light">
            
            <div class="card-header">
                
                <h3 class="card-title">{!! Lang::get('lang.webhooks') !!}</h3>
            </div>

            <div class="card-body">
                
                <div class="row">
                    
                    <div class="mb-3 col-md-6 {{ $errors->has('ticket_detail') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('lang.enter_url_to_send_ticket_details'), 'ticket_detail')->class('required') !!}
                        {!! html()->text('ticket_detail', $ticket_detail)->class('form-control')->placeholder('http://www.example.com') !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer">
        {!! html()->submit(Lang::get('lang.update'))->class('btn btn-primary') !!} 
    </div>
    {!! html()->closeModelForm() !!}   
</div>

<a href="#" id="clickGenerate" data-bs-toggle="modal" data-bs-target="#generateModal"></a>    
<div class="modal fade" id="generateModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{!! Lang::get('lang.api_key') !!}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" id="messageBody">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">{!! Lang::get('lang.close') !!}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script type="text/javascript">
jQuery(document).ready(function() {
// Close a ticket
    $('#generate').on('click', function(e) {
        $.ajax({
            type: "GET",
            url: "{!! url('generate-api-key') !!}",
            beforeSend: function() {
                $("#generate").empty();
                var message = "<i class='fas fa-arrows-rotate fa-spin'> </i>  <?php echo Lang::get('lang.generate_key'); ?>";
                $('#generate').html(message);
            },
            success: function(response) {
                // alert(response);

                $("#messageBody").empty();
                var message = "<div class='alert alert-info mb-0'>Copy and paste in the api to set a different Api key</div> <br/><b>Api key</b> : " + response;
                $('#messageBody').html(message);

                $('#clickGenerate').trigger("click");
                $("#generate").empty();
                var message = "<i class='fas fa-arrows-rotate'> </i>  <?php echo Lang::get('lang.generate_key'); ?>";
                $('#generate').html(message);
            }
        })
        return false;
    });
});
</script>
@stop
