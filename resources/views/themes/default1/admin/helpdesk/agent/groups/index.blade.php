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

@section('groups')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{{Lang::get('lang.groups')}}</h3>
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
<div class="alert alert-success alert-dismissible">
    <i class="fa  fa-circle-check"></i>
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
        <h3 class="card-title">{!! Lang::get('lang.list_of_groups') !!}</h3>
        <div class="card-tools d-flex">
            <a href="{{route('groups.create')}}" class="btn btn-secondary btn-tool">
                <span class="fa-solid fa-plus"></span>&nbsp;{{Lang::get('lang.create_group')}}
            </a>        
        </div>
    </div>

    <div class="card-body">
        
        <!-- Table -->
        <table class="table table-bordered dataTable" style="overflow:scroll;">
            <tr>
                <th>{{Lang::get('lang.group_name')}}</th>
                <th>{{Lang::get('lang.status')}}</th>
                <th>{{Lang::get('lang.action')}}</th>
            </tr>
            @foreach($groups as $group)
            <tr>
                <td><a href="{{route('groups.edit', $group->id)}}"> {{$group -> name }}</a></td>
                <td>
                    @if($group->group_status=='1')
                    <span style="color:green">{{'Active'}}</span>
                    @else
                    <span style="color:red">{{'Inactive'}}</span>
                    @endif
                <td>
                    {!! html()->form('DELETE', route('groups.destroy', [$group->id]))->open() !!}
                    <a href="{{route('groups.edit', $group->id)}}" class="btn btn-primary btn-xs"><i class="fa-solid fa-pen-to-square"> </i> {{trans('lang.edit')}}</a>
                    <!-- To pop up a confirm Message -->
                    {!! html()->button('<i class="fa-solid fa-trash"> </i>'.trans('lang.delete'))->class('btn btn-danger btn-xs')->attributes(['type' => 'submit', 'onclick' => 'return confirm("Are you sure?")']) !!}
                    {!! html()->closeModelForm() !!}
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@stop
