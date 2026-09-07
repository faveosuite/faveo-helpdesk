@extends('themes.default1.client.layout.client')

@section('content')               
<?php
$tickets = App\Model\helpdesk\Ticket\Tickets::where('id', '=', \Crypt::decrypt($id))->first();
$thread = App\Model\helpdesk\Ticket\Ticket_Thread::where('ticket_id', '=', \Crypt::decrypt($id))->first();
//$user = App\User::where('id','=',$id1)->first();
?>
<!-- Main content -->
<div class="box box-primary">
    <div class="box-header">
        <div class="row">
            <div class="col-md-9">
                <section class="app-content-header"><h3 class="box-title"><i class="fa-solid fa-user"> </i> {{$thread->title}} </h3> ( {{$tickets->ticket_number}} )
                </section>
            </div> 
            <div class="col-md-3">
                <div class="pull-right">

                    <!-- <button type="button" class="btn btn-secondary"><i class="fa-solid fa-pen-to-square text-success"> </i> Edit</button> -->                            
                    {{-- <button type="button" class="btn btn-secondary"><i class="fa-solid fa-print text-primary"> </i> {!! link_to_route('ticket.print','Print',[$tickets->id]) !!}</button> --}}
                    <!-- </div> -->
                    @if( $common_setting->status == '1')
                    <div class="btn-group"> 
                        <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown"><i class="fa-solid fa-exchange text-teal"> </i>
                            {!! Lang::get('lang.change_status') !!} <span class="caret"></span>
                        </button>
                        <?php $statuses = \App\Model\helpdesk\Ticket\Ticket_Status::all(); ?>

                        <ul class="dropdown-menu">
                            <li><a href="#" id="open"><i class="fa-solid fa-folder-open" style="color:#FFD600;"> </i>{!! Lang::get('lang.open') !!}</a></li>
                            <li><a href="#" id="close"><i class="fa-solid fa-check" style="color:#15F109;"> </i>{!! Lang::get('lang.close') !!}</a></li>
                            <li><a href="#" id="resolved"><i class="fa-solid fa-circle-check " style="color:#0EF1BE;"> </i> {!! Lang::get('lang.resolved') !!}</a></li>
                        </ul>
                    </div>@endif
                    {!! html()->closeModelForm() !!}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="ticketratings pull-right">    
                    <table><tbody>
                            <?php $ratings = App\Model\helpdesk\Ratings\Rating::orderby('display_order')->get(); ?>
                        <form id="foo">
                            @foreach($ratings as $rating) 

                            @if($rating->rating_area == 'Helpdesk Area')
                            <?php
                            $rating_value = App\Model\helpdesk\Ratings\RatingRef::where('rating_id', '=', $rating->id)->where('ticket_id', '=', $tickets->id)->first();
                            if ($rating_value == null) {
                                $ratingval = '0';
                            } else {
                                $ratingval = $rating_value->rating_value;
                            }
                            ?>

                            <tr>
                                <th><div class="ticketratingtitle">{!! $rating->name !!} &nbsp;</div></th>&nbsp

                            <td>
                                <?php for ($i = 1; $i <= $rating->rating_scale; $i++) { ?>
                                    <input type="radio" class="star" id="star5" name="{!! $rating->name !!}" value="{!! $i !!}"<?php echo ($ratingval == $i) ? 'checked' : '' ?> />
                                <?php } ?>
                            </td> 
                            </tr>

                            @endif
                            @endforeach
                        </form></tbody> </table> 
                </div>
            </div>
        </div>

    </div>
    <div class="box-body" style="margin-bottom:-10px">
        <div class="row">
            <div id="loader" style="display:none;">
                <div class="col-5">
                </div>
                <div class="col-1">
                    <img src="{{asset("lb-faveo/media/images/gifloader.gif")}}"><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
                </div>
                <div class="col-6">
                </div>
            </div>
            <section class="app-content"  id="refresh" style="margin-bottom:-10px;margin-top:-10px">
                <div class="col-md-12"> 
                    <?php
                    $priority = App\Model\helpdesk\Ticket\Ticket_Priority::where('priority_id', '=', $tickets->priority_id)->first();
                    ?>
                    <div class="callout callout-default ">
                        <div class="row">
                            <div class="col-md-3"> 
                                <?php
                                $sla = $tickets->sla;
                                $SlaPlan = App\Model\helpdesk\Manage\Sla_plan::where('id', '=', 1)->first();
                                ?>
                                <b>{!! Lang::get('lang.sla_plan') !!}: {{$SlaPlan->grace_period}} </b> 
                            </div>
                            <div class="col-md-3"> 
                                <b>{!! Lang::get('lang.created_date') !!}: </b> {{ UTC::usertimezone($tickets->created_at) }}
                            </div>
                            <div class="col-md-3"> 
                                <b>{!! Lang::get('lang.due_date') !!}: </b> 
                                <?php
                                $time = $tickets->created_at;
                                $time = date_create($time);
                                date_add($time, date_interval_create_from_date_string($SlaPlan->grace_period));
                                echo UTC::usertimezone(date_format($time, 'd/m/Y H:i:s'));
                                ?>
                            </div>
                            <div class="col-md-3">
                                <?php $response = App\Model\helpdesk\Ticket\Ticket_Thread::where('ticket_id', '=', $tickets->id)->where('is_internal', '=', 0)->get(); ?>
                                @foreach($response as $last)
                                <?php $ResponseDate = $last->created_at; ?>
                                @endforeach
                                <b>{!! Lang::get('lang.last_response') !!}: </b> {{ UTC::usertimezone($ResponseDate)}} 
                            </div>
                        </div>
                    </div>
                </div>      
                <div class="col-md-6"> 
                    <table class="table table-hover">
                        <!-- <tr><th></th><th></th></tr> -->
                        <tr><td><b>{!! Lang::get('lang.status') !!}:</b></td>       <?php $status = App\Model\helpdesk\Ticket\Ticket_Status::where('id', '=', $tickets->status)->first(); ?>

                            @if($status->id == 1)
                            <td title="{{$status->properties}}" style="color:orange">{{$status->name}}</td></tr>
                        @elseif($status->id == 2)
                        <td title="{{$status->properties}}" style="color:green">{{$status->name}}</td></tr>
                        @elseif($status->id == 3)
                        <td title="{{$status->properties}}" style="color:green">{{$status->name}}</td></tr>
                        @endif

                        <tr><td><b>{!! Lang::get('lang.priority') !!}:</b></td>     <?php $priority = App\Model\helpdesk\Ticket\Ticket_Priority::where('priority_id', '=', $tickets->priority_id)->first(); ?>

                            <td title="{{$priority->priority}}">{{$priority->priority}}</td>

                        </tr>
                        <tr><td><b>{!! Lang::get('lang.department') !!}:</b></td>   
                            <?php
                            $help_topic = App\Model\helpdesk\Manage\Help_topic::where('id', '=', $tickets->help_topic_id)->first();
                            $department = App\Model\helpdesk\Agent\Department::where('id', '=', $help_topic->department)->first();
                            ?>
                            <td title="{{ $department->name }}">{!! $department->name !!}</td></tr>
                    </table>
                    <!-- </div> -->
                </div>
                <div class="col-md-6"> 
                    <!-- <div class="callout callout-success"> -->
                    <table class="table table-hover">
                        <!-- <tr><th></th><th></th></tr> -->
                        <tr><td><b>{!! Lang::get('lang.help_topic') !!}:</b></td>     <?php $help_topic = App\Model\helpdesk\Manage\Help_topic::where('id', '=', $tickets->help_topic_id)->first(); ?><td title="{{$help_topic->topic}}">{{$help_topic->topic}}</td></tr>
                        <tr><td><b>{!! Lang::get('lang.last_message') !!}:</b></td>   <td>{{ucwords($last->poster)}}</td></tr>
                    </table>
                </div>
                <!-- </div> -->
            </section> 
        </div>
    </div>
