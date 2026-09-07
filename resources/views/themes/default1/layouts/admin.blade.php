<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Faveo | HELP DESK</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <!-- Bootstrap 3.3.2 -->
        <link href="{{asset("lb-faveo/downloads/bootstrap.min.css")}}" rel="stylesheet" type="text/css" />
        <!-- Font Awesome Icons -->
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Ionicons -->
        <link href="{{asset("lb-faveo/downloads/ionicons.min.css")}}" rel="stylesheet" type="text/css" />
        <!-- fullCalendar 2.2.5-->
        <link href="{{asset("lb-faveo/plugins/fullcalendar/fullcalendar.min.css")}}" rel="stylesheet" type="text/css" />
        <link href="{{asset("lb-faveo/plugins/fullcalendar/fullcalendar.print.css")}}" rel="stylesheet" type="text/css" media='print' />
        <!-- Theme style -->
        <link href="{{asset("lb-faveo/dist/css/AdminLTE.min.css")}}" rel="stylesheet" type="text/css" />
        <!-- AdminLTE Skins. Choose a skin from the css/skins
             folder instead of downloading all of them to reduce the load. -->
        <link href="{{asset("lb-faveo/dist/css/skins/_all-skins.min.css")}}" rel="stylesheet" type="text/css" />
        <!-- iCheck -->
        <link href="{{asset("lb-faveo/plugins/iCheck/flat/blue.css")}}" rel="stylesheet" type="text/css" />
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <link rel="stylesheet" href="{{asset("lb-faveo/dist/css/tabby.css")}}" type="text/css">
        <link href="{{asset("lb-faveo/downloads/jquerysctipttop.css")}}" rel="stylesheet" type="text/css">
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <link rel="stylesheet" href="{{asset("lb-faveo/dist/css/editor.css")}}" type="text/css">
        <link href="{{asset("lb-faveo/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css")}}" rel="stylesheet" type="text/css" />
        <link href="{{asset("css/common.css")}}" rel="stylesheet" type="text/css" />
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
        @yield('HeadInclude')
    </head>
    <body class="skin-yellow fixed">
        <div class="app-wrapper">

            <header class="main-header">
                <a href="../../index2.html" class="logo"><b>Faveo </b>HELPDESK</a>
                <!-- Header Navbar: style can be found in header.less -->
                <nav class="navbar navbar-static-top" role="navigation">
                    <!-- Sidebar toggle button-->
                    <a href="#" class="sidebar-toggle" data-bs-toggle="offcanvas" role="button">
                        <span class="visually-hidden">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <!-- Collect the nav links, forms, and other content for toggling -->
                    <div class="collapse navbar-collapse" id="navbar-collapse">
                        <ul class="tabs tabs-horizontal nav navbar-nav">
                            <li><a data-bs-target="#tabA" href="#">Home</a></li>
                            <li @yield('Staffs')><a data-bs-target="#tabB" href="#">Staffs</a></li>
                            <li @yield('Emails')><a data-bs-target="#tabC" href="#">Emails</a></li>
                            <li @yield('Manage')><a data-bs-target="#tabD" href="#">Manage</a></li>
                            <li @yield('Settings')><a data-bs-target="#tabE" href="#">Settings</a></li>
                            <li @yield('Themes')><a data-bs-target="#tabF" href="#">Themes</a></li>
                        </ul>

                        <ul class="nav navbar-nav navbar-right">
                            <li><a href="{{url('user')}}">Agent Panel</a></li>
                            <!-- User Account: style can be found in dropdown.less -->
                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                                @if(Auth::user())
                                    @if(Auth::user()->profile_pic)
                                        <img src="{{asset('lb-faveo/dist/img')}}{{'/'}}{{Auth::user()->profile_pic}}"class="user-image" alt="User Image"/>
                                    @else
                                        <img src="{{ Gravatar::src(Auth::user()->email) }}" class="user-image" alt="User Image">
                                    @endif
                                    <span class="hidden-xs">{!! Auth::user()->first_name." ".Auth::user()->last_name !!}</span>
                                @endif
                                </a>
                                <ul class="dropdown-menu">
                                    <!-- User image -->
                                    <li class="user-header" style="background-color:#343F44;">
                                    @if(Auth::user())
                                        @if(Auth::user()->profile_pic)
                                            <img src="{{asset('lb-faveo/lb-faveo/dist/img')}}{{'/'}}{{Auth::user()->profile_pic}}" class="img-circle" alt="User Image" />
                                        @else
                                            <img src="{{ Gravatar::src(Auth::user()->email) }}" class="img-circle" alt="User Image">
                                        @endif
                                        <p>
                                            {!! Auth::user()->first_name !!}{!! " ". Auth::user()->last_name !!} - {{Auth::user()->role}}
                                            <small></small>
                                        </p>
                                    @endif
                                    </li>
                                    <!-- Menu Footer-->

                                    <li class="user-footer"  style="background-color:#1a2226;">
                                        <div class="pull-left">
                                            <a href="{{url('admin-profile')}}" class="btn btn-info btn-sm"><b>Profile</b></a>
                                        </div>
                                        <div class="pull-right">
                                            <a href="{{url('auth/logout')}}" class="btn btn-danger btn-sm"><b>Sign out</b></a>
                                        </div>
                                    </li>
                                </ul>
                            </li>

                            </nav>
                            </header>
                            <!-- Left side column. contains the logo and sidebar -->
                            <aside class="app-sidebar">
                                <!-- sidebar: style can be found in sidebar.less -->
                                <section class="sidebar">
                                    <div class="user-panel">
                                    <div class = "row">
                                        <div class="col-3"></div>

                                        <div class="col-2" style="width:50%;">
                                        @if(Auth::user() && Auth::user()->profile_pic)
                                            <img src="{{asset('lb-faveo/dist/img')}}{{'/'}}{{Auth::user()->profile_pic}}" class="img-circle" alt="User Image" />
                                        @else
                                            <img src="{{ Gravatar::src(Auth::user()->email) }}" class="img-circle" alt="User Image">
                                        @endif

                                        </div>
                                    </div>
                                        <div class="info" style="text-align:center;">
                                            @if(Auth::user())
                                                <p>{!! Auth::user()->first_name !!}{!! " ". Auth::user()->last_name !!}</p>
                                            @endif
                                            @if(Auth::user() && Auth::user()->active==1)
                                                <a href="#"><i class="fa-solid fa-circle text-success"></i> Online</a>
                                            @else
                                                <a href="#"><i class="fa-solid fa-circle"></i> Offline</a>
                                            @endif
                                        </div>
                                    </div>
                                    <!-- search form -->
                                    <form action="#" method="get" class="sidebar-form">
                                        <div class="input-group">
                                            <input type="text" name="q" class="form-control" placeholder="Search..."/>
                                            <span class="input-group-btn">
                                                <button type='submit' name='seach' id='search-btn' class="btn "><i class="fa-solid fa-magnifying-glass"></i></button>
                                            </span>
                                        </div>
                                    </form>
                                    <!-- /.search form -->
                                    <!-- sidebar menu: : style can be found in sidebar.less -->
                                    <ul class="sidebar-menu">
                                                                                <li class="header">TICKETS</li>

