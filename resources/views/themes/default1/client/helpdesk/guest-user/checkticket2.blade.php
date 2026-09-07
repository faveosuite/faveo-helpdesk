@extends('themes.default1.client.layout.client')
<?php $user = App\User::where('id','=',$tickets->user_id)->first();?>

@section('nav1')
class="active"
@stop

@section('My')
class="active"
@stop

@section('sidebar')
<li class="header">TICKET INFORMATION</li>
<li>
    <a href="">
    <span>TICKET ID</span> 
    </br><b>#{{$tickets->ticket_number}}</b>
    </a>
</li>
<li>
    <a href="">
    <span>USER</span> 
    </br><i class="fa-solid fa-user"> </i> <b>{{$user->email}}</b>
    </a>
</li>
<li>
    <a href="">
    @if($tickets->assigned_to > 0)
        <span>ASSIGNED TO</span> 
        </br> <b>{{$tickets ->assigned_to}}</b>
    @else
        <span>UNASSIGNED</span> 
    @endif
    </a>
</li>
@stop


@section('content')

 <!-- Main content -->
               

                    <!-- Main content -->
                    <div class="box box-primary">
                        <div class="box-header">

                            <section class="app-content-header"><h3 class="box-title"><i class="fa-solid fa-user"> </i> {{$thread->title}} </h3> ( {{$tickets->ticket_number}} )
                            </section>
                            <div class="pull-right">
                                <!-- <button type="button" class="btn btn-secondary"><i class="fa-solid fa-pen-to-square text-success"> </i> Edit</button> -->

                                <button type="button" class="btn btn-secondary">
                                    <i class="fa-solid fa-print text-primary"></i>
                                    <a href="{{ route('ticket.print', [$tickets->id]) }}">Print</a>
                                </button>
                      
                                <!-- </div> -->
                                <div class="btn-group"> 
                                    <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown"><i class="fa-solid fa-exchange text-teal"> </i>
                                        Change Status <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                    
                                        <li><a href="#" id="open"><i class="fa-solid fa-folder-open-o" style="color:yellow;"> </i>Open</a></li>
                                    
                                        <li><a href="#" id="close"><i class="fa-solid fa-check text-success"> </i>Close</a></li>
                                    
                                        <li><a href="#" id="resolved"><i class="fa-solid fa-circle-check-o  text-success"> </i> Resolved</a></li>
                                    </ul>
                                </div>
                                
                                {!! html()->closeModelForm() !!}
                            </div>
                        </div>

                        <div class="box-body">
                            <div class="row">
                                <section class="app-content"  id="refresh">
                                    <div class="col-md-12"> 
                                        <?php 
                                        $priority = App\Model\Ticket\Ticket_Priority::where('priority_id','=',$tickets->priority_id)->first();
                                        ?>
                                        <div class="callout callout-{!! $priority->priority_color !!}">
                                            <div class="row">
                                                <div class="col-md-3"> 
                                                <?php
                                                $sla = $tickets->sla;
                                                $SlaPlan = App\Model\Manage\Sla_plan::where('id','=',1)->first();?>
                                                    <b>SLA Plan: {{$SlaPlan->grace_period}} </b> 
                                                </div>
                                                <div class="col-md-3"> 
                                                    <b>Created Date: </b> {{date_format($tickets->created_at, 'd/m/Y H:i:s')}}
                                                </div>
                                                <div class="col-md-3"> 
                                                    <b>Due Date: </b> 
                                                    <?php 
                                                    $time = $tickets->created_at;
                                                    $time = date_create($time);
                                                    date_add($time, date_interval_create_from_date_string($SlaPlan->grace_period));
                                                    echo date_format($time, 'd/m/Y H:i:s');
                                                    ?>
                                                </div>
                                                <div class="col-md-3">
                                                <?php $response = App\Model\Ticket\Ticket_Thread::where('ticket_id','=',$tickets->id)->get();?>
                                                @foreach($response as $last)
                                                <?php $ResponseDate  = $last->created_at; ?>
                                                @endforeach
                                                    <b>Last Response: </b> {{date_format($ResponseDate, 'd/m/Y H:i:s')}} 
                                                </div>
                                            </div>
                                        </div>
                                    </div>      
                                    <div class="col-md-6"> 
                                        <table class="table table-hover">
                                            <!-- <tr><th></th><th></th></tr> -->
                                            <tr><td><b>Status:</b></td>       <?php $status = App\Model\Ticket\Ticket_Status::where('id','=',$tickets->status)->first();?><td title="{{$status->properties}}">{{$status->state}}</td></tr>
                                            <tr><td><b>Priority:</b></td>     <?php $priority = App\Model\Ticket\Ticket_Priority::where('priority_id','=',$tickets->priority_id)->first();?><td title="{{$priority->priority_desc}}">{{$priority->priority}}</td></tr>
                                            <tr><td><b>Department:</b></td>   <?php $help_topic = App\Model\Manage\Help_topic::where('id','=',$tickets->help_topic_id)->first();?><td title="{{$help_topic->topic}}">{{$help_topic->department}}</td></tr>                                            
                                            
                                        </table>
                                        <!-- </div> -->
                                    </div>
                                    <div class="col-md-6"> 
                                        <!-- <div class="callout callout-success"> -->
                                        <table class="table table-hover">
                                            <!-- <tr><th></th><th></th></tr> -->
                                            <tr><td><b>Help Topic:</b></td>     <?php $help_topic = App\Model\Manage\Help_topic::where('id','=',$tickets->help_topic_id)->first();?><td title="{{$help_topic->topic}}">{{$help_topic->topic}}</td></tr>
                                            <tr><td><b>Last Message:</b></td>   <td>{{$last->poster}}</td></tr>
                                        </table>
                                    </div>
                                    <!-- </div> -->
                                </section> 
                            </div>
                        </div>

                    </div>



                    <div class='row'>
                        <div class='col-12'>
                            <div class="nav-tabs-custom">
                                <ul class="nav nav-tabs">
                                    <li class="active"><a href="#General" data-bs-toggle="tab" style="color:green;"><i class="fa-solid fa-reply-all"> </i> Reply</a></li>
                                    
                                </ul>
                                <div class="tab-content">

                                    <div class="tab-pane active" id="General">
                                        <div id="t1">
                                            {!! html()->form('POST', route('ticket.reply'))->open() !!}
                                        <div class="mb-3">

                                        </div>
                                            <div class="mb-3">
                                                <div class="row">
                                                <input type="hidden" name="ticket_ID" value="{{$tickets->id}}">
                                                    <div class="mb-3 {{ $errors->has('title') ? 'has-error' : '' }}">
                                                        <div class="col-md-2">
                                                            {!! html()->label('To:', 'To') !!}
                                                        </div>
                                                        <div class="col-md-10">
                                                            {!! html()->text('To', $user->email)->class('form-control')->attributes(['style' => 'width:55%']) !!}
                                                            {!! $errors->first('To', '<spam class="help-block text-red">:message</spam>') !!}
                                                        </div>
                                                    </div>
                                                </div>          
                                            </div>
                                            
                                            <div class="mb-3">
                                                <div class="row">
                                                    <div class="mb-3 {{ $errors->has('title') ? 'has-error' : '' }}">
                                                        <div class="col-md-2">
                                                            {!! html()->label('reply_content:', 'Reply Content') !!}
                                                        </div>
                                                        <div class="col-md-10">
                                                            <textarea name="reply_content"></textarea> 
                                                            {!! $errors->first('reply_content', '<spam class="help-block text-red">:message</spam>') !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="row">
                                                    <div class="mb-3 {{ $errors->has('title') ? 'has-error' : '' }}">
                                                        <div class="col-md-2">
                                                        
                                                        </div>
                                                        <div class="col-md-10">
                                                            <button  type="submit" class="btn btn-primary"><i class="fa-solid fa-check-square-o text-primary"> </i> Update</button>            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                        {!! html()->closeModelForm() !!}
                                        </div>

                                        <div id="t2" style="display:none">
                                            {!! html()->form('POST', route('ticket.reply'))->open() !!}
                                            <div class="mb-3">
                                                <button type="submit" id="tt1" class="btn btn-secondary"><i class="fa-solid fa-check-square-o text-success"> </i> Update</button>
                                                <button style="display:none;" type="submit" id="tt2" class="btn btn-secondary"><i class="fa-solid fa-check-square-o text-primary"> </i> Update</button>
                                                <button type="button" class="btn btn-secondary">
                                                    <i class="fa-solid fa-hand-o-right text-orange"></i>
                                                    <a href="{{ route('assign.ticket') }}">Assign</a>
                                                </button>
                                                <button type="button" id="internal" class="btn btn-secondary"><i class="fa-solid fa-file-text text-primary"> </i>  Internal Notes</button>
                                                <button type="button" class="btn btn-secondary"><i class="fa-solid fa-arrows-alt text-danger"> </i>  Surrender</button>
                                            </div>
                                            <div class="mb-3">
                                                <div class="row">
                                                <input type="hidden" name="ticket_ID" value="{{$tickets->id}}">
                                                    <div class="mb-3 {{ $errors->has('title') ? 'has-error' : '' }}">
                                                        <div class="col-md-2">
                                                            <label>Subject</label>
                                                        </div>
                                                        <div class="col-md-10">
                                                            {!! html()->text('To', $user->email)->class('form-control')->attributes(['style' => 'width:55%']) !!}
                                                            {!! $errors->first('To', '<spam class="help-block text-red">:message</spam>') !!}
                                                        </div>
                                                    </div>
                                                </div>          
                                            </div>
                                            <div class="mb-3">
                                                <div class="row">
                                                    <div class="mb-3 {{ $errors->has('title') ? 'has-error' : '' }}">
                                                        <div class="col-md-2">
                                                            <label>Message</label>
                                                        </div>
                                                        <div class="col-md-10">
                                                            <textarea name="reply_content"></textarea> 
                                                            {!! $errors->first('reply_content', '<spam class="help-block text-red">:message</spam>') !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {!! html()->closeModelForm() !!}
                                        </div>

                                    </div>
                                    <div class="tab-pane" id="Reply" >
                                        <div class="mb-3">
                                            <button type="button" class="btn btn-secondary"><i class="fa-solid fa-mail-forward text-success"> </i> Send</button>
                                            <button type="button" class="btn btn-secondary"><i class="fa-solid fa-th-large text-teal"> </i> Option</button>
                                            <button type="button" class="btn btn-secondary"><i class="fa-solid fa-file-text text-primary"> </i> Internal Notes</button>
                                        </div>
                                        <form>
                                            <div class="mb-3">
                                                <div class="row">
                                                    <div class="col-md-2">
                                                        <label>From</label>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" class="form-control" name="from" id="from" style="width:40%" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="row">
                                                    <div class="col-md-2">
                                                        <label>To</label>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" class="form-control" name="to" id="to" style="width:55%" />
                                                    </div>
                                                </div>      
                                            </div>
                                            <div class="mb-3">
                                                <div class="row">
                                                    <div class="col-md-2">
                                                        <label>Subject</label>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <input type="text" class="form-control" name="from" id="from" style="width:100%" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="row">
                                                    <div class="col-md-2">
                                                        <label>Response</label>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <select class="form-control" style="width:55%" >
                                                            <option>Select a canned response</option>
                                                            <option>Original Message</option>
                                                            <option>Last Message</option>
                                                        </select>
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="mb-3">
                                                <div class="row">
                                                    <div class="col-md-2">
                                                        <label>Reply Content</label>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <textarea id="txtEditor2"> </textarea>    
                                                    </div>
                                                </div>
                                            </div>
                                        </form>               
                                    </div>
                                </div>

                            </div>
                            <!-- row -->
                            <div class="row">
                                <div class="col-md-12">
                                    <!-- The time line -->
                                    <ul class="timeline">
                                        <!-- timeline time label -->
                                        <?php $conversations = App\Model\Ticket\Ticket_Thread::where('ticket_id','=',$tickets->id)->paginate(2);
                                        foreach ($conversations as $conversation) { 
                                        ?>
                                            <li class="time-label">
                                                    <?php 
                                                    $ConvDate1 = $conversation->created_at;
                                                    $ConvDate = explode(' ',$ConvDate1);

                                                    $date = $ConvDate[0];
                                                    $time = $ConvDate[1];
                                                    $time = substr($time, 0, -3);
                                                    if(isset($data) && $date==$data){ 
                                                    } else {
                                                        ?> <span class="text-bg-success">
                                                        {{date_format($conversation->created_at, 'd/m/Y')}}
                                                        </span> <?php
                                                        $data = $ConvDate[0];
                                                    }
                                                    ?>
                                            </li>
                                            <li>
                                            <?php if($conversation->staff_id > 0) { ?>
                                                <i class="fa-solid fa-group text-bg-warning" title="<?= Lang::get('lang.posted_by_support_team') ?>"></i>
                                            <?php } elseif($conversation->user_id > 0) { ?>   
                                                <i class="fa-solid fa-user text-bg-info" title="<?= Lang::get('lang.posted_by_customer') ?>"></i>
                                            <?php } else { ?>   
                                                <i class="fa-solid fa-mail-reply-all text-bg-purple" title="<?= Lang::get('lang.posted_by_system') ?>"></i>
                                            <?php } ?>
                                                <div class="timeline-item">
                                                    <span id="date" class="time"><i class="fa-solid fa-clock-o"> </i> {{date_format($conversation->created_at, 'd/m/Y H:i:s')}}</span>
                                                    <h3 class="timeline-header"><a href="#">{{$conversation->poster}}</a></h3>
                                                    <div class="timeline-body">
                                                    {!! $conversation->body !!}
                                                    </div>
                                                </div>
                                            </li>
                                                    <?php  $lastid = $conversation->id ?>
                                        <?php  } ?>
                                        <li>
                                            <i class="fa-solid fa-clock-o text-bg-secondary"></i>
                                        </li>
                                        <ul class="pull-right">
                                            <?php echo $conversations->setPath( url('/thread/'.'1'))->render(); ?>
                                        </ul>
                                    </ul>
                                </div><!-- /.col -->
                            </div><!-- /.row -->
                        </div>
                    </div>
             


<script type="text/javascript">

jQuery(document).ready(function($) {
    $('#close').on('click', function (e) {
        $.ajax({
            type        :   "GET",
            url         :   "http://localhost/faveo/public/ticket/close/{{$tickets->id}}",
            success : function(response) {
                    $( "#refresh" ).load( "http://localhost/faveo/public/thread/{{$tickets->id}}   #refresh");
            }
        })
        return false;
    });

    $('#resolved').on('click', function (e) {
        $.ajax({
            type        :   "GET",
            url         :   "http://localhost/faveo/public/ticket/resolve/{{$tickets->id}}",
            success : function(response) {
                    $( "#refresh" ).load( "http://localhost/faveo/public/thread/{{$tickets->id}}  #refresh");
            }
        })
        return false;
    });

    $('#open').on('click', function (e) {
        $.ajax({
            type        :   "GET",
            url         :   "http://localhost/faveo/public/ticket/open/{{$tickets->id}}",
            success : function(response) {
                    $( "#refresh" ).load( "http://localhost/faveo/public/thread/{{$tickets->id}}   #refresh");
                $('#refresh').load('thread/2 #refresh');
            }
        })
        return false;
    });


});







</script>
@stop

                
                    
                

               