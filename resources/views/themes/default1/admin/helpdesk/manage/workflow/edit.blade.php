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

@section('workflow')
class="nav-link active"
@stop

@section('HeadInclude')
@stop

<!-- header -->
@section('PageHeader')
<h1>{!! Lang::get('lang.edit_workflow') !!}</h1>
@stop
<!-- /header -->
<!-- breadcrumbs -->
@section('breadcrumbs')
<ol class="breadcrumb">
    <li><a href="{!! URL::route('setting') !!}"><i class="fas fa-tachometer-alt"></i> {!! Lang::get('lang.home') !!}</a></li>
    <li><a href="{!! URL::route('workflow') !!}">{!! Lang::get('lang.ticket_workflow') !!}</a></li>
    <li class="active"><a href="">{!! Lang::get('lang.edit_workflow') !!}</a></li>
</ol>
@stop
<!-- /breadcrumbs -->
<!-- content -->
@section('content')
<form class="form-horizontal" action="{!! URL::route('workflow.update', $id) !!}" method="POST">
    {{ csrf_field() }}
    <!-- check whether success or not -->
    @if(Session::has('success'))
    <div class="alert alert-success alert-dismissable">
        <i class="fas fa-check-circle"></i>
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {{Session::get('success')}}
    </div>
    @endif
    <!-- failure message -->
    @if(Session::has('fails'))
    <div class="alert alert-danger alert-dismissable">
        <i class="fas fa-ban"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <b>{!! Lang::get('lang.alert') !!} !</b><br>
        {{Session::get('fails')}}
    </div>
    @endif
    @if(Session::has('errors'))
    <div class="alert alert-danger alert-dismissable">
        <i class="fas fa-ban"></i>
        <b>{!! Lang::get('lang.alert') !!}!</b>
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <br/>
        @if($errors->first('name'))
        <li class="error-message-padding">{!! $errors->first('name', ':message') !!}</li>
        @endif
        @if($errors->first('execution_order'))
        <li class="error-message-padding">{!! $errors->first('execution_order', ':message') !!}</li>
        @endif
        @if($errors->first('target_channel'))
        <li class="error-message-padding">{!! $errors->first('target_channel', ':message') !!}</li>
        @endif
        @if($errors->first('rule'))
        <li class="error-message-padding">{!! $errors->first('rule', ':message') !!}</li>
        @endif
        @if($errors->first('action'))
        <li class="error-message-padding">{!! $errors->first('action', ':message') !!}</li>
        @endif
    </div>
    @endif
    <div class="card card-light">
        <!-- /.box-header -->
        <div class="card-header">
            <h3 class="card-title">{!! Lang::get('lang.edit_workflow') !!}</h3>    
        </div>
        
        <div class="card-body">

            <div class="row">
                
                <div class="form-group col-sm-6 {!! $errors->has('name') ? 'has-error' : '' !!}">
                    <label for="inputName">{!! Lang::get('lang.name') !!}</label>
                    <input type="text" class="form-control" placeholder="Name" id="name" name="name" value="{!! $workflow->name !!}" required>
                </div>
                
                <div class="form-group col-sm-6 {!! $errors->has('status') ? 'has-error' : '' !!}">
                    <label> {!! lang::get('lang.status') !!}</label>
                    <div>
                        <input type="radio" id="inputEmail2" name="status" value="1" <?php
                        if ($workflow->status == 1) {
                            echo "checked";
                        }
                        ?> >&nbsp;&nbsp;{!! Lang::get('lang.active') !!}&nbsp;&nbsp;
                        <input type="radio" id="inputEmail1" name="status" value="0" <?php
                        if ($workflow->status == 0) {
                            echo "checked";
                        }
                        ?> >&nbsp;&nbsp;{!! Lang::get('lang.inactive') !!}&nbsp;&nbsp;
                    </div>
                </div>
            </div>
            
            <div class="row">
                
                <div class="form-group col-sm-6 {!! $errors->has('execution_order') ? 'has-error' : '' !!}">
                    <label for="Exceution">{!! Lang::get('lang.execution_order') !!}</label>
                    <input type="number" class="form-control" id="execution_order" name="execution_order" placeholder="{!! Lang::get('lang.execution_order') !!}" value="{!! $workflow->order !!}" required>
                </div>

                <div class="form-group col-sm-6 {!! $errors->has('target_channel') ? 'has-error' : '' !!}">
                    <label>{!! Lang::get('lang.target_channel') !!}</label>
                    <select class="form-control" name="target_channel" required>
                        <option value=""> -- {!! Lang::get('lang.select_a_channel') !!} -- </option>
                        <option value="A-0" <?php
                        if ($workflow->target == "A-0") {
                            echo "selected='selected'";
                        }
                        ?> >Any</option>
                        <option value="A-1" <?php
                        if ($workflow->target == "A-1") {
                            echo "selected='selected'";
                        }
                        ?> >Web Forms</option>
                        <option value="A-4" <?php
                        if ($workflow->target == "A-4") {
                            echo "selected='selected'";
                        }
                        ?> >API Calls</option>
                        <option value="A-2" <?php
                        if ($workflow->target == "A-2") {
                            echo "selected='selected'";
                        }
                        ?> >Emails</option>

                    </select>
                </div>
            </div>

            <div class="card card-light">
                
                <div class="card-header">
                    <h3 class="card-title">{!! Lang::get('lang.workflow_rules') !!}</h3>
                </div>  

                <div class="card-body">
                    <table  class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <td>{!! Lang::get('lang.rules') !!}</td>
                                <td>{!! Lang::get('lang.condition') !!}</td>
                                <td>{!! Lang::get('lang.statement') !!}</td>
                                <td>{!! Lang::get('lang.action') !!}</td>
                            </tr>
                        </thead>
                        <tbody class="button1">
                            <?php $j = 0; ?>
                            @foreach($workflow_rules as $workflow_rule)
                            <?php $j++; ?>
                            <tr id="firstdata{!! $j !!}">
                                <td>
                                    <select class="form-control" name="rule[{!! $j-1 !!}][a]" required>
                                        <option value="">-- {!! Lang::get('lang.select_one') !!} --</option>
                                        <option value="email" <?php
                                        if ($workflow_rule->matching_scenario == 'email') {
                                            echo "selected='selected'";
                                        }
                                        ?> >{!! Lang::get('lang.email') !!}</option>
                                        <option value="email_name" <?php
                                        if ($workflow_rule->matching_scenario == 'email_name') {
                                            echo "selected='selected'";
                                        }
                                        ?> >{!! Lang::get('lang.email_name') !!}</option>
                                        <option value="subject" <?php
                                        if ($workflow_rule->matching_scenario == 'subject') {
                                            echo "selected='selected'";
                                        }
                                        ?>>{!! Lang::get('lang.subject') !!}</option>
                                        <option value="message"  <?php
                                        if ($workflow_rule->matching_scenario == 'message') {
                                            echo "selected='selected'";
                                        }
                                        ?> >{!! Lang::get('lang.message') !!}/{!! Lang::get('lang.body') !!}</option>
                                    </select>
                                </td>
                                <td>
                                    <select class="form-control" name="rule[{!! $j-1 !!}][b]" required>
                                        <option value="">-- {!! Lang::get('lang.select_one') !!} --</option>
                                        <option value="equal" <?php
                                        if ($workflow_rule->matching_relation == 'equal') {
                                            echo "selected='selected'";
                                        }
                                        ?> >{!! Lang::get('lang.equal_to') !!}</option>
                                        <option value="not_equal" <?php
                                        if ($workflow_rule->matching_relation == 'not_equal') {
                                            echo "selected='selected'";
                                        }
                                        ?> >{!! Lang::get('lang.not_equal_to') !!}</option>
                                        <option value="contains" <?php
                                        if ($workflow_rule->matching_relation == 'contains') {
                                            echo "selected='selected'";
                                        }
                                        ?> >{!! Lang::get('lang.contains') !!}</option>
                                        <option value="dn_contain" <?php
                                        if ($workflow_rule->matching_relation == 'dn_contain') {
                                            echo "selected='selected'";
                                        }
                                        ?> >{!! Lang::get('lang.does_not_contain') !!}</option>
                                        <option value="starts" <?php
                                        if ($workflow_rule->matching_relation == 'starts') {
                                            echo "selected='selected'";
                                        }
                                        ?> >{!! Lang::get('lang.starts_with') !!}</option>
                                        <option value="ends" <?php
                                        if ($workflow_rule->matching_relation == 'ends') {
                                            echo "selected='selected'";
                                        }
                                        ?> >{!! Lang::get('lang.ends_with') !!}</option>
                                        <!--                                                        <option value="match" <?php
                                        if ($workflow_rule->matching_relation == 'match') {
                                            echo "selected='selected'";
                                        }
                                        ?> >Match Regular Expressions</option>
                                                                                                <option value="not_match" <?php
                                        if ($workflow_rule->matching_relation == 'not_match') {
                                            echo "selected='selected'";
                                        }
                                        ?> >Does not match Regular Expression</option>-->
                                    </select>
                                </td>
                                <td>
                                    <input class="form-control" type="text" name="rule[{!! $j-1 !!}][c]" value="{!! $workflow_rule->matching_value !!}" required>
                                </td>
                                <td style="text-align: center">
                                    <div class="tools"> 
                                        <span class="btnRemove1" data-toggle="modal" data-target="#">
                                            <a data-toggle="tooltip" data-placement="top" title="{!! Lang::get('lang.delete') !!}" onclick="document.getElementById('firstdata{!! $j !!}').innerHTML = ''">
                                                <i class="fas fa-trash text-red"></i>
                                            </a>
                                        </span> 
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-2">
                        <div class="float-right" >
                            <a class="btn btn-primary btnAdd1" href="javascript:;"><i class="fas fa-plus"></i> {!! Lang::get('lang.add') !!}</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-light">
                
                <div class="card-header">
                    <h3 class="card-title">{!! Lang::get('lang.workflow_action') !!}</h3>
                </div>  

                <div class="card-body">
                    <table  class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <td>{!! Lang::get('lang.condition') !!}</td>
                                <td>{!! Lang::get('lang.rules') !!}</td>
                                <td>{!! Lang::get('lang.action') !!}</td>
                            </tr>
                        </thead>
                        <tbody class="buttons">
                            <?php $i = 0; ?>
                            @foreach($workflow_actions as $workflow_action)
                            <?php $i++; ?>
                            <tr id="seconddata{!! $i !!}">
                                <td>
                                    <select class="form-control" onChange="selectdata({!! $i !!})" id="selected{!! $i !!}" name="action[{!! $i !!}][a]" required>
                                        <option value="">-- {!! Lang::get('lang.select_an_action') !!} --</option>
                                        <optgroup label="{{ trans('lang.ticket') }}">
                                            <option value="reject" <?php
                                            if ($workflow_action->condition == 'reject') {
                                                echo "selected='selected'";
                                            }
                                            ?> >{!! Lang::get('lang.reject_ticket') !!}</option>                        
                                            <option value="department" <?php
                                            if ($workflow_action->condition == 'department') {
                                                echo "selected='selected'";
                                            }
                                            ?> >{!! Lang::get('lang.set_department') !!}</option>
                                            <option value="priority" <?php
                                            if ($workflow_action->condition == 'priority') {
                                                echo "selected='selected'";
                                            }
                                            ?> >{!! Lang::get('lang.set_priority') !!}</option>
                                            <option value="sla" <?php
                                            if ($workflow_action->condition == 'sla') {
                                                echo "selected='selected'";
                                            }
                                            ?> >{!! Lang::get('lang.set_sla_plan') !!}</option>
                                            <option value="team" <?php
                                            if ($workflow_action->condition == 'team') {
                                                echo "selected='selected'";
                                            }
                                            ?> >{!! Lang::get('lang.assign_team') !!}</option>
                                            <option value="agent" <?php
                                            if ($workflow_action->condition == 'agent') {
                                                echo "selected='selected'";
                                            }
                                            ?> >{!! Lang::get('lang.assign_agent') !!}</option>
                                            <option value="helptopic" <?php
                                            if ($workflow_action->condition == 'helptopic') {
                                                echo "selected='selected'";
                                            }
                                            ?> >{!! Lang::get('lang.set_help_topic') !!}</option>
                                            <option value="status" <?php
                                            if ($workflow_action->condition == 'status') {
                                                echo "selected='selected'";
                                            }
                                            ?> >{!! Lang::get('lang.set_ticket_status') !!}</option>
                                        </optgroup> 
                                    </select>
                                </td>
                                <td id="fill{!! $i !!}">
                                    <?php
                                    if ($workflow_action->condition == 'reject') {
                                        echo "<input type='hidden' name='action[" . $i . "][b]' class='form-control' value='reject'><span text-red>Reject</span>";
                                    } elseif ($workflow_action->condition == 'department') {
                                        $departments = App\Model\helpdesk\Agent\Department::all();
                                        $var = "<select name='action[" . $i . "][b]' class='form-control' required>";
                                        foreach ($departments as $department) {
                                            if ($workflow_action->action == $department->id) {
                                                $depart = "selected";
                                            } else {
                                                $depart = "";
                                            }
                                            $var .= "<option value='" . $department->id . "' " . $depart . ">" . $department->name . "</option>";
                                        }
                                        $var .= "</select>";
                                        echo $var;
                                    } elseif ($workflow_action->condition == 'priority') {
                                        $priorities = App\Model\helpdesk\Ticket\Ticket_Priority::all();
                                        $var = "<select name='action[" . $i . "][b]' class='form-control' required>";
                                        foreach ($priorities as $priority) {
                                            if ($workflow_action->action == $priority->priority_id) {
                                                $priority1 = "selected";
                                            } else {
                                                $priority1 = "";
                                            }
                                            $var .= "<option value='" . $priority->priority_id . "' " . $priority1 . ">" . $priority->priority_desc . "</option>";
                                        }
                                        $var .= "</select>";
                                        echo $var;
                                    } elseif ($workflow_action->condition == 'sla') {
                                        $sla_plans = App\Model\helpdesk\Manage\Sla_plan::where('status', '=', 1)->get();
                                        $var = "<select name='action[" . $i . "][b]' class='form-control' required>";
                                        foreach ($sla_plans as $sla_plan) {
                                            if ($workflow_action->action == $sla_plan->id) {
                                                $sla = "selected";
                                            } else {
                                                $sla = "";
                                            }
                                            $var .= "<option value='" . $sla_plan->id . "' " . $sla . ">" . $sla_plan->grace_period . "</option>";
                                        }
                                        $var .= "</select>";
                                        echo $var;
                                    } elseif ($workflow_action->condition == 'team') {
                                        $teams = App\Model\helpdesk\Agent\Teams::where('status', '=', 1)->get();
                                        $var = "<select name='action[" . $i . "][b]' class='form-control' required>";
                                        foreach ($teams as $team) {
                                            if ($workflow_action->action == $team->id) {
                                                $team1 = "selected";
                                            } else {
                                                $team1 = "";
                                            }
                                            $var .= "<option value='" . $team->id . "' " . $team1 . ">" . $team->name . "</option>";
                                        }
                                        $var .= "</select>";
                                        echo $var;
                                    } elseif ($workflow_action->condition == 'agent') {
                                        $users = App\User::where('role', '!=', 'user')->where('active', '=', 1)->get();
                                        $var = "<select name='action[" . $i . "][b]' class='form-control' required>";
                                        foreach ($users as $user) {
                                            if ($workflow_action->action == $user->id) {
                                                $user1 = "selected";
                                            } else {
                                                $user1 = "";
                                            }
                                            $var .= "<option value='" . $user->id . "' " . $user1 . ">" . $user->first_name . " " . $user->last_name . "</option>";
                                        }
                                        $var .= "</select>";
                                        echo $var;
                                    } elseif ($workflow_action->condition == 'helptopic') {
                                        $help_topics = App\Model\helpdesk\Manage\Help_topic::where('status', '=', 1)->get();
                                        $var = "<select name='action[" . $i . "][b]' class='form-control' required>";
                                        foreach ($help_topics as $help_topic) {
                                            if ($workflow_action->action == $help_topic->id) {
                                                $help_topic1 = "selected";
                                            } else {
                                                $help_topic1 = "";
                                            }
                                            $var .= "<option value='" . $help_topic->id . "' " . $help_topic1 . ">" . $help_topic->topic . "</option>";
                                        }
                                        $var .= "</select>";
                                        echo $var;
                                    } elseif ($workflow_action->condition == 'status') {
                                        $ticket_status = App\Model\helpdesk\Ticket\Ticket_Status::all();
                                        $var = "<select name='action[" . $i . "][b]' class='form-control' required>";
                                        foreach ($ticket_status as $status) {
                                            if ($workflow_action->action == $status->id) {
                                                $status1 = "selected";
                                            } else {
                                                $status1 = "";
                                            }
                                            $var .= "<option value='" . $status->id . "' " . $status1 . ">" . $status->name . "</option>";
                                        }
                                        $var .= "</select>";
                                        echo $var;
                                    }
                                    ?>

                                </td>
                                <td style="text-align: center">
                                    <div class="tools"> 
                                        <span class="btnRemove" data-toggle="modal" data-target="#">
                                            <a data-toggle="tooltip" data-placement="top" title="Delete" onclick="document.getElementById('seconddata{!! $i !!}').innerHTML = ''">
                                                <i class="fas fa-trash text-red"></i>
                                            </a>
                                        </span> 
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-2">
                        <div class="float-right">
                            <a class="btn btn-primary btnAdd" href="javascript:;"><i class="fas fa-plus"></i> {!! Lang::get('lang.add') !!}</a>                                            
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label>{!! Lang::get('lang.internal_notes') !!}</label>
                <textarea name="internal_note" class="textarea" placeholder="Please Enter an internal note for your team!" style="width: 100%; height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;"></textarea>       
            </div>
        </div>

        <div class="card-footer">
            <input type="submit" class="btn btn-primary" value="{!! Lang::get('lang.submit') !!}">
        </div>
    </div>