</div>
<?php
$conversations = App\Model\helpdesk\Ticket\Ticket_Thread::where('ticket_id', '=', $tickets->id)->where('is_internal', '=', 0)->paginate(10);
foreach ($conversations as $conversation) {
    $ConvDate1 = $conversation->created_at;
    $ConvDate = explode(' ', $ConvDate1);

    $date = $ConvDate[0];
    $time = $ConvDate[1];
    $time = substr($time, 0, -3);
    if (isset($data) && $date == $data) {
        
    } else {
        $data = $ConvDate[0];
    }
    $role = App\User::where('id', '=', $conversation->user_id)->first();

    $attachment = App\Model\helpdesk\Ticket\Ticket_attachments::where('thread_id', '=', $conversation->id)->first();
    if ($attachment == null) {
        $body = $conversation->body;
    } else {
        $body = $conversation->body;
        $attachments = App\Model\helpdesk\Ticket\Ticket_attachments::where('thread_id', '=', $conversation->id)->orderBy('id', 'DESC')->get();
        foreach ($attachments as $attachment) {
            if ($attachment->type == 'pdf') {
                
            } elseif ($attachment->type == 'docx') {
                
            } else {
                $image = @imagecreatefromstring($attachment->file);
                ob_start();
                imagejpeg($image, null, 80);
                $data = ob_get_contents();
                ob_end_clean();
                $var = '<img src="data:image/jpg;base64,' . base64_encode($data) . '" />';
                $body = str_replace($attachment->name, "data:image/jpg;base64," . base64_encode($data), $body);

                $string = $body;
                $start = "<head>";
                $end = "</head>";
                if (strpos($string, $start) == false || strpos($string, $start) == false) {
                    
                } else {
                    $ini = strpos($string, $start);
                    $ini += strlen($start);
                    $len = strpos($string, $end, $ini) - $ini;
                    $parsed = substr($string, $ini, $len);
                    $body2 = $parsed;
                    $body = str_replace($body2, " ", $body);
                }
            }
        }
    }
    $string = $body;
    $start = "<head>";
    $end = "</head>";
    if (strpos($string, $start) == false || strpos($string, $start) == false) {
        
    } else {
        $ini = strpos($string, $start);
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        $parsed = substr($string, $ini, $len);
        $body2 = $parsed;
        $body = str_replace($body2, " ", $body);
    }
    ?>
    <ol class="comment-list" >
        <li class="comment">
            <article class="comment-body">
                <footer class="comment-meta"<?php if ($role->role == "user") { ?> style="background-color: hsla(100, 100%, 51%, 0.15)" <?php } else { ?> style="background-color:#FFFCB3" <?php } ?>  > 
                    <div class="comment-author">

                        <img src="{{$role->profile_pic}}"alt="" height="50" width="50" class="avatar" <?php if ($role->role == "user") { ?>style="box-shadow: 0 1px 3px #00FF26;" <?php } else { ?> style="box-shadow: 0 1px 3px #FFEC00;" <?php } ?> >
                        @if($role->role == "user")
                        <b class="fn"><a href="#" rel="external" class="url">{{$role->user_name}}</a></b>
                        @else
                        <b class="fn"><a href="#" rel="external" class="url">{{$role->first_name." ".$role->last_name}}</a></b>
                        <div class="ticketratings pull-right">   <table><tbody>
                                    @foreach($ratings as $rating) 
                                    @if($rating->rating_area == 'Comment Area')
                                    <?php
                                    $rating_value = App\Model\helpdesk\Ratings\RatingRef::where('rating_id', '=', $rating->id)->where('thread_id', '=', $conversation->id)->first();
                                    if ($rating_value == null) {
                                        $ratingval = '0';
                                    } else {
                                        $ratingval = $rating_value->rating_value;
                                    }
                                    ?>
                                <form class="foo2">
                                    <tr>
                                        <th><div class="ticketratingtitle">{!! $rating->name !!} &nbsp;</div></th>&nbsp

                                    <td>
                                        <?php for ($i = 1; $i <= $rating->rating_scale; $i++) { ?>
                                            <input type="radio" class="star" id="star5" name="{!! $rating->name !!},{!! $conversation->id !!}" value="{!! $i !!}"<?php echo ($ratingval == $i) ? 'checked' : '' ?> />
                                        <?php } ?>
            <!--    <input type="radio" class="star" id="star4" name="rating" value="2"<?php echo ($tickets->rating == '2') ? 'checked' : '' ?> />
                <input type="radio" class="star" id="star3" name="rating" value="3"<?php echo ($tickets->rating == '3') ? 'checked' : '' ?>/>
                <input type="radio" class="star" id="star2" name="rating" value="4"<?php echo ($tickets->rating == '4') ? 'checked' : '' ?>/>
                <input type="radio" class="star" id="star1" name="rating" value="5"<?php echo ($tickets->rating == '5') ? 'checked' : '' ?> />-->
                                    </td> 
                                    </tr>
                                </form>
                                @endif
                                @endforeach
                                </tbody></table></div>
                        @endif
                    </div><!-- .comment-author -->
                    <div class="comment-metadata">
                        <small class="date text-muted">
                            <time datetime="2013-10-23T01:50:50+00:00"><i class="fa-solid fa-clock-o"> </i> {{ UTC::usertimezone($conversation->created_at) }}</time>
                        </small>
                    </div><!-- .comment-metadata -->
                </footer><!-- .comment-meta -->
                <div class="comment-content">
                    <p>{!! $body !!}</p>
                </div><!-- .comment-content -->
                <div class="timeline-footer" style="margin-bottom:-5px">
                    <?php
                    $attachments = App\Model\helpdesk\Ticket\Ticket_attachments::where('thread_id', '=', $conversation->id)->get();
                    $i = 0;
                    foreach ($attachments as $attachment) {
                        if ($attachment->poster == 'ATTACHMENT') {
                            $i++;
                        }
                    }
                    if ($i > 0) {
                        echo "<hr style='border-top: 1px dotted #FFFFFF;margin-top:0px;margin-bottom:0px;background-color:#8B8C90;'><h4 class='box-title'><b>" . $i . " </b> Attachments</h4>";
                    }
                    ?>
                    <ul class='mailbox-attachments clearfix'>
                        <?php
                        foreach ($attachments as $attachment) {
                            $size = $attachment->size;
                            $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
                            $power = $size > 0 ? floor(log($size, 1024)) : 0;
                            $value = number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
                            if ($attachment->poster == 'ATTACHMENT') {
                                if ($attachment->type == 'jpg' || $attachment->type == 'JPG' || $attachment->type == 'jpeg' || $attachment->type == 'JPEG' || $attachment->type == 'png' || $attachment->type == 'PNG' || $attachment->type == 'gif' || $attachment->type == 'GIF') {
                                    $image = @imagecreatefromstring($attachment->file);
                                    ob_start();
                                    imagejpeg($image, null, 80);
                                    $data = ob_get_contents();
                                    ob_end_clean();
                                    $var = '<a href="' . URL::route('image', ['image_id' => $attachment->id]) . '" target="_blank"><img style="max-width:200px;height:133px;" src="data:image/jpg;base64,' . base64_encode($data) . '"/></a>';
                                    echo '<li style="background-color:#f4f4f4;"><span class="mailbox-attachment-icon has-img">' . $var . '</span><div class="mailbox-attachment-info"><b style="word-wrap: break-word;">' . $attachment->name . '</b><br/><p>' . $value . '</p></div></li>';
                                } else {
                                    $var = '<a style="max-width:200px;height:133px;color:#666;" href="' . URL::route('image', ['image_id' => $attachment->id]) . '" target="_blank"><span class="mailbox-attachment-icon" style="background-color:#fff;">' . strtoupper($attachment->type) . '</span><div class="mailbox-attachment-info"><span ><b style="word-wrap: break-word;">' . $attachment->name . '</b><br/><p>' . $value . '</p></span></div></a>';
                                    echo '<li style="background-color:#f4f4f4;">' . $var . '</li>';
                                }
                            }
                        }
                        ?>
                    </ul>
                </div>
            </article><!-- .comment-body -->
        </li><!-- .comment -->    
    </ol>

    <?php
    ?>


<?php }
?>
<div class="pull-right" style="margin-top:-30px;margin-bottom:-30px">
    <?php echo $conversations->setPath(url('check_ticket/{' . $id . '}'))->render(); ?>
