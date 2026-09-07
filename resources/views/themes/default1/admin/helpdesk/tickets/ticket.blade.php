!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Faveo | HELP DESK</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <!-- Bootstrap 3.3.2 -->
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <!-- Font Awesome Icons -->
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Ionicons -->
        <link href="http://code.ionicframework.com/ionicons/2.0.0/css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <!-- fullCalendar 2.2.5-->
        <link href="plugins/fullcalendar/fullcalendar.min.css" rel="stylesheet" type="text/css" />
        <link href="plugins/fullcalendar/fullcalendar.print.css" rel="stylesheet" type="text/css" media='print' />
        <!-- Theme style -->
        <link href="dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
        <!-- AdminLTE Skins. Choose a skin from the css/skins
             folder instead of downloading all of them to reduce the load. -->
        <link href="dist/css/skins/_all-skins.min.css" rel="stylesheet" type="text/css" />
        <link href="{{asset("css/common.css")}}" rel="stylesheet" type="text/css" />
        <!-- iCheck -->
        <link href="plugins/iCheck/flat/blue.css" rel="stylesheet" type="text/css" />
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <link rel="stylesheet" href="dist/css/tabby.css" type="text/css">
        <link href="http://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
    </head>
    <body class="skin-blue">
        <div class="app-wrapper">

            <header class="main-header">
                <a href="../../index2.html" class="logo"><b>Faveo</b> HELP DESK</a>
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
                            <li class="active"><a data-bs-target="#tabA" href="#">Home</a></li>
                            <li><a data-bs-target="#tabB" href="#">Staff</a></li>
                            <li><a data-bs-target="#tabC" href="#">Department</a></li>
                            <li><a data-bs-target="#tabD" href="#">Users</a></li>
                        </ul>
                        <ul class="nav navbar-nav navbar-right">
                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                                    <img src="dist/img/user2-160x160.jpg" class="user-image" alt="User Image"/>
                                    <span class="hidden-xs">Alexander Pierce</span>
                                </a>
                                <ul class="dropdown-menu">
                                    <!-- User image -->
                                    <li class="user-header">
                                        <img src="dist/img/user2-160x160.jpg" class="img-circle" alt="User Image" />
                                        <p>
                                            Alexander Pierce - Web Developer
                                            <small>Member since Nov. 2012</small>
                                        </p>
                                    </li>
                                    <!-- Menu Body -->
                                    <li class="user-body">
                                        <div class="col-4 text-center">
                                            <a href="#">Followers</a>
                                        </div>
                                        <div class="col-4 text-center">
                                            <a href="#">Sales</a>
                                        </div>
                                        <div class="col-4 text-center">
                                            <a href="#">Friends</a>
                                        </div>
                                    </li>
                                    <!-- Menu Footer-->
                                    <li class="user-footer">
                                        <div class="pull-left">
                                            <a href="#" class="btn btn-secondary ">Profile</a>
                                        </div>
                                        <div class="pull-right">
                                            <a href="#" class="btn btn-secondary ">Sign out</a>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                        </ul>


                        <!-- <form class="navbar-form navbar-left" role="search">
                          <div class="mb-3">
                            <input type="text" class="form-control" id="navbar-search-input" placeholder="Search">
                          </div>
                        </form> -->

                    </div><!-- /.navbar-collapse -->
                </nav>
            </header>
            <!-- Left side column. contains the logo and sidebar -->
            <aside class="app-sidebar">
                <!-- sidebar: style can be found in sidebar.less -->
                <section class="sidebar">
                    <!-- Sidebar user panel -->
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
                        <li>
                            <a href="../widgets.html">
                                <i class="fa-solid fa-envelope"></i> <span>Inbox</span> <small class="badge float-end text-bg-success">5</small>
                            </a>
                        </li>
                        <li>
                            <a href="../widgets.html">
                                <i class="fa-solid fa-user"></i> <span>My Tickets</span> <small class="badge float-end text-bg-success">2</small>
                            </a>
                        </li>
                        <li>
                            <a href="../widgets.html">
                                <i class="fa-solid fa-th"></i> <span>Unassigned</span> <small class="badge float-end text-bg-success">4</small>
                            </a>
                        </li>
                        <li>
                            <a href="../widgets.html">
                                <i class="fa-solid fa-trash-o"></i> <span>Trash</span> <small class="badge float-end text-bg-success">89</small>
                            </a>
                        </li>
                        <li class="treeview">
                            <a href="#">
                                <i class="fa-solid fa-folder-open"></i> <span>General</span> <i class="fa-solid fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href=""><i class="fa-solid fa-circle-o"></i>Open<small class="badge float-end text-bg-success">4</small></a></li>
                                <li><a href=""><i class="fa-solid fa-circle-o"></i>Inprogress<small class="badge float-end text-bg-success">3</small></a></li>
                                <li><a href=""><i class="fa-solid fa-circle-o"></i>Closed<small class="badge float-end text-bg-success">55</small></a></li>
                            </ul>
                        </li>
                        <li class="treeview">
                            <a href="#">
                                <i class="fa-solid fa-folder-open"></i> <span>Support</span> <i class="fa-solid fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href=""><i class="fa-solid fa-circle-o"></i>Open<small class="badge float-end text-bg-success">1</small></a></li>
                                <li><a href=""><i class="fa-solid fa-circle-o"></i>Inprogress<small class="badge float-end text-bg-success">6</small></a></li>
                                <li><a href=""><i class="fa-solid fa-circle-o"></i>Closed<small class="badge float-end text-bg-success">88</small></a></li>
                            </ul>
                        </li>


                        <li class="header">LABELS</li>
                        <li><a href="#"><i class="fa-solid fa-circle-o text-danger"></i> Important</a></li>
                        <li><a href="#"><i class="fa-solid fa-circle-o text-warning"></i> Warning</a></li>
                        <li><a href="#"><i class="fa-solid fa-circle-o text-info"></i> Information</a></li>
                    </ul>
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
                                    <li><a href="#">Link1</a></li>
                                    <li><a href="#">Link2</a></li>
                                </ul>
                            </div>
                            <div class="tabs-pane" id="tabB">
                                <ul class="nav navbar-nav">
                                    <li><a href="#">Link3</a></li>
                                    <li><a href="#">Link4</a></li>
                                </ul>
                            </div>
                            <div class="tabs-pane" id="tabC">
                                <ul class="nav navbar-nav">
                                    <li><a href="#">Link5</a></li>
                                    <li><a href="#">Link6</a></li>
                                </ul>
                            </div>
                            <div class="tabs-pane" id="tabD">
                                <ul class="nav navbar-nav">
                                    <li><a href="#">Link7</a></li>
                                    <li><a href="#">Link8</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <section class="app-content-header">
                    <h1>
                        Tickets
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa-solid fa-dashboard"></i> Home</a></li>
                        <li class="active">Mailbox</li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="app-content">

                    <div class="row">
                        <div class="col-md-12">
                            <div class="box box-primary">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Inbox </h3> <small> 5 new messages</small>
                                    <div class="box-tools pull-right">
                                        <div class="has-feedback">
                                            <input type="text" class="form-control input-sm" placeholder="Search Mail"/>
                                            <span class="glyphicon glyphicon-search form-control-feedback"></span>
                                        </div>
                                    </div><!-- /.box-tools -->
                                </div><!-- /.box-header -->
                                <div class="box-body no-padding">
                                    <div class="mailbox-controls">
                                        <!-- Check all button -->
                                        <button class="btn btn-secondary btn-sm checkbox-toggle"><i class="fa-solid fa-square-o"></i></button>
                                        <div class="btn-group">
                                            <button class="btn btn-secondary btn-sm"><i class="fa-solid fa-trash-o"></i></button>
                                            <button class="btn btn-secondary btn-sm"><i class="fa-solid fa-reply"></i></button>
                                            <button class="btn btn-secondary btn-sm"><i class="fa-solid fa-share"></i></button>
                                        </div><!-- /.btn-group -->
                                        <button class="btn btn-secondary btn-sm"><i class="fa-solid fa-refresh"></i></button>
                                        <div class="pull-right">
                                            1-5 /5
                                            <div class="btn-group">
                                                <button class="btn btn-secondary btn-sm"><i class="fa-solid fa-chevron-left"></i></button>
                                                <button class="btn btn-secondary btn-sm"><i class="fa-solid fa-chevron-right"></i></button>
                                            </div><!-- /.btn-group -->
                                        </div><!-- /.pull-right -->
                                    </div>
                                    <div class="table-responsive mailbox-messages">
                                        <table class="table table-hover table-striped">
                                            <thead>
                                            <th>
                                            </th>
                                            <th>
                                            </th>
                                            <th>
                                                subject
                                            </th>
                                            <th>
                                                Ticket ID
                                            </th>
                                            <th>
                                                last Replier
                                            </th>
                                            <th>
                                                Replies
                                            </th>
                                            <th>
                                                Priority
                                            </th>
                                            <th>
                                                Last Activity
                                            </th>
                                            <th>
                                                Reply Due
                                            </th>
                                            </thead>
                                            <tbody>

                                                <tr>
                                                    <td><input type="checkbox" /></td>
                                                    <td class="mailbox-star"><a href="#"><i class="fa-solid fa-star text-yellow"></i></a></td>
                                                    @foreach($threads as $thread)
                                                    <td class="mailbox-name"><a href="timeline.html">{{$thread->ticket_subject}}</a></td>

                                                    <td class="mailbox-Id">{{$thread->ticket_id}}</td>
                                                     @endforeach
                                                    <td class="mailbox-last-reply">client</td>
                                                    <td class="mailbox-replies">11</td>
                                                    <td class="mailbox-priority"><spam class="text-green">Low</spam></td>
                                            <td class="mailbox-last-activity">11h 59m 23s</td>
                                            <td class="mailbox-date">5h 23m 03s</td>
                                            </tr>
                                            <tr>
                                                <td><input type="checkbox" /></td>
                                                <td class="mailbox-star"><a href="#"><i class="fa-solid fa-star text-yellow"></i></a></td>
                                                <td class="mailbox-name"><a href="timeline.html">Testing</a></td>
                                                <td class="mailbox-Id">#12345</td>
                                                <td class="mailbox-last-reply">client</td>
                                                <td class="mailbox-replies">11</td>
                                                <td class="mailbox-priority"><spam class="text-yellow">Medium</spam></td>
                                            <td class="mailbox-last-activity">11h 59m 23s</td>
                                            <td class="mailbox-date">5h 23m 03s</td>
                                            </tr>
                                            <tr>
                                                <td><input type="checkbox" /></td>
                                                <td class="mailbox-star"><a href="#"><i class="fa-solid fa-star text-yellow"></i></a></td>
                                                <td class="mailbox-name"><a href="timeline.html">Testing</a></td>
                                                <td class="mailbox-Id">#12345</td>
                                                <td class="mailbox-last-reply">client</td>
                                                <td class="mailbox-replies">11</td>
                                                <td class="mailbox-priority"><spam class="text-red">High</spam></td>
                                            <td class="mailbox-last-activity">11h 59m 23s</td>
                                            <td class="mailbox-date">5h 23m 03s</td>
                                            </tr>
                                            <tr>
                                                <td><input type="checkbox" /></td>
                                                <td class="mailbox-star"><a href="#"><i class="fa-solid fa-star text-yellow"></i></a></td>
                                                <td class="mailbox-name"><a href="timeline.html">Testing</a></td>
                                                <td class="mailbox-Id">#12345</td>
                                                <td class="mailbox-last-reply">client</td>
                                                <td class="mailbox-replies">11</td>
                                                <td class="mailbox-priority"><spam class="text-yellow">Medium</spam></td>
                                            <td class="mailbox-last-activity">11h 59m 23s</td>
                                            <td class="mailbox-date">5h 23m 03s</td>
                                            </tr>
                                            <tr>
                                                <td><input type="checkbox" /></td>
                                                <td class="mailbox-star"><a href="#"><i class="fa-solid fa-star text-yellow"></i></a></td>
                                                <td class="mailbox-name"><a href="timeline.html">Testing</a></td>
                                                <td class="mailbox-Id">#12345</td>
                                                <td class="mailbox-last-reply">client</td>
                                                <td class="mailbox-replies">11</td>
                                                <td class="mailbox-priority"><spam class="text-red">High</spam></td>
                                            <td class="mailbox-last-activity">11h 59m 23s</td>
                                            <td class="mailbox-date">5h 23m 03s</td>
                                            </tr>

                                            </tbody>
                                        </table><!-- /.table -->
                                    </div><!-- /.mail-box-messages -->
                                </div><!-- /.box-body -->
                                <div class="box-footer no-padding">
                                    <div class="mailbox-controls">
                                        <!-- Check all button -->
                                        <button class="btn btn-secondary btn-sm checkbox-toggle"><i class="fa-solid fa-square-o"></i></button>
                                        <div class="btn-group">
                                            <button class="btn btn-secondary btn-sm"><i class="fa-solid fa-trash-o"></i></button>
                                            <button class="btn btn-secondary btn-sm"><i class="fa-solid fa-reply"></i></button>
                                            <button class="btn btn-secondary btn-sm"><i class="fa-solid fa-share"></i></button>
                                        </div><!-- /.btn-group -->
                                        <button class="btn btn-secondary btn-sm"><i class="fa-solid fa-refresh"></i></button>
                                        <div class="pull-right">
                                            1-5/5
                                            <div class="btn-group">
                                                <button class="btn btn-secondary btn-sm"><i class="fa-solid fa-chevron-left"></i></button>
                                                <button class="btn btn-secondary btn-sm"><i class="fa-solid fa-chevron-right"></i></button>
                                            </div><!-- /.btn-group -->
                                        </div><!-- /.pull-right -->
                                    </div>
                                </div>
                            </div><!-- /. box -->
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </section><!-- /.content -->
            </div><!-- /.content-wrapper -->
            <footer class="app-footer">
                <div class="pull-right hidden-xs">
                    <b>{!! Lang::get('lang.version') !!}</b> 0.1
                </div>
                <strong>Copyright &copy; 2014-2015 <a href="http://www.ladybirdweb.com">Ladybird Web Solution</a>.</strong> All rights reserved.
            </footer>
        </div><!-- ./wrapper -->

        <!-- jQuery 2.1.1 -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <!-- Bootstrap 3.3.2 JS -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js" type="text/javascript"></script>
        <!-- Slimscroll -->
        <script src="plugins/slimScroll/jquery.slimscroll.min.js" type="text/javascript"></script>
        <!-- FastClick -->
        <script src='plugins/fastclick/fastclick.min.js'></script>
        <!-- AdminLTE App -->
        <script src="dist/js/app.min.js" type="text/javascript"></script>
        <!-- AdminLTE for demo purposes -->
        <script src="dist/js/demo.js" type="text/javascript"></script>
        <!-- iCheck -->
        <script src="plugins/iCheck/icheck.min.js" type="text/javascript"></script>
        <!-- Page Script -->
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
        <!-- // <script src="../plugins/jQuery/jQuery-2.1.3.min.js"></script> -->
        <script src="dist/js/tabby.js"></script>

    </body>
</html>
