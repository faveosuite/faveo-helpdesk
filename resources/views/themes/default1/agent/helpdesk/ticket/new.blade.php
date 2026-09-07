@extends('themes.default1.agent.layout.agent')

@section('Tickets')
class="nav-link active"
@stop

@section('ticket-bar')
active
@stop

@section('newticket')
class="nav-link active"
@stop

@section('ticket')
class="active"
@stop
<style>
    .clear-input {
        position: absolute;
        top: 20%;
        right: 5%;
        bottom: 0;
        width: 30px;
        margin: auto;
    }
</style>
@section('PageHeader')
<h3>{{Lang::get('lang.tickets')}}</h3>
@stop

@section('content')

<!-- Main content -->
{!! html()->form('POST', route('post.newticket'))->attributes(['id' => 'form'])->open() !!}
@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa-solid fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('success')}}
</div>
@endif
<!-- failure message -->
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
    @if($errors->first('email'))
    <li class="error-message-padding">{!! $errors->first('email', ':message') !!}</li>
    @endif
    @if($errors->first('first_name'))
    <li class="error-message-padding">{!! $errors->first('first_name', ':message') !!}</li>
    @endif
    @if($errors->first('phone'))
    <li class="error-message-padding">{!! $errors->first('phone', ':message') !!}</li>
    @endif
    @if($errors->first('subject'))
    <li class="error-message-padding">{!! $errors->first('subject', ':message') !!}</li>
    @endif
    @if($errors->first('body'))
    <li class="error-message-padding">{!! $errors->first('body', ':message') !!}</li>
    @endif
    @if($errors->first('code'))
    <li class="error-message-padding">{!! $errors->first('code', ':message') !!}</li>
    @endif
    @if($errors->first('mobile'))
    <li class="error-message-padding">{!! $errors->first('mobile', ':message') !!}</li>
    @endif
</div>
@endif

