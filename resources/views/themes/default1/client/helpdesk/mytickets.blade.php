@extends('themes.default1.client.layout.client')

@section('title')
My Tickets -
@stop

@section('myticket')
class="nav-item active"
@stop
@section('breadcrumb')
    {{--<div class="site-hero clearfix">--}}
    <ol class="breadcrumb float-sm-right ">
        <li class="breadcrumb-item"> <i class="fa-solid fa-home"> </i> {!! Lang::get('lang.you_are_here') !!} : &nbsp;</li>
        <li><a href="{!! URL::route('ticket') !!}">{!! Lang::get('lang.my_tickets') !!}</a></li>
    </ol>

@stop
@section('content')
<style type="text/css">
    .table th {
            border-top: none !important;
    }
</style>
<!-- Main content -->
<div id="content" class="site-content col-md-12">
    <?php
    $open = App\Model\helpdesk\Ticket\Tickets::where('user_id', '=', Auth::user()->id)
            ->where('status', '=', 1)
            ->orderBy('id', 'DESC')
            ->paginate(10);
    ?>
    <?php
    $close = App\Model\helpdesk\Ticket\Tickets::where('user_id', '=', Auth::user()->id)
            ->whereIn('status', [2, 3])
            ->orderBy('id', 'DESC')
            ->paginate(10);
    ?>
    <div class="nav-tabs-custom">

        <ul class="nav nav-tabs">
                    
            <li class="nav-item">

                <a style="cursor: pointer;" class="nav-link text-dark active" id="tab_1-tab" data-bs-toggle="pill" href="#tab_1">
                
                    <b>{!! Lang::get('lang.opened') !!}</b>
                
                    <span class="badge bg-pill" style="background: #337ab7; color: white;">{!! $open->total() !!}</span>
                
                </a>
            </li>

            <li class="nav-item">

                <a class="nav-link" id="tab_2-tab" data-bs-toggle="pill" href="#tab_2" style="color: #343a40!important">
                
                    <b>{!! Lang::get('lang.closed') !!}</b>
                
                    <span class="badge bg-pill" style="background: #337ab7; color: white;">{!! $close->total() !!}</span>
                
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane active" id="tab_1">
                {!! html()->form('POST', route('select_all'))->open() !!}
                <div class="mailbox-controls mt-3">
                    <!-- Check all button -->
                    <a class="btn btn-light btn-sm checkbox-toggle" style="background-color: whitesmoke"><i class="fa-regular fa-square"></i></a>
                    <a class="btn btn-light btn-sm" id="click1" style="background-color: whitesmoke"><i class="fa-solid fa-arrows-rotate"></i></a>
                    @if($user_ticket_status && $user_ticket_status->status == 1)
                    <input type="submit" class="btn btn-light text-warning btn-sm" name="submit" value="{!! Lang::get('lang.close') !!}"style="color: #F39C12;background-color: whitesmoke">
                    @endif
                    <div class="float-end" id="refresh21">
                        {!! $open->count().'-'.$open->total(); !!}
                    </div>
                </div>
                <div class=" table-responsive mailbox-messages"  id="refresh1">
                    <p style="display:none;text-align:center;" id="show1" class="text-red"><b>Loading...</b></p>
                    <!-- table -->
                    <table class="table table-hover table-striped">
                        <thead>
                        <th></th>
                        <th>
                            {!! Lang::get('lang.subject') !!}
                        </th>
                        <th>
                            {!! Lang::get('lang.ticket_id') !!}
                        </th>
                        <th>
                            {!! Lang::get('lang.priority') !!}
                        </th>
                        <th>
                            {!! Lang::get('lang.last_replier') !!}
                        </th>
                        <th>
                            {!! Lang::get('lang.last_activity') !!}
                        </th>
                        <th>
                            {!! Lang::get('lang.status') !!}
                        </th>
                        </thead>
                        <tbody id="hello">
                            @foreach ($open  as $ticket )
                            <tr <?php if ($ticket->seen_by == null) { ?> style="color:green;" <?php }
    ?> >
                                <td><input type="checkbox" class="icheckbox_flat-blue" name="select_all[]" value="{{$ticket->id}}"/></td>
                                <?php
                                $title = App\Model\helpdesk\Ticket\Ticket_Thread::where('ticket_id', '=', $ticket->id)->orderBy('id')->first();
                                $string = strip_tags($title->title);
                                if (strlen($string) > 40) {
                                    $stringCut = substr($string, 0, 25);
                                    $string = $stringCut.'....';
                                }
                                $TicketData = App\Model\helpdesk\Ticket\Ticket_Thread::where('ticket_id', '=', $ticket->id)
                                    ->where('user_id', '!=' , null)
                                    ->max('id');
                                $TicketDatarow = App\Model\helpdesk\Ticket\Ticket_Thread::where('id', '=', $TicketData)->first();
                                $LastResponse = App\User::where('id', '=', $TicketDatarow->user_id)->first();
                                if ($LastResponse->role == "user") {
                                    $rep = "#F39C12";
                                    $username = $LastResponse->user_name;
                                } else {
                                    $rep = "#000";
                                    $username = $LastResponse->first_name . " " . $LastResponse->last_name;
                                    if ($LastResponse->first_name == null || $LastResponse->last_name == null) {
                                        $username = $LastResponse->user_name;
                                    }
                                }
                                $titles = App\Model\helpdesk\Ticket\Ticket_Thread::where('ticket_id', '=', $ticket->id)->where('is_internal', '=', 0)->get();
                                $count = count($titles);
                                foreach ($titles as $title) {
                                    $title = $title;
                                }
                                ?>
                                <td class="mailbox-name"><a href="{!! URL('check_ticket',[Crypt::encrypt($ticket->id)]) !!}" title="{!! $title->title !!}">{{$string}}   </a> ({!! $count!!}) <i class="fa-solid fa-comment"></i></td>
                                <td class="mailbox-Id">#{!! $ticket->ticket_number !!}</td>
                                <?php $priority = App\Model\helpdesk\Ticket\Ticket_Priority::where('priority_id', '=', $ticket->priority_id)->first(); ?>
                                <td class="mailbox-priority"><span>{{$priority->priority}}</span></td>

                        <td class="mailbox-last-reply" style="color: {!! $rep !!}">{!! $username !!}</td>
                        <?php $updated = $title->updated_at ?>
                        <td class="mailbox-last-activity">{!! UTC::usertimezone($updated) !!}</td>
                        <?php $status = App\Model\helpdesk\Ticket\Ticket_Status::where('id', '=', $ticket->status)->first(); ?>
                        <td class="mailbox-date">{!! $status->name !!}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table><!-- /.table -->
                    <div class="float-end">
                        <?php echo $open->setPath(url('mytickets'))->render(); ?>&nbsp;
                    </div>
                </div><!-- /.mail-box-messages -->
                {!! html()->closeModelForm() !!}
            </div><!-- /.box-body -->
            {{-- /.tab_1 --}}
            <div class="tab-pane" id="tab_2">
                {!! html()->form('POST', route('select_all'))->open() !!}
                <div class="mailbox-controls mt-3">
                    <!-- Check all button -->
                    <a class="btn btn-light btn-sm checkbox-toggle" style="background-color: whitesmoke"><i class="fa-regular fa-square" ></i></a>
                    <a class="btn btn-light btn-sm" id="click2" style="background-color: whitesmoke"><i class="fa-solid fa-arrows-rotate"></i></a>
                    <input type="submit" class="btn btn-light text-primary btn-sm" name="submit" value="{!! Lang::get('lang.open') !!}" style="background-color: whitesmoke">
                    <div class="float-end" id="refresh22">
                        {!! $close->count().'-'.$close->total(); !!}
                    </div>
                </div>
                <div class=" table-responsive mailbox-messages" id="refresh2">
                    <p style="display:none;text-align:center;" id="show2" class="text-red"><b>Loading...</b></p>
                    <!-- table -->
                    <table class="table table-hover table-striped">
                        <thead>
                        <th></th>
                        <th>
                            {!! Lang::get('lang.subject') !!}
                        </th>
                        <th>
                            {!! Lang::get('lang.ticket_id') !!}
                        </th>
                        <th>
                            {!! Lang::get('lang.priority') !!}
                        </th>
                        <th>
                            {!! Lang::get('lang.last_replier') !!}
                        </th>
                        <th>
                            {!! Lang::get('lang.last_activity') !!}
                        </th>
                        <th>
                            {!! Lang::get('lang.status') !!}
                        </th>
                        </thead>
                        <tbody id="hello">
                            @foreach ($close  as $ticket )
                            <tr <?php if ($ticket->seen_by == null) { ?> style="color:green;" <?php }
                        ?> >
                                <td><input type="checkbox" class="icheckbox_flat-blue" name="select_all[]" value="{{$ticket->id}}"/></td>
                                <?php
                                $title = App\Model\helpdesk\Ticket\Ticket_Thread::where('ticket_id', '=', $ticket->id)->first();
                                $string = strip_tags($title->title);
                                if (strlen($string) > 40) {
                                    $stringCut = substr($string, 0, 40);
                                    $string = substr($stringCut, 0, strrpos($stringCut, ' ')) . ' ...';
                                }
                                $TicketData = App\Model\helpdesk\Ticket\Ticket_Thread::where('ticket_id', '=', $ticket->id)->max('id');
                                $TicketDatarow = App\Model\helpdesk\Ticket\Ticket_Thread::where('id', '=', $TicketData)->first();
                                $LastResponse = App\User::where('id', '=', $TicketDatarow->user_id)->first();
                                if ($LastResponse->role == "user") {
                                    $rep = "#F39C12";
                                    $username = $LastResponse->user_name;
                                } else {
                                    $rep = "#000";
                                    $username = $LastResponse->first_name . " " . $LastResponse->last_name;
                                    if ($LastResponse->first_name == null || $LastResponse->last_name == null) {
                                        $username = $LastResponse->user_name;
                                    }
                                }
                                $titles = App\Model\helpdesk\Ticket\Ticket_Thread::where('ticket_id', '=', $ticket->id)->where("is_internal", "=", 0)->get();
                                $count = count($titles);
                                foreach ($titles as $title) {
                                    $title = $title;
                                }
                                ?>
                                <td class="mailbox-name"><a href="{!! URL('check_ticket',[Crypt::encrypt($ticket->id)]) !!}" title="{!! $title->title !!}">{{$string}}   </a> ({!! $count!!}) <i class="fa-solid fa-comment"></i></td>
                                <td class="mailbox-Id">#{!! $ticket->ticket_number !!}</td>
                                <?php $priority = App\Model\helpdesk\Ticket\Ticket_Priority::where('priority_id', '=', $ticket->priority_id)->first(); ?>
                                <td class="mailbox-priority"><span>{{$priority->priority}}</span></td>
                        <td class="mailbox-last-reply" style="color: {!! $rep !!}">{!! $username !!}</td>
                        <td class="mailbox-last-activity">{!! $title->updated_at !!}</td>
                        <?php $status = App\Model\helpdesk\Ticket\Ticket_Status::where('id', '=', $ticket->status)->first(); ?>
                        <td class="mailbox-date">{!! $status->name !!}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table><!-- /.table -->
                    <div class="float-end">
                        <?php echo $close->setPath(url('mytickets'))->render(); ?>&nbsp;
                    </div>
                </div><!-- /.mail-box-messages -->
                {!! html()->closeModelForm() !!}
            </div>
        </div><!-- /. box -->
    </div>
