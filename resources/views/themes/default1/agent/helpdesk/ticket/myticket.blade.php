@extends('themes.default1.agent.layout.agent')

@section('Tickets')
class="nav-link active"
@stop

@section('ticket-bar')
active
@stop

@section('dept-ticket-bar')
class="nav-link active"
@stop

@section('myticket')
class="nav-link active"
@stop

@section('PageHeader')
<h1>{{Lang::get('lang.tickets')}}</h1>
<style>
.tooltip1 {
    position: relative;
    /*display: inline-block;*/
    /*border-bottom: 1px dotted black;*/
}

.tooltip1 .tooltiptext {
     visibility: hidden;
     width:300px;
     max-height: 250px;
     overflow: auto;
     background-color: black;
     color: #fff;
     text-align: center;
     border-radius: 6px;
     padding: 10px;
     font-weight: 300px !important;
 
     /* Position the tooltip */
     position: absolute;
     z-index: 1;
 }

.tooltip1:hover .tooltiptext {
    visibility: visible;
}
</style>
@stop
@section('content')
<?php
$date_time_format = UTC::getDateTimeFormat();
if (Auth::user()->role == 'agent') {
    $dept = App\Model\helpdesk\Agent\Department::where('id', '=', Auth::user()->primary_dpt)->first();
    $tickets = App\Model\helpdesk\Ticket\Tickets::where('status', '=', 1)->where('assigned_to', '=', Auth::user()->id)->orderBy('id', 'ASC')->count();
} else {
    $tickets = App\Model\helpdesk\Ticket\Tickets::where('status', '=', 1)->where('assigned_to', '=', Auth::user()->id)->orderBy('id', 'ASC')->count();
}
?>
<!-- Main content -->
   @if(Session::has('success'))
    <div class="alert alert-success alert-dismissible">
        <i class="fa-solid fa-circle-check"> </i>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
        {{Session::get('success')}}
    </div>
    @endif
    <!-- failure message -->
    @if(Session::has('fails'))
    <div class="alert alert-danger alert-dismissible">
        <i class="fa-solid fa-ban"> </i> <b> {!! Lang::get('lang.alert') !!}! </b>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
        {{Session::get('fails')}}
    </div>
    @endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.my_tickets') !!} </h3> &nbsp;<small id="title_refresh">({!! $tickets !!} {!! Lang::get('lang.tickets') !!})</small>
    </div><!-- /.box-header -->

    <div class="card-body ">
        {!! html()->form('POST', route('select_all'))->attributes(['id' => 'modalpopup'])->open() !!}
        <!--<div class="mailbox-controls">-->
        <!-- Check all button -->
        <a class="btn btn-secondary btn-sm checkbox-toggle"><i class="fa-regular fa-square"></i></a>
        {{-- <a class="btn btn-secondary btn-sm" id="click"><i class="fa-solid fa-refresh"></i></a> --}}

        <input type="submit" class="submit btn btn-secondary text-orange btn-sm" id="delete" name="submit" value="{!! Lang::get('lang.delete') !!}">
        <input type="submit" class="submit btn btn-secondary text-yellow btn-sm" id="close" name="submit" value="{!! Lang::get('lang.close') !!}">
        <button type="button" class="btn btn-sm btn-secondary text-green" id="Edit_Ticket" data-bs-toggle="modal" data-bs-target="#MergeTickets"><i class="fa-solid fa-gears"> </i> {!! Lang::get('lang.merge') !!}</button>
        <!--</div>-->
        
        <button type="button" class="btn btn-sm btn-secondary" id="assign_Ticket" data-bs-toggle="modal" data-bs-target="#AssignTickets" style="display: none;"><i class="fa-solid fa-hand-point-right"> </i> {!! Lang::get('lang.assign') !!}</button>
        <p><p/>
        <div class="mailbox-messages" id="refresh">
            <!--datatable-->
            <table id="chumper" class="table table-bordered w-100 d-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>{{Lang::get('lang.subject')}}</th>
                        <th>{{Lang::get('lang.ticket_id')}}</th>
                        <th>{{Lang::get('lang.priority')}}</th>
                        <th>{{Lang::get('lang.from')}}</th>
                        <th>{{Lang::get('lang.assigned_to')}}</th>
                        <th>{{Lang::get('lang.last_activity')}}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
             
            
            <!-- /.datatable -->
        </div><!-- /.mail-box-messages -->
        {!! html()->closeModelForm() !!}
    </div><!-- /.box-body -->
