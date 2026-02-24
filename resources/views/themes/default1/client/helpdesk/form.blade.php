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
    <ol class="breadcrumb float-sm-right ">
        <style>
            .words {
                margin-right: 10px; /* Adjust the value to increase or decrease the gap between list items */
            }
        </style>
        <li class="breadcrumb-item"> <i class="fas fa-home"> </i> {!! Lang::get('lang.you_are_here') !!} : &nbsp;</li>
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

            <section id="section-categories" class="section">
        
                <h2 class="section-title h4 clearfix">

                    <i class="line"></i>{!! Lang::get('lang.have_a_ticket') !!}?
                </h2>

                @if(Session::has('check'))
                @if (count($errors) > 0)
                <div class="alert alert-danger alert-dismissable">
                    <i class="fa fa-ban"></i>
                    <b>{!! Lang::get('lang.alert') !!} !</b>
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </div>
                @endif
                @endif

                <div>
                     {!! html()->form('POST', url('checkmyticket'))->open() !!}
                    {!! html()->label(Lang::get('lang.email'), 'email') !!}<span class="text-red"> *</span>
                    {!! html()->text('email_address', null)->class('form-control form-group') !!}
                    {!! html()->label(Lang::get('lang.ticket_number'), 'ticket_number') !!}<span class="text-red"> *</span>
                    {!! html()->text('ticket_number', null)->class('form-control form-group') !!}
                    <button type="submit" class="btn btn-info" style=" border-color: rgb(0, 192, 239); background-color: rgb(0, 154, 186) !important; color: white">
                        <i class="fas fa-save"></i> {!! Lang::get('lang.check_ticket_status') !!}
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
        <div class="alert alert-success alert-dismissable">
            <i class="fas  fa-check-circle"></i>
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {!! Session::get('message') !!}
        </div>
        @endif
        @if (count($errors) > 0)
        @if(Session::has('check'))
        <?php goto a; ?>
        @endif
        @if(!Session::has('error'))
        <div class="alert alert-danger alert-dismissable">
            <i class="fas fa-ban"></i>
            <b>{!! Lang::get('lang.alert') !!} !</b>
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        <?php a: ?>
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

                        <div class="col-md-12 form-group {{ $errors->has('Name') ? 'has-error' : '' }}">
                            {!! html()->label(Lang::get('lang.name'), 'Name') !!}<span class="text-red"> *</span>
                            {!! html()->text('Name', null)->class('form-control') !!}
                        </div>
                        @endif

                        @if(Auth::user())

                        {!! html()->hidden('Email', Auth::user()->email)->class('form-control') !!}

                        @else
                        <div class="col-md-12 form-group {{ $errors->has('Email') ? 'has-error' : '' }}">
                            {!! html()->label(Lang::get('lang.email'), 'Email') !!}
                            @if($email_mandatory->status == 1 || $email_mandatory->status == '1')
                                <span class="text-red"> *</span>
                            @endif
                            {!! html()->email('Email', null)->class('form-control') !!}
                        </div>
                        @endif

                        @if(!Auth::user())

                        <div class="col-md-2 form-group {{ Session::has('country_code_error') ? 'has-error' : '' }}">
                            {!! html()->label(Lang::get('lang.country-code'), 'Code') !!}
                             @if($email_mandatory->status == 0 || $email_mandatory->status == '0')
                                    <span class="text-red"> *</span>
                                    @endif

                            {!! html()->text('Code', null)->class('form-control')->placeholder($phonecode)->attributes(['title' => Lang::get('lang.enter-country-phone-code')]) !!}
                        </div>
                        <div class="col-md-5 form-group {{ $errors->has('mobile') ? 'has-error' : '' }}">
                            {!! html()->label(Lang::get('lang.mobile_number'), 'mobile') !!}
                             @if($email_mandatory->status == 0 || $email_mandatory->status == '0')
                                    <span class="text-red"> *</span>
                                    @endif
                            {!! html()->text('mobile', null)->class('form-control') !!}
                        </div>
                        <div class="col-md-5 form-group {{ $errors->has('Phone') ? 'has-error' : '' }}">
                            {!! html()->label(Lang::get('lang.phone'), 'Phone') !!}
                            {!! html()->text('Phone', null)->class('form-control') !!}
                        </div>
                        @else
                            {!! html()->hidden('mobile', Auth::user()->mobile)->class('form-control') !!}
                            {!! html()->hidden('Code', Auth::user()->country_code)->class('form-control') !!}
                            {!! html()->hidden('Phone', Auth::user()->phone_number)->class('form-control') !!}

                       @endif
                        <div class="col-md-12 form-group {{ $errors->has('help_topic') ? 'has-error' : '' }}">
                            {!! html()->label(Lang::get('lang.choose_a_help_topic'), 'help_topic') !!}
                            {!! $errors->first('help_topic', '<spam class="help-block">:message</spam>') !!}
                            <?php
                            $forms = App\Model\helpdesk\Form\Forms::get();
                            $helptopic = App\Model\helpdesk\Manage\Help_topic::where('status', '=', 1)->get();
//                            ?><!---->
                            <select name="helptopic" class="form-control" id="selectid">

                                @foreach($helptopic as $topic)
                                <option value="{!! $topic->id !!}">{!! $topic->topic !!}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- priority -->
                         <?php
                         $Priority = App\Model\helpdesk\Settings\CommonSettings::select('status')->where('option_name','=', 'user_priority')->first();
                         $user_Priority=$Priority->status;
                        ?>

                         @if(Auth::user())

                         @if(Auth::user()->active == 1)
                        @if($user_Priority == 1)

                        <div class="col-md-12 form-group">
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
                        <div class="col-md-12 form-group {{ $errors->has('Subject') ? 'has-error' : '' }}">
                            {!! html()->label(Lang::get('lang.subject'), 'Subject') !!}<span class="text-red"> *</span>
                            {!! html()->text('Subject', null)->class('form-control') !!}
                        </div>
                        <div class="col-md-12 form-group {{ $errors->has('Details') ? 'has-error' : '' }}">
                            {!! html()->label(Lang::get('lang.message'), 'Details') !!}<span class="text-red"> *</span>
                            {!! html()->textarea('Details', null)->class('form-control') !!}
                        </div>
                        <div class="col-md-12 form-group">
                            <input type="file" name="attachment[]" multiple/><br/>
                            {!! Lang::get('lang.max') !!}. {!! $max_size_in_actual !!}
                        </div>
                        {{-- Event fire --}}
                        <?php \Illuminate\Support\Facades\Event::dispatch(new App\Events\ClientTicketForm()); ?>
                        <div class="col-md-12" id="response"> </div>
                        <div id="ss" class="xs-md-6 form-group {{ $errors->has('') ? 'has-error' : '' }}"> </div>
                                <div class="col-md-12 form-group">
                                    {!! html()->button('<i class="fas fa-save"></i> ' . Lang::get('lang.submit'))->class('btn btn-info float-right')->attribute('data-v-fce8d630')->attributes(['type' => 'submit', 'style' => 'style="border-color: rgb(0, 192, 239); background-color: rgb(0, 154, 186); color: white;', 'onclick' => 'this.disabled=true;this.innerHTML="Sending, please wait...";this.form.submit();']) !!}
                                </div>
                            <div class="col-md-12" id="response"> </div>
                        <div id="ss" class="xs-md-6 form-group {{ $errors->has('') ? 'has-error' : '' }}"> </div>

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