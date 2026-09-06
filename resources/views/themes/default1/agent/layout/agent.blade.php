<!DOCTYPE html>
<html dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>

        <meta charset="UTF-8" ng-app="myApp">

        <title>Faveo | HELP DESK</title>

        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

        <meta name="_token" content="{!! csrf_token() !!}"/>

        <!-- Favicon -->
        <link href="{{ assetLink('css', 'favicon') }}" rel="shortcut icon">

        @if(Lang::getLocale() == 'ar')
        <!-- Bootstrap + AdminLTE + Common (RTL) -->
        <link href="{{ assetLink('css', 'bootstrap-rtl') }}" rel="stylesheet" type="text/css" />
        <link href="{{ assetLink('css', 'adminte-rtl') }}" rel="stylesheet" type="text/css" />
        <link href="{{ assetLink('css', 'common-rtl') }}" rel="stylesheet" type="text/css" />
        @else
        <!-- Bootstrap + AdminLTE + Common (LTR) -->
        <link href="{{ assetLink('css', 'bootstrap') }}" rel="stylesheet" type="text/css" />
        <link href="{{ assetLink('css', 'adminlte') }}" rel="stylesheet" type="text/css" />
        <link href="{{ assetLink('css', 'common') }}" rel="stylesheet" type="text/css" />
        @endif

        <!-- Font Awesome Icons -->
        <link href="{{ assetLink('css', 'font-awesome') }}" rel="stylesheet" type="text/css" />

        <!-- Ionicons -->
        <link href="{{ assetLink('css', 'ionicons') }}" rel="stylesheet" type="text/css" />

        <link href="{{ assetLink('css', 'overlay-scrollbars') }}" rel="stylesheet" type="text/css" />
        <link href="{{ assetLink('css', 'editor') }}" rel="stylesheet" type="text/css" />
        <link href="{{ assetLink('css', 'datatables') }}" rel="stylesheet" type="text/css" />
        <link href="{{ assetLink('css', 'jquery-rating') }}" rel="stylesheet" type="text/css" />

        <!-- Select2 -->
        <link href="{{ assetLink('css', 'select2') }}" rel="stylesheet" type="text/css" />
        <link href="{{ assetLink('css', 'close-button') }}" rel="stylesheet" type="text/css" />

        <!-- Daterangepicker -->
        <link href="{{ assetLink('css', 'datetimepicker') }}" rel="stylesheet" type="text/css" />
        <link href="{{ assetLink('css', 'summernote') }}" rel="stylesheet" type="text/css" />

        <!-- Google Fonts -->
        <link href="{{ assetLink('external', 'google-fonts') }}" rel="stylesheet" type="text/css" />

        <!-- jQuery UI (Agent Panel) -->
        <link href="{{ assetLink('css', 'jquery-ui-agent') }}" rel="stylesheet" type="text/css" />

        <!-- JS -->
        <script src="{{ assetLink('js', 'jquery') }}" type="text/javascript"></script>
        <script src="{{ assetLink('js', 'jquery-migrate') }}" type="text/javascript"></script>
        <script src="{{ assetLink('js', 'popper') }}" type="text/javascript"></script>
        <script src="{{ assetLink('js', 'bootstrap') }}" type="text/javascript"></script>

        @yield('HeadInclude')

        <style type="text/css">

             .dataTables_wrapper table {display: table !important;}

            .product-description { overflow: visible !important;white-space: unset !important; }

            .notification-list { max-height: 350px; overflow-y: auto; }

            .noti_User { color: #6c757d !important; }

            .brand-image{float: none !important; margin-left: 0 !important;}

            .table { display: block;width: 100%;overflow-x: auto; }

            td{ word-break: break-word !important; }

            .table { width: 100% !important;display: table !important; }

            .list-group-item{ margin-bottom: auto !important; }

            .help-block { color : #dd4b39; }

             .text-red { color: red; }

             .nav-sidebar .nav-header:not(:first-of-type) {
                padding: 0.5rem;
            }

            .has-error label {
                color: #dd4b39 !important;
            }

            .has-error .form-control {
                border-color: #dd4b39 !important;
                box-shadow: none;
            }

            .btn.disabled, .btn[disabled], fieldset[disabled] .btn {
                box-shadow: none;
                cursor: not-allowed;
                opacity: 0.65;
                pointer-events: none;
            }

            .sidebar-wrapper {
                padding: 0.1px;
            }
        </style>
    </head>

    <body class="layout-fixed sidebar-expand-lg sidebar-mini bg-body-tertiary app-loaded fs-8 sidebar-open">

        <div class="app-wrapper">

            <?php
            $replacetop = \Illuminate\Support\Facades\Event::dispatch('service.desk.agent.topbar.replace', []);

            if (count($replacetop) == 0) {
                $replacetop = 0;
            } else {
                $replacetop = $replacetop[0];
            }

            $replaceside = \Illuminate\Support\Facades\Event::dispatch('service.desk.agent.sidebar.replace', []);

            if (count($replaceside) == 0) {
                $replaceside = 0;
            } else {
                $replaceside = $replaceside[0];
            }
            ?>

             <nav class="app-header navbar navbar-expand bg-body">
                <div class="container-fluid">
                <!-- Sidebar toggle button-->
                <ul class="navbar-nav">

                    <li class="nav-item">

                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button"><i class="nav-icon fa-solid fa-bars"></i></a>
                    </li>
                </ul>

                @if($replacetop==0)


                <ul class="navbar-nav">

                    <li class="nav-item d-none d-sm-inline-block">

                        <a id="dash" @yield('Dashboard') href="{{URL::route('dashboard')}}" onclick="clickDashboard(event);"
                            class="nav-link">
                            {!! Lang::get('lang.dashboard') !!}
                        </a>
                    </li>

                    <li class="nav-item d-none d-sm-inline-block">
                        <a href="#tab_user" data-bs-toggle="tab" @yield('Users') class="nav-link" onclick="clickUser(event);" id="user_tab">
                            {!! Lang::get('lang.users') !!}
                        </a>
                    </li>

                    <li class="nav-item d-none d-sm-inline-block">
                        <a href="#tab_ticket" data-bs-toggle="tab" @yield('Tickets') class="nav-link" onclick="clickTickets(event);" id="ticket_tab">
                            {!! Lang::get('lang.tickets') !!}
                        </a>
                    </li>

                    <li class="nav-item d-none d-sm-inline-block">
                        <a href="#tab_tools" data-bs-toggle="tab" @yield('Tools') class="nav-link" onclick="clickTools(event);" id="tools_tab">
                            {!! Lang::get('lang.tools') !!}
                        </a>
                    </li>

                    @if($auth_user_role == 'admin')
                    <li class="nav-item d-none d-sm-inline-block">
                        <a href="{{URL::route('report.index')}}" onclick="clickReport(event);" @yield('Report') class="nav-link">{!! Lang::get('lang.report') !!}</a>
                    </li>
                    @endif

                    <?php \Illuminate\Support\Facades\Event::dispatch('calendar.topbar', []); ?>
                </ul>
                @else
                <?php \Illuminate\Support\Facades\Event::dispatch('service.desk.agent.topbar', []); ?>
                @endif

                <ul class="navbar-nav ms-auto">

                    @if($auth_user_role == 'admin')

                    <li class="nav-item d-none d-sm-inline-block">
                        <a href="{{url('admin')}}" class="nav-link">{!! Lang::get('lang.admin_panel') !!}</a>
                    </li>
                    @endif

                    @include('themes.default1.update.notification')

                    <li class="nav-item dropdown notifications-menu" id="myDropdown">

                        <a href="#" class="nav-link" data-bs-toggle="dropdown" onclick="myFunction()">

                            <i class="nav-icon  fa-solid fa-bell"></i>

                            <span class="badge bg-warning text-dark navbar-badge" id="count">{!! $notifications->count() !!}</span>
                        </a>

                        <div class="dropdown-menu dropdown-menu-xl dropdown-menu-end">

                            <div id="alert11" class="alert alert-success alert-dismissible d-none">

                                <button id="dismiss11" type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>

                                <h4><i class="icon fa-solid fa-check"></i>Alert!</h4>

                                <div id="message-success1"></div>
                            </div>

                            <ul class="products-list product-list-in-card notification-list ps-2 pe-2">

                                 <li class="dropdown-header">You have {!! $notifications->count() !!} notifications.

                                    <a class="float-end" id="read-all" href="#">Mark all as read.</a>
                                </li>

                                @if($notifications->count())
                                @foreach($notifications->orderBy('created_at', 'desc')->get()->take(10) as $notification)
                                @if($notification->notification->type->type == 'registration')
                                @if($notification->is_read == 1)

                                <li class="item task d-flex align-items-center gap-2">
                                    <div class="product-img flex-shrink-0">
                                        <img src="{{$notification -> users -> profile_pic}}" alt="Product Image" class="img-size-50 rounded-circle">
                                    </div>
                                    <div class="product-info flex-grow-1">
                                        <span class="product-description">
                                            <a href="{!! route('user.show', $notification->notification->model_id) !!}" id="{{$notification -> notification_id}}"
                                                class='noti_User'>{!! $notification->notification->type->message !!}
                                            </a>
                                        </span>
                                    </div>
                                </li>

                                @else

                                <li class="item d-flex align-items-center gap-2">
                                    <div class="product-img flex-shrink-0">
                                        <img src="{{$notification -> users -> profile_pic}}" alt="Product Image" class="img-size-50 rounded-circle">
                                    </div>
                                    <div class="product-info flex-grow-1">
                                        <span class="product-description">
                                            <a href="{!! route('user.show', $notification->notification->model_id) !!}" id="{{$notification -> notification_id}}"
                                                class='noti_User'>{!! $notification->notification->type->message !!}
                                            </a>
                                        </span>
                                    </div>
                                </li>
                                @endif
                                @else
                                @if($notification->is_read == 1)

                                <li class="item task d-flex align-items-center gap-2">
                                    <div class="product-img flex-shrink-0">
                                        <img src="{{$notification -> users -> profile_pic}}" alt="Product Image" class="img-size-50 rounded-circle">
                                    </div>
                                    <div class="product-info flex-grow-1">
                                        <span class="product-description">
                                            <a href="{!! route('ticket.thread', $notification->notification->model_id) !!}" id='{{ $notification -> notification_id}}'
                                                class='noti_User'>
                                                {!! $notification->notification->type->message !!} with id "{!!$notification->notification->model->ticket_number!!}"
                                            </a>
                                        </span>
                                    </div>
                                </li>

                                @elseif($notification->notification->model)

                                <li class="item d-flex align-items-center gap-2">
                                    <div class="product-img flex-shrink-0">
                                        <img src="{{$notification -> users -> profile_pic}}" alt="Product Image" class="img-size-50 rounded-circle">
                                    </div>
                                    <div class="product-info flex-grow-1">
                                        <span class="product-description">
                                            <a href="{!! route('ticket.thread', $notification->notification->model_id) !!}" id='{{ $notification -> notification_id}}'
                                                class='noti_User'>
                                                {!! $notification->notification->type->message !!} with id "{!!$notification->notification->model->ticket_number!!}"
                                            </a>
                                        </span>
                                    </div>
                                </li>
                                @endif
                                @endif
                                @endforeach
                                @endif

                                <li class="item" style="position: relative;top: -5px;">

                                    <img src="{{asset("lb-faveo/media/images/gifloader.gif")}}" style="display: none;margin-left: 100px;" id="notification-loader"
                                        class="img-size-50">
                                </li>

                                <li class="dropdown-footer"><a class="text-dark" href="{{ url('notifications-list')}}">{{trans('lang.view_all')}}</a></li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item dropdown">

                        <?php $src = Lang::getLocale().'.png'; ?>

                        <a href="#" class="nav-link" data-bs-toggle="dropdown" aria-expanded="true">
                            <img class="mb-1" src="{{asset("lb-faveo/flags/$src")}}" style="height: 12px; width: 20px">
                        </a>

                        <div class="dropdown-menu dropdown-menu-sm dropdown-menu-end p-0" style="width:290px;">

                            @foreach($langs as $key => $value)
                                    <?php $src = $key . ".png"; ?>

                                <a href="#"
                                   class="dropdown-item d-flex align-items-center gap-2"
                                   id="{{$key}}"
                                   onclick="changeLang(this.id)">

                                    <img src="{{asset("lb-faveo/flags/$src")}}" width="20" height="13" class="me-2">

                                    <span>
                                     {{$value[0]}}
                                    @if(Lang::getLocale() == "ar")
                                       &rlm;
                                    @endif
                                  ({{$value[1]}})
                                   </span>

                                </a>

                            @endforeach

                        </div>
                    </li>

                    <li class="nav-item dropdown user-menu">

                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            @if($auth_user_id)
                            <img src="{{$auth_user_profile_pic}}"class="user-image rounded-circle shadow-sm" alt="User Image"/>
                            <span class="d-none d-md-inline">{{$auth_name}}</span>
                            @endif
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end">
                            <!-- User image -->
                            <li class="user-header bg-secondary"  style="background-color:#343F44;">

                                <img src="{{$auth_user_profile_pic}}" class="rounded-circle shadow-sm" alt="User Image" />

                                <p style="margin-top: 0px;">{{$auth_name}}
                                    <small class="text-capitalize">{{$auth_user_role}}</small>
                                </p>
                            </li>
                            <!-- Menu Footer-->
                            <li class="user-footer">

                                <a href="{{URL::route('profile')}}" class="btn btn-primary ">{!! Lang::get('lang.profile') !!}</a>

                                <a href="{{url('auth/logout')}}" class="btn btn-danger  float-end">{!! Lang::get('lang.sign_out') !!}</a>
                            </li>
                        </ul>
                    </li>
                </ul>
                </div>
            </nav>

            <!-- Left side column. contains the logo and sidebar -->
            <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
                <div class="sidebar-brand">

                <a href="http://www.faveohelpdesk.com" class="brand-link " style="text-align: center;">
                    <img src="{{ asset('lb-faveo/media/images/logo.png')}}" class="brand-image" alt="Company Log0">
                </a>
                </div>

                <div class="sidebar-wrapper">
                    <div class="profile-container">

                        <div class="d-flex align-items-center px-3 py-2">
                        @if (trim($__env->yieldContent('profileimg')))
                            @yield('profileimg')
                        @else
                            <img id="sidebar-profile-img" src="{{$auth_user_profile_pic}}" alt="User Image"
                                class="rounded-circle shadow-sm me-3" style="width: 30px;height: 30px;">
                        @endif
                        @if($auth_user_id)
                            <a class="text-truncate text-sm" href="{!! url('profile') !!}">{{$auth_name}}</a>
                        @endif
                    </div>

                    </div>

                    <nav class="mt-2">

                        <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">

                            @if($replaceside==0)
                            @yield('sidebar')
                            <li class="nav-header">{!! Lang::get('lang.Tickets') !!}</li>

                            <li class="nav-item">
                                <a href="{{ url('tickets')}}" id="load-inbox" @yield('inbox') class="nav-link">
                                    <i class="nav-icon fa-solid fa-envelope"></i>
                                    <p>{!! Lang::get('lang.inbox') !!} <span class="badge bg-success nav-badge">{{$tickets -> count()}}</span></p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{url('/tickets?show=mytickets')}}" id="load-myticket" @yield('myticket') class="nav-link" >
                                    <i class="nav-icon fa-solid fa-user"></i>
                                    <p>{!! Lang::get('lang.my_tickets') !!} <span class="badge bg-success nav-badge">{{$myticket -> count()}}</span></p>
                                </a>
                            </li>

                            <li class="nav-item">
                                 <a href="{{url('/tickets?assigned[]=0')}}" id="load-unassigned" @yield('unassigned') class="nav-link">
                                    <i class="nav-icon fa-solid fa-table-cells"></i>
                                    <p>{!! Lang::get('lang.unassigned') !!} <span class="badge bg-success nav-badge">{{$unassigned -> count()}}</span></p>
                                </a>
                            </li>

                            <li class="nav-item">
                                 <a href="{{url('/tickets?show=overdue')}}" id="load-unassigned" @yield('overdue') class="nav-link">
                                    <i class="nav-icon fa-solid fa-calendar-xmark"></i>
                                    <p>{!! Lang::get('lang.overdue') !!} <span class="badge bg-success nav-badge">{{$overdues->count()}}</span></p>
                                </a>
                            </li>

                            <li class="nav-item">
                                 <a href="{{url('/tickets?show=trash')}}" @yield('trash',) class="nav-link">
                                    <i class="nav-icon fa-solid fa-trash"></i>
                                    <p>{!! Lang::get('lang.trash') !!} <span class="badge bg-success nav-badge">{{$deleted -> count()}}</span></p>
                                </a>
                            </li>

                            <li class="nav-header">{!! Lang::get('lang.Departments') !!}</li>


                            <?php
                            $flattened = $department->flatMap(function ($values) {
                                return $values->keyBy('status');
                            });
                            $statuses = $flattened->keys();
                            ?>
                            <?php
                                $segments = \Request::segments();
                                $segment = "";
                                foreach($segments as $seg){
                                    $segment.="/".$seg;
                                }
                                if(count($segments) > 2) {
                                    $dept2 = $segments[1];
                                    $status2 = $segments[2];
                                } else {
                                     $dept2 = '';
                                    $status2 = '';
                                }
                            ?>

                            @foreach($department as $name=>$dept)

                            <li class="nav-item">

                                <a href="#" @if($dept2 === $name) @yield('ticket-bar') @endif class="nav-link">
                                    <i class="nav-icon fa-solid fa-folder-open"></i>
                                    <p>{!!trans('lang.'.strtolower($name))!!}<i class="nav-arrow fa-solid fa-angle-left"></i></p>
                                </a>

                                @foreach($statuses as $status)
                                @if($dept->get($status))

                                <ul class="nav nav-treeview">

                                    <li class="nav-item">
                                        <a href="{!! url('tickets?departments='.$name.'&status='.$dept->get($status)->status) !!}" @if($status2 == $dept->get($status)->status && $dept2 === $name) @yield('inbox') @endif class="nav-link">
                                            <i class="fa-regular fa-circle nav-icon"></i>
                                            <p>{!!trans('lang.'.strtolower($dept->get($status)->status)) !!} <span class="badge bg-success nav-badge">{{$dept->get($status)->count}}</span></p>
                                        </a>
                                    </li>
                                </ul>
                                @endif
                                @endforeach
                            </li>
                            @endforeach
                            @else

                            <?php \Event::dispatch('service.desk.agent.sidebar', []); ?>
                            @endif
                        </ul>
                    </nav>
                </div>
            </aside>

            <?php
            $agent_group = $auth_user_assign_group;
            $group = App\Model\helpdesk\Agent\Groups::where('id', '=', $agent_group)->first();
            ?>
            <!-- Right side column. Contains the navbar and content of the page -->
            <main class="app-main">

                <div class="app-content-top-area tab-content">
                  @if($replacetop==0)
                  <div @yield('user') class="tab-pane" id="tab_user">

                        <nav class="navbar navbar-expand" data-bs-theme="light">

                          <ul class="navbar-nav">

                            <li class="nav-item d-none d-sm-inline-block">
                              <a href="{{ url('user')}}" @yield('user-directory') class="nav-link">{!! Lang::get('lang.user_directory') !!}</a>
                            </li>

                            <li class="nav-item d-none d-sm-inline-block">
                              <a href="{{ url('organizations')}}" @yield('organizations') class="nav-link">{!! Lang::get('lang.organizations') !!}</a>
                            </li>
                          </ul>
                        </nav>
                    </div>

                    <div @yield('ticket') class="tab-pane" id="tab_ticket">

                        <nav class="navbar navbar-expand" data-bs-theme="light">

                          <ul class="navbar-nav">

                            <li class="nav-item d-none d-sm-inline-block">
                              <a href="{{ url('/tickets?last-response-by[]=Client') }}" @yield('open') class="nav-link" id="load-open">{!! Lang::get('lang.not-answered') !!}</a>
                            </li>

                            <li class="nav-item d-none d-sm-inline-block">
                              <a href="{{ url('/tickets?last-response-by[]=Agent')}}" @yield('answered') class="nav-link" id="load-answered">{!! Lang::get('lang.answered') !!}</a>
                            </li>

                            <li class="nav-item d-none d-sm-inline-block">
                              <a href="{{ url('/tickets?assigned[]=1') }}" @yield('assigned') class="nav-link" id="load-assigned">{!! Lang::get('lang.assigned') !!}</a>
                            </li>

                            <li class="nav-item d-none d-sm-inline-block">
                              <a href="{{ url('/tickets?show=closed') }}" @yield('closed') class="nav-link">{!! Lang::get('lang.closed') !!}</a>
                            </li>

                            <?php if ($group->can_create_ticket == 1) { ?>
                            <li class="nav-item d-none d-sm-inline-block">
                              <a href="{{ url('/newticket')}}" @yield('newticket') class="nav-link">{!! Lang::get('lang.create_ticket') !!}</a>
                            </li>
                            <?php } ?>
                          </ul>
                        </nav>
                    </div>

                    <div @yield('tool') class="tab-pane" id="tab_tools">

                        <nav class="navbar navbar-expand" data-bs-theme="light">

                          <ul class="navbar-nav">

                            <li class="nav-item d-none d-sm-inline-block">
                              <a href="{{ url('/canned/list')}}" @yield('tools') class="nav-link">{!! Lang::get('lang.canned_response') !!}</a>
                            </li>

                            <li class="nav-item d-none d-sm-inline-block">
                              <a href="{{ url('/comment')}}" @yield('kb') class="nav-link">{!! Lang::get('lang.knowledge_base') !!}</a>
                            </li>
                          </ul>
                        </nav>
                    </div>
                    @endif
                    <?php \Event::dispatch('service.desk.agent.topsubbar', []); ?>
                  <!-- /.tab-pane -->
                </div>
                <!-- Content Header (Page header) -->
                <div class="app-content-header">
                  <div class="container-fluid">
                    <div class="row mb-2">
                      <div class="col-sm-6">
                        <h3 class="m-0 text-dark">@yield('PageHeader')</h3>
                      </div><!-- /.col -->
                      <div class="col-sm-6">

                        {!! Breadcrumbs::render() !!}
                      </div><!-- /.col -->
                    </div><!-- /.row -->
                  </div><!-- /.container-fluid -->
                </div>

                <div class="app-content">

                    <div class="container-fluid">

                        @if($dummy_installation == 1 || $dummy_installation == '1')
                        <div class="alert alert-info alert-dismissible">
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                            <i class="icon fa-solid fa-triangle-exclamation"></i> @if (\Auth::user()->role == 'admin')
                                {{Lang::get('lang.dummy_data_installation_message')}} <a href="{{route('clean-database')}}">{{Lang::get('lang.click')}}</a> {{Lang::get('lang.clear-dummy-data')}}
                            @else
                                {{Lang::get('lang.clear-dummy-data-agent-message')}}
                            @endif
                        </div>
                        @elseif (!$is_mail_conigured)
                        <div class="row">
                            <div class="col-md-12">
                                <div class="callout callout-warning bg-warning">
                                    <p>
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                        @if (\Auth::user()->role == 'admin')
                                            {{Lang::get('lang.system-outgoing-incoming-mail-not-configured')}}&nbsp;<a href="{{URL::route('emails.create')}}">{{Lang::get('lang.confihure-the-mail-now')}}</a>
                                        @else
                                            {{Lang::get('lang.system-mail-not-configured-agent-message')}}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif

                        @yield('content')
                    </div>
                </div>
            </main>

            <footer class="app-footer">

                <div class="float-end d-none d-sm-inline">

                    <span style="font-weight: 500">{!! Lang::get('lang.version') !!}</span> {!! Config::get('app.version') !!}
                </div>

                <span style="font-weight: 500">{!! Lang::get('lang.copyright') !!} &copy; {!! date('Y') !!}  <a href="{!! $company->website !!}" target="_blank">{!! $company->company_name !!}</a>.</span> {!! Lang::get('lang.all_rights_reserved') !!}. {!! Lang::get('lang.powered_by') !!} <a href="http://www.faveohelpdesk.com/" target="_blank">Faveo</a>
            </footer>
        </div><!-- ./wrapper -->
        <script src="{{ assetLink('js', 'adminlte') }}" type="text/javascript"></script>

        <!-- Slimscroll -->
        <script src="{{ assetLink('js', 'overlay-scrollbars') }}" type="text/javascript"></script>

        <script src="{{ assetLink('js', 'datatables') }}" type="text/javascript"></script>

        <script src="{{ assetLink('js', 'datatables-bootstrap') }}" type="text/javascript"></script>

        <script src="{{ assetLink('js', 'jquery-rating') }}" type="text/javascript"></script>

        <script src="{{ assetLink('js', 'select2') }}" type="text/javascript"></script>

        <script src="{{ assetLink('js', 'moment') }}" type="text/javascript"></script>

        <!-- Full Calendar -->
        <script src="{{ assetLink('js', 'fullcalendar') }}" type="text/javascript"></script>

        <script src="{{ assetLink('js', 'daterangepicker') }}" type="text/javascript"></script>

        <script src="{{ assetLink('js', 'datetimepicker') }}" type="text/javascript"></script>

        <script src="{{ assetLink('js', 'summernote') }}" type="text/javascript"></script>

        <script src="{{ assetLink('js', 'icheck') }}" type="text/javascript"></script>

        {{-- jQuery UI (Agent Panel) --}}
        <script src="{{ assetLink('js', 'jquery-ui-agent') }}" type="text/javascript"></script>
        <script>
                    $(document).ready(function () {

            $('.noti_User').click(function () {
            var id = this.id;
                    var dataString = 'id=' + id;
                    $.ajax
                    ({
                    type: "POST",
                            url: "{{url('mark-read')}}" + "/" + id,
                            data: dataString,
                            cache: false,
                            success: function (html)
                            {
                            }
                    });
            });
            });
                    $('#read-all').click(function () {

            var id2 = <?php echo $auth_user_id ?>;
                    var dataString = 'id=' + id2;
                    $.ajax
                    ({
                    type: "POST",
                            url: "{{url('mark-all-read')}}" + "/" + id2,
                            data: dataString,
                            cache: false,
                            beforeSend: function () {
                            $('#myDropdown').on('hide.bs.dropdown', function () {
                            return false;
                            });
                                    $("#refreshNote").hide();
                                    $("#notification-loader").show();
                            },
                            success: function (response) {
                            $("#refreshNote").load("<?php echo $_SERVER['REQUEST_URI']; ?>  #refreshNote");
                                    $("#notification-loader").hide();
                                    $('#myDropdown').removeClass('show');
                            }
                    });
            });</script>
        <script>
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
                            //Handle starring for font awesome
                            $(".mailbox-star").click(function(e) {
                    e.preventDefault();
                            var $this = $(this).find("a > i");
                            $this.toggleClass("fa-solid");
                            $this.toggleClass("fa-regular");
                    });
                    });</script>

        <!-- Language Changer. The base url has to be handed over, without it the script
             falls back to a relative path which 404s on nested routes. -->
        <script>window.faveoBaseUrl = @json(url('/'));</script>
        <script src="{{asset("lb-faveo/js/languagechanger.js")}}" type="text/javascript"></script>
        <script src="{{asset("lb-faveo/plugins/filebrowser/plugin.js")}}" type="text/javascript"></script>

        <script type="text/javascript">
                    $.ajaxSetup({
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') }
                    });</script>
        <script type="text/javascript">

              function clickDashboard(e) {
                    if (e.ctrlKey === true) {
                    window.open('{{URL::route("dashboard")}}', '_blank');
                    } else {
                    window.location = "{{URL::route('dashboard')}}";
                    }
                    }

                    function clickUser(e) {
                        $(".app-content-top-area").show();
                        $("#ticket_tab").removeClass("active");
                        $("#tools_tab").removeClass("active");
                        $("#tab_ticket").removeClass("active");
                        $("#tab_ticket").css('display',"none");
                        $("#tab_tools").removeClass("active");
                        $("#tab_tools").css('display',"none");
                        $("#tab_user").css('display',"block");
                    }

                    function clickTickets(e) {
                        $(".app-content-top-area").show();
                        $("#user_tab").removeClass("active");
                        $("#tools_tab").removeClass("active");
                        $("#tab_user").removeClass("active");
                        $("#tab_user").css('display',"none");
                        $("#tab_tools").removeClass("active");
                        $("#tab_tools").css('display',"none");
                        $("#tab_ticket").css('display',"block");
                    }

                    function clickTools(e) {
                        $(".app-content-top-area").show();
                        $("#ticket_tab").removeClass("active");
                        $("#user_tab").removeClass("active");
                        $("#tab_ticket").removeClass("active");
                        $("#tab_ticket").css('display',"none");
                        $("#tab_tools").css('display',"block");
                        $("#tab_user").removeClass("active");
                        $("#tab_user").css('display',"none");
                    }

            function clickReport(e) {
            if (e.ctrlKey === true) {
            window.open('{{URL::route("report.index")}}', '_blank');
            } else {
            window.location = "{{URL::route('report.index')}}";
            }
            }

            $(document).ready(function() {
                // Hide sub-bar container on page load if no pane is visible
                if (!$('#tab_user').is(':visible') && !$('#tab_ticket').is(':visible') && !$('#tab_tools').is(':visible')) {
                    $('.app-content-top-area').hide();
                }
            });
        </script>
        <script>
</script>
<?php \Illuminate\Support\Facades\Event::dispatch('show.calendar.script', []); ?>
<?php \Illuminate\Support\Facades\Event::dispatch('load-calendar-scripts', []); ?>
        @yield('FooterInclude')
    </body>
</html>
