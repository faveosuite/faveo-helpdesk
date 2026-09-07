<!DOCTYPE html>
<html dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
        <meta charset="UTF-8">
        <title>Faveo | HELP DESK</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

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
        <link href="{{ assetLink('css', 'jquery-ui') }}" rel="stylesheet" type="text/css" />

        <!-- Colorpicker -->
        <link href="{{ assetLink('css', 'colorpicker') }}" rel="stylesheet" type="text/css" />

        <!-- Google Fonts -->
        <link href="{{ assetLink('external', 'google-fonts') }}" rel="stylesheet" type="text/css" />

        <!-- JS -->
        <script src="{{ assetLink('js', 'jquery') }}" type="text/javascript"></script>
        <script src="{{ assetLink('js', 'jquery-migrate') }}" type="text/javascript"></script>
        <script src="{{ assetLink('js', 'jquery-ui') }}" type="text/javascript"></script>
        <script src="{{ assetLink('js', 'popper') }}" type="text/javascript"></script>
        <script src="{{ assetLink('js', 'bootstrap') }}" type="text/javascript"></script>
        @yield('HeadInclude')

        <style type="text/css">

            .dataTables_wrapper table {display: table !important;}

            .product-description { overflow: visible !important;white-space: unset !important; }

            .noti_User { color: #6c757d !important; }

            .brand-image{float: none !important; margin-left: 0 !important;}

            .table { display: block;width: 100%;overflow-x: auto; }

            td{ word-break: break-all !important; }

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
        </style>
    </head>
    <body class="layout-fixed sidebar-expand-lg sidebar-mini bg-body-tertiary app-loaded fs-8 sidebar-open">

        <?php
        $replacetop = 0;
        $replacetop = \Illuminate\Support\Facades\Event::dispatch('service.desk.admin.topbar.replace', []);
        if (count($replacetop) == 0) {
            $replacetop = 0;
        } else {
            $replacetop = $replacetop[0];
        }
        $replaceside = 0;
        $replaceside = \Illuminate\Support\Facades\Event::dispatch('service.desk.admin.sidebar.replace', []);
        if (count($replaceside) == 0) {
            $replaceside = 0;
        } else {
            $replaceside = $replaceside[0];
        }
        //dd($replacetop);
        ?>

        <div class="app-wrapper">

            <nav class="app-header navbar navbar-expand bg-body">
                <div class="container-fluid">

                <!-- Sidebar toggle button-->
                <ul class="navbar-nav">

                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button"><i class="nav-icon fa-solid fa-bars"></i></a>
                    </li>
                </ul>

                <?php $notifications = App\Http\Controllers\Common\NotificationController::getNotifications(); ?>

                @if($replacetop==0)
                <ul class="navbar-nav">

                    <li class="nav-item">
                        <a id="dash" @yield('settings') href="{!! url('dashboard') !!}" class="nav-link mb-1">
                            {!! Lang::get('lang.agent_panel') !!}
                        </a>
                    </li>
                </ul>
                @else
                <?php \Illuminate\Support\Facades\Event::dispatch('service.desk.admin.topbar', []); ?>
                @endif

                <ul class="navbar-nav d-flex align-content-sm-center ms-auto">

                    <li class="nav-item d-none d-sm-inline-block">
                        <a href="{{url('admin')}}" class="nav-link">{!! Lang::get('lang.admin_panel') !!}</a>
                    </li>

                    @include('themes.default1.update.notification')

                    <li class="nav-item dropdown notifications-menu" id="myDropdown">

                        <a href="#" class="nav-link" data-bs-toggle="dropdown" onclick="myFunction()">

                            <i class="nav-icon fa-solid fa-bell"></i>

                            <span class="badge bg-warning text-dark navbar-badge" id="count">{!! $notifications->count() !!}</span>
                        </a>

                        <div class="dropdown-menu dropdown-menu-xl dropdown-menu-end">

                            <div id="alert11" class="alert alert-success alert-dismissible d-none">

                                <button id="dismiss11" type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>

                                <h4><i class="icon fa-solid fa-check"></i>Alert!</h4>

                                <div id="message-success1"></div>
                            </div>

                            <ul class="products-list product-list-in-card ps-2 pe-2" style="height: 350px;overflow-y: scroll;">

                                <li class="dropdown-header">You have {!! $notifications->count() !!} notifications.

                                    <a class="float-end" id="read-all" href="#">Mark all as read.</a>
                                </li>

                                @if($notifications->count())
                                @foreach($notifications->orderBy('created_at', 'desc')->get()->take(10) as $notification)

                                @if($notification->notification->type->type == 'registration')
                                @if($notification->is_read == 1)

                                <li class="item" class="task">

                                    <div class="product-img">

                                        <img src="{{$notification -> users -> profile_pic}}" alt="Product Image" class="img-size-50 rounded-circle">
                                    </div>

                                    <div class="product-info">

                                        <span class="product-description">

                                            <a href="{!! route('user.show', $notification->notification->model_id) !!}" id="{{$notification -> notification_id}}"
                                                class='noti_User'>{!! $notification->notification->type->message !!}
                                            </a>
                                        </span>
                                    </div>
                                </li>

                                @else

                                <li class="item">

                                    <div class="product-img">

                                        <img src="{{$notification -> users -> profile_pic}}" alt="Product Image" class="img-size-50 rounded-circle">
                                    </div>

                                    <div class="product-info">

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

                                <li class="item" class="task">

                                    <div class="product-img">

                                        <img src="{{$notification -> users -> profile_pic}}" alt="Product Image" class="img-size-50 rounded-circle">
                                    </div>

                                    <div class="product-info">

                                        <span class="product-description">

                                            <a href="{!! route('ticket.thread', $notification->notification->model_id) !!}" id='{{ $notification -> notification_id}}'
                                                class='noti_User'>
                                                {!! $notification->notification->type->message !!} with id "{!!$notification->notification->model->ticket_number!!}"
                                            </a>
                                        </span>
                                    </div>
                                </li>

                                @elseif($notification->notification->model)

                                <li class="item">

                                    <div class="product-img">

                                        <img src="{{$notification -> users -> profile_pic}}" alt="Product Image" class="img-size-50 rounded-circle">
                                    </div>

                                    <div class="product-info">

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

                                <li class="dropdown-footer"><a class="text-dark" href="{{ url('notifications-list')}}">View all</a>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item dropdown">

                        <?php $src = Lang::getLocale().'.png'; ?>

                        <a href="#" class="nav-link" data-bs-toggle="dropdown" aria-expanded="true">
                            <img class="mb-1" src="{{asset("lb-faveo/flags/$src")}}" style="height: 12px; width: 20px">
                        </a>

                       <div class="dropdown-menu dropdown-menu-sm dropdown-menu-end p-0" style="width: 290px;">

                           @foreach($langs as $key => $value)
                            <?php $src = $key.".png"; ?>
                            <a href="#" class="dropdown-item d-flex align-items-center gap-2" id="{{$key}}" onclick="changeLang(this.id)">
                                <img src="{{asset("lb-faveo/flags/$src")}}" width="20" height="13" class="me-2">
                                <span>{{$value[0]}}
                                @if(Lang::getLocale() == "ar")
                                &rlm;
                                @endif
                                ({{$value[1]}})</span>
                            </a>
                            @endforeach
                       </div>
                    </li>

                    <li class="nav-item dropdown user-menu">

                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            @if(Auth::user())
                            <img src="{{Auth::user()->profile_pic}}" class="user-image rounded-circle shadow-sm" alt="User Image"/>
                            <span class="d-none d-md-inline">{!! Auth::user()->first_name." ".Auth::user()->last_name !!}</span>
                            @endif
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end">
                            <!-- User image -->
                            <li class="user-header bg-secondary"  style="background-color:#343F44;">
                                @if(Auth::user())
                                <img src="{{Auth::user()->profile_pic}}" class="rounded-circle shadow-sm" alt="User Image" />

                                <p style="margin-top: 0px;">{!! Auth::user()->first_name !!}{!! " ". Auth::user()->last_name !!}
                                    <small class="text-capitalize">{{Auth::user()->role}}</small>
                                </p>
                                @endif
                            </li>
                            <!-- Menu Footer-->
                            <li class="user-footer">

                                <a href="{{url('admin-profile')}}" class="btn btn-primary ">{!! Lang::get('lang.profile') !!}</a>

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
                    <a href="http://www.faveohelpdesk.com" class="brand-link" style="text-align: center;">
                        <img src="{{ asset('lb-faveo/media/images/logo.png')}}" class="brand-image" alt="Company Log0">
                    </a>
                </div>

                <div class="sidebar-wrapper">
                    <div class="profile-container">

                    <div class="d-flex align-items-center px-3 py-2">
                        <img id="sidebar-profile-img" src="{{Auth::user()->profile_pic}}" alt="User Image"
                            class="rounded-circle shadow-sm me-2" style="width: 30px;height: 30px;">
                        @if(Auth::user())
                            <a class="text-truncate text-sm" href="{!! url('admin-profile') !!}">{{Auth::user()->first_name}} {{Auth::user()->last_name}}</a>
                        @endif
                    </div>
                    </div>

                    <nav class="mt-2">

                        <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                            @if($replaceside==0)

                            <li class="nav-header">{!! Lang::get('lang.settings-2') !!}</li>

                            <li @yield('staff-menu-parent') class="nav-item">

                                <a  href="#" @yield('Staffs') class="nav-link">
                                    <i class="nav-icon fa-solid fa-users"></i>
                                    <p>{!! Lang::get('lang.staffs') !!} <i class="nav-arrow fa-solid fa-angle-left"></i></p>
                                </a>

                                <ul @yield('staff-menu-open') class="nav nav-treeview">

                                    <li class="nav-item">
                                        <a href="{{ url('agents') }}" @yield('agents') class="nav-link">
                                            <i class="nav-icon fa-solid fa-user"></i>
                                            <p>{!! Lang::get('lang.agents') !!}</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{ url('departments') }}" @yield('departments') class="nav-link">
                                            <i class="nav-icon fa-solid fa-sitemap"></i>
                                            <p>{!! Lang::get('lang.departments') !!}</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{ url('teams') }}" @yield('teams') class="nav-link">
                                            <i class="nav-icon fa-solid fa-users"></i>
                                            <p>{!! Lang::get('lang.teams') !!}</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{ url('groups') }}" @yield('groups') class="nav-link">
                                            <i class="nav-icon fa-solid fa-object-group"></i>
                                            <p>{!! Lang::get('lang.groups') !!}</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li @yield('email-menu-parent') class="nav-item">

                                <a href="#" @yield('Emails') class="nav-link">
                                    <i class="nav-icon fa-solid fa-envelope"></i>
                                    <p>{!! Lang::get('lang.email') !!} <i class="nav-arrow fa-solid fa-angle-left"></i></p>
                                </a>

                                <ul @yield('email-menu-open') class="nav nav-treeview">

                                    <li class="nav-item">
                                        <a href="{{ url('emails') }}" @yield('emails') class="nav-link">
                                            <i class="nav-icon fa-solid fa-envelope"></i>
                                            <p>{!! Lang::get('lang.emails') !!}</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{ url('banlist') }}" @yield('ban') class="nav-link">
                                            <i class="nav-icon fa-solid fa-ban"></i>
                                            <p>{!! Lang::get('lang.ban_lists') !!}</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{ url('template-sets') }}" @yield('template') class="nav-link">
                                            <i class="nav-icon fa-solid fa-reply"></i>
                                            <p>{!! Lang::get('lang.templates') !!}</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{url('getemail')}}" @yield('email') class="nav-link">
                                            <i class="nav-icon fa-solid fa-at"></i>
                                            <p>{!! Lang::get('lang.email-settings') !!}</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{ url('queue') }}" @yield('queue') class="nav-link">
                                            <i class="nav-icon fa-solid fa-upload"></i>
                                            <p>{!! Lang::get('lang.queues') !!}</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{ url('getdiagno') }}" @yield('diagnostics') class="nav-link">
                                            <i class="nav-icon fa-solid fa-plus"></i>
                                            <p>{!! Lang::get('lang.diagnostics') !!}</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li @yield('manage-menu-parent') class="nav-item">

                                <a href="#" @yield('Manage') class="nav-link">
                                    <i class="nav-icon fa-solid fa-cubes"></i>
                                    <p>{!! Lang::get('lang.manage') !!} <i class="nav-arrow fa-solid fa-angle-left"></i></p>
                                </a>

                                <ul @yield('manage-menu-open') class="nav nav-treeview">

                                    <li class="nav-item">
                                        <a href="{{url('helptopic')}}" @yield('help') class="nav-link">
                                            <i class="nav-icon fa-solid fa-file-lines"></i>
                                            <p>{!! Lang::get('lang.help_topics') !!}</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{url('sla')}}" @yield('sla') class="nav-link">
                                            <i class="nav-icon fa-solid fa-clock"></i>
                                            <p>{!! Lang::get('lang.sla_plans') !!}</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{url('forms')}}" @yield('forms') class="nav-link">
                                            <i class="nav-icon fa-solid fa-file-lines"></i>
                                            <p>{!! Lang::get('lang.forms') !!}</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{url('workflow')}}" @yield('workflow') class="nav-link">
                                            <i class="nav-icon fa-solid fa-sitemap"></i>
                                            <p>{!! Lang::get('lang.workflow') !!}</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{url('ticket/priority')}}" @yield('priority') class="nav-link">
                                            <i class="nav-icon fa-solid fa-asterisk"></i>
                                            <p>{!! Lang::get('lang.priority') !!}</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{url('url/settings')}}" @yield('url') class="nav-link">
                                            <i class="nav-icon fa-solid fa-server"></i>
                                            <p>{!! Lang::get('lang.url') !!}</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li @yield('ticket-menu-parent') class="nav-item">

                                <a href="#" @yield('Tickets') class="nav-link">
                                    <i class="nav-icon fa-solid fa-ticket-alt"></i>
                                    <p>{!! Lang::get('lang.tickets') !!} <i class="nav-arrow fa-solid fa-angle-left"></i></p>
                                </a>

                                <ul @yield('ticket-menu-open') class="nav nav-treeview">

                                    <li class="nav-item">
                                        <a href="{{url('getticket')}}" @yield('tickets') class="nav-link">
                                            <i class="nav-icon fa-solid fa-file-lines"></i>
                                            <p>{!! Lang::get('lang.ticket') !!}</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{url('getresponder')}}" @yield('auto-response') class="nav-link">
                                            <i class="nav-icon fa-solid fa-reply-all"></i>
                                            <p>{!! Lang::get('lang.auto_response') !!}</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{url('getalert')}}" @yield('alert') class="nav-link">
                                            <i class="nav-icon fa-solid fa-bell"></i>
                                            {!! Lang::get('lang.alert_notices') !!}
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{url('setting-status')}}" @yield('status') class="nav-link">
                                            <i class="nav-icon fa-solid fa-plus-square"></i>
                                            <p>{!! Lang::get('lang.status') !!}</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{url('getratings')}}" @yield('ratings') class="nav-link">
                                            <i class="nav-icon fa-solid fa-star"></i>
                                            <p>{!! Lang::get('lang.ratings') !!}</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{url('close-workflow')}}" @yield('close-workflow') class="nav-link">
                                            <i class="nav-icon fa-solid fa-sitemap"></i>
                                            <p>{!! Lang::get('lang.close-workflow') !!}</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li @yield('settings-menu-parent') class="nav-item">

                                <a href="#" @yield('Settings') class="nav-link">
                                    <i class="nav-icon fa-solid fa-gear"></i>
                                    <p>{!! Lang::get('lang.settings') !!} <i class="nav-arrow fa-solid fa-angle-left"></i></p>
                                </a>

                                <ul @yield('settings-menu-open') class="nav nav-treeview">

                                    <li class="nav-item">
                                        <a href="{{url('getcompany')}}" @yield('company') class="nav-link">
                                            <i class="nav-icon fa-solid fa-building"></i>
                                            <p>{!! Lang::get('lang.company') !!}</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{url('getsystem')}}" @yield('system') class="nav-link">
                                            <i class="nav-icon fa-solid fa-laptop"></i>
                                            <p>{!! Lang::get('lang.system') !!}</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{ url('social/media') }}" @yield('social-login') class="nav-link">
                                            <i class="nav-icon fa-solid fa-globe"></i>
                                            <p>{!! Lang::get('lang.social-login') !!}</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{url('languages')}}" @yield('languages') class="nav-link">
                                            <i class="nav-icon fa-solid fa-language"></i>
                                            <p>{!! Lang::get('lang.language') !!}</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{url('job-scheduler')}}" @yield('cron') class="nav-link">
                                            <i class="nav-icon fa-solid fa-hourglass"></i>
                                            <p>{!! Lang::get('lang.cron') !!}</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{url('security')}}" @yield('security') class="nav-link">
                                            <i class="nav-icon fa-solid fa-lock"></i>
                                            <p>{!! Lang::get('lang.security') !!}</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{url('settings-notification')}}" @yield('notification') class="nav-link">
                                            <i class="nav-icon fa-solid fa-bell"></i>
                                            <p>{!! Lang::get('lang.notifications') !!}</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{url('storage')}}" @yield('storage') class="nav-link">
                                            <i class="nav-icon fa-solid fa-floppy-disk"></i>
                                            <p>{!! Lang::get('storage::lang.storage') !!}</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li @yield('error-menu-parent') class="nav-item">

                                <a href="#" @yield('error-bugs') class="nav-link">
                                    <i class="nav-icon fa-solid fa-heartbeat"></i>
                                    <p>{!! Lang::get('lang.error-debug') !!} <i class="nav-arrow fa-solid fa-angle-left"></i></p>
                                </a>

                                <ul @yield('error-menu-open') class="nav nav-treeview">

                                    <li class="nav-item">
                                        <a href="{{ route('err.debug.settings') }}" @yield('debugging-option') class="nav-link">
                                            <i class="nav-icon fa-solid fa-bug"></i>
                                            <p>{!! Lang::get('lang.debug-options') !!}</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li @yield('widget-menu-parent') class="nav-item">

                                <a href="#" @yield('Themes') class="nav-link">
                                    <i class="nav-icon fa-solid fa-chart-pie"></i>
                                    <p>{!! Lang::get('lang.widgets') !!} <i class="nav-arrow fa-solid fa-angle-left"></i></p>
                                </a>

                                <ul @yield('widget-menu-open') class="nav nav-treeview">

                                    <li class="nav-item">
                                        <a href="{{ url('widgets') }}" @yield('widget') class="nav-link">
                                            <i class="nav-icon fa-solid fa-list-alt"></i>
                                            <p>{!! Lang::get('lang.widgets') !!}</p>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{ url('social-buttons') }}" @yield('social') class="nav-link">
                                            <i class="nav-icon fa-solid fa-cubes"></i>
                                            <p>{!! Lang::get('lang.social') !!}</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li class="nav-item">
                                <a href="{{ url('plugins') }}" @yield('Plugins') class="nav-link">
                                    <i class="nav-icon fa-solid fa-plug"></i>
                                    <p>{!! Lang::get('lang.plugin') !!}</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ url('api') }}" @yield('API') class="nav-link">
                                    <i class="nav-icon fa-solid fa-gears"></i>
                                    <p>{!! Lang::get('lang.api') !!}</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ url('logs') }}" @yield('Log') class="nav-link">
                                    <i class="nav-icon fa-solid fa-lock"></i>
                                    <p>{{Lang::get('log::lang.logs')}}</p>
                                </a>
                            </li>
                            @endif
                            <?php \Illuminate\Support\Facades\Event::dispatch('service.desk.admin.sidebar', []); ?>
                        </ul>
                    </nav>
                </div>
            </aside>

            <main class="app-main">
                <!-- Right side column. Contains the navbar and content of the page -->
                <div class="app-content" style="padding-bottom: 1px;">

                    <div class="app-content-header">
                        <div class="container-fluid">
                            <div class="row mb-2">
                                <div class="col-sm-6">
                                    <h1 class="m-0 text-dark">@yield('PageHeader')</h1>
                                </div><!-- /.col -->
                                <div class="col-sm-6">
                                    {!! Breadcrumbs::render() !!}
                                </div><!-- /.col -->
                            </div><!-- /.row -->
                        </div><!-- /.container-fluid -->
                    </div>

                    <div class="app-content-body">

                        <div class="container-fluid">

                            @if($dummy_installation == 1 || $dummy_installation == '1')

                                <div class="alert alert-info alert-dismissible">
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                                    <i class="icon fa-solid fa-triangle-exclamation"></i> {{Lang::get('lang.dummy_data_installation_message')}}
                                    <a href="{{route('clean-database')}}">{{Lang::get('lang.click')}}</a> {{Lang::get('lang.clear-dummy-data')}}
                                </div>

                            @elseif (!$is_mail_conigured)
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="alert alert-warning bg-warning">
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
                </div>
            </main>

            <footer class="app-footer">

                <div class="float-end d-none d-sm-block">

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

        <!-- Colorpicker -->
        <script src="{{ assetLink('js', 'colorpicker') }}" type="text/javascript"></script>

        @if (trim($__env->yieldContent('no-toolbar')))
            <h3>@yield('no-toolbar')</h3>
        @else
            <script>
            $(function () {
            //Add text editor
                $("textarea").summernote({
                    height: 300,
                    tabsize: 2,
                    toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']]
                  ]
                  });
            });
            </script>
        @endif
    <script>
        $('#read-all').click(function () {

            var id2 = <?php echo \Auth::user()->id ?>;
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
                            $('#myDropdown').removeClass('open');
                        }
                    });
        });</script>

    <!-- CK Editor -->
    <script src="{{asset("lb-faveo/plugins/filebrowser/plugin.js")}}"></script>
    <!-- Language Changer. The base url has to be handed over, without it the script
         falls back to a relative path which 404s on nested routes. -->
    <script>window.faveoBaseUrl = @json(url('/'));</script>
    <script src="{{asset("lb-faveo/js/languagechanger.js")}}" type="text/javascript"></script>
    @yield('FooterInclude')
</body>
</html>
