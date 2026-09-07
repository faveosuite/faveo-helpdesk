@extends('themes.default1.agent.layout.agent')

@section('Dashboard')
class="nav-link active"
@stop

@section('dashboard-bar')
active
@stop

@section('profile')
class="active"
@stop

@section('PageHeader')
<h3>{{Lang::get('lang.view-profile')}}</h3>
@stop

@section('profileimg')
<img src="{{Auth::user()->profile_pic}}" id="sidebar-profile-img" class="rounded-circle shadow-sm me-2 profile-img" alt="User Image">
@stop

@section('content')
    <style>
        .profile-img {
            width: 30px;
            height: 30px;
        }

        .card-contact {
            box-shadow: 0 0 1px rgba(0, 0, 0, .125), 0 1px 3px rgba(0, 0, 0, .2);
            margin-bottom: 1rem;
        }
    </style>


    @if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa-solid fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('success')}}
</div>
@endif
<!-- fail message -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!} !</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('fails')}}
</div>
@endif

<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.profile') !!}&nbsp;&nbsp;
            <a href="{{URL::route('agent-profile-edit')}}"><i class="fa-solid fa-fw fa-pen-to-square"> </i></a>
        </h3>
    </div>

    <?php
    if ($user->primary_dpt) {
        $dept = App\Model\helpdesk\Agent\Department::where('id', '=', $user->primary_dpt)->first();
        $dept = $dept->name;
    } else {
        $dept = "";
    }
    if ($user->assign_group) {
        $grp = App\Model\helpdesk\Agent\Groups::where('id', '=', $user->assign_group)->first();
        $grp = $grp->name;
    } else {
        $grp = "";
    }
    if ($user->agent_tzone) {
        $timezone = App\Model\helpdesk\Utility\Timezones::where('id', '=', $user->agent_tzone)->first();
        $timezone = $timezone->name;
    } else {
        $timezone = "";
    }
    ?>
    
    <div class="card-body">
        
        <div class="row">
            <div class="col-md-6">
                
                <div class="card card-light">
                    
                    <div class="card-header">
                        <h3 class="card-title">{!! Lang::get('lang.user_information') !!}</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 row">
                            @if($user->gender == 1)
                            <div class='col-sm-4'><label>{!! Lang::get('lang.gender') !!}:</label></div> <div class='col-sm-7'>{{ 'Male' }}</div>
                            @else
                            <div class='col-sm-4'><label>{!! Lang::get('lang.gender') !!}:</label></div> <div class='col-sm-7'>{{ 'Female' }}</div>
                            @endif
                        </div>
                        <div class="mb-3  row">
                            <div class='col-sm-4'><label>{!! Lang::get('lang.department') !!}:</label></div> <div class='col-sm-7'> {{ $dept }}</div>
                        </div>
                        <div class="mb-3  row">
                            <div class='col-sm-4'><label>{!! Lang::get('lang.group') !!}:</label></div> <div class='col-sm-7'> {{ $grp }}</div>
                        </div>
                        <div class="mb-3  row">
                            <div class='col-sm-4'><label>{!! Lang::get('lang.company') !!}:</label></div> <div class='col-sm-7'> {{ $user->company }}</div>
                        </div>
                        <div class="mb-3  row">
                            <div class='col-sm-4'><label>{!! Lang::get('lang.role') !!}:</label></div> <div class='col-sm-7'>  {{ $user->role }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                
                <div class="card card-light card-contact">
                    
                    <div class="card-header">
                        <h3 class="card-title">{!! Lang::get('lang.contact_information') !!}</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 row">
                            <div class='col-sm-4'><label>{!! Lang::get('lang.email') !!}:</label></div> <div class='col-sm-7'> {{ $user->email }}</div>
                        </div>
                        <div class="mb-3 row">
                            <div class='col-sm-4'><label>{!! Lang::get('lang.phone_number') !!}:</label></div> <div class='col-sm-7'> {{ $user->ext }}{{ $user->phone_number }}</div>
                        </div>
                        <div class="mb-3 row">
                            <div class='col-sm-4'><label>{!! Lang::get('lang.mobile') !!}:</label></div> <div class='col-sm-7'> {{ $user->mobile }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop