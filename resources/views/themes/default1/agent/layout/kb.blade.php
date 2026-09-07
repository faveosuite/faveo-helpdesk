<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Faveo Knowledge Base</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <!-- Bootstrap 3.3.2 -->
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <!-- Font Awesome Icons -->
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Ionicons -->
        <link href="http://code.ionicframework.com/ionicons/2.0.0/css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <!-- fullCalendar 2.2.5-->
        <link href="{{asset("lb-faveo/plugins/fullcalendar/fullcalendar.min.css")}}" rel="stylesheet" type="text/css" />

        <link href="{{asset("lb-faveo/plugins/fullcalendar/fullcalendar.print.css")}}" rel="stylesheet" type="text/css" media='print' />
        <!-- Theme style -->
        <link href="{{asset("lb-faveo/dist/css/AdminLTE.min.css")}}" rel="stylesheet" type="text/css" />
        <!-- AdminLTE Skins. Choose a skin from the css/skins
             folder instead of downloading all of them to reduce the load. -->
        <link href="{{asset("lb-faveo/dist/css/skins/_all-skins.min.css")}}" rel="stylesheet" type="text/css" />
        <link href="{{asset("css/common.css")}}" rel="stylesheet" type="text/css" />
        <!-- iCheck -->
        <link href="{{asset("lb-faveo/plugins/iCheck/flat/blue.css")}}" rel="stylesheet" type="text/css" />
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <link rel="stylesheet" href="{{asset("lb-faveo/dist/css/tabby.css")}}" type="text/css">

        <link href="http://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">

        <link type="text/css" href="http://code.jquery.com/ui/1.9.1/themes/redmond/jquery-ui.css" rel="stylesheet">

        <link href="http://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">

        <script type="text/javascript" src="//cdn.datatables.net/1.10.0/js/jquery.dataTables.js"></script>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

        <script type="text/javascript" src="{{asset('lb-faveo/dist/js/nicEdit.js')}}"></script>

        @yield('HeadInclude')
    </head>
    <body class="skin-blue">
        <div class="app-wrapper" id="RefreshAssign">

            <header class="main-header">
            <?php $settings = App\Model\kb\Settings::where('id', '=', '1')->first();?>
                @if($settings->logo)
                    <a href=""><img src="{{asset('lb-faveo/Img/icon/'.$settings->logo)}}" class="logo" alt="KNOWLEDGE BASE"/></a>
                @else
                    <a href="" class="logo">KNOWLEDGE BASE</a>
                @endif
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
                                @if(Auth::user())
                                    @if(Auth::user()->profile_pic)
                                        <img src="{{asset('lb-faveo/dist/img')}}{{'/'}}{{Auth::user()->profile_pic}}" class="user-image" alt="User Image"/>
                                    @else
                                        <img src="{{ Gravatar::src(Auth::user()->email) }}" class="user-image" alt="User Image">
                                    @endif
                                @endif
                                    <span class="hidden-xs">{!! Auth::user()->firstname." ".Auth::user()->lastname !!}</span>
                                </a>
                                <ul class="dropdown-menu">
                                    <!-- User image -->
                                    <li class="user-header">
                                @if(Auth::user())
                                    @if(Auth::user()->profile_pic)
                                        <img src="{{asset('lb-faveo/dist/img')}}{{'/'}}{{Auth::user()->profile_pic}}" class="img-circle" alt="User Image"/>
                                    @else
                                        <img src="{{ Gravatar::src(Auth::user()->email) }}" class="img-circle" alt="User Image">
                                    @endif
                                @endif
                                        <p>
                                            <span class="hidden-xs">{!! Auth::user()->firstname." ".Auth::user()->lastname !!}</span>
                                        </p>
                                    </li>
                                    <!-- Menu Body -->
                                    <!-- Menu Footer-->
                                    <li class="user-footer">
                                        <div class="pull-left">
                                            <a href="{{url('profile')}}" class="btn btn-secondary ">{{Lang::get('lang.profile')}}</a>
                                        </div>
                                        <div class="pull-right">
                                            <a href="{{ url('/auth/logout') }}" class="btn btn-secondary ">{{Lang::get('lang.signout')}}</a>
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
                        <div class="user-panel">
                            <div class="pull-left image">
                                @if(Auth::user() && Auth::user()->profile_pic)
                                    <img src="{{asset('lb-faveo/dist/img')}}{{'/'}}{{Auth::user()->profile_pic}}" class="img-circle" alt="User Image" />
                                @else
                                    <img src="{{ Gravatar::src(Auth::user()->email) }}" class="img-circle" alt="User Image">
                                @endif
                            </div>
                            <div class="pull-left info">
                                            @if(Auth::user())
                                                <p>{!! Auth::user()->firstname !!}{!! " ". Auth::user()->lastname !!}</p>
                                            @endif
                                            @if(Auth::user() && Auth::user()->active==1)
                                                <a href="#"><i class="fa-solid fa-circle text-success"></i> Online</a>
                                            @else
                                                <a href="#"><i class="fa-solid fa-circle"></i> Offline</a>
                                            @endif
                            </div>
                        </div>
                        <li class="treeview @yield('category')">
                            <a href="#">
                                <i class="fa-solid fa-list-ul"></i> <span>{{Lang::get('lang.category')}}</span>
                                <i class="fa-solid fa-angle-left pull-right"></i>
                            </a>
                                    <ul class="treeview-menu">
                                        <li @yield('add-category')><a href="{{url('category/create')}}"><i class="fa-solid fa-circle-o"></i> {{Lang::get('lang.addcategory')}}</a></li>
                                         <li @yield('all-category')><a href="{{url('category')}}"><i class="fa-solid fa-circle-o"></i> {{Lang::get('lang.allcategory')}}</a></li>
                                     </ul>
                        </li>
                        <li class="treeview @yield('article')">
                            <a href="#">
                                <i class="fa-solid fa-pen-to-square"></i> <span>{{Lang::get('lang.article')}}</span>
                                <i class="fa-solid fa-angle-left pull-right"></i>
                            </a>
                                    <ul class="treeview-menu">
                                        <li @yield('add-article')><a href="{{url('article/create')}}"><i class="fa-solid fa-circle-o"></i> {{Lang::get('lang.addarticle')}}</a></li>
                                         <li @yield('all-article')><a href="{{url('article')}}"><i class="fa-solid fa-circle-o"></i> {{Lang::get('lang.allarticle')}}</a></li>
                                     </ul>
                        </li>

                        <li class="treeview @yield('pages')">
                            <a href="#">
                                <i class="fa-solid fa-file-text"></i> <span>{{Lang::get('lang.pages')}}</span>
                                <i class="fa-solid fa-angle-left pull-right"></i>
                            </a>
                                    <ul class="treeview-menu">
                                        <li @yield('add-pages')><a href="{{url('page/create')}}"><i class="fa-solid fa-circle-o"></i> {{Lang::get('lang.addpages')}}</a></li>
                                         <li @yield('all-pages')><a href="{{url('page')}}"><i class="fa-solid fa-circle-o"></i> {{Lang::get('lang.allpages')}}</a></li>
                                     </ul>
                        </li>
                        <li class="treeview @yield('widget')">
                            <a href="#">
                                <i class="fa  fa-th"></i> <span>{{Lang::get('lang.widgets')}}</span>
                                <i class="fa-solid fa-angle-left pull-right"></i>
                            </a>
                                    <ul class="treeview-menu">
                                        <li @yield('footer1')><a href="{{url('create-footer')}}"><i class="fa-solid fa-circle-o"></i> {{Lang::get('lang.footer1')}}</a></li>
                                        <li @yield('footer2')><a href="{{url('create-footer2')}}"><i class="fa-solid fa-circle-o"></i> {{Lang::get('lang.footer2')}}</a></li>
                                        <li @yield('footer3')><a href="{{url('create-footer3')}}"><i class="fa-solid fa-circle-o"></i> {{Lang::get('lang.footer3')}}</a></li>
                                        <li @yield('footer4')><a href="{{url('create-footer4')}}"><i class="fa-solid fa-circle-o"></i> {{Lang::get('lang.footer4')}}</a></li>
                                        <li @yield('side1')><a href="{{url('side1')}}"><i class="fa-solid fa-circle-o"></i> {{Lang::get('lang.sidewidget1')}}</a></li>
                                        <li @yield('side2')><a href="{{url('side2')}}"><i class="fa-solid fa-circle-o"></i> {{Lang::get('lang.sidewidget2')}}</a></li>
                                        <li @yield('social')><a href="{{url('social')}}"><i class="fa-solid fa-circle-o"></i> Social</a></li>
                                     </ul>
                        </li>
                         <li @yield('comment')>
                            <a href="{{url('comment')}}">
                                <i class="fa-solid fa-comments-o"></i>
                                <span>{{Lang::get('lang.comments')}}</span>
                            </a>
                        </li>
                         <li @yield('settings')>
                            <a href="{{url('settings')}}">
                                 <i class="fa-solid fa-wrench"></i>
                                <span>{{Lang::get('lang.settings')}}</span>
                            </a>
                        </li>


                    </ul>

                </section>
                <!-- /.sidebar -->
            </aside>

            <!-- Right side column. Contains the navbar and content of the page -->
            <div class="app-main">
                <!-- Content Header (Page header) -->
                <div class="tab-content" style="background-color: white; border-top:1px solid #F0F0F0;">
                    <div class="collapse navbar-collapse" id="navbar-collapse">
                        <div class="tabs-content">

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
                    <b>{!! Lang::get('lang.version') !!}</b> {{$settings->version}}
                </div>
                <strong>Copyright &copy; {{date("Y")}} <a href="{{$settings->website}}"> {{$settings->company_name}}</a>.  Powered By <a href="http://www.faveohelpdesk.com">Faveo</a>.</strong>
            </footer>
        </div><!-- ./wrapper -->

        <!-- jQuery 2.1.1 -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

        <!-- Bootstrap 3.3.2 JS -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js" type="text/javascript"></script>

        <script type="text/javascript" src="//cdn.datatables.net/1.10.0/js/jquery.dataTables.js"></script>
        <!-- Slimscroll -->
        <script src="{{asset("lb-faveo/plugins/slimScroll/jquery.slimscroll.min.js")}}" type="text/javascript"></script>
        <!-- FastClick -->
        <script src='{{asset("lb-faveo/plugins/fastclick/fastclick.min.js")}}'></script>
        <!-- AdminLTE App -->
        <script src="{{asset("lb-faveo/dist/js/app.min.js")}}" type="text/javascript"></script>
        <!-- iCheck -->
        <script src="{{asset("lb-faveo/plugins/iCheck/icheck.min.js")}}" type="text/javascript"></script>

        <script type="text/javascript" src="js/jquery.maskedinput.min.js"></script>

        <script src="{{asset("lb-faveo/plugins/filebrowser/plugin.js")}}"></script>
        <!-- Bootstrap WYSIHTML5 -->
        <script src="{{asset('lb-faveo/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js')}}" type="text/javascript"></script>

        <script type="text/javascript">
            bkLib.onDomLoaded(function() { nicEditors.allTextAreas() });
        </script>

        @yield('FooterInclude')
    </body>
</html>
