@extends('themes.default1.agent.layout.agent')

@section('Users')
class="nav-link active"
@stop

@section('user-bar')
class="nav-link active"
@stop

@section('user')
class="active"
@stop

@section('user-directory')
class="nav-link active"
@stop

@section('HeadInclude')
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

.dataTables_wrapper table { display:block !important; }
</style>
@stop
<!-- header -->
@section('PageHeader')

@if($users->role == 'user')
<h3>{!! Lang::get('lang.user_profile') !!} </h3>

@elseif($users->role == 'agent')
<h3>{!! Lang::get('lang.agent_profile') !!} </h3>
@endif
@stop
<!-- /header -->
<!-- breadcrumbs -->
@section('breadcrumbs')

@stop
<!-- /breadcrumbs -->
<!-- content -->
@section('content')
<!-- success message -->
<div id="alert-success" class="alert alert-success alert-dismissible d-none">
    <i class="fa-solid fa-circle-check"> </i> <b>  <span id="get-success"></span></b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
</div>
<!-- INfo message -->
<div id="alert-danger" class="alert alert-danger alert-dismissible d-none">
    <i class="fa-solid fa-ban"> </i> <b>  <span id="get-danger"></span></b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
</div>
@if(Session::has('success1'))
<div id="success-alert" class="alert alert-success alert-dismissible">
    <i class="fa-solid  fa-circle-check"> </i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('success1')}}
