@extends('themes.default1.admin.layout.admin')

@section('Manage')
class="nav-link active"
@stop

@section('manage-menu-parent')
class="nav-item menu-open"
@stop

@section('manage-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('priority')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{!! Lang::get('lang.ticket_priority') !!}</h3>
@stop
<!-- /header -->
<!-- breadcrumbs -->
@section('breadcrumbs')
<ol class="breadcrumb">
</ol>
@stop

<!-- content -->
@section('content')

@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa-solid fa-circle-check"></i>
    <b>Success!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {!! Session::get('success') !!}
</div>
@endif
<!-- failure message -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>Fail!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {!! Session::get('fails') !!}
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.priority') !!}</h3>
        <div class="card-tools d-flex">
             <a href="{{route('priority.create')}}" class="btn btn-secondary btn-tool"> 
                <span class="fa-solid fa-plus"></span>&nbsp;{{Lang::get('lang.create_ticket_priority')}}
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="test" style="border-bottom:1px solid #F4F4F4;padding-bottom: 10px">
            <a class="right" title="" data-bs-placement="right" data-bs-toggle="tooltip" href="#" data-bs-original-title="{{Lang::get('lang.active_user_can_select_the_priority_while_creating_ticket')}}">

                <span class="lead" >{!! Lang::get('lang.user_priority_status') !!}</span>
           </a>

            <div class="btn-group" id="toggle_event_editing" style="float: right; margin-bottom: 10">
                <button type="button"  class="btn {{$user_status->status == '0' ? 'btn-info' : 'btn-secondary'}} locked_active">{{Lang::get('lang.inactive')}}</button>
                <button type="button"  class="btn {{$user_status->status == '1' ? 'btn-info' : 'btn-secondary'}} unlocked_inactive">{{Lang::get('lang.active')}}</button>
            </div>
        </div>
        <div class="priority-table" style="padding-top: 10px">
            <table id="priorityTable" class="table table-bordered w-100 d-table">
                <thead>
                    <tr>
                        <th>{{Lang::get('lang.priority')}}</th>
                        <th>{{Lang::get('lang.priority_desc')}}</th>
                        <th>{{Lang::get('lang.priority_color')}}</th>
                        <th>{{Lang::get('lang.status')}}</th>
                        <th>{{Lang::get('lang.action')}}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <script type="text/javascript">
                jQuery(document).ready(function () {
                    jQuery('#priorityTable').dataTable({
                        "sPaginationType": "full_numbers",
                        "bProcessing": true,
                        "bServerSide": true,
                        "ajax": {
                            url: "{{route('priority.index1')}}"
                        },
                        "columns": [
                            {data: "priority"},
                            {data: "priority_desc"},
                            {data: "priority_color"},
                            {data: "status"},
                            {data: "action"}
                        ]
                    });
                });
            </script>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('a').tooltip()
</script>

<script>
    function confirmDelete(priority_id) {
        var r = confirm('Are you sure?');
        if (r == true) {
            // alert('{!! url("ticket_priority") !!}/' + priority_id + '/destroy');
            window.location = '{!! url("ticket/priority") !!}/' + priority_id + '/destroy';
            //    $url('ticket_priority/' . $model->priority_id . '/destroy')
        } else {
            return false;
        }
    }
</script>
<script>
    $('#toggle_event_editing button').click(function () {

        var user_settings_priority=1;
         var user_settings_priority=0;
        if ($(this).hasClass('locked_active') ) {
         

            user_settings_priority = 0
        } if ( $(this).hasClass('unlocked_inactive')) {
          
            user_settings_priority = 1;
        }

        /* reverse locking status */
        $('#toggle_event_editing button').eq(0).toggleClass('locked_inactive locked_active btn-secondary btn-info');
        $('#toggle_event_editing button').eq(1).toggleClass('unlocked_inactive unlocked_active btn-info btn-secondary');
        $.ajax({
            type: 'post',
            url: '{{route("user.priority.index")}}',
            data: {
                "_token": "{{ csrf_token() }}",
                user_settings_priority: user_settings_priority},
            success: function (result) {
                // with('success', Lang::get('lang.approval_settings-created-successfully'));
                // alert("Hi, testing");
                alert(result);
                location.reload(); 
            }
        });
    });
</script>
@stop