</div><!-- /. box -->

<!-- merge tickets modal -->
<div class="modal fade" id="MergeTickets">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{!! Lang::get('lang.merge-ticket') !!} </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="merge-close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div><!-- /.modal-header-->
            <div class ="modal-body">
                <div class="row">
                    <div class="col-md-4">
                    </div>
                    <div class="col-md-6" id="merge_loader"  style="display:none;">
                        <img src="{{asset("lb-faveo/media/images/gifloader.gif")}}"><br/><br/><br/>
                    </div><!-- /.merge-loader -->
                </div>
                <div id="merge_body">
                    <div id="merge-body-alert">
                        <div class="row">
                            <div class="col-md-12">
                                <div id="merge-succ-alert" class="alert alert-success alert-dismissible d-none" >
                                    <!--<button id="dismiss-merge" type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>-->
                                    <h4><i class="icon fa-solid fa-check"></i>{!! Lang::get('lang.alert') !!}!</h4>
                                    <div id="message-merge-succ"></div>
                                </div>
                                <div id="merge-err-alert" class="alert alert-danger alert-dismissible d-none">
                                    <!--<button id="dismiss-merge2" type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>-->
                                    <h4><i class="icon fa-solid fa-ban"></i>{!! Lang::get('lang.alert') !!}!</h4>
                                    <div id="message-merge-err"></div>
                                </div>
                            </div>
                        </div>
                    </div><!-- /.merge-alert -->
                    <div id="merge-body-form">
                        <div class="row">
                            <div class="col-md-6">
                                {!! html()->form('PATCH', url()->current())->attributes(['id' => 'merge-form'])->open() !!}
                                <label>{!! Lang::get('lang.title') !!}</label>
                                <input type="text" name='title' class="form-control" value="" placeholder="Optional" />
                            </div>
                            <div class="col-md-6">
                                <label>{!! Lang::get('lang.select-pparent-ticket') !!}</label>
                                <select class="form-control" id="select-merge-parent"  name='p_id' data-placeholder="{!! Lang::get('lang.select_tickets') !!}" style="width: 100%;"><option value=""></option></select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <label>{!! Lang::get('lang.merge-reason') !!}</label>
                                <textarea  name="reason" class="form-control"></textarea>
                            </div>

                        </div>
                    </div><!-- mereg-body-form -->
                </div><!-- merge-body -->
            </div><!-- /.modal-body -->
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="dismis2">{!! Lang::get('lang.close') !!}</button>
                <input  type="submit" id="merge-btn" class="btn btn-primary" value="{!! Lang::get('lang.merge') !!}">
                {!! html()->closeModelForm() !!}
            </div><!-- /.modal-footer -->
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- Assign ticket model-->
<div class="modal fade" id="AssignTickets">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{!! Lang::get('lang.assign-ticket') !!} </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="assign-close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div><!-- /.modal-header-->
            <div class ="modal-body">
                <div class="row">
                    <div class="col-md-4">
                    </div>
                    <div class="col-md-6" id="assign_loader"  style="display:none;">
                        <img src="{{asset("lb-faveo/media/images/gifloader.gif")}}"><br/><br/><br/>
                    </div><!-- /.merge-loader -->
                </div>
                <div id="assign_body">
                        <div class="row">
                            <div class="col-md-12">
                                {!! html()->form('PATCH', url()->current())->attributes(['id' => 'assign-form'])->open() !!}
                                <label>{!! Lang::get('lang.whome_do_you_want_to_assign_ticket') !!}</label>
                                <select class="form-control" id="select-assign-agent"  name="assign_to" data-placeholder="{!! Lang::get('lang.select_agent') !!}" style="width: 100%;"><option value=""></option></select>
                            </div>
                        </div>
                    </div><!-- mereg-body-form -->
                </div><!-- merge-body -->
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="dismis2">{!! Lang::get('lang.close') !!}</button>
                <input  type="submit" id="merge-btn" class="btn btn-primary" value="{!! Lang::get('lang.assign') !!}">
                {!! html()->closeModelForm() !!}
            </div><!-- /.modal-footer -->
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- Assign ticket model-->
<!-- Modal -->   
<div class="modal fade" id="myModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel"></h4>
                <button type="button" class="btn-close closemodal" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body" id="custom-alert-body" >
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary no">{{Lang::get('lang.cancel')}}</button>
                <button type="button" class="btn btn-primary yes" data-bs-dismiss="modal">{{Lang::get('lang.ok')}}</button>
            </div>
        </div>
    </div>
