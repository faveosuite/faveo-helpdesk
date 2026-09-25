@extends('themes.default1.admin.layout.admin')

@section('Staffs')
class="nav-link active"
@stop

@section('staff-menu-parent')
class="nav-item menu-open"
@stop

@section('staff-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('teams')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{{$teams->name}}</h3>
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

<?php
 if($team_lead_name= App\User::whereId($teams->team_lead)->first())
          {
            $team_lead = $team_lead_name->first_name . " " . $team_lead_name->last_name;
             // $assign_team_agent=App\Model\helpdesk\Agent\Assign_team_agent::all();
            // $total_members = $assign_team_agent->where('team_id',$id)->count();
        }
?>
            
<div class="card card-light">

    <div class="card-header">
    @if($team_lead_name)
        <h3 class="lead card-title">{!! Lang::get('lang.team_lead') !!} : {!! $team_lead !!} </h3>
     @endif
        <h3 class="lead card-title"> &nbsp;| {!! Lang::get('lang.status') !!} : <?php if($teams->status == 1) { $stat = Lang::get('lang.active'); } elseif($teams->status == 0) { $stat = Lang::get('lang.inactive'); } ?>{!! $stat !!} </h3>
        
        <div class="card-tools d-flex">
            <a href="{{URL::route('teams.index')}}" class="btn btn-secondary btn-tool">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> {{Lang::get('lang.go_back')}}
            </a>
        </div>
    </div>
    <input type="hidden" name="show_id" value={{$id}}>
    <!-- /.box-header -->
    <div class="card-body">             
        {{-- Was Datatable::table()->render(), the helper of the removed bllim/datatables
             package ('Class "Datatable" not found'). Same server-side DataTables pattern
             as the other lists, e.g. common/template/inbox.blade.php. --}}
        <div class="table-responsive">
            <table id="teamMembersTable" class="table table-bordered w-100 d-table">
                <thead>
                    <tr>
                        <th>{{Lang::get('lang.user_name')}}</th>
                        <th>{{Lang::get('lang.name')}}</th>
                        <th>{{Lang::get('lang.status')}}</th>
                        <th>{{Lang::get('lang.group')}}</th>
                        <th>{{Lang::get('lang.depertment')}}</th>
                        <th>{{Lang::get('lang.role')}}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@stop

@section('FooterInclude')
<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('#teamMembersTable').dataTable({
            "sPaginationType": "full_numbers",
            "bProcessing": true,
            "bServerSide": true,
            "ajax": {
                url: "{{ route('teams.getshow.list', $id) }}"
            },
            "columns": [
                {data: "user_name", name: "users.user_name"},
                {data: "first_name", name: "users.first_name"},
                {data: "active", name: "users.active", searchable: false},
                {data: "assign_group", name: "users.assign_group", searchable: false},
                {data: "primary_dpt", name: "users.primary_dpt", searchable: false},
                {data: "role", name: "users.role"}
            ]
        });
    });
</script>
@stop
