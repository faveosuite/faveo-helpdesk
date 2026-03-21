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

@section('organizations')
class="nav-link active"
@stop

@section('HeadInclude')
@stop

<!-- header -->
@section('PageHeader')
<h3>{!! Lang::get('lang.organization_profile') !!}</h3>
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

<div class="row">
    <?php $org_hd = App\Model\helpdesk\Agent_panel\Organization::where('id', '=', $orgs->id)->first(); ?>
    
    <div class="col-sm-12">
        <div id="alert-success" class="alert alert-success alert-dismissible d-none">
            <i class="fa-solid  fa-circle-check"> </i> <b> Success <span id="get-success"></span></b>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
        </div>

        @if(Session::has('success'))
        <div id="success-alert" class="alert alert-success alert-dismissible" style="margin-top: 15px;">
            <i class="fa-solid fa-circle-check"> </i>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
            {{Session::get('success')}}
        </div>
        @endif
        <!-- failure message -->
        @if(Session::has('fails'))
        <div class="alert alert-danger alert-dismissible" style="margin-top: 15px;">
            <i class="fa-solid fa-ban"> </i> <b> {!! Lang::get('lang.alert') !!} ! </b>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
            {{Session::get('fails')}}
        </div>
        @endif
    </div> 

    <div class="col-md-4">
  
        <div class="card card-light card-outline">

            <div class="card-body box-profile">
                <h3 class="profile-username text-center has-tooltip" title="{{$orgs->name}}">{{Str::limit($orgs->name,15)}}</h3> 

                <p class="text-muted text-center">Organization</p> 

                <a href="{{route('organizations.edit', $orgs->id)}}" class="btn btn-primary btn-block has-tooltip">

                    <i class="fa-solid fa-pen-to-square"></i> {!! Lang::get('lang.edit') !!}
                </a> 

                <ul class="list-group list-group-unbordered mb-3">

                    <li class="list-group-item">

                        <label>{!! Lang::get('lang.website') !!}</label> 
                        <a class="float-end" title="{{$orgs->website}}">{!! Str::limit($orgs->website,15) !!}</a>
                    </li>

                    @if($orgs->phone)
                    <li class="list-group-item">

                        <label>{!! Lang::get('lang.phone') !!}</label> 
                        <a class="float-end" title="{{$orgs->phone}}">{!! Str::limit($orgs->phone,15) !!}</a>
                    </li>
                    @endif

                    @if($orgs->address)
                    <li class="list-group-item">

                        <label>{!! Lang::get('lang.address') !!}</label> 
                        <br>
                        {!! $orgs->address !!}
                    </li>
                    @endif

                    @if($orgs->internal_notes)
                    <li class="list-group-item">

                        <label>{!! Lang::get('lang.internal_notes') !!}</label> 
                        <br>
                        {!! $orgs->internal_notes !!}
                    </li>
                    @endif
                </ul>

                <button data-bs-toggle="modal" data-bs-target="#assign_head" id="button_select" class="btn btn-primary btn-block">
                    <i class="fa-solid fa-plus"> </i> {!! Lang::get('lang.select_organization_manager') !!}
                </button>
            </div>
        </div>

        <div id="refresh1"> 
            @if($org_hd->head > 0)
            <?php $users = App\User::where('id', '=', $org_hd->head)->first(); ?>

            <div class="card card-widget widget-user-2">

                <div class="widget-user-header bg-warning">

                    <div class="widget-user-image">

                        <img src="{{ Gravatar::src( $users->email) }}" class="img-circle elevation-2">
                    </div>

                    <h3 class="widget-user-username" title="{{$users->user_name}}">{!! Str::limit($users->user_name,15) !!}</h3> 

                    <h5 class="widget-user-desc" style="font-size: 14px;">Organization Manager</h5>
                </div>

                <div class="card-footer p-0">

                    <ul class="nav flex-column">

                        <li class="nav-item">

                            <a href="javascript:;" class="nav-link text-dark"> {!! Lang::get('lang.e-mail') !!}  

                                <span class="float-end" title="{{$users->email}}">{!! Str::limit($users->email,15) !!}</span>
                            </a>
                        </li>

                        <li class="nav-item">

                            <a href="javascript:;" class="nav-link text-dark"> {!! Lang::get('lang.phone') !!}  

                                <span class="float-end" title="{{$users->phone_number}}">{!! Str::limit($users->phone_number,15) !!}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            @endif
        </div>
    </div>
    <div class="col-md-8">
        
        <div class="card card-light">
            <?php
            $user_orgs = App\Model\helpdesk\Agent_panel\User_org::where('org_id', '=', $orgs->id)->paginate(20);
            ?>
            <div class="card-header">
                <h3 class="card-title">{!! Lang::get('lang.users_of') !!} {{$orgs->name}}</h3>
                <div class="card-tools d-flex">
                    <?php echo $user_orgs->setPath(route('organizations.show', $orgs->id))->render(); ?>
                </div>
            </div>   
            <div class="card-body">
                <table class="table table-hover table-bordered">
                    <tbody><tr>
                            <th>{!! Lang::get('lang.name') !!}</th>
                            <th>{!! Lang::get('lang.email') !!}</th>
                            <th>{!! Lang::get('lang.phone') !!}</th>
                            <th>{!! Lang::get('lang.status') !!}</th>
                            <th>{!! Lang::get('lang.ban') !!}</th>
                        </tr>
                        @foreach($user_orgs as $user_org)
                        <?php
                        $user_detail = App\User::where('id', '=', $user_org->user_id)->first();
                        ?>
                        <tr>
                            <td><a href="{!! route('user.show',$user_detail->id) !!}">{!! $user_detail->user_name !!}</a></td>
                            <td><a href="{!! route('user.show',$user_detail->id) !!}">{!! $user_detail->email !!}</a></td>
                            <td>{!! $user_detail->phone_number !!}</td>
                            @if($user_detail->active == 1)
                            <td><span class="badge text-bg-success">{!! Lang::get('lang.active') !!}</span></td>
                            @elseif($user_detail->active == 0)
                            <td><span class="badge text-bg-warning">{!! Lang::get('lang.inactive') !!}</span></td>
                            @endif
                            <td>{!! $user_detail->ban !!}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>    
        </div>

        <?php
        $user_orga_relation_id = [];
        $user_orga_relations = App\Model\helpdesk\Agent_panel\User_org::where('org_id', '=', $orgs->id)->get();
        foreach ($user_orga_relations as $user_orga_relation) {
            $user_orga_relation_id[] = $user_orga_relation->user_id;
        }
