<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
        <title>Faveo | KB</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Bootstrap 3.3.4 -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- Font Awesome Icons -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <!-- Ionicons -->
    <link href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <!-- jvectormap -->
    <link href="plugins/jvectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <!-- Theme style -->
    <link href="dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link href="dist/css/skins/_all-skins.min.css" rel="stylesheet" type="text/css" />
    <link href="{{asset("css/common.css")}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('dist/css/bootstrap.css')}}" rel="stylesheet">
    <link href="{{asset('dist/css/dataTables.bootstrap.css')}}" rel="stylesheet">
    <!-- Data tables CDN -->
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.3/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="{{asset('dist/js/jquery-1.11.1.min.js')}}"></script>
    <script type="text/javascript" src="//cdn.datatables.net/1.10.0/js/jquery.dataTables.js"></script>
    <script type="text/javascript" language="javascript" src="//cdn.datatables.net/plug-ins/505bef35b56/integration/bootstrap/3/dataTables.bootstrap.js"></script>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body class="skin-blue">
    <div class="app-wrapper" id="RefreshAssign">

      <header class="main-header">
            <?php $settings = App\Model\Settings::where('id', '=', '1')->first();?>
            <img src="{{asset('Img/icon/faveokb.jpg')}}" class="logo" alt="Knowledge Base"/>
                {{-- <a href="../../index2.html" class="logo"><b>Faveo</b> Knowledge</a> --}}
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
                        </ul>

                        <ul class="nav navbar-nav navbar-right">

                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                                 @if(Auth::user()->profile_pic==NULL)
                                    <img src="{{asset('dist/img/avatar.png')}}" class="user-image" alt="User Image"/>
                                    @else
                                    <img src="{{asset('dist')}}{{'/'}}{{Auth::user()->profile_pic}}" class="user-image" alt="User Image"/>
                                 @endif
                                    <span class="hidden-xs">{{Auth::user()->name}}</span>
                                </a>
                                <ul class="dropdown-menu">
                                    <!-- User image -->
                                    <li class="user-header">
                                    @if(Auth::user()->profile_pic==NULL)
                                    <img src="{{asset('dist/img/avatar.png')}}" class="img-circle" alt="User Image"/>
                                    @else
                                        <img src="{{asset('dist')}}{{'/'}}{{Auth::user()->profile_pic}}" class="img-circle" alt="User Image" />
                                     @endif
                                        <p>
                                            {{Auth::user()->name}}

                                        </p>
                                    </li>
                                    <!-- Menu Body -->

                                    <!-- Menu Footer-->
                                    <li class="user-footer">
                                        <div class="pull-left">
                                            <a href="{{url('profile')}}" class="btn btn-secondary ">Profile</a>
                                        </div>
                                        <div class="pull-right">
                                            <a href="{{ url('/auth/logout') }}" class="btn btn-secondary ">Sign out</a>
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

                    <!-- /.search form -->
                    <!-- sidebar menu: : style can be found in sidebar.less -->

                    <ul class="sidebar-menu">
                        <li class="treeview @yield('category')">
                            <a href="#">
                                <i class="fa-solid fa-list-ul"></i> <span>Category</span>
                                <i class="fa-solid fa-angle-left pull-right"></i>
                            </a>
                                    <ul class="treeview-menu">
                                        <li @yield('add-category')><a href="{{url('category/create')}}"><i class="fa-solid fa-circle-o"></i>Add Category</a></li>
                                         <li @yield('all-category')><a href="{{url('category')}}"><i class="fa-solid fa-circle-o"></i> All Category</a></li>
                                     </ul>
                        </li>
                        <li class="treeview @yield('article')">
                            <a href="#">
                                <i class="fa-solid fa-pen-to-square"></i> <span>Article</span>
                                <i class="fa-solid fa-angle-left pull-right"></i>
                            </a>
                                    <ul class="treeview-menu">
                                        <li @yield('add-article')><a href="{{url('article/create')}}"><i class="fa-solid fa-circle-o"></i>Add Article</a></li>
                                         <li @yield('all-article')><a href="{{url('article')}}"><i class="fa-solid fa-circle-o"></i> All Article</a></li>
                                     </ul>
                        </li>

                        <li class="treeview @yield('pages')">
                            <a href="#">
                                <i class="fa-solid fa-file-text"></i> <span>Pages</span>
                                <i class="fa-solid fa-angle-left pull-right"></i>
                            </a>
                                    <ul class="treeview-menu">
                                        <li @yield('add-pages')><a href="{{url('page/create')}}"><i class="fa-solid fa-circle-o"></i>Add Pages</a></li>
                                         <li @yield('all-pages')><a href="{{url('page')}}"><i class="fa-solid fa-circle-o"></i> All Pages</a></li>
                                     </ul>
                        </li>
                        <li class="treeview @yield('widget')">
                            <a href="#">
                                <i class="fa  fa-th"></i> <span>Widgets</span>
                                <i class="fa-solid fa-angle-left pull-right"></i>
                            </a>
                                    <ul class="treeview-menu">
                                        <li @yield('footer1')><a href="{{url('create-footer')}}"><i class="fa-solid fa-circle-o"></i>Footer 1</a></li>
                                         <li @yield('footer2')><a href="{{url('create-footer2')}}"><i class="fa-solid fa-circle-o"></i> Footer 2</a></li>
                                         <li @yield('footer3')><a href="{{url('create-footer3')}}"><i class="fa-solid fa-circle-o"></i> Footer 3</a></li>
                                         <li @yield('footer4')><a href="{{url('create-footer4')}}"><i class="fa-solid fa-circle-o"></i> Footer 4</a></li>
                                         <li @yield('side1')><a href="{{url('side1')}}"><i class="fa-solid fa-circle-o"></i>Side Widget 1</a></li>
                                         <li @yield('side2')><a href="{{url('side2')}}"><i class="fa-solid fa-circle-o"></i>Side Widget 2</a></li>
                                         <li @yield('social')><a href="{{url('social')}}"><i class="fa-solid fa-circle-o"></i>Social</a></li>
                                     </ul>
                        </li>
                         <li @yield('comment')>
                            <a href="{{url('comment')}}">
                                <i class="fa-solid fa-comments-o"></i>
                                <span>Comments</span>
                            </a>
                        </li>
                         <li @yield('settings')>
                            <a href="{{url('settings')}}">
                                 <i class="fa-solid fa-wrench"></i>
                                <span>Settings</span>
                            </a>
                        </li>

                    </ul>

                </section>
                <!-- /.sidebar -->
            </aside>

      <!-- Content Wrapper. Contains page content -->
      <div class="app-main">
        <!-- Content Header (Page header) -->
        <!-- Content Header (Page header) -->
                <div class="tab-content" style="background-color: white; border-top:1px solid #F0F0F0;">
                    <div class="collapse navbar-collapse" id="navbar-collapse">
                        <div class="tabs-content">

                        </div>
                    </div>
                </div>


        <!-- Main content -->
        <section class="app-content">
          <!-- Info boxes -->
          @yield('content')
        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->

      <footer class="app-footer">
                <div class="pull-right hidden-xs">
                    <b>{!! Lang::get('lang.version') !!}</b> {{$settings->version}}
                </div>
                 <strong>Copyright &copy; {{date("Y")}} <a href="{{$settings->website}}"> {{$settings->company_name}}</a>.  Powered By <a href="http://www.faveohelpdesk.com">Faveo</a>.</strong>
            </footer>

      <!-- Control Sidebar -->

    </div><!-- ./wrapper -->

    <!-- jQuery 2.1.4 -->
    <script src="plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <!-- Bootstrap 3.3.2 JS -->
    <script src="bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <!-- FastClick -->
    <script src='plugins/fastclick/fastclick.min.js'></script>
    <!-- AdminLTE App -->
    <script src="dist/js/app.min.js" type="text/javascript"></script>
    <!-- Sparkline -->
    <script src="plugins/sparkline/jquery.sparkline.min.js" type="text/javascript"></script>
    <!-- jvectormap -->
    <script src="plugins/jvectormap/jquery-jvectormap-1.2.2.min.js" type="text/javascript"></script>
    <script src="plugins/jvectormap/jquery-jvectormap-world-mill-en.js" type="text/javascript"></script>
    <!-- SlimScroll 1.3.0 -->
    <script src="plugins/slimScroll/jquery.slimscroll.min.js" type="text/javascript"></script>
    <!-- ChartJS 1.0.1 -->
    <script src="plugins/chartjs/Chart.min.js" type="text/javascript"></script>

    <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
    <script src="dist/js/pages/dashboard2.js" type="text/javascript"></script>

    <!-- AdminLTE for demo purposes -->
    <script src="dist/js/demo.js" type="text/javascript"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.1/js/bootstrap.min.js"></script>
    <script src="{{asset('dist/js/blogs.js')}}"></script>
  </body>
</html>
