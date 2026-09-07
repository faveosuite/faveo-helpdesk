@extends('themes.default1.admin.layout.admin')

@section('Tickets')
class="nav-link active"
@stop

@section('ticket-menu-parent')
class="nav-item menu-open"
@stop

@section('ticket-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('alert')
class="nav-link active"
@stop


@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{{Lang::get('lang.settings')}}</h3>
@stop
<!-- /header -->
<!-- breadcrumbs -->
@section('breadcrumbs')
<ol class="breadcrumb">

</ol>
@stop
<!-- /breadcrumbs -->
<!-- content -->
@section('content')
<!-- open a form -->
{!! html()->modelForm($alerts, 'PATCH', url('postalert/'.$alerts->id))->open() !!}
@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa-solid fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {!!Session::get('success')!!}
</div>
@endif
<!-- failure message -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i><button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <b>{!! lang::get('lang.alert') !!}!</b><br/>
    {!!Session::get('fails')!!}
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{{Lang::get('lang.alert_notices_setitngs')}}</h3> 
    </div>

    <div class="card-body">
        <div class="row">
            <!-- left column -->
            <div class="col-md-6">
                <!-- general form elements -->
                <div class="card card-light">
                    <div class="card-header">
                        <h3 class="card-title">{{Lang::get('lang.new_ticket_alert')}}</h3>
                    </div><!-- /.box-header -->
                    <!-- form start -->
                    <div class="card-body">
                        <div class="mb-3">
                            <!-- Status:     Enable   Disable     -->
                            {!! html()->label(Lang::get('lang.status').":", 'ticket_status') !!}&nbsp;&nbsp;
                            {!! html()->radio('ticket_status', null, 1) !!} {!! Lang::get('lang.enable') !!} &nbsp;&nbsp; {!! html()->radio('ticket_status', null, 0) !!}  {!! Lang::get('lang.disable') !!}
                        </div>
                        <div class="mb-3">
                            <!-- Admin Email -->
                            {!! html()->checkbox('ticket_admin_email', null, 1) !!}
                            {!! html()->label(Lang::get('lang.admin_email_2'), 'ticket_admin_email') !!}
                        </div>
                        <!-- Department Members -->
                        <div class="mb-3">
                            {!! html()->checkbox('ticket_department_member', null, 1) !!}
                            {!! html()->label(Lang::get('lang.department_members'), 'ticket_department_member') !!}
                        </div>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
                <!-- /.box -->
            </div><!--/.col (left) -->
            <div class="col-md-6">
                <div class="card card-light">
                    <div class="card-header">
                        <h3 class="card-title">{{Lang::get('lang.ticket_assignment_alert')}}</h3>
                    </div><!-- /.box-header -->
                    <div class="card-body">
                        <!-- Status:     Enable      Disable      -->
                        <div class="mb-3">
                            {!! html()->label(Lang::get('lang.status').":", 'assignment_status') !!}
                            {!! html()->radio('assignment_status', null, 1) !!} {!! Lang::get('lang.enable') !!} &nbsp;&nbsp; {!! html()->radio('assignment_status', null, 0) !!}  {!! Lang::get('lang.disable') !!}
                        </div>
                        <!-- Assigned Agent / Team -->
                        <div class="mb-3">
                            {!! html()->checkbox('assignment_assigned_agent', null, 1) !!}
                            {!! html()->label(Lang::get('lang.agent'), 'assignment_assigned_agent') !!}
                        </div>
                        <!-- Team Members -->
                        <div class="mb-3">
                            {!! html()->checkbox('assignment_team_member', null, 1) !!}
                            {!! html()->label(Lang::get('lang.team_members'), 'assignment_team_member') !!}
                        </div>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div><!--/.col (left) -->
        </div>
    </div>

    <div class="card-footer">
        {!! html()->submit(Lang::get('lang.submit'))->class(' btn btn-primary') !!}
    </div>
</div>
@stop