</div>
@endif
<!-- failure message -->
@if(Session::has('fails1'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"> </i> <b> {!! Lang::get('lang.alert') !!} ! </b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('fails1')}}
</div>
@endif
<div class="row">
    <div class="col-md-3">
        <div class="card" >
            
            <div class="card-body ">
                <div>
                    <center>
                        <img src="{{ $users->profile_pic }}" class="img-circle" alt="User Image" style="border:3px solid #CBCBDA;padding:3px;width: 100px;height:100px;">  
                        @if($users->first_name || $users->last_name)
                        <?php $name_of_user = $users->first_name.' '.$users->last_name; ?>
                        @else
                        <?php  $name_of_user = $users->user_name; ?>
                        @endif
                        <h4 class="" title="{{$name_of_user}}">{{Str::limit($name_of_user, 25)}}</h4>
                    </center>
                </div>

                <ul class="list-group list-group-unbordered mb-3 mt-3">
                     @if($users->user_name)
                    <li class="list-group-item mb-0">
                        <b>{{Lang::get('lang.user_name')}}</b> <a class="float-end" title="{{$users->user_name}}">{{Str::limit($users->user_name,10) }}</a>
                    </li>
                     @endif
                     <li class="list-group-item mb-0">
                        <b>{{Lang::get('lang.email')}}</b> <a class="float-end" title="{{$users->email}}"> {{Str::limit($users->email,10) }}</a>
                    </li>

                    @if($users->is_delete != '1')
                    @if($users->role=='user')
                        
                    <li class="list-group-item mb-0">

                        <?php
                        $user_org = App\Model\helpdesk\Agent_panel\User_org::where('user_id', '=', $users->id)->first();
                        ?>
                       
                       @if($user_org == null)
                        <b>{!! Lang::get('lang.organization') !!}</b>
                        <a href="" class="float-end"  data-bs-toggle="modal" data-bs-target="#assign"><i class="fa-solid fa-hand-point-right"> </i> {!! Lang::get('lang.assign') !!} </a>
                        <a href="" data-bs-toggle="modal" data-bs-target="#create_org" class="float-end"> {{Lang::get('lang.create')}} |&nbsp;</a>
                        @endif

                        @if($user_org != null)
                        <?php
                        $org_id = $user_org->org_id;
                        $organization = App\Model\helpdesk\Agent_panel\Organization::where('id', '=', $org_id)->first();
                        ?>
                        <b>{!! Lang::get('lang.organization') !!}</b>

                        &nbsp;&nbsp;&nbsp;

                        <a href=""  data-bs-toggle="modal" data-bs-target="#editassign" title="{{$organization->name}}"> <span style="color:green;">{{Str::limit($organization->name,10)}}</span> </a>


                        <a class="float-end" href="#" data-bs-toggle="modal" data-bs-target="#delete-{{$org_id}}" title="{!! Lang::get('lang.remove') !!}"><i class="fa-solid fa-xmark text-danger"> </i></a>

                        <div class="modal fade" id="delete-{{$org_id}}">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                       
                                        <h4 class="modal-title">Remove user from Organization</h4>

                                         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <span>Are you sure you want to Remove ?</span>
                                    </div>
                                    <div class="modal-footer justify-content-between">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <a href="{{ route('removeuser.org', [$org_id]) }}" id="delete" class="btn btn-danger">
                                            Remove User
                                        </a>
                                    </div>
                                </div> 
                            </div>
                        </div> 
                        @endif
                    </li>
                    @endif

                    <li class="list-group-item mb-0">
                        <b>{{Lang::get('lang.role')}}</b> <a class="float-end text-success"> {!! $users->role !!}</a>
                    </li>

                    <li class="list-group-item mb-0">
                        <b>{{Lang::get('lang.status')}}</b> 
                        <a class="float-end">
                            @if($users->active == '1')
                            <span style="color:green;"> 
                                <span class="glyphicon glyphicon-ok-circle"></span>  
                                <span class="glyphicon glyphicon-user"></span>
                            </span>
                            @else
                            <span style="color:red;">
                                <span class="glyphicon glyphicon-ban-circle"></span>
                                <span class="glyphicon glyphicon-user"></span>
                            </span>
                            @endif
                        </a>
                    </li>

                    @if($users->country_code)
                    <li class="list-group-item mb-0">
                        <b>{{Lang::get('lang.country_code')}}</b> <a class="float-end" title="{{$users->country_code}}">
                            {{Str::limit($users->country_code,10) }}</a>
                    </li>
                    @endif

                    @if($users->ext)
                    <li class="list-group-item mb-0">
                        <b>{{Lang::get('lang.ext')}}</b> <a class="float-end" title="{{$users->ext}}"> {{Str::limit($users->ext,10)}}</a>
                    </li>
                    @endif

                    @if($users->mobile)
                    <li class="list-group-item mb-0">
                        <b>{{Lang::get('lang.mobile')}}</b> <a class="float-end" title="{{$users->mobile}}"> {{Str::limit($users->mobile,10)}}</a>
                    </li>
                    @endif

                    @if($users->phone_number)
                    <li class="list-group-item mb-0">
                        <b>{{Lang::get('lang.phone')}}</b><a class="float-end" title="{{$users->phone_number}}">{{Str::limit($users->phone_number,10)}}</a>
                    </li>
                    @endif

                    @if($users->internal_note)
                    <li class="list-group-item mb-0">
                        <b>{{Lang::get('lang.internal_notes')}}</b> 
                        <br/>
                        {!! $users->internal_note !!}
                    </li>
                    @endif

                    @if($users->twitterLink()!=="")
                    <li class="list-group-item mb-0">
                         {!! $users->twitterLink() !!}
                    </li>
                    @endif
                    @endif
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-9" >
        {{-- detals table starts --}}
        <?php
        $user = App\User::where('id', $users->id)->first();
        // dd( $user->role);

        if ($users->role != 'user') {
            $open = count(App\Model\helpdesk\Ticket\Tickets::where('assigned_to', '=', $users->id)->where('status', '=', '1')->get());
            $tickets = App\Model\helpdesk\Ticket\Tickets::where('assigned_to', '=', $users->id)->where('status', '=', '3')->orderBy('id', 'DESC')->paginate(20);
            $counted = count(App\Model\helpdesk\Ticket\Tickets::where('assigned_to', '=', $users->id)->where('status', '=', '3')->get());
            $deleted = count(App\Model\helpdesk\Ticket\Tickets::where('assigned_to', '=', $users->id)->where('status', '=', '5')->get());
        }
        if ($users->role == 'user') {
            $open = count(App\Model\helpdesk\Ticket\Tickets::where('user_id', '=', $users->id)->where('status', '=', '1')->get());
            $tickets = App\Model\helpdesk\Ticket\Tickets::where('user_id', '=', $users->id)->where('status', '=', '3')->orderBy('id', 'DESC')->paginate(20);
            $counted = count(App\Model\helpdesk\Ticket\Tickets::where('user_id', '=', $users->id)->where('status', '=', '3')->get());
            $deleted = count(App\Model\helpdesk\Ticket\Tickets::where('user_id', '=', $users->id)->where('status', '=', '5')->get());
            // dd($deleted);
        }
        ?>

    
        <div class="card">

            <div class="card-body">
                
                      <div class="text-end mb-3">
                
                <div>
                
                    @if($users->is_delete != '1')

                    @if(Auth::user()->role == 'admin')
                    @if($users->role == 'user')
                    <div class="btn-group">
                        <button type="button" href="#myPopup" data-rel="popup" class="btn btn-secondary btn-sm" data-bs-toggle="modal" 
                            data-bs-target="#addNewCategoryModal1">{{Lang::get('lang.change_role_to_agent')}}</button>
                    </div>

                    <div class="btn-group">
                        <button type="button" href="#myPopup" data-rel="popup" class="btn btn-secondary btn-sm" data-bs-toggle="modal" 
                            data-bs-target="#addNewCategoryModal4">{{Lang::get('lang.change_role_to_admin')}}</button>
                    </div>
                    @elseif($users->role == 'agent')
                    <div class="btn-group">
                        <button type="button" href="#myPopup" data-rel="popup" class="btn btn-secondary btn-sm" data-bs-toggle="modal" 
                            data-bs-target="#addNewCategoryModal2">{{Lang::get('lang.change_role_to_user')}}</button>
                    </div>
                    <div class="btn-group">
                        <button type="button" href="#myPopup" data-rel="popup" class="btn btn-secondary btn-sm" data-bs-toggle="modal" 
                            data-bs-target="#addNewCategoryModal4">{{Lang::get('lang.change_role_to_admin')}}</button>
                    </div>
                    @else
                    <div class="btn-group">
                        <button type="button" href="#myPopup" data-rel="popup" class="btn btn-secondary btn-sm" data-bs-toggle="modal" 
                            data-bs-target="#addNewCategoryModal2">{{Lang::get('lang.change_role_to_user')}}</button>
                    </div>
                    <div class="btn-group">
                        <button type="button" href="#myPopup" data-rel="popup" class="btn btn-secondary btn-sm" data-bs-toggle="modal" 
                            data-bs-target="#addNewCategoryModal1">{{Lang::get('lang.change_role_to_agent')}}</button>
                    </div>
                    @endif
                    @endif

                    @if(Auth::user()->role == 'admin')
                    <a href="{{route('user.edit', $users->id)}}">
                        <button type="button"  href="{{route('user.edit', $users->id)}}" class="btn btn-secondary btn-sm">{{Lang::get('lang.edit')}}</button>
                    </a>

                    <button type="button" href="#myPopup" data-rel="popup" class="btn btn-secondary btn-sm" data-bs-toggle="modal" 
                        data-bs-target="#addNewCategoryModal">{{Lang::get('lang.change_password')}}</button>
                    
                    <button type="button" href="#myPopup" data-rel="popup" class="btn btn-secondary btn-sm" data-bs-toggle="modal" 
                        data-bs-target="#addNewCategoryModal3">{{Lang::get('lang.delete')}}</button>
                    @endif

                    @if(Auth::user()->role == 'agent')
                    @if($users->role == 'user')
                    <a href="{{route('user.edit', $users->id)}}">
                        <button type="button"  href="{{route('user.edit', $users->id)}}" class="btn btn-secondary btn-sm">{{Lang::get('lang.edit')}}</button>
                    </a>

                    <button type="button" href="#myPopup" data-rel="popup" class="btn btn-secondary btn-sm" data-bs-toggle="modal" 
                        data-bs-target="#addNewCategoryModal">{{Lang::get('lang.change_password')}}</button>

                    <button type="button" href="#myPopup" data-rel="popup" class="btn btn-secondary btn-sm" data-bs-toggle="modal" 
                        data-bs-target="#addNewCategoryModal3">{{Lang::get('lang.delete')}}</button>
                    @endif
                    @endif

                    @else
                    @if(Auth::user()->role == 'admin')

                    <div id="page" class="hfeed site">
                        
                             <article class="hentry error404 text-center">
                        
                            <h1 class="error-title"><i class="fa-solid fa-trash text-info" style="color: grey"></i>
                        
                            </h1>

                            <h2 class="entry-title text-muted">{!! Lang::get('lang.user-account-is-deleted') !!}</h2>
                        
                            <div class="entry-content clearfix">
                                <p class="lead">{!! Lang::get('lang.delete-account-caution-info') !!}</p>
                                <p>
                                    <button type="button" href="#myPopup" data-rel="popup" class="btn btn-primary btn-sm" data-bs-toggle="modal" 
                                        data-bs-target="#addNewCategoryModal8">{{Lang::get('lang.restore-user')}}
                                    </button>
                                </p>
                            </div><!-- .entry-content -->
                        </article><!-- .hentry -->
                    </div><!-- #page -->
                    @endif

                    @if(Auth::user()->role == 'agent')
                    @if($users->role == 'user')
                    This is a deleted contact 
                    <button type="button" href="#myPopup" data-rel="popup" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addNewCategoryModal8">{{Lang::get('lang.restore')}}</button>
                    @elseif($users->role == 'agent')
                    This is a deleted contact 
                    @endif
                    @endif
                @endif
                </div>
            </div>
                @if($users->is_delete != '1')
                <div>
                    
                    <ul class="nav nav-tabs" >

                        <li class="nav-item">
                            <a class="nav-link active" href="#tab_1" id="open_tab" data-bs-toggle="tab">{!! Lang::get('lang.open_tickets') !!} ({{$open}})</a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="#tab_2" id="closed_tab" data-bs-toggle="tab">{!! Lang::get('lang.closed_tickets') !!} ({{$counted}})</a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="#tab_3" id="deleted_tab" data-bs-toggle="tab">{!! Lang::get('lang.deleted_tickets') !!} ({{$deleted}})</a>
                        </li>
                    </ul>
                
                    <div class="tab-content mt-2">
                    
                        @if(Session::has('success'))
                        <div id="success-alert" class="alert alert-success alert-dismissible">
                            <i class="fa-solid  fa-circle-check"> </i>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                            {{Session::get('success')}}
                        </div>
                        @endif
                        <!-- failure message -->
                        @if(Session::has('fails'))
                        <div class="alert alert-danger alert-dismissible">
                            <i class="fa-solid fa-ban"> </i> <b> {!! Lang::get('lang.alert') !!} ! </b>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                            {{Session::get('fails')}}
                        </div>
                        @endif

                        {!! html()->form('POST', route('select_all'))->open() !!}
                        
                        <div class="mailbox-controls">

                            <a class="btn btn-secondary btn-sm checkbox-toggle"><i class="fa-regular fa-square"></i></a>
                       
                            <input type="submit" class="btn btn-secondary btn-sm" name="submit" value="{!! Lang::get('lang.delete') !!}">
                       
                            <input type="submit" class="btn btn-secondary btn-sm" name="submit" value="{!! Lang::get('lang.close') !!}">
                       
                            <div id="more-option" class="btn-group">
                       
                                <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-bs-toggle="dropdown" id="d2">
                                        {!! Lang::get('lang.sort-by') !!} <span class="caret"></span>
                                </button>

                                <div  class="dropdown-menu">
                                    <a data-bs-toggle="modal" data-bs-target="#ChangeOwner" class="dropdown-item toggle-vis" href="#">
                                        <i class="fa-solid fa-plus text-success"> </i> {!!Lang::get('lang.created-at')!!}
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane active" id="tab_1"></div>

                        <div class="tab-pane active" id="tab_2"></div>

                        <div class="tab-pane active" id="tab_3"></div>

                        <div class="mt-2 mailbox-messages"  id="refresh">
                        
                            <p style="display:none;text-align:center; position:fixed; margin-left:40%;margin-top:-70px;" id="show" class="text-red">
                                <b>{!! Lang::get('lang.loading') !!}...</b>
                            </p>
                            
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
                    </div><!-- /.col -->          
                </div>
            </div>
        </div>
        {!! html()->closeModelForm() !!}
           
         @endif

        @if($users->is_delete != '1')
        
        <div class="card card-light">
           
            <div class="card-header">
                @if($users->role=='user')
                <h3 class="card-title">{!! Lang::get('lang.user_report') !!}</h3>
                @endif 
                @if($users->role=='agent')
                <h3 class="card-title">{!! Lang::get('lang.agent_report') !!}</h3>
                @endif
            </div>

            <div class="card-body">
                <form id="foo">
                {{ csrf_field() }}
                    <div  class="mb-3">
                        <div class="row">
                            <div class='col-sm-3'>
                                {!! html()->label(Lang::get("lang.start_date").':', 'date') !!}
                                {!! html()->text('start_date', null)->class('form-control')->id('datepicker4') !!}
                            </div>
                            <?php
                            $start_date = App\Model\helpdesk\Ticket\Tickets::where('id', '=', '1')->first();
                            if ($start_date != null) {
                                $created_date = $start_date->created_at;
                                $created_date = explode(' ', $created_date);
                                $created_date = $created_date[0];
                                $start_date = date("m/d/Y", strtotime($created_date . ' -1 months'));
                            } else {
                                $start_date = date("m/d/Y", strtotime(date("m/d/Y") . ' -1 months'));
                            }
                            ?>
                            <script type="text/javascript">
                                $(function() {
                                    var timestring1 = "{!! $start_date !!}";
                                    var timestring2 = "{!! date('m/d/Y') !!}";
                                    $('#datepicker4').datetimepicker({
                                        format: 'DD/MM/YYYY',
                                        minDate: moment(timestring1).startOf('day'),
                                        maxDate: moment(timestring2).startOf('day')
                                    });
                                    //                $('#datepicker').datepicker()
                                });
                            </script>
                            <div class='col-sm-3'>
                                {!! html()->label(Lang::get("lang.end_date").':', 'start_time') !!}
                                {!! html()->text('end_date', null)->class('form-control')->id('datetimepicker3') !!}
                            </div>
                            <script type="text/javascript">
                                $(function() {
                                    var timestring1 = "{!! $start_date !!}";
                                    var timestring2 = "{!! date('m/d/Y') !!}";
                                    $('#datetimepicker3').datetimepicker({
                                        format: 'DD/MM/YYYY',
                                        minDate: moment(timestring1).startOf('day'),
                                        maxDate: moment(timestring2).startOf('day')
                                    });
                                });
                            </script>
                            <div class='col-sm-3'>
                                {!! html()->label('Filter:', 'filter')->attributes(['style' => 'visibility:hidden;']) !!}<br>
                                <input type="submit" value="{!! Lang::get('lang.submit') !!}" class="btn btn-primary">
                            </div>
                            <div class="col-sm-10">
                                <div class="row mt-2">
                                    <style>
                                        #legend-holder { border: 1px solid #ccc; float: left; width: 25px; height: 25px; margin: 1px; }
                                    </style>

                                    @if($users->role=='user')

                                    <div class="col-md-4"><span id="legend-holder" style="background-color: #6C96DF;"></span>&nbsp; <span class="lead"> <span id="total-created-tickets" ></span> {!! Lang::get('lang.tickets') !!} {!! Lang::get('lang.created') !!} </span></div>
                                    @endif 
                                    @if($users->role=='agent')
                                    <div class="col-md-4"><span id="legend-holder" style="background-color: #6C96DF;"></span>&nbsp; <span class="lead"> <span id="total-created-tickets" ></span> {!! Lang::get('lang.assign_tickets') !!}  </span></div>

                                    @endif


                                    <div class="col-md-4"><span id="legend-holder" style="background-color: #6DC5B2;"></span>&nbsp; <span class="lead"> <span id="total-reopen-tickets" class="lead"></span> {!! Lang::get('lang.tickets') !!} {!! Lang::get('lang.reopen') !!}  </span></div> 
                                    <div class="col-md-4"><span id="legend-holder" style="background-color: #E3B870;"></span>&nbsp; <span class="lead"> <span id="total-closed-tickets" class="lead"></span> {!! Lang::get('lang.tickets') !!} {!! Lang::get('lang.closed') !!}  </span></div> 
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div id="legendDiv"></div>
                <div class="chart">
                    <canvas class="chart-data" id="tickets-graph" width="1000" height="250"></canvas>   
                </div>
            </div><!-- /.box-body -->
        </div>
    </div>
    
    <!-- END CUSTOM TABS -->
    {{-- MODAL POPUPS --}}
    <div class="modal fade" id="create_org">
        <div class="modal-dialog" style="width:84%;height:70%;">
            <div class="modal-content">
                {!! html()->modelForm($users->id, 'PATCH', url()->current())->attributes(['id' => 'form'])->open() !!}
                <div class="modal-header">
                    
                    <h5 class="modal-title">{!! Lang::get('lang.create_organization') !!}</h4>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidd en="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <!-- failure message -->                                
                    <div class="alert alert-danger alert-dismissible" id="alert-danger-create-org" style="display:none;"> 
                        <i class="fa-solid fa-ban"> </i> <b> {!! Lang::get('lang.alert') !!} ! <span id="get-danger-create-org"></span> </b>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                    </div>                              
                    <div class="row" id="hide">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label>{!! Lang::get('lang.name') !!}</label>
                                <input type="text" name="name" class="form-control">
                                <spam id="error-name" style="display:none;position:fixed" class="call-out text-red">This is a required field</spam>
                                <spam id="error-name1" style="display:none;position:fixed" class="call-out text-red">! Allready Taken</spam>
                                <br/>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label>{!! Lang::get('lang.phone') !!}</label>
                                <input type="number" name="phone" class="form-control">
                                <br/>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label>{!! Lang::get('lang.website') !!}</label>
                                <input type="url" name="website" placeholder="https://www.example.com" class="form-control">
                                <spam id="error-website" style="display:none" class="help-block text-red">! Allready Taken</spam>
                                <br/>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>{!! Lang::get('lang.address') !!}</label>
                                <textarea name="address" id="address" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>{!! Lang::get('lang.internal_notes') !!}</label>
                                <textarea name="internal" id="internal" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    <div id="show2" style="display:none;">
                        <div class="row">
                            <div class="col-sm-4">
                            </div>
                            <div class="col-sm-2">
                                <img src="{{asset("lb-faveo/media/images/gifloader.gif")}}">
                            </div>
                            <div class="col-sm-5">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="dismis">{!! Lang::get('lang.close') !!}</button>
                    <input type="submit" class="btn btn-primary" value="{!! Lang::get('lang.update') !!}">
                </div>
                {!! html()->closeModelForm() !!}
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
        <script>

            $('#create_org').on('hidden.bs.modal', function (e) {
                $(this).find('form')[0].reset();
                $('#address').summernote('reset');
                $('#internal').summernote('reset');
            });

        </script>
    </div><!-- /.modal -->
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // create org
            $('#form').on('submit', function() {
                $.ajax({
                    type: "POST",
                    url: "../user-org/{{$users->id}}",
                    dataType: "html",
                    data: $(this).serialize(),
                    beforeSend: function() {
                        $("#hide").addClass('d-none');
                        $("#show2").removeClass('d-none');
                    },
                    success: function(response) {
                        $("#show2").addClass('d-none');
                        $("#hide").removeClass('d-none');
                        if (response == 0) {
                            message = "Organization added successfully."
                            $("#dismis").trigger("click");
                            $("#refresh-org").load("../user/{{ $users->id }}  #refresh-org");
                            // $("#refresh2").load("../thread/{{$users->id}}   #refresh2");
                            // $("#show").removeClass('d-none');
                            $("#alert-success").removeClass('d-none');
                            $('#get-success').html(message);
                            setInterval(function() {
                                $("#alert-success").addClass('d-none');
                            }, 6000);
                            window.location.reload(true);

                        } else {
                            message = response;
                            $("#alert-danger-create-org").removeClass('d-none');
                            $('#get-danger-create-org').html(message);
                        }
                    }
                })
                return false;
            });
        });
    </script>
    <!-- Organisation Assign Modal -->
    <div class="modal fade" id="assign">
        <div class="modal-dialog">
            <div class="modal-content">
                {!! html()->modelForm($users->id, 'PATCH', url()->current())->attributes(['id' => 'org_assign'])->open() !!}
                <div class="modal-header">

                    <h5 class="modal-title">{!! Lang::get('lang.assign') !!}</h4>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" id="dismiss" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    
                </div>
        
                <div class="modal-body">

                    <div class="alert alert-danger alert-dismissible" id="alert-danger-assign-org" style="display:none;"> 
                        <i class="fa-solid fa-ban"> </i> <b> {!! Lang::get('lang.alert') !!} ! <span id="get-danger-assign-org"></span> </b>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                        </div>
                        <div class="col-md-6" id="assign_loader" style="display:none;">
                            <img src="{{asset("lb-faveo/media/images/gifloader.gif")}}"><br/><br/><br/>
                        </div>
                    </div>
                    <div id="assign_body">
                        <p>{!! Lang::get('lang.please_select_an_organization') !!}</p>

                        <input type="text" id="org" class="form-control" name="org">

                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="dismis4">{!! Lang::get('lang.close') !!}</button>
                    <button type="submit" class="btn btn-success" id="submt2">{!! Lang::get('lang.assign') !!}</button>
                </div>
                <script>
                    $('#assign').on('hidden.bs.modal', function (e) {
                        $(this).find('form')[0].reset();

                    });
                </script>

                {!! html()->closeModelForm() !!}
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->


    <!-- Edit Organisation Assign Modal -->
    <!-- edit assign oranization -->

    <?php
    $assign_org_id = App\Model\helpdesk\Agent_panel\User_org::where('user_id', '=', $users->id)->first();
    if ($assign_org_id) {
        $organization = App\Model\helpdesk\Agent_panel\Organization::where('id', '=', $assign_org_id->org_id)->first();
    }
    ?>
    @if($assign_org_id)

    <div class="modal fade" id="editassign">
        <div class="modal-dialog">

            <div class="modal-content">
                {!! html()->modelForm($users->id, 'PATCH', url()->current())->attributes(['id' => 'org_edit_assign'])->open() !!}
                <div class="modal-header">
                    
                    <h5 class="modal-title">{!! Lang::get('lang.assign') !!}</h4>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" id="dismiss" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <!--   <div id="assign_alert" class="alert alert-success alert-dismissible d-none">
                      <button id="assign_dismiss" type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                      <h4><i class="icon fa-solid fa-check"></i>Alert!</h4>
                      <div id="message-success1"></div>
                  </div> -->
                <div class="modal-body">
                    <div class="alert alert-danger alert-dismissible" id="alert-danger-edit-org" style="display:none;"> 
                        <i class="fa-solid fa-ban"> </i> <b> {!! Lang::get('lang.alert') !!} ! <span id="get-danger-edit-org"></span> </b>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                        </div>
                        <div class="col-md-6" id="assign_loader" style="display:none;">
                            <img src="{{asset("lb-faveo/media/images/gifloader.gif")}}"><br/><br/><br/>
                        </div>
                    </div>
                    <div id="assign_body">
                        <p>{!! Lang::get('lang.please_select_an_organization') !!}</p>



                        <input type="text" id="editorg" class="form-control" name="org" value="{{$organization->name}}">

                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="dismis4">{!! Lang::get('lang.close') !!}</button>
                    <button type="submit" class="btn btn-success" id="submt3">{!! Lang::get('lang.assign') !!}</button>
                </div>
                {!! html()->closeModelForm() !!}
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    @endif
    @endif
    @include('vendor.Chumper.tuser-javascript')
    <script type="text/javascript">
        // Assign a ticket
        jQuery(document).ready(function($) {
            // create org
            $('#org_assign').on('submit', function() {
                $.ajax({
                    type: "POST",
                    url: "../user-org-assign/{{$users->id}}",
                    dataType: "html",
                    data: $(this).serialize(),
                    beforeSend: function() {
                        $("#hide").addClass('d-none');
                        $("#show2").removeClass('d-none');
                    },
                    success: function(response) {
                        $("#show2").addClass('d-none');
                        $("#hide").removeClass('d-none');

                        if (response == 1) {
                            message = "Organization added successfully."
                            $("#dismiss").trigger("click");
                            $("#refresh-org").load("../user/{{ $users->id }}  #refresh-org");
                            // $("#refresh2").load("../thread/{{$users->id}}   #refresh2");
                            // $("#show").removeClass('d-none');
                            $("#alert-success").removeClass('d-none');
                            $('#get-success').html(message);
                            setInterval(function() {
                                $("#alert-success").addClass('d-none');
                            }, 4000);
                            window.location.reload(true);
                        }



                        if (response == 0) {
                            message = " Organization not found"
                            // $("#dismiss").trigger("click");
                            $("#refresh-org").load("../user/{{ $users->id }}  #refresh-org");
                            // $("#refresh2").load("../thread/{{$users->id}}   #refresh2");
                            // $("#show").removeClass('d-none');
                            $("#alert-danger-assign-org").removeClass('d-none');
                            $('#get-danger-assign-org').html(message);
                            setInterval(function() {
                                $("#alert-danger-assign-org").addClass('d-none');
                            }, 4000);
                        }

                        if (response == 2) {
                            message = "Select Organization"
                            // $("#dismiss").trigger("click");
                            $("#refresh-org").load("../user/{{ $users->id }}  #refresh-org");
                            // $("#refresh2").load("../thread/{{$users->id}}   #refresh2");
                            // $("#show").removeClass('d-none');
                            $("#alert-danger-assign-org").removeClass('d-none');
                            $('#get-danger-assign-org').html(message);
                            setInterval(function() {
                                $("#alert-danger-assign-org").addClass('d-none');
                            }, 4000);
                        }




                    }
                })
                return false;
            });
        });

        // edit assign organization

        jQuery(document).ready(function($) {
            // create org
            $('#org_edit_assign').on('submit', function() {
                $.ajax({
                    type: "POST",
                    url: "../user-org-edit-assign/{{$users->id}}",
                    dataType: "html",
                    data: $(this).serialize(),
                    beforeSend: function() {
                        $("#hide").addClass('d-none');
                        $("#show2").removeClass('d-none');
                    },
                    success: function(response) {
                        $("#show2").addClass('d-none');
                        $("#hide").removeClass('d-none');

                        if (response == 1) {
                            $("#editassign").modal('hide');
                            message = "Organization added successfully."
                            $("#dismiss").trigger("click");
                            $("#refresh-org").load("../user/{{ $users->id }}  #refresh-org");
                            // $("#refresh2").load("../thread/{{$users->id}}   #refresh2");
                            // $("#show").removeClass('d-none');
                            $("#alert-success").removeClass('d-none');
                            $('#get-success').html(message);
                            setInterval(function() {
                                $("#alert-success").addClass('d-none');
                            }, 4000);
                            window.location.reload(true);
                        }

                        if (response == 0) {
                            message = " Organization not found"
                            // $("#dismiss").trigger("click");
                            $("#refresh-org").load("../user/{{ $users->id }}  #refresh-org");
                            // $("#refresh2").load("../thread/{{$users->id}}   #refresh2");
                            // $("#show").removeClass('d-none');
                            $("#alert-danger-edit-org").removeClass('d-none');
                            $('#get-danger-edit-org').html(message);
                            setInterval(function() {
                                $("#alert-danger-edit-org").addClass('d-none');
                            }, 4000);

                        }

                        if (response == 2) {
                            message = "Select Organization"
                            // $("#dismiss").trigger("click");
                            $("#refresh-org").load("../user/{{ $users->id }}  #refresh-org");
                            // $("#refresh2").load("../thread/{{$users->id}}   #refresh2");
                            // $("#show").removeClass('d-none');
                            $("#alert-danger-edit-org").removeClass('d-none');
                            $('#get-danger-edit-org').html(message);
                            setInterval(function() {
                                $("#alert-danger-edit-org").addClass('d-none');
                            }, 4000);

                        }
                    }
                })
                return false;
            });
        });