</div>
<br/><br/>
@if(Session::has('success1'))
<div class="alert alert-success alert-dismissible" id='formabc'>
    <i class="fa  fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('success1')}}
</div>
@endif
<!-- failure message -->
@if(Session::has('fails1'))
<div class="alert alert-danger alert-dismissible" id='formabc'>
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('fails1')}}
</div>
@endif
<?php $id2 = Crypt::decrypt($id); ?>
<div id="respond" class="comment-respond form-border">
    <h3 id="reply-title" class="comment-reply-title section-title"><i class="line"></i>{!! Lang::get('lang.leave_a_reply') !!}</h3>
    @if(Auth::user()) 
    {!! html()->form('POST', url('post/reply/'.$id2.'#formabc'))->open() !!}
    @else
    {!! html()->form('POST', url('post-ticket-reply/'.$id.'#formabc'))->open() !!}
    @endif
    <div class="row">
        <div class="mb-3">
            <div class="col-md-12">
                <div class="mb-3 ">
                    <textarea class="form-control" name="comment" cols="30" rows="8"></textarea>
                </div>
            </div>                                              
        </div>
    </div>
    <div class="text-end">
        <button type="submit" class="btn btn-custom btn-lg">{!! Lang::get('lang.post_comment') !!}</button>
    </div>
    {!! html()->closeModelForm() !!}
