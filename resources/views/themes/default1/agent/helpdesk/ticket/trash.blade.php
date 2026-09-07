@extends('themes.default1.agent.layout.agent')

@section('Tickets')
class="active"
@stop

@section('ticket-bar')
active
@stop

@section('trash')
class="active"
@stop
@section('PageHeader')
<h3>{{Lang::get('lang.tickets')}}</h3>
<style>
 .tooltip1 {
     position: relative;
     /*display: inline-block;*/
     /*border-bottom: 1px dotted black;*/
 }
 
 .tooltip1 .tooltiptext {
     visibility: hidden;
     width: 100%;
     background-color: black;
     color: #fff;
     text-align: center;
     border-radius: 6px;
     padding: 5px 0;
 
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
// $date_time_format = UTC::getDateTimeFormat();
if (Auth::user()->role == 'agent') {
    $dept = App\Model\helpdesk\Agent\Department::where('id', '=', Auth::user()->primary_dpt)->first();
    $tickets = App\Model\helpdesk\Ticket\Tickets::where('status', '=', 5)->where('dept_id', '=', $dept->id)->orderBy('id', 'DESC')->count();
} else {
    $tickets = App\Model\helpdesk\Ticket\Tickets::where('status', '=', 5)->orderBy('id', 'DESC')->count();
}
?>
<!-- Main content -->
<div class="box box-warning">
    <div class="box-header with-border">
        <h3 class="box-title">{!! Lang::get('lang.trash') !!} </h3> <small id="title_refresh">{!! $tickets !!} {!! Lang::get('lang.tickets') !!}</small>
    </div><!-- /.box-header -->
    <div class="box-body">
        @if(Session::has('success'))
        <div class="alert alert-success alert-dismissible">
            <i class="fa  fa-circle-check"> </i> 
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
            {{Session::get('success')}}
        </div>
        @endif
        <!-- failure message -->
        @if(Session::has('fails'))
        <div class="alert alert-danger alert-dismissible">
            <i class="fa-solid fa-ban"> </i> 
            <b> {!! Lang::get('lang.alert') !!}! </b>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
            {{Session::get('fails')}}
        </div>
        @endif
        {!! html()->form('POST', route('select_all'))->attributes(['id' => 'modalpopup'])->open() !!}
        <!--<div class="mailbox-controls">-->
        <!-- Check all button -->
        <a class="btn btn-secondary btn-sm checkbox-toggle"><i class="fa-solid fa-square-o"></i></a>
        <!--<input type="submit" class="btn btn-secondary text-blue btn-sm" id="delete"  name="submit" value="{!! Lang::get('lang.open') !!}">
        <input type="submit" class="btn btn-secondary text-yellow btn-sm" name="submit"  id="close" value="{!! Lang::get('lang.close') !!}">-->
        <div class="btn-group">
            <button type="button" class="btn btn-secondary dropdown-toggle btn-sm" data-bs-toggle="dropdown" id="d1"><i class="fa-solid fa-exchange text-teal" id="hidespin"> </i>
                {!! Lang::get('lang.change_status') !!} <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li ><input type="submit" class="btn btn-block btn-secondary btn-sm text-green" id="delete"  name="submit" value="{!! Lang::get('lang.open') !!}">
                </li>
                <li ><input type="submit" class="btn btn-block btn-secondary btn-sm text-yellow" name="submit"  id="close" value="{!! Lang::get('lang.close') !!}"></li>

            </ul>
        </div>
        <input type="submit" class="btn btn-secondary text-yellow btn-sm" name="submit"  id="hard-delete" value="{{Lang::get('lang.clean-up')}}" title="{{Lang::get('lang.trash-delete-title-msg')}}">
        
        <!--</div>-->
        <p><p/>
        <div class="mailbox-messages"  id="refresh">
            <p style="display:none;text-align:center; position:fixed; margin-left:40%;margin-top:-70px;" id="show" class="text-red"><b>{!! Lang::get('lang.loading') !!}...</b></p>
            <!-- table -->

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

        </div><!-- /.mail-box-messages -->
        {!! html()->closeModelForm() !!}
    </div><!-- /.box-body -->
</div><!-- /. box -->
<!-- Modal -->   
<div class="modal fade in" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none; padding-right: 15px;background-color: rgba(0, 0, 0, 0.7);">
    <div class="modal-dialog" role="document">
        <div class="col-md-2"></div>
        <div class="col-md-8">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close closemodal" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                    <h4 class="modal-title" id="myModalLabel"></h4>
                </div>
                <div class="modal-body" id="custom-alert-body" >
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary pull-left yes" data-bs-dismiss="modal">{{Lang::get('lang.ok')}}</button>
                    <button type="button" class="btn btn-secondary no">{{Lang::get('lang.cancel')}}</button>
                </div>
            </div>
        </div>
    </div>
</div>

@include('vendor.Chumper.ticket-javascript')
<script>
    var option = null;
    $(function() {
        //Enable check and uncheck all functionality
        $(".checkbox-toggle").click(function() {
            var clicks = $(this).data('clicks');
            if (clicks) {
                //Uncheck all checkboxes
                $(".mailbox-messages input[type='checkbox']").iCheck("uncheck");
                $(".fa", this).removeClass("fa-check-square-o").addClass('fa-square-o');
            } else {
                //Check all checkboxes
                $(".mailbox-messages input[type='checkbox']").iCheck("check");
                $(".fa", this).removeClass("fa-square-o").addClass('fa-check-square-o');
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
        $('#click').click(function() {
            $('#refresh').load('trash #refresh');
            $('#title_refresh').load('trash #title_refresh');
            $('#count_refresh').load('trash #count_refresh');
            $("#show").removeClass('d-none');
        });

        $('#delete').on('click', function() {
            option = 0;
            $('#myModalLabel').html("{{Lang::get('lang.open-tickets')}}");
        });

        $('#close').on('click', function() {
            option = 1;
            $('#myModalLabel').html("{{Lang::get('lang.close-tickets')}}");
        });

        $('#hard-delete').on('click', function() {
            option = 2;
            $('#myModalLabel').html("{{Lang::get('lang.trash-delete-ticket')}}");
        });

        $("#modalpopup").on('submit', function(e) {
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
            $("#myModal").css("display", "block");
        });
        $(".closemodal, .no").click(function() {

            $("#myModal").css("display", "none");

        });

        $('.yes').click(function() {
            var values = getValues();
            if (values == "") {
                $("#myModal").css("display", "none");
            } else {
                $("#myModal").css("display", "none");
                $("#modalpopup").unbind('submit');
                if (option == 0) {
                    //alert('delete');
                    $('#delete').click();
                } else if (option == 1) {
                    //alert('close');
                    $('#close').click();
                } else {
                    $('#hard-delete').click();
                }
            }
        });

        function getValues() {
            var values = $('.selectval:checked').map(function() {
                return $(this).val();
            }).get();
            return values;
        }

    });

</script>
@stop