<div class="card card-light">
    
    <div class="card-header" id='box-header1'>
        <h3 class="card-title">{!! Lang::get('lang.create_ticket') !!}</h3>
    </div><!-- /.box-header -->
    
    <div class="card-body">
        
        <div class="card card-light">
            
            <div class="card-header">
                <h3 class="card-title">{!! Lang::get('lang.user_details') !!}:</h3>
            </div>

            <div class="card-body">

                <div class="mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <!-- email -->
                            <div class="mb-3 {{ $errors->has('email') ? 'has-error' : '' }}">
                                {!! html()->label(Lang::get('lang.email'), 'email') !!}
                                @if ($email_mandatory->status == 1)
                                <span class="text-red"> *</span>
                                @endif

                                {!! html()->text('email', null)->class('form-control')->id('email') !!}
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <!-- email -->
                            <div class="mb-3 {{ $errors->has('first_name') ? 'has-error' : '' }}">
                                {!! html()->label(Lang::get('lang.first_name'), 'email') !!} <span class="text-red"> *</span>
                               <!--  {!! html()->text('email', null)->class('form-control') !!} -->
                               <input type="text" name="first_name" id="first_name" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- full name -->
                            <div class="mb-3 {{ $errors->has('last_name') ? 'has-error' : '' }}">
                                {!! html()->label(Lang::get('lang.last_name'), 'fullname') !!} <span class="text-red"></span>
                                <input type="text" name="last_name" id="last_name" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-1 mb-3 {{ Session::has('country_code_error') ? 'has-error' : '' }}">
                            <div class="mb-3 {{ $errors->has('code') ? 'has-error' : '' }}">
                            {!! html()->label(Lang::get('lang.country-code'), 'code') !!}
                            @if ($email_mandatory->status == 0 || $settings->status == 1)
                                 <span class="text-red"> *</span>
                            @endif

                            {!! html()->text('code', null)->class('form-control')->id('country_code')->placeholder($phonecode)->attributes(['title' => Lang::get('lang.enter-country-phone-code')]) !!}
                            </div>
                        </div>
                        <div class="col-md-5">
                            <!-- phone -->
                            <div class="mb-3 {{ $errors->has('mobile') ? 'has-error' : '' }}">
                                <label>{!! Lang::get('lang.mobile_number') !!}:</label>
                                @if ($email_mandatory->status == 0 || $settings->status == 1)
                                 <span class="text-red"> *</span>
                                @endif
                                {!! html()->number('mobile', null)->class('form-control')->id('mobile') !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- phone -->
                            <div class="mb-3 {{ $errors->has('phone') ? 'has-error' : '' }}">
                                <label>{!! Lang::get('lang.phone') !!}:</label>
                                {!! html()->number('phone', null)->class('form-control')->id('phone_number') !!}
                                {!! $errors->first('phone', '<spam class="help-block text-red">:message</spam>') !!}
                            </div>
                        </div>
                        <!--  <div class="mb-3">
                             <div class="col-md-2">
                                 <label>Ticket Notice:</label>
                             </div>
                             <div class="col-md-6">
                                 <input type="checkbox" name="notice" id=""> Send alert to User
                             </div>
                         </div> -->
                    </div>
                </div>
            </div>
        </div>
    

        <div class="card card-light">
            
            <div class="card-header">

                <h3 class="card-title">{!! Lang::get('lang.ticket_option') !!}:</h3>
            </div>

            <div class="card-body">
                <!-- ticket options -->
                <div class="mb-3">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label>{!! Lang::get('lang.help_topic') !!}:</label>
                                <!-- helptopic -->
                                <?php $helptopic = App\Model\helpdesk\Manage\Help_topic::where('status', '=', 1)->select('topic', 'id')->get(); ?>
                                {!! html()->select('helptopic', ['Helptopic'=>$helptopic->pluck('topic','id')->toArray()], null)->class('form-control select')->id('selectid') !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <!-- sla plan -->
                            <div class="mb-3">
                                <label>{!! Lang::get('lang.sla_plan') !!}:</label>
                                <?php $sla_plan = App\Model\helpdesk\Manage\Sla_plan::where('status', '=', 1)->select('grace_period', 'id')->get(); ?>
                                {!! html()->select('sla', ['SLA'=>$sla_plan->pluck('grace_period','id')->toArray()], null)->class('form-control select') !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <!-- due date -->
                            <div class="mb-3" id="duedate">
                                <label>{!! Lang::get('lang.due_date') !!}:</label>
                                {!! html()->text('duedate', null)->class('form-control')->id('datemask') !!}
                                <button class="btn  clear-input" id="duedates" style="display: none" type="button"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <!-- assign to -->
                            <div class="mb-3">
                                <label>{!! Lang::get('lang.assign_to') !!}:</label>
                                <?php $agents = App\User::where('role', '!=', 'user')->where('active', '=', 1)->get(); ?>
                                {!! html()->select('assignto', [''=>'Select an Agent','Agents'=>$agents->pluck('first_name','id')->toArray()], null)->class('form-control select') !!}
                            </div>
                        </div>
                        <div id="response" class="col-md-6 mb-3"></div>
                    </div>
                    <div class="row">
                    {{-- Event fire --}}
                    <?php \Illuminate\Support\Facades\Event::dispatch(new App\Events\ClientTicketForm()); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-light">
            
            <div class="card-header">
                <h3 class="card-title">{!! Lang::get('lang.ticket_detail') !!}:</h3>
            </div>

            <div class="card-body">
                <!-- ticket details -->
                <div class="mb-3">
                    <!-- subject -->
                    <div class="mb-3 {{ $errors->has('subject') ? 'has-error' : '' }}">
                        <div class="row">
                            <div class="col-md-1">
                                <label>{!! Lang::get('lang.subject') !!}:<span class="text-red"> *</span></label>
                            </div>
                            <div class="col-md-11">
                                {!! html()->text('subject', null)->class('form-control') !!}
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 {{ $errors->has('body') ? 'has-error' : '' }}">
                        <!-- details -->
                        <div class="row">
                            <div class="col-md-1">
                                <label>{!! Lang::get('lang.detail') !!}:<span class="text-red"> *</span></label>
                            </div>
                            <div class="col-md-11">
                                {!! html()->textarea('body', null)->class('form-control')->id('body')->attributes(['style' => "width:100%; height:150px;"]) !!}

                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <!-- priority -->
                        <div class="row">
                            <div class="col-md-1">
                                <label>{!! Lang::get('lang.priority') !!}:</label>
                            </div>
                            <div class="col-md-5">
                                <?php $Priority = App\Model\helpdesk\Ticket\Ticket_Priority::where('status','=',1)->get(); ?>
                                {!! html()->select('priority', [Lang::get('lang.priorities')=>$Priority->pluck('priority_desc','priority_id')->toArray()], null)->class('form-control select') !!}
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer">
        <div class="row">
            <div class="col-md-3">
                <input type="submit" value="{!! Lang::get('lang.create_ticket') !!}" class="btn btn-primary" onclick="this.disabled=true;this.value='Sending, please wait...';this.form.submit();">
            </div>
        </div>
    </div>
</div><!-- /. box -->
{!! html()->closeModelForm() !!}
<script type="text/javascript">
    $(document).ready(function () {
        var helpTopic = $("#selectid").val();
        send(helpTopic);
        $("#selectid").on("change", function () {
            helpTopic = $("#selectid").val();
            send(helpTopic);
        });
        function send(helpTopic) {
            $.ajax({
                url: "{{url('/get-helptopic-form')}}",
                data: {'helptopic': helpTopic},
                type: "GET",
                dataType: "html",
                success: function (response) {
                    $("#response").html(response);
                },
                error: function (response) {
                    $("#response").html(response);
                }
            });
        }
    });
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

    $(document).ready(function () {
        $('#form').submit(function () {
            var duedate = document.getElementById('datemask').value;
            if (duedate) {
                var pattern = /^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/;
                if (pattern.test(duedate) === true) {
                    $('#duedate').removeClass("has-error");
                    $('#clear-up').remove();
                } else {
                    $('#duedate').addClass("has-error");
                    $('#clear-up').remove();
                    $('#box-header1').append("<div id='clear-up'><br><br><div class='alert alert-danger alert-dismissible'><i class='fa-solid fa-ban'></i><button type='button' class='btn-close' data-bs-dismiss='alert' aria-hidden='true'></button> Invalid Due date</div></div>");
                    return false;
                }
            }
        });
    });
                $(document).ready(function(){                   
                    $("#email").autocomplete({
                        source:"{!!URL::route('post.newticket.autofill')!!}",
                        minLength:1,
                        select:function(evt, ui) {
                            // this.form.phone_number.value = ui.item.phone_number;
                            // this.form.user_name.value = ui.item.user_name;
                            if(ui.item.first_name) {
                                this.form.first_name.value = ui.item.first_name;
                            }
                            if(ui.item.last_name) {
                                this.form.last_name.value = ui.item.last_name;
                            }
                            if(ui.item.country_code) {
                                this.form.country_code.value = ui.item.country_code;
                            }
                            if(ui.item.phone_number) {
                                this.form.phone_number.value = ui.item.phone_number;
                            }
                            if(ui.item.mobile) {
                                this.form.mobile.value = ui.item.mobile;
                            }
                        }
                    });
                });

   $(function () {
        var picker = $('#datemask').datetimepicker({
            format: 'DD/MM/YYYY',
        });
        picker.on('dp.change', function(e) {
            if (e.date) {
                $('.clear-input').removeClass('d-none');
            } else {
                $('.clear-input').addClass('d-none');
            }
        });

        $('.clear-input').click(function() {
            $('#datemask').val('');
            $('.clear-input').addClass('d-none');

        });
    });
</script>

@stop



