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

@section('departments')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h1>{{Lang::get('lang.departments')}}</h1>
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
    <i class="fa  fa-check-circle"></i>
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    {!! Session::get('success') !!}
</div>
@endif
<!-- failure message -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissable">
    <i class="fa fa-ban"></i>
    <b>{!! Lang::get('lang.fails') !!}!</b>
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    {!! Session::get('fails') !!}
</div>
@endif

<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.list_of_departments') !!}</h3>

        <div class="card-tools">
            
            <a href="{{route('departments.create')}}" class="btn btn-default btn-tool">
                <span class="fas fa-plus"></span>&nbsp;{{Lang::get('lang.create_a_department')}}
            </a>        
        </div>    
    </div>

    <div class="card-body">
       
        <!-- table -->
        <table class="table table-bordered dataTable" style="overflow:scroll;">
            <tr>
                <th>{{Lang::get('lang.name')}}</th>
                <th>{{Lang::get('lang.type')}}</th>
                <th>{{Lang::get('lang.sla_plan')}}</th>
                <th>{{Lang::get('lang.department_manager')}}</th>
                <th>{{Lang::get('lang.action')}}</th>
            </tr>
            <?php
            $default_department = App\Model\helpdesk\Settings\System::where('id', '=', '1')->first();
            $default_department = $default_department->department;
            ?>
            @foreach($departments as $department)
            <tr>
                <td><a href="{{route('departments.edit', $department->id)}}"> {{$department -> name }}
                        @if($default_department == $department->id)
                        ( Default )
                        <?php
                        $disable = 'disabled';
                        ?>
                        @else
                        <?php
                        $disable = '';
                        ?>
                        @endif
                    </a></td>
                <td>
                    @if($department->type=='1')
                    <span style="color:green">{!! Lang::get('lang.public') !!}</span>
                    @else
                    <span style="color:red">{!! Lang::get('lang.private') !!}</span>
                    @endif
                </td>
                <?php
                if ($department->manager == 0) {
                    $manager = "";
                } else {
                    $manager = App\User::whereId($department->manager)->first();
                    $manager = $manager->full_name;
                }

                if ($department->sla == null) {
                    $sla = "";
                } else {
                    $sla = App\Model\helpdesk\Manage\Sla_plan::whereId($department->sla)->first();
                    $sla = $sla->grace_period;
                }
                ?>

                <td>{{ $sla }}</td>
                <td>{{ $manager }}</td>
                <td>
                    {!! html()->form('DELETE', route('departments.destroy', [$department->id]))->open() !!}
                    <a href="{{route('departments.edit', $department->id)}}" class="btn btn-primary btn-xs"><i class="fas fa-edit"> </i> {!! Lang::get('lang.edit') !!}</a>
                    {{-- @if($default_department == $department->id) --}}
                    {{-- @else --}}
                    <!-- To pop up a confirm Message -->
                   
                    @if($default_department == $department->id)
                    {!! html()->button('<i class="fas fa-trash"> </i> '.Lang::get('lang.delete'))->class('btn btn-danger btn-xs '.$disable) !!}
                    @else
                     {!! html()->button('<i class="fas fa-trash"> </i> '.Lang::get('lang.delete'))->class('btn btn-danger btn-xs')->attributes(['type' => 'submit', 'onclick' => 'return confirm("Are you sure?")']) !!}
                    @endif

                    {{-- @endif --}}

                    {!! html()->closeModelForm() !!}
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@stop