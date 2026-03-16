@extends('themes.default1.client.layout.client')

@section('Tickets')
class="active"
@stop

@section('ticket-bar')
active
@stop

@section('ticket')
class="active"
@stop


@section('content')
<!-- Main content -->
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Ticket </h3> <small> 5 new messages</small>
        <!-- <div class="box-tools pull-right">
            <div class="has-feedback">
                <input type="text" class="form-control input-sm" placeholder="Search Mail"/>
                <span class="glyphicon glyphicon-search form-control-feedback"></span>
            </div>
        </div> --><!-- /.box-tools -->
    </div><!-- /.box-header -->
    <div class="box-body no-padding">
        <div class="mailbox-controls">
            <!-- Check all button -->
            <button class="btn btn-secondary btn-sm checkbox-toggle"><i class="fa-solid fa-square-o"></i></button>
            <button class="btn btn-secondary btn-sm"><i class="fa-solid fa-trash-o"></i></button>
            <button class="btn btn-secondary btn-sm" onclick="click()" id="click"><i class="fa-solid fa-refresh"></i></button>
            <div class="pull-right">
                <?php
$counted = count(App\Model\Ticket\Tickets::where('status', '=', 1)->get());
if ($counted < 20) {
	echo $counted . "/" . $counted;
} else {
	echo "20/" . $counted;
}
?>
            </div>
        </div>
        <div class=" table-responsive mailbox-messages"  id="refresh">
        <!-- table -->
            <table class="table table-hover table-striped">
                <thead>
                <th>
                </th>
                <th>
                    Subject
                </th>
                <th>
                    Ticket ID
                </th>
                <th>
                    Priority
                </th>
                <th>
                    Last Replier
                </th>
                <th>
                    Last Activity
                </th>
                <th>
                    Reply Due
                </th>
                </thead>
                <tbody id="hello">
                    <?php $tickets = App\Model\Ticket\Tickets::where('status', '=', 1)->orderBy('id', 'DESC')->paginate(20);?>

                     @foreach ($tickets  as $ticket )
                    <tr <?php if ($ticket->seen_by == null) {?> style="color:green;" <?php }
?> >
                        <td><input type="checkbox" value="{{$ticket->id}}"/></td>
                        <?php $title = App\Model\Ticket\Ticket_Thread::where('ticket_id', '=', $ticket->id)->first();
                        $string = strip_tags($title->title);
                        if (strlen($string) > 40) {
                            $stringCut = substr($string, 0, 40);
                            $string = substr($stringCut, 0, strrpos($stringCut, ' ')).' ...'; 
                        }
                        $TicketData = App\Model\Ticket\Ticket_Thread::where('ticket_id', '=', $ticket->id)->max('id');
                        $TicketDatarow = App\Model\Ticket\Ticket_Thread::where('id', '=', $TicketData)->first();
                        $LastResponse = App\User::where('id', '=', $TicketDatarow->user_id)->first(); 
                        if($LastResponse->role == "user") {
                            $rep = "#F39C12";
                            $username = $LastResponse->user_name;
                            } else { $rep = "#000"; $username = $LastResponse->first_name ." ". $LastResponse->last_name; 
                            if($LastResponse->first_name==null || $LastResponse->last_name==null) {
                                $username = $LastResponse->user_name;
                            }}   
                        $titles = App\Model\Ticket\Ticket_Thread::where('ticket_id', '=', $ticket->id)->get();
                        $count = count($titles);
                        foreach($titles as $title)
                        {
                            $title = $title;
                        }   ?>
                        <td class="mailbox-name"><a href="{!! URL('myticket',[$ticket->id]) !!}" title="{!! $title->title !!}">{{$string}}   </a> ({!! $count!!}) <i class="fa-solid fa-comment"></i></td>
                        <td class="mailbox-Id">#{!! $ticket->ticket_number !!}</td>
                        <?php $priority = App\Model\Ticket\Ticket_Priority::where('priority_id', '=', $ticket->priority_id)->first();?>
                        <td class="mailbox-priority"><spam class="btn btn-{{$priority->priority_color}} btn-xs">{{$priority->priority}}</spam></td>
                        
                <td class="mailbox-last-reply" style="color:{!! $rep !!}">{!! $username !!}</td>
                <td class="mailbox-last-activity">{!! $title->updated_at !!}</td>
                <td class="mailbox-date"></td>
                </tr>
                @endforeach
                </tbody>
            </table><!-- /.table -->
            <div class="pull-right">
                <?php echo $tickets->setPath(url('/ticket'))->render();?>&nbsp;
            </div>
        </div><!-- /.mail-box-messages -->
    </div><!-- /.box-body -->
</div><!-- /. box -->


<script>
    $(function() {
        //Enable iCheck plugin for checkboxes
        //iCheck for checkbox and radio inputs
        $('input[type="checkbox"]').iCheck({
            checkboxClass: 'icheckbox_flat-blue',
            radioClass: 'iradio_flat-blue'
        });

        //Enable check and uncheck all functionality
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

        //Handle starring for glyphicon and font awesome
        $(".mailbox-star").click(function(e) {
            e.preventDefault();
            //detect type
            var $this = $(this).find("a > i");
            var glyph = $this.hasClass("glyphicon");
            var fa = $this.hasClass("fa");

            //Switch states
            if (glyph) {
                $this.toggleClass("glyphicon-star");
                $this.toggleClass("glyphicon-star-empty");
            }

            if (fa) {
                $this.toggleClass("fa-star");
                $this.toggleClass("fa-star-o");
            }
        });
    });


    $(document).ready(function() { /// Wait till page is loaded
        $('#click').click(function() {
            $('#refresh').load('ticket #refresh');
        });
    });


    // //  check box get data
    // jQuery(function($) {
    //     $("form input[id='check_all']").click(function() { // triggred check

    //         var inputs = $("form input[type='checkbox']"); // get the checkbox

    //         for(var i = 0; i < inputs.length; i++) { // count input tag in the form
    //             var type = inputs[i].getAttribute("type"); //  get the type attribute
    //                 if(type == "checkbox") {
    //                     if(this.checked) {
    //                         inputs[i].checked = true; // checked
    //                     } else {
    //                         inputs[i].checked = false; // unchecked
    //                      }
    //                 }
    //         }
    //     });

    //     $("form input[id='submit']").click(function() {  // triggred submit

    //         var count_checked = $("[name='data[]']:checked").length; // count the checked
    //         if(count_checked == 0) {
    //             alert("Please select a product(s) to delete.");
    //             return false;
    //         }
    //         if(count_checked == 1) {
    //             return confirm("Are you sure you want to delete these product?");
    //         } else {
    //             return confirm("Are you sure you want to delete these products?");
    //           }
    //     });
    // }); // jquery end

</script>
@stop