</div>

<script type="text/javascript">
    $("#cc_page").on('click', '.search_r', function() {
        var search_r = $('a', this).attr('id');
        $.ajax({
            type: "GET",
            url: "../ticket/status/{{$tickets->id}}/" + search_r,
            beforeSend: function() {
                $("#refresh").hide();
                $("#loader").show();
            },
            success: function(response) {
                $("#refresh").load("../check_ticket/{!! $id !!}  #refresh");
                $("#refresh").show();
                $("#loader").hide();
                var message = response;
                $("#alert11").show();
                $('#message-success1').html(message);
                setInterval(function() {
                    $("#alert11").hide();
                }, 4000);
            }
        });
        return false;
    });

    $(document).ready(function() {
        var Data = $('input[name="rating"]:checked').val();
        var Data2 = $('input[name="rating2"]:checked').val();
        if (Data) {
            $('input[name=rating]').rating('readOnly');
            jQuery('.star').attr('disabled', true);
        }
        $('.star').change(function() {
            $('#foo').submit();
            $('.foo2').submit();
        });
        // process the form
        $('#foo').submit(function(event) {
            // get the form data
            // there are many ways to get this data using jQuery (you can use the class or id also)
            var formData = $(this).serialize();
            //$('#foo').serialize();
            // process the form
            $.ajax({
                type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
                url: '../rating/' +<?php echo $tickets->id ?>, // the url where we want to POST
                data: formData, // our data object
                dataType: 'json', // what type of data do we expect back from the server
                success: function() {
                }
            });
            // using the done promise callback
            // stop the form from submitting the normal way and refreshing the page
            event.preventDefault();
        });
        // process the form
        $('.foo2').submit(function(event) {
            // get the form data
            // there are many ways to get this data using jQuery (you can use the class or id also)
            var formData = $(this).serialize();
            //$('#foo').serialize();
            // process the form
            $.ajax({
                type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
                url: '../rating2/' +<?php echo $tickets->id ?>, // the url where we want to POST
                data: formData, // our data object
                dataType: 'json', // what type of data do we expect back from the server
                success: function() {
                }
            });
            // using the done promise callback
            // stop the form from submitting the normal way and refreshing the page
            event.preventDefault();
        });
    });
    $(function() {
        //Add text editor
        $("textarea").wysihtml5();
    });

    jQuery(document).ready(function() {
        // Close a ticket
        $('#close').on('click', function(e) {
            $.ajax({
                type: "GET",
                url: "../ticket/close/{{$tickets->id}}",
                beforeSend: function() {
                    $("#refresh").hide();
                    $("#loader").show();
                },
                success: function(response) {
                    $("#refresh").load("../check_ticket/{!! $id !!}  #refresh");
                    $("#refresh").show();
                    $("#loader").hide();
                    // $("#d1").trigger("click");
                    // var message = "Success! Your Ticket have been Closed";
                    // $("#alert11").show();
                    // $('#message-success1').html(message);
                    // setInterval(function(){
                    //     $("#alert11").hide();
                    //     setTimeout(function() {
                    //         var link = document.querySelector('#load-inbox');
                    //         if(link) {
                    //             link.click();
                    //         }
                    //     }, 500);
                    // },2000);   
                }
            })
            return false;
        });

        // Resolved  a ticket
        $('#resolved').on('click', function(e) {
            $.ajax({
                type: "GET",
                url: "../ticket/resolve/{{$tickets->id}}",
                beforeSend: function() {
                    $("#refresh").hide();
                    $("#loader").show();
                },
                success: function(response) {
                    $("#refresh").load("../check_ticket/{!! $id !!}  #refresh");
                    $("#refresh").show();
                    $("#loader").hide();
                    var message = "Success! Your Ticket have been Resolved";
                    $("#alert11").show();
                    $('#message-success1').html(message);
                    setInterval(function() {
                        $("#alert11").hide();
                        setTimeout(function() {
                            var link = document.querySelector('#load-inbox');
                            if (link) {
                                link.click();
                            }
                        }, 500);
                    }, 2000);
                }
            })
            return false;
        });

        // Open a ticket
        $('#open').on('click', function(e) {
            $.ajax({
                type: "GET",
                url: "../ticket/open/{{$tickets->id}}",
                beforeSend: function() {
                    $("#refresh").hide();
                    $("#loader").show();
                },
                success: function(response) {
                    $("#refresh").load("../check_ticket/{!! $id !!}  #refresh");
                    $("#refresh").show();
                    $("#loader").hide();

                    var message = "Success! Your Ticket have been Opened";
                    $("#alert11").show();
                    $('#message-success1').html(message);
                    setInterval(function() {
                        $("#alert11").hide();
                    }, 4000);
                }
            })
            return false;
        });
    });
</script>
@stop