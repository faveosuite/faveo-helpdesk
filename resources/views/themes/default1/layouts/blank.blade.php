    <!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">

        <title> SUPPORT CENTER | CLIENT PANEL</title>

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
        <link href="{{asset("dist/css/AdminLTE.min.css")}}" rel="stylesheet" type="text/css" />
        <!-- AdminLTE Skins. Choose a skin from the css/skins
             folder instead of downloading all of them to reduce the load. -->
        <link href="{{asset("dist/css/skins/_all-skins.min.css")}}" rel="stylesheet" type="text/css" />
        <!-- iCheck -->
        <link href="{{asset("lb-faveo/plugins/iCheck/flat/blue.css")}}" rel="stylesheet" type="text/css" />
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <link rel="stylesheet" href="{{asset("dist/css/tabby.css")}}" type="text/css">
        <link href="http://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <link rel="stylesheet" href="{{asset("dist/css/editor.css")}}" type="text/css">

        <script src="ckeditor/ckeditor.js"></script>
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
        @yield('HeadInclude')
    </head>
    <body class="skin-yellow">
        <div class="app-wrapper">

            <header class="main-header">
                <a href="../../index2.html" class="logo"><b>SUPPORT </b> CENTER</a>
                <?php $company = App\Model\Settings\Company::where('id', '=', '1')->first();?>

                @if($company->logo)
                <img src="{{asset('dist')}}{{'/'}}{{$company->logo}}" class="logo" alt="User Image" />
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
                        <ul class="nav navbar-nav navbar-right">

                            <div class="navbar-custom-menu">
                                <ul class="nav navbar-nav">

                                    <div class="navbar-custom-menu">
                                        <ul class="nav navbar-nav">

                                            <li>
                                                <a href="{{url('auth/login')}}" class="logo"><span>Sign In</span></a>
                                            &nbsp;</li>

                                            <li>
                                                <a href="{{url('auth/register')}}" class="logo"><span>Register</span></a>
                                            </li>
                                        </ul>
                                    </div>

                                </ul>
                            </div>
                        </nav>
                            </header>
                            <!-- Left side column. contains the logo and sidebar -->
                            <aside class="app-sidebar">
                                <!-- sidebar: style can be found in sidebar.less -->
                                <section class="sidebar">
                                        <ul class="sidebar-menu">
                                            <li class="header">MAIN NAVIGATION</li>
                                                <li>
                                                    <a href="{{url('getform')}}">
                                                        <i class="fa-solid fa-envelope"></i> <span>Open A New Ticket</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{url('checkticket')}}">
                                                        <i class="fa-solid fa-th"></i> <span>Check your Ticket</span>
                                                    </a>
                                                </li>
                                            </ul>

                                        </ul>
                                </section>
                                <!-- /.sidebar -->
                            </aside>

                            <!-- Right side column. Contains the navbar and content of the page -->
                            <div class="app-main">

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
                           <strong>Copyright &copy; {!! date('Y') !!}  <a href="{!! $company->website !!}">{!! $company->company_name !!}</a>.</strong> All rights reserved. Powered by <a href="http://www.faveohelpdesk.com/">Faveo</a>
                            </footer>
                    </div><!-- ./wrapper -->

                    <!-- jQuery 2.1.1 -->
                    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
                    <!-- Bootstrap 3.3.2 JS -->
                    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js" type="text/javascript"></script>
                    <!-- Slimscroll -->
                    <script src="{{asset("lb-faveo/plugins/slimScroll/jquery.slimscroll.min.js")}}" type="text/javascript"></script>
                    <!-- FastClick -->
                    <script src="{{asset("lb-faveo/plugins/fastclick/fastclick.min.js")}}"></script>
                    <!-- AdminLTE App -->
                    <script src="{{asset("dist/js/app.min.js")}}" type="text/javascript"></script>
                    <!-- AdminLTE for demo purposes -->
                    {{-- // <script src="{{asset("dist/js/demo.js")}}" type="text/javascript"></script> --}}
                    <!-- iCheck -->
                    <script src="{{asset("lb-faveo/plugins/iCheck/icheck.min.js")}}" type="text/javascript"></script>
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
                    <script type="text/javascript">
                        //     $(document).ready(function() {

                        //         $("#content").Editor();

                        //     });
                        // </script>
                   <!-- // <script src="../plugins/jQuery/jQuery-2.1.3.min.js"></script> -->
                    <script src="{{asset("dist/js/tabby.js")}}"></script>
                     <!-- // <script src="{{asset("dist/js/editor.js")}}"></script> -->
                    <!-- CK Editor -->
                    <script src="{{asset("//cdn.ckeditor.com/4.4.3/standard/ckeditor.js")}}"></script>
                    <script src="{{asset("//cdn.ckeditor.com/4.4.3/full/ckeditor.js")}}"></script>
                    <script src="{{asset("lb-faveo/plugins/filebrowser/plugin.js")}}"></script>

                    @yield('FooterInclude')
                    </body>
                    </html>