</div>
@include('vendor.Chumper.ticket-javascript')
<script>
   
    var t_id = [];
    var option = null;
    $(function () {
        //Enable check and uncheck all functionality
        $(".checkbox-toggle").click(function () {
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

    $(function () {
        // Enable check and uncheck all functionality

        $(".checkbox-toggle").click(function () {
            var clicks = $(this).data('clicks');
            if (clicks) {
                //Uncheck all checkboxes
                $("input[type='checkbox']", ".mailbox-messages").iCheck("uncheck");
                // alert($("input[type='checkbox']").val());
                t_id = $('.selectval').map(function () {
                    return $(this).val();
                }).get();
                showAssign(t_id);
                // alert(checkboxValues);
            } else {
                //Check all checkboxes
                $("input[type='checkbox']", ".mailbox-messages").iCheck("check");
                // alert('Hallo');
                t_id = [];
                showAssign(t_id);
            }
            $(this).data("clicks", !clicks);

        });


    });


    $(document).ready(function () { /// Wait till page is loaded
        $('#click').click(function () {
            $('#refresh').load('inbox #refresh');
            $('#title_refresh').load('inbox #title_refresh');
            $('#count_refresh').load('inbox #count_refresh');
            $("#show").removeClass('d-none');
        });

        $(".select2").select2();

        $('#delete').on('click', function () {
            option = 0;
            $('#myModalLabel').html("{{Lang::get('lang.delete-tickets')}}");
        });

        $('#close').on('click', function () {
            option = 1;
            $('#myModalLabel').html("{{Lang::get('lang.close-tickets')}}");
        });

        $("#modalpopup").on('submit', function (e) {
            e.preventDefault();
            var msg = "{{Lang::get('lang.confirm')}}";
            var values = getValues();
            if (values == "") {
                msg = "{{Lang::get('lang.select-ticket')}}";
                $('.yes').html("{{Lang::get('lang.ok')}}");
                $('#myModalLabel').html("{{Lang::get('lang.alert')}}");
            } else {
                $('.yes').html("Yes");
            }
            $('#custom-alert-body').html(msg);
            $("#myModal").modal("show");
        });

        $(".closemodal, .no").click(function () {
            $("#myModal").modal("hide");
        });

        $(".closemodal, .no").click(function () {
            $("#myModal").modal("hide");
        });

        $('.yes').click(function () {
            var values = getValues();
            if (values == "") {
                 $("#myModal").modal("hide");
            } else {
                 $("#myModal").modal("hide");
                $("#modalpopup").unbind('submit');
                if (option == 0) {
                    //alert('delete');
                    $('#delete').click();
                } else {
                    //alert('close');
                    $('#close').click();
                }
            }
        });

        function getValues() {
            var values = $('.selectval:checked').map(function () {
                return $(this).val();
            }).get();
            return values;
        }

        //checking merging tickets
        $('#MergeTickets').on('show.bs.modal', function () {

            // alert("hi");
            $.ajax({
                type: "GET",
                url: "{{route('check.merge.tickets',0)}}",
                dataType: "html",
                data: {data1: t_id},
                beforeSend: function () {
                    $("#merge_body").addClass('d-none');
                    $("#merge_loader").removeClass('d-none');
                },
                success: function (response) {
                    if (response == 0) {
                        $("#merge_body").removeClass('d-none');
                        $("#merge-succ-alert").addClass('d-none');
                        $("#merge-body-alert").removeClass('d-none');
                        $("#merge-body-form").addClass('d-none');
                        $("#merge_loader").addClass('d-none');
                        $("#merge-btn").attr('disabled', true);
                        var message = "{{Lang::get('lang.select-tickets-to merge')}}";
                        $("#merge-err-alert").removeClass('d-none');
                        $('#message-merge-err').html(message);

                    } else if (response == 2) {
                        $("#merge_body").removeClass('d-none');
                        $("#merge-succ-alert").addClass('d-none');
                        $("#merge-body-alert").removeClass('d-none');
                        $("#merge-body-form").addClass('d-none');
                        $("#merge_loader").addClass('d-none');
                        $("#merge-btn").attr('disabled', true);
                        var message = "{{Lang::get('lang.different-users')}}";
                        $("#merge-err-alert").removeClass('d-none');
                        $('#message-merge-err').html(message);
                    } else {

                        $("#merge_body").removeClass('d-none');
                        $("#merge-body-alert").addClass('d-none');
                        $("#merge-body-form").removeClass('d-none');
                        $("#merge_loader").addClass('d-none');
                        $("#merge-btn").attr('disabled', false);
                        $("#merge_loader").addClass('d-none');
                        $.ajax({
                            url: "{{ route('get.merge.tickets',0) }}",
                            dataType: "html",
                            data: {data1: t_id},
                            success: function (data) {
                                $('#select-merge-parent').html(data);
                            }
                            // return false;
                        });
                    }
                }
            });
        });

        //submit merging form
        $('#merge-form').on('submit', function () {
            $.ajax({
                type: "POST",
                url: "{!! url('merge-tickets/') !!}/" + t_id,
                dataType: "json",
                data: $(this).serialize(),
                beforeSend: function () {
                    $("#merge_body").addClass('d-none');
                    $("#merge_loader").removeClass('d-none');
                },
                success: function (response) {
                    if (response == 0) {
                        $("#merge_body").removeClass('d-none');
                        $("#merge-succ-alert").addClass('d-none');
                        $("#merge-body-alert").removeClass('d-none');
                        $("#merge-body-form").addClass('d-none');
                        $("#merge_loader").addClass('d-none');
                        $("#merge-btn").attr('disabled', true);
                        var message = "{{Lang::get('lang.merge-error')}}";
                        $("#merge-err-alert").removeClass('d-none');
                        $('#message-merge-err').html(message);
                    } else {
                        $("#merge_body").removeClass('d-none');
                        $("#merge-err-alert").addClass('d-none');
                        $("#merge-body-alert").removeClass('d-none');
                        $("#merge-body-form").addClass('d-none');
                        $("#merge_loader").addClass('d-none');
                        $("#merge-btn").attr('disabled', true);
                        var message = "{{Lang::get('lang.merge-success')}}";
                        $("#merge-succ-alert").removeClass('d-none');
                        $('#message-merge-succ').html(message);
                        setTimeout(function () {
                            $("#alert11").addClass('d-none');
                            location.reload();
                        }, 1000);
                    }
                }
            })
            return false;
        });

        $('#AssignTickets').on('show.bs.modal', function() {
            $.ajax({
                type: "POST",
                url: "{{route('get-agents')}}",
                dataType: "html",
                beforeSend: function() {
                    $("#assign_body").addClass('d-none');
                    $("#assign_loader").removeClass('d-none');
                },
                success: function(data) {
                    $("#assign_loader").addClass('d-none');
                    $("#assign_body").removeClass('d-none');
                    $('#select-assign-agent').html(data);
                }
            });
        });

        // Assign a ticket
        $('#assign-form').on('submit', function() {
            $.ajax({
                type: "POST",
                url: "../ticket/assign/"+t_id,
                dataType: "html",
                data: $(this).serialize(),
                beforeSend: function() {
                $("#assign_body").addClass('d-none');
                    $("#assign_loader").removeClass('d-none');
                },
                success: function(response) {
                    if (response == 1)
                    {
                        location.reload();
                        var message = "Success!";
                            $("#alert11").removeClass('d-none');
                            $('#message-success1').html(message);
                            setInterval(function(){$("#dismiss11").trigger("click"); }, 2000);
                    }
                    $("#assign_body").removeClass('d-none');
                    $("#assign_loader").addClass('d-none');
                    $("#dismis4").trigger("click");
                }
            })
            return false;
        });
    });

    function someFunction(id) {
        if (document.getElementById(id).checked) {
            t_id.push(id);
            // alert(t_id);
        } else if (document.getElementById(id).checked === undefined) {
            var index = t_id.indexOf(id);
            if (index === -1) {
                t_id.push(id);
            } else {
                t_id.splice(index, 1);
            }
        } else {
            var index = t_id.indexOf(id);
            t_id.splice(index, 1);
            // alert(t_id);
        }
        showAssign(t_id);
    }

    function showAssign(t_id)
    {
        if (t_id.length >= 1) {
            $('#assign_Ticket').css('display', 'inline');
        } else {
            $('#assign_Ticket').css('display', 'none');
        }
    }
</script>
@stop