<?php
 $inbox = App\Model\helpdesk\Ticket\Tickets::get();
    ?>
     <?php $myticket = App\Model\helpdesk\Ticket\Tickets::where('assigned_to', Auth::user()->id)->where('status','1')->get();?>
     <?php $unassigned = App\Model\helpdesk\Ticket\Tickets::where('assigned_to', '0')->where('status','1')->get();
            $tickets = App\Model\helpdesk\Ticket\Tickets::where('status','1')->get();
            $i = count($tickets);
     ?>
                                        <li>
                                            <a href="{{ url('/ticket/open') }}">
                                                <i class="fa-solid fa-envelope"></i> <span>Inbox</span> <small class="badge float-end text-bg-success"><?php echo $i;?></small>
                                            </a>
                                        </li>
<?php
//}
?>

                                        <li @yield('myticket')>
                                             <a href="{{url('ticket/myticket')}}">
                                                <i class="fa-solid fa-user"></i> <span>My Tickets</span>

                                                <small class="badge float-end text-bg-success">{{count($myticket) }}</small>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{url('unassigned')}}">
                                                <i class="fa-solid fa-th"></i> <span>Unassigned</span>

                                                <small class="badge float-end text-bg-success">{{count($unassigned)}}</small>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{url('trash')}}">
                                                <i class="fa-solid fa-trash-o"></i> <span>Trash</span>
                                                <?php $deleted = App\Model\helpdesk\Ticket\Tickets::where('status', '5')->get();?>
                                                <small class="badge float-end text-bg-success">{{count($deleted)}}</small>
                                            </a>
                                        </li>
                                </section>
                                <!-- /.sidebar -->
                            </aside>

                            <!-- Right side column. Contains the navbar and content of the page -->
                            <div class="app-main">
                                <!-- Content Header (Page header) -->
                                <div class="tab-content" style="background-color: white;padding: 0 20px 0 20px">
                                    <div class="collapse navbar-collapse" id="navbar-collapse">
                                        <div class="tabs-content">
                                            <div class="tabs-pane active" id="tabA">
                                                <ul class="nav navbar-nav">

                                                </ul>
                                            </div>
                                            <div class="tabs-pane @yield('staffs-bar')" id="tabB">
                                                <ul class="nav navbar-nav">
                                                    <li id="bar" @yield('staffs')><a href="{{ url('agents') }}" >Staffs</a></li></a></li>
                                                    <li id="bar" @yield('departments')><a href="{{ url('departments') }}" >Departments</a></li></a></li>
                                                    <li id="bar" @yield('teams')><a href="{{ url('teams') }}" >Teams</a></li></a></li>
                                                    <li id="bar" @yield('groups')><a href="{{ url('groups') }}" >Groups</a></li></a></li>
                                                </ul>
                                            </div>
                                            <div class="tabs-pane @yield('emails-bar')" id="tabC">
                                                <ul class="nav navbar-nav">
                                                    <li id="bar" @yield('emails')><a href="{{ url('emails') }}" >Emails</a></li></a></li>
                                                    <li id="bar" @yield('ban')><a href="{{ url('banlist') }}" >Ban List</a></li>
                                                    <li id="bar" @yield('template')><a href="{{ url('template') }}" >Template</a></li>
                                                    <li id="bar" @yield('diagno')><a href="{{ url('getdiagno') }}" >Diagnostic</a></li>
                                                    <li id="bar" @yield('smtp')><a href="{{ url('getsmtp') }}" >Smtp</a></li>
                                                </ul>
                                            </div>
                                            <div class="tabs-pane @yield('manage-bar')" id="tabD">
                                                <ul class="nav navbar-nav">
                                                    <li id="bar" @yield('help')><a href="{{url('helptopic')}}">Help Topic</a></li>
                                                    <li id="bar" @yield('sla')><a href="{{url('sla')}}">SLA Plans</a></li>
                                                    {{-- <li id="bar" @yield('forms')><a href="#">Forms</a></li> --}}
                                                </ul>
                                            </div>
                                            <div class="tabs-pane @yield('settings-bar')" id="tabE">
                                                <ul class="nav navbar-nav">
                                                    <li id="bar" @yield('company')><a href="{{url('getcompany')}}">Company</a></li>
                                                    <li id="bar" @yield('system')><a href="{{url('getsystem')}}">System</a></li>
                                                    <li id="bar" @yield('email')><a href="{{url('getemail')}}">Email</a></li>
                                                    <li id="bar" @yield('tickets')><a href="{{url('getticket')}}">Tickets</a></li>
                                                    <li id="bar" @yield('access')><a href="{{url('getaccess')}}">Access</a></li>
                                                    <li id="bar" @yield('auto-response')><a href="{{url('getresponder')}}">Auto-Responce</a></li>
                                                    <li id="bar" @yield('alert')><a href="{{url('getalert')}}">Alert & Notice</a></li>
                                                </ul>
                                            </div>
                                            <div class="tabs-pane @yield('theme-bar')" id="tabF">
                                                <ul class="nav navbar-nav">
                                                    <li id="bar" @yield('footer')><a href="{{ url('create-footer') }}" >Footer</a></li></a></li>
                                                    <li id="bar" @yield('footer2')><a href="{{ url('create-footer2') }}" >Footer2</a></li></a></li>
                                                    <li id="bar" @yield('footer3')><a href="{{ url('create-footer3') }}" >Footer3</a></li></a></li>
                                                    <li id="bar" @yield('footer4')><a href="{{ url('create-footer4') }}" >Footer4</a></li></a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <section class="app-content-header">
                                    @yield('PageHeader')
                                    @yield('breadcrumbs')
                                </section>

                                <!-- Main content -->
                                <section class="app-content">
                                    @yield('content')
                                </section><!-- /.content -->
                                <!-- /.content-wrapper -->
                            </div>
                            <footer class="app-footer">
                                <div class="pull-right hidden-xs">
                                    <b>{!! Lang::get('lang.version') !!}</b> 0.1
                                </div>
                                <?php
                                $company = App\Model\helpdesk\Settings\Company::where('id','=','1')->first();
                                ?>
                                <strong>Copyright &copy; {!! date('Y') !!}  <a href="{!! $company->website !!}">{!! $company->company_name !!}</a>.</strong> All rights reserved. Powered by <a href="http://www.faveohelpdesk.com/" target="blank">Faveo</a>
                            </footer>
                    </div><!-- ./wrapper -->

                    <!-- jQuery 2.1.3 -->
                    <script src="{{asset("lb-faveo/downloads/ajax-jquery.min.js")}}"></script>
                    <!-- Bootstrap 3.3.2 JS -->
                    <script src="{{asset("lb-faveo/downloads/bootstrap.min.js")}}" type="text/javascript"></script>
                    <!-- Slimscroll -->
                    <script src="{{asset("lb-faveo/plugins/slimScroll/jquery.slimscroll.min.js")}}" type="text/javascript"></script>
                    <!-- FastClick -->
                    <script src="{{asset("lb-faveo/plugins/fastclick/fastclick.min.js")}}"></script>
                    <!-- AdminLTE App -->
                    <script src="{{asset("lb-faveo/dist/js/app.min.js")}}" type="text/javascript"></script>
                    <!-- AdminLTE for demo purposes -->
                    {{-- // <script src="{{asset("dist/js/demo.js")}}" type="text/javascript"></script> --}}
                    <!-- iCheck -->
                    <script src="{{asset("lb-faveo/plugins/iCheck/icheck.min.js")}}" type="text/javascript"></script>
                    <!-- Page Script -->
                    <script src="ckeditor/ckeditor.js"></script>
                    <script>
$(function() {
    //Enable iCheck plugin for checkboxes
    //iCheck for checkbox and radio inputs
    $('input[type="checkbox"]').iCheck({
        checkboxClass: 'icheckbox_flat-blue',
        radioClass: 'iradio_flat-blue'
    });

    //Enable check and uncheck all functionality
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

    //Handle starring for glyphicon and font awesome
    $(".mailbox-star").click(function(e) {
        e.preventDefault();
        //detect type
        var $this = $(this).find("a > i");
        var glyph = $this.hasClass("glyphicon");
        var fa = $this.hasClass("fa");

        //Switch states
        if (glyph) {
            $this.toggleClass("glyphicon-star");
            $this.toggleClass("glyphicon-star-empty");
        }

        if (fa) {
            $this.toggleClass("fa-star");
            $this.toggleClass("fa-star-o");
        }
    });
});
                    </script>
                    <script type="text/javascript">
                        //     $(document).ready(function() {

                        //         $("#content").Editor();

                        //     });
                        // </script>
                   <!-- // <script src="../plugins/jQuery/jQuery-2.1.3.min.js"></script> -->
                    <script src="{{asset("lb-faveo/dist/js/tabby.js")}}"></script>
                     <!-- // <script src="{{asset("dist/js/editor.js")}}"></script> -->
                    <!-- CK Editor -->
                    <!-- // <script src="{{asset("//cdn.ckeditor.com/4.4.3/standard/ckeditor.js")}}"></script> -->
                    <script src="{{asset("lb-faveo/downloads/CKEditor.js")}}"></script>
                    <script src="{{asset("lb-faveo/plugins/filebrowser/plugin.js")}}"></script>
                    <script src="{{asset("lb-faveo/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js")}}" type="text/javascript"></script>
                    <script>
                        $(function () {
                        //Add text editor
                        $("textarea").wysihtml5();
                        });
                    </script>
                    @yield('FooterInclude')
                    </body>
                    </html>