</div>
<script>
    $(function() {
        //Enable check and uncheck all functionality
        $(".checkbox-toggle").click(function() {
            var clicks = $(this).data('clicks');
            if (clicks) {
                //Uncheck all checkboxes
                $(".mailbox-messages input[type='checkbox']").iCheck("uncheck");
                $(".far", this).removeClass("fa-check-square").addClass('fa-square');
            } else {
                //Check all checkboxes
                $(".mailbox-messages input[type='checkbox']").iCheck("check");
                $(".far", this).removeClass("fa-square").addClass('fa-check-square');
            }
            $(this).data("clicks", !clicks);
        });
    });

    $(function() {
        // Enable check and uncheck all functionality
        $(".checkbox-toggle").click(function() {
            var clicks = $(this).data('clicks');
            if (clicks) {
                //Uncheck all checkboxes
                $("input[type='checkbox']", ".mailbox-messages").iCheck("uncheck");
            } else {
                //Check all checkboxes
                $("input[type='checkbox']", ".mailbox-messages").iCheck("check");
            }
            $(this).data("clicks", !clicks);
        });
    });

    $(document).ready(function() { /// Wait till page is loaded
        $('#click1').click(function() {
            $('#refresh1').load('mytickets #refresh1');
            $('#refresh21').load('mytickets #refresh21');
            $("#show1").show();
        });
    });

    $(document).ready(function() { /// Wait till page is loaded
        $('#click2').click(function() {
            $('#refresh2').load('mytickets #refresh2');
            $('#refresh22').load('mytickets #refresh22');
            $("#show2").show();
        });
    });

</script>
@stop