// autocomplete organization name
        $(document).ready(function() {
            $("#org").autocomplete({
                source: "{!!URL::route('post.organization.autofill')!!}",
                minLength: 1,
                select: function(evt, ui) {
                    // // this.form.phone_number.value = ui.item.phone_number;
                    // // this.form.user_name.value = ui.item.user_name;
                    // if(ui.item.first_name) {
                    //     this.form.first_name.value = ui.item.first_name;
                    // }
                    // if(ui.item.last_name) {
                    //     this.form.last_name.value = ui.item.last_name;
                    // }

                }
            });
        });


        $(document).ready(function() {
            $("#editorg").autocomplete({
                source: "{!!URL::route('post.organization.autofill')!!}",
                minLength: 1,
                select: function(evt, ui) {


                }
            });
        });

//close autocomplite


        $(function() {
            //Enable check and uncheck all functionality
            $(".checkbox-toggle").click(function() {
                var clicks = $(this).data('clicks');
                if (clicks) {
                    //Uncheck all checkboxes
                    $(" input[type='checkbox']").iCheck("uncheck");
                    $(".far", this).removeClass("fa-check-square").addClass('fa-square');
                } else {
                    //Check all checkboxes
                    $("input[type='checkbox']").iCheck("check");
                    $(".far", this).removeClass("fa-square").addClass('fa-check-square');
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
                $('#refresh').load('open #refresh');
                $("#show").removeClass('d-none');
            });
        });



        $(function() {
            $("textarea").summernote({
            height: 150,
            tabsize: 2,
            toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']]
          ]
          });
        });
    </script>
    <script src="{{asset("lb-faveo/plugins/chartjs/Chart.min.js")}}" type="text/javascript"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $.getJSON("../user-agen/<?php echo $users->id; ?>", function(result) {
                var labels = [], open = [], closed = [], reopened = [], open_total = 0, closed_total = 0, reopened_total = 0;
                for (var i = 0; i < result.length; i++) {
                    labels.push(result[i].date);
                    open.push(result[i].open);
                    closed.push(result[i].closed);
                    reopened.push(result[i].reopened);

                    open_total += parseInt(result[i].open);
                    closed_total += parseInt(result[i].closed);
                    reopened_total += parseInt(result[i].reopened);
                }
                var buyerData = {
                    labels: labels,
                    datasets: [
                        {
                            label: "Open Tickets",
                            fillColor: "rgba(93, 189, 255, 0.05)",
                            strokeColor: "rgba(2, 69, 195, 0.9)",
                            pointColor: "rgba(2, 69, 195, 0.9)",
                            pointStrokeColor: "#c1c7d1",
                            pointHighlightFill: "#fff",
                            pointHighlightStroke: "rgba(220,220,220,1)",
                            data: open
                        }
                        , {
                            label: "Closed Tickets",
                            fillColor: "rgba(255, 206, 96, 0.08)",
                            strokeColor: "rgba(221, 129, 0, 0.94)",
                            pointColor: "rgba(221, 129, 0, 0.94)",
                            pointStrokeColor: "rgba(60,141,188,1)",
                            pointHighlightFill: "#fff",
                            pointHighlightStroke: "rgba(60,141,188,1)",
                            data: closed

                        }
                        , {
                            label: "Reopened Tickets",
                            fillColor: "rgba(104, 255, 220, 0.06)",
                            strokeColor: "rgba(0, 149, 115, 0.94)",
                            pointColor: "rgba(0, 149, 115, 0.94)",
                            pointStrokeColor: "rgba(60,141,188,1)",
                            pointHighlightFill: "#fff",
                            pointHighlightStroke: "rgba(60,141,188,1)",
                            data: reopened
                        }
                    ]
                };
                $("#total-created-tickets").html(open_total);
                $("#total-reopen-tickets").html(reopened_total);
                $("#total-closed-tickets").html(closed_total);
                var myLineChart = new Chart(document.getElementById("tickets-graph").getContext("2d")).Line(buyerData, {
                    showScale: true,
                    //Boolean - Whether grid lines are shown across the chart
                    scaleShowGridLines: true,
                    //String - Colour of the grid lines
                    scaleGridLineColor: "rgba(0,0,0,.05)",
                    //Number - Width of the grid lines
                    scaleGridLineWidth: 1,
                    //Boolean - Whether to show horizontal lines (except X axis)
                    scaleShowHorizontalLines: true,
                    //Boolean - Whether to show vertical lines (except Y axis)
                    scaleShowVerticalLines: true,
                    //Boolean - Whether the line is curved between points
                    bezierCurve: true,
                    //Number - Tension of the bezier curve between points
                    bezierCurveTension: 0.3,
                    //Boolean - Whether to show a dot for each point
                    pointDot: true,
                    //Number - Radius of each point dot in pixels
                    pointDotRadius: 1,
                    //Number - Pixel width of point dot stroke
                    pointDotStrokeWidth: 1,
                    //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
                    pointHitDetectionRadius: 10,
                    //Boolean - Whether to show a stroke for datasets
                    datasetStroke: true,
                    //Number - Pixel width of dataset stroke
                    datasetStrokeWidth: 1,
                    //Boolean - Whether to fill the dataset with a color
                    datasetFill: true,
                    //String - A legend template
                    //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
                    maintainAspectRatio: true,
                    //Boolean - whether to make the chart responsive to window resizing
                    responsive: true,
                });

            });
            $('#click me').click(function() {
                $('#foo').submit();
            });
            $('#foo').submit(function(event) {
                // get the form data
                // there are many ways to get this data using jQuery (you can use the class or id also)
                var date1 = $('#datepicker4').val();
                var date2 = $('#datetimepicker3').val();
                var formData = date1.split("/").join('-');
                var dateData = date2.split("/").join('-');
                // process the form
                $.ajax({
                    type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
                    url: '../user-chart-range/<?php echo $users->id; ?>/' + dateData + '/' + formData, // the url where we want to POST
                    data: formData, // our data object
                    dataType: 'json', // what type of data do we expect back from the server

                    success: function(result2) {

                        var labels = [], open = [], closed = [], reopened = [], open_total = 0, closed_total = 0, reopened_total = 0;

                        for (var i = 0; i < result2.length; i++) {

                            labels.push(result2[i].date);
                            open.push(result2[i].open);
                            closed.push(result2[i].closed);
                            reopened.push(result2[i].reopened);

                            open_total += parseInt(result2[i].open);
                            closed_total += parseInt(result2[i].closed);
                            reopened_total += parseInt(result2[i].reopened);
                        }
                        var buyerData = {
                            labels: labels,
                            datasets: [
                                {
                                    label: "Open Tickets",
                                    fillColor: "rgba(93, 189, 255, 0.05)",
                                    strokeColor: "rgba(2, 69, 195, 0.9)",
                                    pointColor: "rgba(2, 69, 195, 0.9)",
                                    pointStrokeColor: "#c1c7d1",
                                    pointHighlightFill: "#fff",
                                    pointHighlightStroke: "rgba(220,220,220,1)",
                                    data: open
                                }
                                , {
                                    label: "Closed Tickets",
                                    fillColor: "rgba(255, 206, 96, 0.08)",
                                    strokeColor: "rgba(221, 129, 0, 0.94)",
                                    pointColor: "rgba(221, 129, 0, 0.94)",
                                    pointStrokeColor: "rgba(60,141,188,1)",
                                    pointHighlightFill: "#fff",
                                    pointHighlightStroke: "rgba(60,141,188,1)",
                                    data: closed

                                }
                                , {
                                    label: "Reopened Tickets",
                                    fillColor: "rgba(104, 255, 220, 0.06)",
                                    strokeColor: "rgba(0, 149, 115, 0.94)",
                                    pointColor: "rgba(0, 149, 115, 0.94)",
                                    pointStrokeColor: "rgba(60,141,188,1)",
                                    pointHighlightFill: "#fff",
                                    pointHighlightStroke: "rgba(60,141,188,1)",
                                    data: reopened
                                }
                            ]
                        };
                        $("#total-created-tickets").html(open_total);
                        $("#total-reopen-tickets").html(reopened_total);
                        $("#total-closed-tickets").html(closed_total);
                        var myLineChart = new Chart(document.getElementById("tickets-graph").getContext("2d")).Line(buyerData, {
                            showScale: true,
                            //Boolean - Whether grid lines are shown across the chart
                            scaleShowGridLines: true,
                            //String - Colour of the grid lines
                            scaleGridLineColor: "rgba(0,0,0,.05)",
                            //Number - Width of the grid lines
                            scaleGridLineWidth: 1,
                            //Boolean - Whether to show horizontal lines (except X axis)
                            scaleShowHorizontalLines: true,
                            //Boolean - Whether to show vertical lines (except Y axis)
                            scaleShowVerticalLines: true,
                            //Boolean - Whether the line is curved between points
                            bezierCurve: true,
                            //Number - Tension of the bezier curve between points
                            bezierCurveTension: 0.3,
                            //Boolean - Whether to show a dot for each point
                            pointDot: true,
                            //Number - Radius of each point dot in pixels
                            pointDotRadius: 1,
                            //Number - Pixel width of point dot stroke
                            pointDotStrokeWidth: 1,
                            //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
                            pointHitDetectionRadius: 10,
                            //Boolean - Whether to show a stroke for datasets
                            datasetStroke: true,
                            //Number - Pixel width of dataset stroke
                            datasetStrokeWidth: 1,
                            //Boolean - Whether to fill the dataset with a color
                            datasetFill: true,
                            //String - A legend template
                            //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
                            maintainAspectRatio: true,
                            //Boolean - whether to make the chart responsive to window resizing
                            responsive: true,
                        });
                        myLineChart.options.responsive = false;
                        $("#tickets-graph").remove();
                        $(".chart").html("<canvas id='tickets-graph' width='1000' height='250'></canvas>");
                        var myLineChart1 = new Chart(document.getElementById("tickets-graph").getContext("2d")).Line(buyerData, {
                            showScale: true,
                            //Boolean - Whether grid lines are shown across the chart
                            scaleShowGridLines: true,
                            //String - Colour of the grid lines
                            scaleGridLineColor: "rgba(0,0,0,.05)",
                            //Number - Width of the grid lines
                            scaleGridLineWidth: 1,
                            //Boolean - Whether to show horizontal lines (except X axis)
                            scaleShowHorizontalLines: true,
                            //Boolean - Whether to show vertical lines (except Y axis)
                            scaleShowVerticalLines: true,
                            //Boolean - Whether the line is curved between points
                            bezierCurve: true,
                            //Number - Tension of the bezier curve between points
                            bezierCurveTension: 0.3,
                            //Boolean - Whether to show a dot for each point
                            pointDot: true,
                            //Number - Radius of each point dot in pixels
                            pointDotRadius: 1,
                            //Number - Pixel width of point dot stroke
                            pointDotStrokeWidth: 1,
                            //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
                            pointHitDetectionRadius: 10,
                            //Boolean - Whether to show a stroke for datasets
                            datasetStroke: true,
                            //Number - Pixel width of dataset stroke
                            datasetStrokeWidth: 1,
                            //Boolean - Whether to fill the dataset with a color
                            datasetFill: true,
                            //String - A legend template
                            //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
                            maintainAspectRatio: true,
                            //Boolean - whether to make the chart responsive to window resizing
                            responsive: true,
                        });


                    }
                });
                // using the done promise callback
                // stop the form from submitting the normal way and refreshing the page
                event.preventDefault();
            });
        });

        jQuery(document).ready(function() {
            // Close a ticket
            $('#close').on('click', function(e) {
                $.ajax({
                    type: "GET",
                    url: "agen",
                    beforeSend: function() {
                    },
                    success: function(response) {
                    }
                })
                return false;
            });
        });
    </script>




        <!-- Delete -->
        <form action="{!!URL::route('user.post.delete', $users->id)!!}" method="post" role="form">
        {{ csrf_field() }}
            <div class="modal fade" id="addNewCategoryModal3" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                           
                            @if($users->role=='user')
                            <h4 class="modal-title" id="titleLabel">{{Lang::get('lang.delete_user')}}</h4>
                            @endif 
                            @if($users->role=='agent')
                            <h4 class="modal-title" id="titleLabel">{{Lang::get('lang.delete_agent')}}</h4>
                            @endif

                             <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span>
                            </button>

                        </div>

                        <div class="modal-body">

                            <p>{!! Lang::get('lang.what_should_be_done_with_content_owned_by_this_user') !!}?</p>
