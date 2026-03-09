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

@section('agents')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h1>{{ Lang::get('lang.agents')}} </h1>
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
     <!-- check whether success or not -->
@if(Session::has('success'))
<div class="alert alert-success alert-dismissable">
    <i class="fas  fa-check-circle"></i>
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    {{Session::get('success')}}
</div>
@endif
<!-- failure message -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissable">
    <i class="fas fa-ban"></i>
    <b>{!! Lang::get('lang.fails') !!}!</b>
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    {{Session::get('fails')}}
</div>
@endif
<!-- Warning Message -->
@if(Session::has('warning'))
<div class="alert alert-warning alert-dismissable">
    <i class="fas fa-exclamation-triangle"></i>
    <b>{!! Lang::get('lang.warning') !!}!</b>
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    {{Session::get('warning')}}
</div>
@endif

<div class="card card-light">
    
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.list_of_agents') !!} </h3>

        <div class="card-tools">
                    
            <a href="{{route('agents.create')}}" class="btn btn-default btn-tool">
                <span class="fas fa-plus"></span> &nbsp;{!! Lang::get('lang.create_an_agent') !!}
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php
        $user = App\User::where('role', '!=', 'user')->orderBy('id', 'ASC')->simplePaginate(10);
        ?>
        <!-- Agent table -->
        <table class="table table-bordered dataTable" style="overflow:hidden;">
            <tr>
                <th width="100px">{{Lang::get('lang.name')}}</th>
                <th width="100px">{{Lang::get('lang.user_name')}}</th>
                <th width="100px">{{Lang::get('lang.role')}}</th>
                <th width="100px">{{Lang::get('lang.status')}}</th>
                <th width="100px">{{Lang::get('lang.group')}}</th>
                <th width="100px">{{Lang::get('lang.department')}}</th>
                <th width="100px">{{Lang::get('lang.created')}}</th>
                {{-- <th width="100px">{{Lang::get('lang.lastlogin')}}</th> --}}
                <th width="100px">{{Lang::get('lang.action')}}</th>
            </tr>
            @foreach($user as $use)
            @if($use->role == 'admin' || $use->role == 'agent')
            <tr>
                <td><a href="{{route('agents.edit', $use->id)}}"> {!! $use->first_name !!} {!! " ". $use->last_name !!}</a></td>
                <td><a href="{{route('agents.edit', $use->id)}}"> {!! $use->user_name !!}</td>
                <?php
                if ($use->role == 'admin') {
                    echo '<td><button class="btn btn-success btn-xs">' . Lang::get('lang.admin') . '</button></td>';
                } elseif ($use->role == 'agent') {
                    echo '<td><button class="btn btn-primary btn-xs">' . Lang::get('lang.agent') . '</button></td>';
                }
                ?>
                <td>
                    @if($use->active=='1')
                    <span style="color:green">{!! Lang::get('lang.active') !!}</span>
                    @else
                    <span style="color:red">{!! Lang::get('lang.inactive') !!}</span>
                    @endif
                    <?php
                    $group = App\Model\helpdesk\Agent\Groups::whereId($use->assign_group)->first();
                    $department = App\Model\helpdesk\Agent\Department::whereId($use->primary_dpt)->first();
                    ?>
                <td>{{ $group->name }}</td>
                <td>{{ $department->name }}</td>
                <td>{{ UTC::usertimezone($use->created_at) }}</td>
                {{-- <td>{{$use->Lastlogin_at}}</td> --}}
                <td>
                    {!! html()->form('DELETE', route('agents.destroy', [$use->id]))->open() !!}
                    <a href="{{route('agents.edit', $use->id)}}" class="btn btn-primary btn-xs"><i class="fas fa-edit"> </i> {!! Lang::get('lang.edit') !!} </a>
                    <!-- To pop up a confirm Message -->
                    {{-- {!! html()->button(' <i class="fas fa-trash"> </i> '  . Lang::get('lang.delete'))->class('btn btn-danger btn-xs')->attributes(['type' => 'submit', 'onclick' => 'return confirm("Are you sure?")']) !!} --}}
                    {!! html()->closeModelForm() !!}
                </td>
            </tr>
            @endif
            @endforeach
        </table>
        <div class="float-end">
            {!! $user->links() !!}
        </div>
    </div>
</div>
@stop