//        dd($user_orga_relation_id);
//        $models = \App\Model\helpdesk\Ticket\Tickets::whereIn('user_id', $user_orga_relation_id)->get();

        $open = count(\App\Model\helpdesk\Ticket\Tickets::whereIn('user_id', $user_orga_relation_id)->where('status', '=', '1')->get());
        $counted = count(\App\Model\helpdesk\Ticket\Tickets::whereIn('user_id', $user_orga_relation_id)->where('status', '=', '2')->get());
        $deleted = count(\App\Model\helpdesk\Ticket\Tickets::whereIn('user_id', $user_orga_relation_id)->where('status', '=', '5')->get());
//        dd($open);
        ?>

        <div class="card">
            
            <div class="card-body p-3">
                
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a href="#tab_1" data-bs-toggle="tab"  class="nav-link active">{!! Lang::get('lang.open_tickets') !!} ({{$open}})</a>
                        </li>
                        <li class="nav-item">
                            <a href="#tab_2" data-bs-toggle="tab"  class="nav-link">{!! Lang::get('lang.closed_tickets') !!} ({{$counted}})</a>
                        </li>
                        <li class="nav-item">
                            <a href="#tab_3" data-bs-toggle="tab"  class="nav-link">{!! Lang::get('lang.deleted_tickets') !!} ({{$deleted}})</a>
                        </li>
                    </ul>

                    <div class="tab-content no-padding">

                        <div class="tab-pane active" id="tab_1">
                            <?php $open = count(App\Model\helpdesk\Ticket\Tickets::whereIn('user_id', $user_orga_relation_id)->where('status', '=', '1')->get()); ?>
                            
                            <div>
                                {!! html()->form('POST', route('select_all'))->open() !!}
                                <div class="mailbox-controls p-0 mt-2 mb-2">
                                    <!-- Check all button -->
                                    <a class="btn btn-secondary btn-sm checkbox-toggle"><i class="fa-regular fa-square"></i></a>
                                    <input type="submit" class="btn btn-secondary text-orange btn-sm" name="submit" value="{!! Lang::get('lang.delete') !!}">
                                    <input type="submit" class="btn btn-secondary text-yellow btn-sm" name="submit" value="{!! Lang::get('lang.close') !!}">
                                    <div class="float-end">
                                        <?php
                                        $counted = count(App\Model\helpdesk\Ticket\Tickets::whereIn('user_id', $user_orga_relation_id)->where('status', '=', '1')->get());
                                        if ($counted < 20) {
                                            echo $counted . "/" . $counted;
                                        } else {
                                            echo "20/" . $counted;
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="table-responsive" id="refresh">
                                    <p style="display:none;text-align:center; position:fixed; margin-left:40%;margin-top:-70px;" id="show" class="text-red"><b>Loading...</b></p>
                                    <!-- table -->
                                    <table class="table table-hover table-bordered">
                                        <thead>
                                        <th>
                                        </th>
                                        <th>{!! Lang::get('lang.subject') !!}</th>
                                        <th>{!! Lang::get('lang.ticket_id') !!}</th>
                                        <th>{!! Lang::get('lang.priority') !!}</th>
                                        <th>{!! Lang::get('lang.last_replier') !!}</th>
                                        <th>{!! Lang::get('lang.assigned_to') !!}</th>
                                        <th>{!! Lang::get('lang.last_activity') !!}</th>
                                        </thead>
                                        <tbody id="hello">
                                            <?php $tickets = App\Model\helpdesk\Ticket\Tickets::whereIn('user_id', $user_orga_relation_id)->where('status', '=', '1')->orderBy('id', 'DESC')->paginate(20); ?>
                                            @foreach ($tickets  as $ticket)
                                            <tr <?php if ($ticket->seen_by == null) { ?> style="color:green;" <?php }
                                            ?> >
                                                <td><input type="checkbox" class="icheckbox_flat-blue" name="select_all[]" value="{{$ticket->id}}"/></td>
                                                <?php
                                                //  collaborators
                                                $collaborators = App\Model\helpdesk\Ticket\Ticket_Collaborator::where('ticket_id', '=', $ticket->id)->get();
                                                $collab = count($collaborators);
                                                //  title
                                                $title = App\Model\helpdesk\Ticket\Ticket_Thread::where('ticket_id', '=', $ticket->id)->first();
                                                $string = strip_tags($title->title);
                                                // check atatchments
                                                $attachments = App\Model\helpdesk\Ticket\Ticket_attachments::where('thread_id', '=', $title->id)->count();
                                                $attach = $attachments;

                                                if (strlen($string) > 40) {
                                                    $stringCut = substr($string, 0, 40);
                                                    $string = substr($stringCut, 0, strrpos($stringCut, ' ')) . ' ...';
                                                }
                                                $TicketData = App\Model\helpdesk\Ticket\Ticket_Thread::where('ticket_id', '=', $ticket->id)->max('id');
                                                $TicketDatarow = App\Model\helpdesk\Ticket\Ticket_Thread::where('id', '=', $TicketData)->first();
                                                $LastResponse = App\User::where('id', '=', $TicketDatarow->user_id)->first();
                                                if ($LastResponse->role == "user") {
                                                    $rep = "#F39C12";
                                                    $username = $LastResponse->user_name;
                                                } else {
                                                    $rep = "#000";
                                                    $username = $LastResponse->first_name . " " . $LastResponse->last_name;
                                                    if ($LastResponse->first_name == null || $LastResponse->last_name == null) {
                                                        $username = $LastResponse->user_name;
                                                    }
                                                }
                                                $titles = App\Model\helpdesk\Ticket\Ticket_Thread::where('ticket_id', '=', $ticket->id)->get();
                                                $count = count($titles);
                                                foreach ($titles as $title) {
                                                    $title = $title;
                                                }
                                                $assigned_to = App\User::where('id', '=', $ticket->assigned_to)->first();
                                                if ($assigned_to == null) {
                                                    $assigned = "Unassigned";
                                                } else {
                                                    $assigned = $assigned_to->first_name . " " . $assigned_to->last_name;
                                                }
                                                ?>
                                                <td class="mailbox-name"><a href="{!! route('ticket.thread',[$ticket->id]) !!}" title="{!! $title->title !!}">{{$string}}   </a> ({!! $count!!}) <i class="fa-solid fa-comment"></i>
                                                    @if($collab > 0)&nbsp;<i class="fa-solid fa-users"></i>@endif 
                                                    @if($attach > 0)&nbsp;<i class="fa-solid fa-paperclip"></i>@endif</td>
                                                <td class="mailbox-Id"><a href="{!! route('ticket.thread',[$ticket->id]) !!}" title="{!! $title->title !!}">#{!! $ticket->ticket_number !!}</a></td>
                                                <?php $priority = App\Model\helpdesk\Ticket\Ticket_Priority::where('priority_id', '=', $ticket->priority_id)->first(); ?>
                                                <td class="mailbox-priority">@if($priority != null)<spam class="btn btn-{{$priority->priority_color}} btn-xs">{{$priority->priority}}</spam>@endif</td>
                                        <?php $from = App\User::where('id', '=', $ticket->user_id)->first(); ?> 
                                        <td class="mailbox-last-reply" style="color:{!! $rep !!}">{!! $username !!}</td>
                                        <td>{!! $assigned !!}</td>
                                        <td class="mailbox-last-activity">{!! UTC::usertimezone($title->updated_at) !!}</td>
                                        </tr>
                                        @endforeach
                                        </tbody>
                                    </table><!-- /.table -->
                                    <div class="float-end">
                                        <?php echo $tickets->setPath(url('/organizations/' . $orgs->id))->render(); ?>&nbsp;
                                    </div>
                                </div><!-- /.mail-box-messages -->
                                {!! html()->closeModelForm() !!}

                                {{-- end deleted tickets --}}
                            </div>
                        </div><!-- /.tab-pane -->
                        <div class="tab-pane" id="tab_2">
                            {{-- open tab --}}
                            <?php $closed = count(App\Model\helpdesk\Ticket\Tickets::whereIn('user_id', $user_orga_relation_id)->where('status', 2)->get()); ?>
                            
                            <div>
                                {!! html()->form('POST', route('select_all'))->open() !!}
                                <div class="mailbox-controls p-0 mt-2 mb-2">
                                    <!-- Check all button -->
                                    <a class="btn btn-secondary btn-sm checkbox-toggle"><i class="fa-regular fa-square"></i></a>
                                    <input type="submit" class="btn btn-secondary text-orange btn-sm" name="submit" value="{!! Lang::get('lang.delete') !!}">
                                    <input type="submit" class="btn btn-secondary text-yellow btn-sm" name="submit" value="{!! Lang::get('lang.close') !!}">
                                    <div class="float-end">
                                        <?php
                                        $counted = count(App\Model\helpdesk\Ticket\Tickets::whereIn('user_id', $user_orga_relation_id)->where('status', '=', '2')->get());
                                        if ($counted < 20) {
                                            echo $counted . "/" . $counted;
                                        } else {
                                            echo "20/" . $counted;
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class=" table-responsive" id="refresh">
                                    <p style="display:none;text-align:center; position:fixed; margin-left:40%;margin-top:-70px;" id="show" class="text-red"><b>Loading...</b></p>
                                    <!-- table -->
                                    <table class="table table-hover table-bordered">
                                        <thead>
                                        <th>
                                        </th>
                                        <th>{!! Lang::get('lang.subject') !!}</th>
                                        <th>{!! Lang::get('lang.ticket_id') !!}</th>
                                        <th>{!! Lang::get('lang.priority') !!}</th>
                                        <th>{!! Lang::get('lang.last_replier') !!}</th>
                                        <th>{!! Lang::get('lang.assigned_to') !!}</th>
                                        <th>{!! Lang::get('lang.last_activity') !!}</th>
                                        </thead>
                                        <tbody id="hello">
                                            <?php $tickets = App\Model\helpdesk\Ticket\Tickets::whereIn('user_id', $user_orga_relation_id)->where('status', '=', '2')->orderBy('id', 'DESC')->paginate(20); ?>
                                            @foreach ($tickets  as $ticket)
                                            <tr <?php if ($ticket->seen_by == null) { ?> style="color:green;" <?php } ?> >
                                                <td ><input type="checkbox" class="icheckbox_flat-blue" name="select_all[]" value="{{$ticket->id}}"/></td>
                                                <?php
                                                //  collaborators
                                                $collaborators = App\Model\helpdesk\Ticket\Ticket_Collaborator::where('ticket_id', '=', $ticket->id)->get();
                                                $collab = count($collaborators);
                                                //  title
                                                $title = App\Model\helpdesk\Ticket\Ticket_Thread::where('ticket_id', '=', $ticket->id)->first();
                                                $string = strip_tags($title->title);
                                                // check atatchments
                                                $attachments = App\Model\helpdesk\Ticket\Ticket_attachments::where('thread_id', '=', $title->id)->first();
                                                // dd($attachments);
                                                $attach = ($attachments) ? count($attachments->toArray()): 0;

                                                if (strlen($string) > 40) {
                                                    $stringCut = substr($string, 0, 40);
                                                    $string = substr($stringCut, 0, strrpos($stringCut, ' ')) . ' ...';
                                                }
                                                $TicketData = App\Model\helpdesk\Ticket\Ticket_Thread::where('ticket_id', '=', $ticket->id)->max('id');
                                                $TicketDatarow = App\Model\helpdesk\Ticket\Ticket_Thread::where('id', '=', $TicketData)->first();
                                                $LastResponse = App\User::where('id', '=', $TicketDatarow->user_id)->first();
                                                if ($LastResponse->role == "user") {
                                                    $rep = "#F39C12";
                                                    $username = $LastResponse->user_name;
                                                } else {
                                                    $rep = "#000";
                                                    $username = $LastResponse->first_name . " " . $LastResponse->last_name;
                                                    if ($LastResponse->first_name == null || $LastResponse->last_name == null) {
                                                        $username = $LastResponse->user_name;
                                                    }
                                                }
                                                $titles = App\Model\helpdesk\Ticket\Ticket_Thread::where('ticket_id', '=', $ticket->id)->get();
                                                $count = count($titles);
                                                foreach ($titles as $title) {
                                                    $title = $title;
                                                }
                                                $assigned_to = App\User::where('id', '=', $ticket->assigned_to)->first();
                                                if ($assigned_to == null) {
                                                    $assigned = "Unassigned";
                                                } else {
                                                    $assigned = $assigned_to->first_name . " " . $assigned_to->last_name;
                                                }
                                                ?>
                                                <td class="mailbox-name"><a href="{!! route('ticket.thread',[$ticket->id]) !!}" title="{!! $title->title !!}">{{$string}}   </a> ({!! $count!!}) <i class="fa-solid fa-comment"></i>
                                                    @if($collab > 0)&nbsp;<i class="fa-solid fa-users"></i>@endif 
                                                    @if($attach > 0)&nbsp;<i class="fa-solid fa-paperclip"></i>@endif</td>
                                                <td class="mailbox-Id"><a href="{!! route('ticket.thread',[$ticket->id]) !!}" title="{!! $title->title !!}">#{!! $ticket->ticket_number !!}</a></td>
                                                <?php $priority = App\Model\helpdesk\Ticket\Ticket_Priority::where('priority_id', '=', $ticket->priority_id)->first(); ?>
                                                <td class="mailbox-priority">@if($priority != null)<spam class="btn btn-{{$priority->priority_color}} btn-xs">{{$priority->priority}}</spam>@endif</td>
                                        <?php $from = App\User::where('id', '=', $ticket->user_id)->first(); ?> 
                                        <td class="mailbox-last-reply" style="color:{!! $rep !!}">{!! $username !!}</td>
                                        <td>{!! $assigned !!}</td>
                                        <td class="mailbox-last-activity">{!! UTC::usertimezone($title->updated_at) !!}</td>
                                        </tr>
                                        @endforeach
                                        </tbody>
                                    </table><!-- /.table -->

                                    <div class="float-end">
                                        <?php echo $tickets->setPath(url('/organizations/' . $orgs->id))->render(); ?>&nbsp;
                                    </div>
                                </div><!-- /.mail-box-messages -->
                                {!! html()->closeModelForm() !!}

                                {{-- end deleted tickets --}}
                            </div>
                        </div>
                        <div class="tab-pane" id="tab_3">
                            {{-- open tab --}}
                            <?php $deleted = count(App\Model\helpdesk\Ticket\Tickets::whereIn('user_id', $user_orga_relation_id)->where('status', '=', '5')->get()); ?>
                            <div>

                                {!! html()->form('POST', route('select_all'))->open() !!}
                                <div class="mailbox-controls p-0 mt-2 mb-2">
                                    <!-- Check all button -->
                                    <a class="btn btn-secondary btn-sm checkbox-toggle"><i class="fa-regular fa-square"></i></a>
                                    <input type="submit" class="btn btn-secondary text-orange btn-sm" name="submit" value="{!! Lang::get('lang.delete') !!}">
                                    <input type="submit" class="btn btn-secondary text-yellow btn-sm" name="submit" value="{!! Lang::get('lang.close') !!}">
                                    <div class="float-end">
                                        <?php
                                        $counted = count(App\Model\helpdesk\Ticket\Tickets::whereIn('user_id', $user_orga_relation_id)->where('status', '=', '5')->get());
                                        if ($counted < 20) {
                                            echo $counted . "/" . $counted;
                                        } else {
                                            echo "20/" . $counted;
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class=" table-responsive" id="refresh">
                                    <p style="display:none;text-align:center; position:fixed; margin-left:40%;margin-top:-70px;" id="show" class="text-red"><b>Loading...</b></p>
                                    <!-- table -->
                                    <table class="table table-hover table-bordered">
                                        <thead>
                                        <th>
                                        </th>
                                        <th>{!! Lang::get('lang.subject') !!}</th>
                                        <th>{!! Lang::get('lang.ticket_id') !!}</th>
                                        <th>{!! Lang::get('lang.priority') !!}</th>
                                        <th>{!! Lang::get('lang.last_replier') !!}</th>
                                        <th>{!! Lang::get('lang.assigned_to') !!}</th>
                                        <th>{!! Lang::get('lang.last_activity') !!}</th>
                                        </thead>
                                        <tbody id="hello">
                                            <?php $tickets = App\Model\helpdesk\Ticket\Tickets::whereIn('user_id', $user_orga_relation_id)->where('status', '=', '5')->orderBy('id', 'DESC')->paginate(20); ?>
                                            @foreach ($tickets  as $ticket)
                                            <tr <?php if ($ticket->seen_by == null) { ?> style="color:green;" <?php }
                                            ?> >
                                                <td ><input type="checkbox" class="icheckbox_flat-blue" name="select_all[]" value="{{$ticket->id}}"/></td>
                                                <?php
                                                //  collaborators
                                                $collaborators = App\Model\helpdesk\Ticket\Ticket_Collaborator::where('ticket_id', '=', $ticket->id)->get();
                                                $collab = count($collaborators);
                                                //  title
                                                $title = App\Model\helpdesk\Ticket\Ticket_Thread::where('ticket_id', '=', $ticket->id)->first();
                                                $string = strip_tags($title->title);
                                                // check atatchments
                                                $attachments = App\Model\helpdesk\Ticket\Ticket_attachments::where('thread_id', '=', $title->id)->count();
                                                $attach = $attachments;

                                                if (strlen($string) > 40) {
                                                    $stringCut = substr($string, 0, 40);
                                                    $string = substr($stringCut, 0, strrpos($stringCut, ' ')) . ' ...';
                                                }
                                                $TicketData = App\Model\helpdesk\Ticket\Ticket_Thread::where('ticket_id', '=', $ticket->id)->max('id');
                                                $TicketDatarow = App\Model\helpdesk\Ticket\Ticket_Thread::where('id', '=', $TicketData)->first();
                                                $LastResponse = App\User::where('id', '=', $TicketDatarow->user_id)->first();
                                                if ($LastResponse->role == "user") {
                                                    $rep = "#F39C12";
                                                    $username = $LastResponse->user_name;
                                                } else {
                                                    $rep = "#000";
                                                    $username = $LastResponse->first_name . " " . $LastResponse->last_name;
                                                    if ($LastResponse->first_name == null || $LastResponse->last_name == null) {
                                                        $username = $LastResponse->user_name;
                                                    }
                                                }
                                                $titles = App\Model\helpdesk\Ticket\Ticket_Thread::where('ticket_id', '=', $ticket->id)->get();
                                                $count = count($titles);
                                                foreach ($titles as $title) {
                                                    $title = $title;
                                                }
                                                $assigned_to = App\User::where('id', '=', $ticket->assigned_to)->first();
                                                if ($assigned_to == null) {
                                                    $assigned = "Unassigned";
                                                } else {
                                                    $assigned = $assigned_to->first_name . " " . $assigned_to->last_name;
                                                }
                                                ?>
                                                <td class="mailbox-name"><a href="{!! route('ticket.thread',[$ticket->id]) !!}" title="{!! $title->title !!}">{{$string}}   </a> ({!! $count!!}) <i class="fa-solid fa-comment"></i>
                                                    @if($collab > 0)&nbsp;<i class="fa-solid fa-users"></i>@endif 
                                                    @if($attach > 0)&nbsp;<i class="fa-solid fa-paperclip"></i>@endif</td>
                                                <td class="mailbox-Id"><a href="{!! route('ticket.thread',[$ticket->id]) !!}" title="{!! $title->title !!}">#{!! $ticket->ticket_number !!}</a></td>
                                                <?php $priority = App\Model\helpdesk\Ticket\Ticket_Priority::where('priority_id', '=', $ticket->priority_id)->first(); ?>
                                                <td class="mailbox-priority">@if($priority != null)<spam class="btn btn-{{$priority->priority_color}} btn-xs">{{$priority->priority}}</spam>@endif</td>
                                        <?php $from = App\User::where('id', '=', $ticket->user_id)->first(); ?> 
                                        <td class="mailbox-last-reply" style="color:{!! $rep !!}">{!! $username !!}</td>
                                        <td>{!! $assigned !!}</td>
                                        <td class="mailbox-last-activity">{!! UTC::usertimezone($title->updated_at) !!}</td>
                                        </tr>
                                        @endforeach
                                        </tbody>
                                    </table><!-- /.table -->

                                    <div class="float-end">
                                        <?php echo $tickets->setPath(url('/organizations/' . $orgs->id))->render(); ?>&nbsp;
                                    </div>
                                </div><!-- /.mail-box-messages -->
                                {!! html()->closeModelForm() !!}
                            </div><!-- /.tab-pane -->
                        </div><!-- /.tab-content -->
                    </div><!-- nav-tabs-custom -->
                </div>
            </div>    
        </div>

        <!--  Report of organization  -->  
        <div class="card card-light">
            <div class="card-header">
                <h3 class="card-title">
                    {!! Lang::get('lang.report_of') !!} {!! $orgs->name !!}
                </h3>
            </div>
            <div class="card-body">
                <form id="foo">
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
                            <div class='col-sm-2'>
                                {!! html()->label('Filter:', 'filter')->attributes(['style' => 'visibility:hidden;']) !!}<br>
                                <input type="submit" value="{!! Lang::get('lang.submit') !!}" class="btn btn-primary">
                            </div>
                            
                        </div>

                        <div class="row">
                                    <style>
                                        #legend-holder { border: 1px solid #ccc; float: left; width: 25px; height: 25px; margin: 2px; }
                                    </style>
                                    <div class="col-md-4"><span id="legend-holder" style="background-color: #6C96DF;"></span>&nbsp; <span class="lead"> <span id="total-created-tickets" ></span> {!! Lang::get('lang.tickets') !!} {!! Lang::get('lang.created') !!} </span></div> 
                            <div class="col-md-4"><span id="legend-holder" style="background-color: #6DC5B2;"></span>&nbsp; <span class="lead"> <span id="total-reopen-tickets" class="lead"></span> {!! Lang::get('lang.tickets') !!} {!! Lang::get('lang.reopen') !!}  </span></div> 
                            <div class="col-md-4"><span id="legend-holder" style="background-color: #E3B870;"></span>&nbsp; <span class="lead"> <span id="total-closed-tickets" class="lead"></span> {!! Lang::get('lang.tickets') !!} {!! Lang::get('lang.closed') !!}  </span></div> 
                               
                        </div>
                    </div>
                </form>
                <div id="legendDiv"></div>
                <div class="chart">
                    <canvas class="chart-data" id="tickets-graph" width="1000" height="270"></canvas>   
                </div>
            </div>
        </div>

        <!--<script type="text/javascript">-->
        <div id = "refresh">
            <script src = "{{asset("lb-faveo/plugins/chartjs/Chart.min.js")}}" type = "text/javascript" ></script>
        </div>
        <script type="text/javascript">
                                $(document).ready(function() {
                                    $.getJSON("../org-chart/<?php echo $orgs->id; ?>", function(result) {
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
                                            url: '../org-chart-range/<?php echo $orgs->id; ?>/' + dateData + '/' + formData, // the url where we want to POST
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
                                                    responsive: true
                                                });
                                                myLineChart.options.responsive = false;
                                                $("#tickets-graph").remove();
                                                $(".chart").html("<canvas id='tickets-graph' width='1000' height='270'></canvas>");
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
                                                    responsive: true
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
        <script src="{{asset("lb-faveo/js/bootstrap-datetimepicker4.7.14.min.js")}}" type="text/javascript"></script>
        <script src="{{asset("lb-faveo/plugins/moment-develop/moment.js")}}" type="text/javascript"></script>

    </div>
</div>
<!-- Organisation Assign Modal -->
<div class="modal fade" id="assign_head">
    <div class="modal-dialog">
        <div class="modal-content">
            {!! html()->modelForm($orgs->id, 'PATCH', url()->current())->attributes(['id' => 'org_head'])->open() !!}
            <div class="modal-header">
                <h5 class="modal-title">{!! Lang::get('lang.assign') !!}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="dismiss" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                    </div>
                    <div class="col-md-6" id="assign_loader" style="display:none;">
                        <img src="{{asset("lb-faveo/media/images/gifloader.gif")}}"><br/><br/><br/>
                    </div>
                </div>
                <div id="assign_body">
                    <p>{!! Lang::get('lang.please_select_an_user') !!}</p>
                    <select id="user" class="form-control" name="user">
                        <?php
                        $org_heads = App\Model\helpdesk\Agent_panel\User_org::where('org_id', '=', $orgs->id)->get();
                        ?>
                        <optgroup label="Select Organizations">
                            @foreach($org_heads as $org_head)
                            <?php $user_org_heads = App\User::where('id', '=', $org_head->user_id)->first(); ?>
                            <option  value="{{$user_org_heads->id}}">{!! $user_org_heads->user_name !!}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="dismis4">{!! Lang::get('lang.close') !!}</button>
                <button type="submit" class="btn btn-success" id="submt2">{!! Lang::get('lang.assign') !!}</button>
            </div>
            {!! html()->closeModelForm() !!}
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script type="text/javascript">
// Assign a ticket
                                jQuery(document).ready(function($) {
// create org
                                    $('#org_head').on('submit', function() {
                                        $.ajax({
                                            type: "POST",
                                            url: "../head-org/{!! $orgs->id !!}",
                                            dataType: "html",
                                            data: $(this).serialize(),
                                            beforeSend: function() {
                                                $("#assign_body").addClass('d-none');
                                                $("#assign_loader").removeClass('d-none');
                                            },
                                            success: function(response) {
                                                $("#assign_loader").addClass('d-none');
                                                $("#assign_body").removeClass('d-none');

                                                if (response == 1) {
                                                    message = "Organization head added Successfully."
                                                    $("#dismiss").trigger("click");
                                                    $("#refresh1").load("../organizations/{!! $orgs->id !!}  #refresh1");
                                                    // $("#refresh2").load("../thread/1  #refresh2");
                                                    // $("#show").removeClass('d-none');
                                                    $("#alert-success").removeClass('d-none');
                                                    $('#get-success').html(message);
                                                    setInterval(function() {
                                                        $("#alert-success").addClass('d-none');
                                                    }, 4000);
                                                }
                                            }
                                        })
                                        return false;
                                    });
                                });
</script>

<script type="text/javascript">
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
</script>
@stop
<!-- /content -->