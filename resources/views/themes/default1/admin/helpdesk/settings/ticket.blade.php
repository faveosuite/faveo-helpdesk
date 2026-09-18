@extends('themes.default1.admin.layout.admin')

@section('Tickets')
class="nav-link active"
@stop

@section('ticket-menu-parent')
class="nav-item menu-open"
@stop

@section('ticket-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('tickets')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{{ Lang::get('lang.settings') }}</h3>
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
<!-- open a form -->
{!! html()->modelForm($tickets, 'PATCH', url('postticket/'.$tickets->id))->open() !!}
<!-- check whether success or not -->
@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa-solid fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {!! Session::get('success') !!}
</div>
@endif
<!-- failure message -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {!! Session::get('fails') !!}
</div>
@endif
@if(Session::has('errors'))
<?php //dd($errors); ?>
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <br/>
    @if($errors->first('status'))
    <li class="error-message-padding">{!! $errors->first('status', ':message') !!}</li>
    @endif
    @if($errors->first('priority'))
    <li class="error-message-padding">{!! $errors->first('priority', ':message') !!}</li>
    @endif
    @if($errors->first('sla'))
    <li class="error-message-padding">{!! $errors->first('sla', ':message') !!}</li>
    @endif
    @if($errors->first('help_topic'))
    <li class="error-message-padding">{!! $errors->first('help_topic', ':message') !!}</li>
    @endif
    @if($errors->first('collision_avoid'))
    <li class="error-message-padding">{!! $errors->first('collision_avoid', ':message') !!}</li>
    @endif
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{{Lang::get('lang.ticket-setting')}}</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Default Status: Required : manual: Dropdowm  -->
            <div class="mb-3 col-md-6 {{ $errors->has('status') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.default_status'), 'status') !!}
                <select class="form-control" id="status" name="status">
                    <option value="1" >Open</option>
                </select>
            </div>
            <!-- Default Priority:	Required : manual : Dropdowm  -->
            <div class="mb-3 col-md-6 {{ $errors->has('priority') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.default_priority'), 'priority') !!}
                {!! html()->select('priority', [''=>Lang::get('lang.select_a_priority'),Lang::get('lang.priorities')=>$priority->pluck('priority_desc','priority_id')->toArray()], null)->class('form-control') !!}
            </div>
        </div>
        <div class="row">
            <!-- Agent Collision Avoidance Duration: text-number   -minutes  -->
            <div class="mb-3 col-md-6 {{ $errors->has('collision_avoid') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.agent_collision_avoidance_duration'), 'collision_avoid') !!} 
                <div class="input-group">
                    <input type="number" class="form-control" name="collision_avoid" min="0"  step="1" value="{{$tickets->collision_avoid}}" placeholder="in minutes">
                    <div class="input-group-append">
                        <span class="btn btn-secondary"><i class="fa-solid fa-clock"></i> {!!Lang::get('lang.in_minutes')!!}</span>
                    </div>
                </div>
            </div> 
            <div class="mb-3 col-md-6 {{ $errors->has('help_topic') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.lock_ticket_frequency'), 'help_topic') !!}
                <select name='lock_ticket_frequency' class="form-control">
                    <option @if($tickets->lock_ticket_frequency == null) selected="true" @endif value="0">{!! Lang::get('lang.no')!!}</option>
                    <option @if($tickets->lock_ticket_frequency == 1) selected="true" @endif value="1">{!! Lang::get('lang.only-once')!!}</option>
                    <option @if($tickets->lock_ticket_frequency == 2) selected="true" @endif value="2">{!! Lang::get('lang.frequently')!!}</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="mb-3 col-md-6 {{ $errors->has('num_format') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.format'), 'num_format') !!} 
                 <a href="#" data-bs-toggle="tooltip" data-bs-placement="right" title="{{Lang::get('lang.ticket-number-format')}}"><i class="fa-solid fa-circle-question" style="padding: 0px;"></i></a>
                {!! html()->text('num_format', null)->class('form-control')->id('format') !!}

                <div id="result"></div>
            </div>

            <div class="mb-3 col-md-6 {{ $errors->has('num_sequence') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.type'), 'num_sequence') !!} 
                <a href="#" data-bs-toggle="tooltip" data-bs-placement="right" title="{{Lang::get('lang.ticket-number-type')}}"><i class="fa-solid fa-circle-question" style="padding: 0px;"></i></a>
    
                {!! html()->select('num_sequence', [''=>'Select','sequence'=>'Sequence','random'=>'Random'], null)->class('form-control')->id('type') !!}

                <div id="result"></div>
            </div>
        </div>
    </div>

    <div class="card-footer">
        {!! html()->submit(Lang::get('lang.submit'))->class('btn btn-primary') !!}
    </div>
</div>
{!! html()->closeModelForm() !!}
@stop
@section('FooterInclude')
<script>
    $(document).ready(function () {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
<script>
    $(document).ready(function () {
        var format = $("#format").val();
        var type = $("#type").val();
        send(format, type);
        $("#format").keyup(function () {
            format = $("#format").val();
            type = $("#type").val();
            send(format, type);
        });
        $("#type").on('change', function () {
            format = $("#format").val();
            type = $("#type").val();
            send(format, type);
        });
        function send(format, type) {
            $.ajax({
                url: "{{url('get-ticket-number')}}",
                type: "GET",
                dataType: "html",
                data: {'format': format, 'type': type},
                success: function (response) {
                    $("#result").html("Number :<b> " + response + "</b>");
                },
                error: function (response) {
                    console.log(response);
                    $("#result").html("<i>Invalid format</i>");
                }
            });
        }
    });
</script>
@stop