<br>
                            <?php $user = App\User::where('id', $users->id)->first(); ?>
                            @if($user->role == 'agent')
                            {!! html()->label(Lang::get('lang.delete_all_content'), 'delete_all_content') !!} <span class="text-red"> *</span>
                            <?php
                            $open = App\Model\helpdesk\Ticket\Tickets::where('assigned_to', '=', $users->id)->where('status', '=', '1')->get();
                            ?>
                            <?php $user = App\User::where('id', $users->id)->first(); ?>
                            <?php
                            $open = count(App\Model\helpdesk\Ticket\Tickets::where('assigned_to', '=', $users->id)->where('status', '=', '1')->get());
                            ?>

                            @if(!$open)
                            
                            @elseif($open)
                            <input type="checkbox" id="delete_checkbox" name="delete_all" value="1">

                            @endif
                            @endif
                            <!--    Hi Admin 
                                @if($users->role=='agent')  
                                Assign  tickets of the agent will delete?
                                Create ticket By agent Will Delete?
                                @elseif($users->role=='user')
                                Crete ticket by user Will Delete?
                                @endif -->
                            <!--  -->
                            <?php $user = App\User::where('id', $users->id)->first(); ?>
                            <?php
                            $open = count(App\Model\helpdesk\Ticket\Tickets::where('assigned_to', '=', $users->id)->where('status', '=', '1')->get());
                            $counted = count(App\Model\helpdesk\Ticket\Tickets::where('user_id', '=', $users->id)->where('status', '=', '3')->get());
                            $deleted = count(App\Model\helpdesk\Ticket\Tickets::where('user_id', '=', $users->id)->where('status', '=', '5')->get());
                            ?>
                            @if($open>0 && $user->role == 'agent')  
                            <div id="delete_assign_body">
                                <p>{!! Lang::get('lang.whome_do_you_want_to_assign_ticket') !!}?</p>
                                <select id="asssign" class="form-control" name="assign_to">
                                    <?php
                                    $assign = App\User::where('role', '!=', 'user')->get();
                                    $count_assign = count($assign);
                                    $teams = App\Model\helpdesk\Agent\Teams::all();
                                    $count_teams = count($teams);
                                    ?>
                                    <!--    <optgroup label="Teams ( {!! $count_teams !!} )">
                                           @foreach($teams as $team)
                                           <option  value="team_{{$team->id}}">{!! $team->name !!}</option>
                                           @endforeach
                                       </optgroup> -->
                                    <optgroup label="Agents ( {!! $count_assign !!} )">
                                        @foreach($assign as $user)
                                        <option  value="user_{{$user->id}}">{{$user->first_name." ".$user->last_name}}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                            @endif

                        </div>

                        <div class="modal-footer">
                            {!! html()->submit(Lang::get('lang.confirm_deletion'))->class('btn btn-primary float-end') !!}
                        </div>
                    </div>
                </div>
            </div>
        </form>
 
    <!-- Role -->
    <!-- Admin -->
    <form action="{!!URL::route('user.post.rolechangeadmin', $users->id)!!}" method="post" role="form">
    {{ csrf_field() }}
        <div class="modal fade" id="addNewCategoryModal4" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">

                        <h4 class="modal-title" id="titleLabel">{{Lang::get('lang.role_change')}}:</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span>
                        </button>
                       
                    </div>

                    <div class="modal-body">

                        <?php
                        $groups = App\Model\helpdesk\Agent\Groups::all(['id', 'name']);
                        $departments = App\Model\helpdesk\Agent\Department::all(['id', 'name']);
                        ?>

                        <!-- <div class="col-sm-4 mb-3 {{ $errors->has('group') ? 'has-error' : '' }}"> -->
                        {!! html()->label(Lang::get('lang.assigned_group'), 'assign_group') !!} <span class="text-red"> *</span>
                        {!! html()->select('group', [Lang::get('lang.groups')=>$groups->pluck('name','id')->toArray()], null)->class('form-control select') !!}
                        <!-- </div> -->
                        <!-- primary dept -->
                        <!-- <div class="col-sm-4 mb-3 {{ $errors->has('primary_department') ? 'has-error' : '' }}"> -->
                        {!! html()->label(Lang::get('lang.primary_department'), 'primary_dpt') !!} <span class="text-red"> *</span>
                        {!! html()->select('primary_department', [Lang::get('lang.departments')=>$departments->pluck('name','id')->toArray()], null)->class('form-control select') !!}
                        <!-- </div> -->

                    </div>

                    <div class="modal-footer">
                        {!! html()->submit(Lang::get('lang.submit'))->class('btn btn-primary float-end') !!}
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!-- user -->
    <form action="{!!URL::route('user.post.rolechangeuser', $users->id)!!}" method="post" role="form">
    {{ csrf_field() }}
        <div class="modal fade" id="addNewCategoryModal2" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">

                         <h4 class="modal-title" id="titleLabel">{{Lang::get('lang.role_change')}}:</h4>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span>
                        </button>
                       
                    </div>

                    <div class="modal-body">


                        Are u sure?
                        Role Change Agent To User

                    </div>

                    <div class="modal-footer">
                        {!! html()->submit(Lang::get('lang.submit'))->class('btn btn-primary float-end') !!}
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!-- agent -->
    <form action="{!!URL::route('user.post.rolechangeagent', $users->id)!!}" method="post" role="form">
    {{ csrf_field() }}
        <div class="modal fade" id="addNewCategoryModal1" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">

                        <h4 class="modal-title" id="titleLabel">{{Lang::get('lang.role_change')}}:</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span>
                        </button>
                        
                    </div>

                    <div class="modal-body">

                        <?php
                        $groups = App\Model\helpdesk\Agent\Groups::all(['id', 'name']);
                        $departments = App\Model\helpdesk\Agent\Department::all(['id', 'name']);
                        ?>

                        <!-- <div class="col-sm-4 mb-3 {{ $errors->has('group') ? 'has-error' : '' }}"> -->
                        {!! html()->label(Lang::get('lang.assigned_group'), 'assign_group') !!} <span class="text-red"> *</span>
                        {!! html()->select('group', [Lang::get('lang.groups')=>$groups->pluck('name','id')->toArray()], null)->class('form-control select') !!}
                        <!-- </div> -->
                        <!-- primary dept -->
                        <!-- <div class="col-sm-4 mb-3 {{ $errors->has('primary_department') ? 'has-error' : '' }}"> -->
                        {!! html()->label(Lang::get('lang.primary_department'), 'primary_dpt') !!} <span class="text-red"> *</span>
                        {!! html()->select('primary_department', [Lang::get('lang.departments')=>$departments->pluck('name','id')->toArray()], null)->class('form-control select') !!}
                        <!-- </div> -->

                    </div>

                    <div class="modal-footer">
                        {!! html()->submit(Lang::get('lang.submit'))->class('btn btn-primary float-end') !!}
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!-- Change password -->

    <div class="modal fade" id="addNewCategoryModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">

                    <h4 class="modal-title" id="titleLabel">{{Lang::get('lang.change_password')}}:</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span>
                    </button>
                    
                </div>

                <div class="modal-body">

                    <button class="btn btn-warning float-end btn-sm mb-1" id="changepassword">{{Lang::get('lang.password_generator')}}</button>

                    <form name="myForm" action="{!!URL::route('user.post.changepassword', $users->id)!!}" method="post" role="form" onsubmit="return validateForm()">
                    {{ csrf_field() }}
                        <div class="mb-3">

                            <!-- <div class="mb-3 {{ $errors->has('change_password') ? 'has-error' : '' }}"> -->
                            {!! html()->label(Lang::get('lang.new_password'), 'New password') !!} <span class="text-red"> *</span>
                            <input type="text" class="form-control" name="change_password" id="changepassword1" >

                            <p id="demo" style="color:red"></p>

                            <!-- </div> -->

                        </div>

                </div>

                <div class="modal-footer">
                    {!! html()->submit(Lang::get('lang.submit'))->class('btn btn-primary float-end')->id('savepassword') !!}
                </div>
            </div>
            </form>
        </div>
    </div>

    <!-- Restore -->
      <!-- Admin -->
    <form action="{!!URL::route('user.restore', $users->id)!!}" method="post" role="form">
    {{ csrf_field() }}
        <div class="modal modal-fade" id="addNewCategoryModal8" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">

                          <h4 class="modal-title" id="titleLabel">{{Lang::get('lang.restore')}}:</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span>
                        </button>
                      
                    </div>

                    <div class="modal-body">

                     Are you Sure?

                    </div>

                    <div class="modal-footer">
                        {!! html()->submit(Lang::get('lang.submit'))->class('btn btn-primary float-end') !!}
                    </div>
                </div>
            </div>
        </div>
    </form>
    <script>
$(document).ready(function(){ 
        $("#delete_checkbox").click(function() {
            // alert('ok');

            $("#delete_assign_body").toggle();

        });
        });

    </script>

    <script>
        function validateForm() {
            var x = document.forms["myForm"]["change_password"].value;
            if (x == null || x == "") {
                // alert("please enter your password");
                document.getElementById("demo").innerHTML = "Enter Password";
                return false;
            }
        }
    </script>
    <script>

        $('#changepassword').click(function() {
            $.ajax({
                type: 'get',
                url: '{{route("user.changepassword")}}',
                // data: {settings_approval: settings_approval},
                success: function(result) {
                    // with('success', Lang::get('lang.approval_settings-created-successfully'));
                    // alert("Hi, testing");
                    // var x =result;
                    var sum = result;

                    document.getElementById("changepassword1").value = sum;

                }
            });

        });

    </script>
    @stop
