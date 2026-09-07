@extends('themes.default1.client.layout.client')

@section('title')
{!! Lang::get('lang.submit_a_ticket') !!} -
@stop

@section('submit')
class = "nav-item active"
@stop
<!-- breadcrumbs -->
@section('breadcrumb')
{{--<div class="site-hero clearfix">--}}
    <ol class="breadcrumb float-sm-end">
        <style>
            .words {
                margin-right: 10px; /* Adjust the value to increase or decrease the gap between list items */
            }
        </style>
        <li class="breadcrumb-item"> <i class="fa-solid fa-home"> </i> {!! Lang::get('lang.you_are_here') !!} : &nbsp;</li>
        <li><a class="words" href="{{url('/')}}">{!! Lang::get('lang.home') !!}</a></li>
        <li class="words" style="margin-right: 10px">></li>

        <li><a href="{!! URL::route('form') !!}">{!! Lang::get('lang.submit_a_ticket') !!}</a></li>
    </ol>
</div>
@stop
<!-- /breadcrumbs -->
@section('check')

    <div id="sidebar" class="site-sidebar col-md-3">

        <div id="form-border" class="comment-respond form-border" style="background : #fff">

            <section id="section-categories" class="section check-my-ticket-section">

                <h2 class="section-title h4 clearfix">

                    <i class="line"></i>{!! Lang::get('lang.have_a_ticket') !!}?
                </h2>

                <div>
                     {!! html()->form('POST', url('checkmyticket'))->open() !!}
                    {!! html()->label(Lang::get('lang.email'), 'email') !!}<span class="text-red"> *</span>
                    {!! html()->text('email_address', null)->class('form-control' . (Session::has('check') && $errors->has('email_address') ? ' is-invalid' : '') . ' mb-1') !!}
                    @if(Session::has('check') && $errors->has('email_address'))
                        <div class="invalid-feedback d-block mb-2">{{ $errors->first('email_address') }}</div>
                    @else
                        <div class="mb-2"></div>
                    @endif
                    {!! html()->label(Lang::get('lang.ticket_number'), 'ticket_number') !!}<span class="text-red"> *</span>
                    {!! html()->text('ticket_number', null)->class('form-control' . (Session::has('check') && $errors->has('ticket_number') ? ' is-invalid' : '') . ' mb-1') !!}
                    @if(Session::has('check') && $errors->has('ticket_number'))
                        <div class="invalid-feedback d-block mb-2">{{ $errors->first('ticket_number') }}</div>
                    @else
                        <div class="mb-2"></div>
                    @endif
                    <button type="submit" class="btn btn-primary float-start">
                        <i class="fa-solid fa-floppy-disk"></i> {!! Lang::get('lang.check_ticket_status') !!}
                    </button>
                    {!! html()->closeModelForm() !!}
                </div>
            </section>
        </div>
    </div><!-- #sidebar -->
@stop
<!-- content -->
@section('content')

    <div id="content" class="site-content col-md-9">

        @if(Session::has('message'))
        <div class="alert alert-success alert-dismissible">
            <i class="fa-solid fa-circle-check"></i>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
            {!! Session::get('message') !!}
        </div>
        @endif

        <?php
        $encrypter = app('Illuminate\Encryption\Encrypter');
        $encrypted_token = $encrypter->encrypt(csrf_token());
        ?>
        <input id="token" type="hidden" value="{{$encrypted_token}}">
        {!! html()->form('POST', route('client.form.post'))->acceptsFiles()->open() !!}

        <article class="hentry">

            <div id="form-border" class="comment-respond form-border" style="background : #fff">

                <section id="section-categories">

                    <h2 class="section-title h4 clearfix mb-0">

                        <i class="line" style="border-color: rgb(0, 154, 186);"></i>{!! Lang::get('lang.submit_a_ticket') !!}
                    </h2>

                    <div class="row mt-4">

                        @if(Auth::user())

                        {!! html()->hidden('Name', Auth::user()->user_name)->class('form-control') !!}

                        @else

                        <div class="col-md-12 mb-3">
                            {!! html()->label(Lang::get('lang.name'), 'Name') !!}<span class="text-red"> *</span>
                            {!! html()->text('Name', null)->class('form-control' . ($errors->has('Name') ? ' is-invalid' : '')) !!}
                            @if($errors->has('Name'))
                                <div class="invalid-feedback d-block">{{ $errors->first('Name') }}</div>
                            @endif
                        </div>
                        @endif

                        @if(Auth::user())

                        {!! html()->hidden('Email', Auth::user()->email)->class('form-control') !!}

                        @else
                        <div class="col-md-12 mb-3">
                            {!! html()->label(Lang::get('lang.email'), 'Email') !!}
                            @if($email_mandatory->status == 1 || $email_mandatory->status == '1')
                                <span class="text-red"> *</span>
                            @endif
                            {!! html()->email('Email', null)->class('form-control' . ($errors->has('Email') ? ' is-invalid' : '')) !!}
                            @if($errors->has('Email'))
                                <div class="invalid-feedback d-block">{{ $errors->first('Email') }}</div>
                            @endif
                        </div>
                        @endif

                        @if(!Auth::user())

                        <div class="col-md-2 mb-3">
                            {!! html()->label(Lang::get('lang.country-code'), 'Code') !!}
                            @if($email_mandatory->status == 0 || $email_mandatory->status == '0')
                                <span class="text-red"> *</span>
                            @endif
                            {!! html()->text('Code', null)->class('form-control' . (Session::has('country_code_error') ? ' is-invalid' : ''))->placeholder($phonecode)->attributes(['title' => Lang::get('lang.enter-country-phone-code')]) !!}
                            @if(Session::has('country_code_error'))
                                <div class="invalid-feedback d-block">{{ Session::get('country_code_error') }}</div>
                            @endif
                        </div>
                        <div class="col-md-5 mb-3">
                            {!! html()->label(Lang::get('lang.mobile_number'), 'mobile') !!}
                            @if($email_mandatory->status == 0 || $email_mandatory->status == '0')
                                <span class="text-red"> *</span>
                            @endif
                            {!! html()->text('mobile', null)->class('form-control' . ($errors->has('mobile') ? ' is-invalid' : '')) !!}
                            @if($errors->has('mobile'))
                                <div class="invalid-feedback d-block">{{ $errors->first('mobile') }}</div>
                            @endif
                        </div>
                        <div class="col-md-5 mb-3">
                            {!! html()->label(Lang::get('lang.phone'), 'Phone') !!}
                            {!! html()->text('Phone', null)->class('form-control' . ($errors->has('Phone') ? ' is-invalid' : '')) !!}
                            @if($errors->has('Phone'))
                                <div class="invalid-feedback d-block">{{ $errors->first('Phone') }}</div>
                            @endif
                        </div>
                        @else
                            {!! html()->hidden('mobile', Auth::user()->mobile)->class('form-control') !!}
                            {!! html()->hidden('Code', Auth::user()->country_code)->class('form-control') !!}
                            {!! html()->hidden('Phone', Auth::user()->phone_number)->class('form-control') !!}

                       @endif
                        <div class="col-md-12 mb-3">
                            {!! html()->label(Lang::get('lang.choose_a_help_topic'), 'help_topic') !!}
                            <?php
                            $forms = App\Model\helpdesk\Form\Forms::get();
                            $helptopic = App\Model\helpdesk\Manage\Help_topic::where('status', '=', 1)->get();