</form>
<script>
            $(function() {
            $("#example1").DataTable();
                    $('#example2').DataTable({
            "paging": true,
                    "lengthChange": false,
                    "searching": false,
                    "ordering": true,
                    "info": true,
                    "autoWidth": false
            });
            });
            function getSelectVal(val) {

            $.ajax({
            type: "POST",
                    url: "",
                    data: 'select_box=' + val,
                    success: function(data) {
                    $("#select-list").html(data);
                    }
            });
            }


    $(document).ready(function() {
    var x = 0;
            var n = {!! $i !!};
            $('.btnAdd').click(function() {
    n++;
            $('.buttons').append('<tr id="firstdata1">' +
            '<td>' +
            '<select class="form-control" onChange="selectdata(' + n + ')" name="action[' + n + '][a]" id="selected' + n + '" required>' +
            '<option value="">-- {{ Lang::get('lang.select_an_action') }} --</option>' +
            '<optgroup label="Ticket">' +
            '<option value="reject">{{ Lang::get('lang.reject_ticket') }}</option>' +
            '<option value="department">{{ Lang::get('lang.set_department') }}</option>' +
            '<option value="priority">{{ Lang::get('lang.set_priority') }}</option>' +
            '<option value="sla">{{ Lang::get('lang.set_sla_plan') }}</option>' +
            '<option value="team">{{ Lang::get('lang.assign_team') }}</option>' +
            '<option value="agent">{{ Lang::get('lang.assign_agent') }} </option>' +
            '<option value="helptopic">{{ Lang::get('lang.set_help_topic') }} </option>' +
            '<option value="status">{{ Lang::get('lang.set_ticket_status') }} </option>' +
            '</select>' +
            '</td>' +
            '<td id="fill' + n + '">' +
            '</td>' +
            '<td style="text-align: center">' +
            '<div class="tools">' +
            '<span class="btnRemove" data-toggle="modal" data-target="#">' +
            '<a data-toggle="tooltip" data-placement="top" title="Delete">' +
            '<i class="fas fa-trash text-red"></i>' +
            '</a>' +
            '</span>' +
            '</div>' +
            '</td>' +
            '</tr>'); // end append
            $('div .btnRemove').last().click(function(e) {
    e.preventDefault();
            $(this).closest('tr').remove();
            x--;
    });
    });
    });
            $(document).ready(function() {
    var x = 0;
            var n = {!! $j !!};
            $('.btnAdd1').click(function() {
    n++;
            $('.button1').append('<tr>' +
            '<td>' +
            '<select class="form-control" name="rule[' + n + '][a]" required>' +
            '<option>-- {{ Lang::get('lang.select_one') }} --</option>' +
            '<option value="email">{{ Lang::get('lang.email') }}</option>' +
            '<option value="email_name">{{ Lang::get('lang.email_name') }}</option>' +
            '<option value="subject">{{ Lang::get('lang.subject') }}</option>' +
            '<option value="message">{{ Lang::get('lang.message') }}/{{ Lang::get('lang.body') }}</option>' +
            '</select>' +
            '</td>' +
            '<td>' +
            '<select class="form-control" name="rule[' + n + '][b]" required>' +
            '<option value="">-- {{ Lang::get('lang.select_one') }} --</option>' +
            '<option value="equal">{{ Lang::get('lang.equal_to') }}</option>' +
            '<option value="not_equal">{{ Lang::get('lang.not_equal_to') }}</option>' +
            '<option value="contains">{{ Lang::get('lang.contains') }}</option>' +
            '<option value="dn_contain">{{ Lang::get('lang.does_not_contain') }}</option>' +
            '<option value="starts">{{ Lang::get('lang.starts_with') }}</option>' +
            '<option value="ends">{{ Lang::get('lang.ends_with') }}</option>' +
            '</select>' +
            '</td>' +
            '<td> <input class="form-control" type="text" name="rule[' + n + '][c]" required> </td>' +
            '<td style="text-align: center">' +
            '<div class="tools"> <span class="btnRemove1" data-toggle="modal" data-target="#"><a data-toggle="tooltip" data-placement="top" title="Delete"><i class="fas fa-trash text-red"></i></a></span> </div>' +
            '</td>' +
            '</tr>'); // end append
            $('div .btnRemove1').last().click(function(e) {
    e.preventDefault();
            $(this).closest('tr').remove();
            x--;
    });
    });
    });
            function selectdata(id) {
            var selected_data = document.getElementById('selected' + id).value;
                    $.ajax({
                    url: "{!! url('workflow/action-rule') !!}" + "/" + id,
                            type: "get",
                            data: {option: selected_data},
                            headers: {
                            'X-CSRF-Token': $('meta[name="_token"]').attr('content')
                            },
                            success: function(data) {
                            //adds the echoed response to our container
                            $("#fill" + id).html(data);
                            }
                    });
            }

</script>

@stop
