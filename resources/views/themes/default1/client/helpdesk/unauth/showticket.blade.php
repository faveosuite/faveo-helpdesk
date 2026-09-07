@extends('themes.default1.client.layout.client')

@section('content')
<?php
$thread = App\Model\helpdesk\Ticket\Ticket_Thread::where('ticket_id', '=', $tickets->id)->first();
//$user = App\User::where('id','=',$id1)->first();
?>

<style type="text/css">
    .caret {
        border-left: 4px solid transparent;
        border-right: 4px solid transparent;
        border-top: 4px solid;
        display: inline-block;
        height: 0;
        margin-left: 2px;
        vertical-align: middle;
        width: 0;
    }

    .mailbox-attachments li{float:left;width:200px;border:1px solid #eee;margin-bottom:10px;margin-right:10px}

    .mailbox-attachment-name{font-weight:bold;color:#666;word-break: break-word;}

    .mailbox-attachment-icon,.mailbox-attachment-info,.mailbox-attachment-size{display:block}

    .mailbox-attachment-info{padding:10px;background:#f4f4f4;height: auto;}

    .mailbox-attachment-size{color:#999;font-size:12px}

    .mailbox-attachment-icon{text-align:center;font-size:65px;color:#666;padding:20px 10px}

    .mailbox-attachment-icon.has-img{padding:0}

    .mailbox-attachment-icon.has-img>img{max-width:100%;height:auto}

    #clearfix {display: inline-block!important;list-style-type: none!important;}

    .ml40 {margin-left: -40px!important;}
</style>


<!-- Main content -->
<div id="content" class="site-content col-sm-12">

    <article class="hentry">
        <header class="entry-header">
            <div class="row">

                <div class="col-sm-9">

                    <h3 class="entry-title"><i class="fa-solid fa-ticket-alt"> </i> {{$thread->title}}

                        <small> ( {{$tickets->ticket_number}} ) </small>
                    </h3>
                </div>

                <div class="col-sm-3 text-end">

                        <div>
                            <button class="btn btn-light dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false" style="background-color: whitesmoke">

                                <i class="fa-solid fa-right-left text-teal"> </i>

                                {!! Lang::get('lang.change_status') !!}

                                <span class="caret"></span>
                            </button>
                                <?php $statuses = \App\Model\helpdesk\Ticket\Ticket_Status::all(); ?>

                            <div class="dropdown-menu">

                                <a class="dropdown-item" href="#" id="open">
                                    <i class="fa-solid fa-folder-open" style="color:#FFD600;"> </i> {!! Lang::get('lang.open') !!}
                                </a>

                                <a class="dropdown-item" href="#" id="close">
                                    <i class="fa-solid fa-check" style="color:#15F109;"> </i> {!! Lang::get('lang.close') !!}
                                </a>

                                <a class="dropdown-item" href="#" id="resolved">
                                    <i class="fa-solid fa-circle-check " style="color:#0EF1BE;"> </i> {!! Lang::get('lang.resolved') !!}
                                </a>
                            </div>
                        </div>
                    {!! html()->closeModelForm() !!}
                </div>
            </div>
        <br/>
        <div class="row">

            <div class="col-md-12 mb-1">

                <div class="ticketratings float-end">

                    <table>

                        <tbody>
                        <?php $ratings = App\Model\helpdesk\Ratings\Rating::orderby('display_order')->get(); ?>
                        <form id="foo">
                            {!! csrf_field() !!}
                            <input type="hidden" name="tid_token" value="{!! $token !!}">
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
                        </form>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </header>

        <div class="entry-content clearfix">

            <div class="col-md-12 offset-md-5" id="loader" style="display:none;">

                <img src="{{asset("lb-faveo/media/images/gifloader.gif")}}"><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
            </div>

            <div  id="refresh">
                <div>
                    <?php
                    $priority = App\Model\helpdesk\Ticket\Ticket_Priority::where('priority_id', '=', $tickets->priority_id)->first();
                    ?>
                    <div class="alert alert-secondary">
                        <div class="row">
                            <div class="col-md-3">
                                <?php
                                $sla = $tickets->sla;
                                $SlaPlan = App\Model\helpdesk\Manage\Sla_plan::where('id', '=', $sla)->first();
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
                                echo UTC::usertimezone(date_format($time, 'Y-m-d H:i:s'));
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

                <div class="row">

                    <div class="col-md-6">

                        <table class="table">

                            <tr>

                                <td><b>{!! Lang::get('lang.status') !!}:</b></td>

                                <?php $status = App\Model\helpdesk\Ticket\Ticket_Status::where('id', '=', $tickets->status)->first(); ?>

                                @if($status->id == 1)

                                    <td title="{{$status->properties}}" style="color:orange">{{$status->name}}</td>

                                @elseif($status->id == 2)

                                    <td title="{{$status->properties}}" style="color:green">{{$status->name}}</td>

                                @elseif($status->id == 3)

                                    <td title="{{$status->properties}}" style="color:green">{{$status->name}}</td>

                                @endif
                            </tr>

                            <tr>

                                <td><b>{!! Lang::get('lang.priority') !!}:</b></td>

                                <?php $priority = App\Model\helpdesk\Ticket\Ticket_Priority::where('priority_id', '=', $tickets->priority_id)->first(); ?>

                                @if($priority->priority_id == 1)

                                    <td title="{{$priority->priority_desc}}" style="color:green">{{$priority->priority_desc}}</td>

                                @elseif($priority->priority_id == 2)

                                    <td title="{{$priority->priority_desc}}" style="color:orange">{{$priority->priority_desc}}</td>

                                @elseif($priority->priority_id == 3)

                                    <td title="{{$priority->priority_desc}}" style="color:red">{{$priority->priority_desc}}</td>

                                @endif
                            </tr>

                            <tr>

                                <td><b>{!! Lang::get('lang.department') !!}:</b></td>
                                <?php
                                $help_topic = App\Model\helpdesk\Manage\Help_topic::where('id', '=', $tickets->help_topic_id)->first();
                                $department = App\Model\helpdesk\Agent\Department::where('id', '=', $help_topic->department)->first();
                                ?>
                                <td title="{{ $department->name }}">{!! $department->name !!}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-6">

                        <table class="table">

                            <tr>

                                <td><b>{!! Lang::get('lang.help_topic') !!}:</b></td>

                                <?php $help_topic = App\Model\helpdesk\Manage\Help_topic::where('id', '=', $tickets->help_topic_id)->first(); ?>

                                <td title="{{$help_topic->topic}}">{{$help_topic->topic}}</td>
                            </tr>

                            <tr>

                                <td><b>{!! Lang::get('lang.last_message') !!}:</b></td>

                                <td>{{ucwords($last->poster)}}</td>
                            </tr>
                        </table>
                    </div>
    </div>
</div>
            <div class="comments-area mb-0" id="comments">
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
                                    <input type="hidden" name="tid_token" value="{!! $token !!}">
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
    <?php echo $conversations->setPath(route('show.ticket', ['ids' => $tickets->id, 'code' => $token]))->render(); ?>
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
<?php $id2 = $tickets->id; ?>

                <div id="respond" class="comment-respond form-border">

                    <h3 id="reply-title" class="comment-reply-title section-title"><i class="line"></i>{!! Lang::get('lang.leave_a_reply') !!}</h3>
                    @if(Auth::user())
                        {!! html()->form('POST', url('post/reply/'.$id2.'#formabc'))->open() !!}
                    @else
                        {!! html()->form('POST', url('post-ticket-reply/'.$tickets->id.'#formabc'))->open() !!}
                        <input type="hidden" name="tid_token" value="{!! $token !!}">
                    @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3 " style="background-color: white">
                                <textarea class="form-control" id="reply-input" name="comment" cols="30" rows="8" ></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="text-end">

                        <button type="submit" onClick="return checkFunction();" class="btn btn-custom btn-lg" style="background-color: #009aba; hov: #00c0ef; color: #fff">{!! Lang::get('lang.post_comment') !!}</button>
                    </div>
                    {!! html()->closeModelForm() !!}
                </div>
            </div>
        </div>
    </article>
</div>


<script type="text/javascript">

$("#cc_page").on('click', '.search_r', function () {
    var search_r = $('a', this).attr('id');
    $.ajax({
        type: "GET",
        url: "../../show/change-status/"+ id +"/"+ {!! $tickets->id !!},
        beforeSend: function () {
            $("#refresh").hide();
            $("#loader").show();
        },
        success: function (response) {
            $("#refresh").load("../show-ticket/{!! $tickets->id !!}/{!! $token !!}  #refresh");
            $("#refresh").show();
            $("#loader").hide();
            var message = response;
            $("#alert11").show();
            $('#message-success1').html(message);
            setInterval(function () {
                $("#alert11").hide();
            }, 4000);
        }
    });
    return false;
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
                url: '../../show/rating/' +<?php echo $tickets->id ?>, // the url where we want to POST
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
                url: '../../show/rating2/' +<?php echo $tickets->id ?>, // the url where we want to POST
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

    function changeStatus(id, ticket_id){
        $.ajax({
                type: "POST",
                url: "../../show/change-status/"+ id +"/"+ {!! $tickets->id !!},
                data:{"_token": "{{ csrf_token() }}", "tid_token": "{!! $token !!}"},
                beforeSend: function() {
                    $("#refresh").hide();
                    $("#loader").show();
                },
                success: function(response) {
                    $("#refresh").load("../../show-ticket/{!! $tickets->id !!}/{!! $token !!}  #refresh");
                    $("#refresh").show();
                    $("#loader").hide();
                    var message = response;
                    $("#alert11").show();
                    $('#message-success1').html(message);
                }
            })
            return false;
    }

jQuery(document).ready(function () {
    // Close a ticket
    $('#close').on('click', function (e) {
        $.ajax({
            type: "POST",

            url: "../../show/close/{!! $tickets->id !!}",
            data:{"_token": "{{ csrf_token() }}", "tid_token": "{!! $token !!}"},
            beforeSend: function () {
                $("#refresh").hide();
                $("#loader").show();
            },
            success: function (response) {
                $("#refresh").load("../../show-ticket/{!! $tickets->id !!}/{!! $token !!}  #refresh");
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
    $('#resolved').on('click', function (e) {
        $.ajax({
            type: "POST",
            url: "../../show/resolve/{!! $tickets->id !!}",
            data:{"_token": "{{ csrf_token() }}", "tid_token": "{!! $token !!}"},
            beforeSend: function () {
                $("#refresh").hide();
                $("#loader").show();
            },
            success: function (response) {
                $("#refresh").load("../../show-ticket/{!! $tickets->id !!}/{!! $token !!}  #refresh");
                $("#refresh").show();
                $("#loader").hide();
                var message = "Success! Your Ticket have been Resolved";
                $("#alert11").show();
                $('#message-success1').html(message);
                setInterval(function () {
                    $("#alert11").hide();
                    setTimeout(function () {
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
    $('#open').on('click', function (e) {
        $.ajax({
            type: "POST",
            url: "../../show/open/{!! $tickets->id !!}",
            data:{"_token": "{{ csrf_token() }}", "tid_token": "{!! $token !!}"},
            beforeSend: function () {
                $("#refresh").hide();
                $("#loader").show();
            },
            success: function (response) {
                $("#refresh").load("../../show-ticket/{!! $tickets->id !!}/{!! $token !!}  #refresh");
                $("#refresh").show();
                $("#loader").hide();

                var message = "Success! Your Ticket have been Opened";
                $("#alert11").show();
                $('#message-success1').html(message);
                setInterval(function () {
                    $("#alert11").hide();
                }, 4000);
            }
        })
        return false;
    });
});
</script>
@stop