//                            ?><!---->
                            <select name="helptopic" class="form-control{{ $errors->has('help_topic') ? ' is-invalid' : '' }}" id="selectid">

                                @foreach($helptopic as $topic)
                                <option value="{!! $topic->id !!}">{!! $topic->topic !!}</option>
                                @endforeach
                            </select>
                            @if($errors->has('help_topic'))
                                <div class="invalid-feedback d-block">{{ $errors->first('help_topic') }}</div>
                            @endif
                        </div>
                        <!-- priority -->
                         <?php
                         $Priority = App\Model\helpdesk\Settings\CommonSettings::select('status')->where('option_name','=', 'user_priority')->first();
                         $user_Priority=$Priority->status;
                        ?>

                         @if(Auth::user())

                         @if(Auth::user()->active == 1)
                        @if($user_Priority == 1)

                        <div class="col-md-12 mb-3">
                            <div class="row">
                                <div class="col-md-1">
                                    <label>{!! Lang::get('lang.priority') !!}:</label>
                                </div>
                                <div class="col-md-12">
                                    <?php $Priority = App\Model\helpdesk\Ticket\Ticket_Priority::where('status','=',1)->get(); ?>
                                    {!! html()->select('priority', ['Priority'=>$Priority->pluck('priority_desc','priority_id')->toArray()], null)->class('form-control select') !!}
                                </div>
                             </div>
                        </div>
                        @endif
                        @endif
                        @endif
                        <div class="col-md-12 mb-3">
                            {!! html()->label(Lang::get('lang.subject'), 'Subject') !!}<span class="text-red"> *</span>
                            {!! html()->text('Subject', null)->class('form-control' . ($errors->has('Subject') ? ' is-invalid' : '')) !!}
                            @if($errors->has('Subject'))
                                <div class="invalid-feedback d-block">{{ $errors->first('Subject') }}</div>
                            @endif
                        </div>
                        <div class="col-md-12 mb-3">
                            {!! html()->label(Lang::get('lang.message'), 'Details') !!}<span class="text-red"> *</span>
                            {!! html()->textarea('Details', null)->class('form-control' . ($errors->has('Details') ? ' is-invalid' : '')) !!}
                            @if($errors->has('Details'))
                                <div class="invalid-feedback d-block">{{ $errors->first('Details') }}</div>
                            @endif
                        </div>
                        <div class="col-md-12 mb-3">
                            <input type="file" name="attachment[]" multiple/><br/>
                            {!! Lang::get('lang.max') !!}. {!! $max_size_in_actual !!}
                        </div>
                        {{-- Event fire --}}
                        <?php \Illuminate\Support\Facades\Event::dispatch(new App\Events\ClientTicketForm()); ?>
                        <div class="col-md-12" id="response"> </div>
                        <div id="ss" class="xs-md-6 mb-3 {{ $errors->has('') ? 'has-error' : '' }}"> </div>
                                <div class="col-md-12 mb-3">
                                    {!! html()->button('<i class="fa-solid fa-floppy-disk"></i> ' . Lang::get('lang.submit'))->class('btn btn-primary float-end')->attribute('data-v-fce8d630')->attributes(['type' => 'submit', 'onclick' => 'this.disabled=true;this.innerHTML="Sending, please wait...";this.form.submit();']) !!}
                                </div>
                            <div class="col-md-12" id="response"> </div>
                        <div id="ss" class="xs-md-6 mb-3 {{ $errors->has('') ? 'has-error' : '' }}"> </div>

                    {!! html()->closeModelForm() !!}
                    </div>
                </section>
            </div>
        </article>
    </div>
<!--
|====================================================
| SELECTED FORM STORED IN SCRIPT
|====================================================
-->
<script type="text/javascript">
$(document).ready(function(){
   var helpTopic = $("#selectid").val();
   send(helpTopic);
   $("#selectid").on("change",function(){
       helpTopic = $("#selectid").val();
       send(helpTopic);
   });
   function send(helpTopic){
       $.ajax({
           url:"{{url('/get-helptopic-form')}}",
           data:{'helptopic':helpTopic},
           type:"GET",
           dataType:"html",
           success:function(response){
               $("#response").html(response);
           },
           error:function(response){
              $("#response").html(response);
           }
       });
   }
});

$(function() {
//Add text editor
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