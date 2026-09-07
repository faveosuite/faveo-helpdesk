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
        <link href="{{asset("lb-faveo/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css")}}" rel="stylesheet" type="text/css" />
        <!-- Theme style -->
        <link href="{{asset("lb-faveo/dist/css/app.css")}}" rel="stylesheet" type="text/css" />
        <link href="{{asset("lb-faveo/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css")}}" rel="stylesheet" type="text/css" />
        {{-- // <script src="ckeditor/ckeditor.js"></script> --}}
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
        @yield('HeadInclude')
    </head>
    <body>
	<div id="page" class="hfeed site">
            <header id="masthead" class="site-header" role="banner">
                <div class="container">
                <div id="logo" class="site-logo text-center" style="font-size: 30px;">
                <?php
                $company = App\Model\helpdesk\Settings\Company::where('id', '=', '1')->first();
				$system = App\Model\helpdesk\Settings\System::where('id', '=', '1')->first();
				?>
				@if($system->url)
					<a href="{!! $system->url !!}" rel="home">
				@else
					<a href="{{url('home')}}" rel="home">
				@endif
                @if($company->use_logo == 1)
                	<img src="{{asset('lb-faveo/dist')}}{{'/'}}{{$company->logo}}" alt="User Image" width="200px" height="200px"/>
                @else
                	@if($system->name)
                		{!! $system->name !!}
                	@else
                		<b>SUPPORT</b> CENTER
                	@endif
                @endif
                </a>

				</div><!-- #logo -->
				<div id="navbar" class="navbar-wrapper text-center">
					<nav class="navbar navbar-default site-navigation" role="navigation">
						<ul class="nav navbar-nav navbar-menu">
							<li class="active"><a href="{{url('home')}}">Home</a></li>
							@if($system->first()->status == 1)
								<li><a href="{{URL::route('form')}}">Submit A Ticket</a></li>
							@endif
                        @if(Auth::user())
							<li><a href="{{url('mytickets')}}">My Tickets</a></li>
							<li><a href="#" >My Profile</a>
								<ul class="dropdown-menu">
									<li>
										<div class="banner-wrapper user-menu text-center clearfix">
											@if(Auth::user()->profile_pic)
                                        		<img src="{{asset('lb-faveo/dist/img')}}{{'/'}}{{Auth::user()->profile_pic}}"class="img-circle" alt="User Image" height="80" width="80"/>
                                         	@else
	                                            <img src="{{ Gravatar::src(Auth::user()->email) }}" class="img-circle" alt="User Image">
                                    		@endif
                                    <h3 class="banner-title text-info h4">{{Auth::user()->first_name." ".Auth::user()->last_name}}</h3>
											<div class="banner-content">
												{{-- <a href="{{url('kb/client-profile')}}" class="btn btn-custom btn-xs">Edit Profile</a> --}} <a href="{{url('auth/logout')}}" class="btn btn-custom btn-xs">Log out</a>
											</div>
										</div>
									</li>
								</ul>
							</li>
						</ul><!-- .navbar-user -->
                        @else
						<ul class="nav navbar-nav navbar-login">
							<li><a href="#" class="collapsed" data-bs-toggle="collapse" data-bs-target="#login-form">Login <i class="sub-indicator fa-solid fa-chevron-circle-down fa-fw text-muted"></i></a></li>
						</ul><!-- .navbar-login -->
						<div id="login-form" class="login-form collapse fade clearfix">
            {!! html()->form('POST', route('post.login'))->open() !!}

			<div class="mb-3 has-feedback {{ $errors->has('email') ? 'has-error' : '' }}">
				{!! html()->text('email', null)->placeholder('Email')->class('form-control') !!}
				{!! $errors->first('email', '<spam class="help-block">:message</spam>') !!}
				<span class="glyphicon glyphicon-envelope form-control-feedback"></span>
          	</div>

			<div class="mb-3 has-feedback {{ $errors->has('password') ? 'has-error' : '' }}">
	            {!! html()->password('password')->placeholder('Password')->class('form-control') !!}
				{!! $errors->first('password', '<spam class="help-block">:message</spam>') !!}
	            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
          	</div>
								<ul class="list-unstyled pull-left">
									<li><a href="{{url('password/email')}}">Forgot password</a><br></li>
									<li><a href="#">Create Account</a></li>
								</ul>
								<button type="submit" class="btn btn-custom pull-right">Login</button>
							{!! html()->closeModelForm() !!}
						</div><!-- #login-form -->
                        @endif
					</nav><!-- #site-navigation -->
				</div><!-- #navbar -->
	<div id="header-search" class="site-search clearfix">
    @if(Session::has('success'))
    	<div class="alert alert-success alert-dismissible">
	        <i class="fa  fa-circle-check"></i>
	        <b>Success!</b>
	        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
	        {{Session::get('success')}}
    	</div>
    @endif
    <!-- failure message -->
    @if(Session::has('fails'))
	    <div class="alert alert-danger alert-dismissible">
	        <i class="fa-solid fa-ban"></i>
	        <b>Alert!</b> Failed.
	        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
	        {{Session::get('fails')}}
	    </div>
    @endif

    @if($errors->first('email') || $errors->first('password'))
		<div class="alert alert-danger alert-dismissible">
	        <i class="fa-solid fa-ban"></i>
	        <b>Alert!</b> Failed.
	        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
	        <li>{!! $errors->first('email', '<spam class="help-block">:message</spam>') !!}</li>
			<li>{!! $errors->first('password', '<spam class="help-block ">:message</spam>') !!}</li>
	    </div>
	@endif

                            </div>
                </div>
                            </header>
                            <!-- Left side column. contains the logo and sidebar -->
                            <!-- Right side column. Contains the navbar and content of the page -->
@yield('breadcrumb')
                                <!-- Main content -->
        <div id="main" class="site-main clearfix">
			<div class="container">
				<div class="content-area">
					<div class="row">
                                   @yield('content')
                        <div id="sidebar" class="site-sidebar col-md-3">
							<div class="widget-area">
							<section id="section-banner" class="section">
									@yield('check')
								</section><!-- #section-banner -->
								<section id="section-categories" class="section">
								@yield('category')
								</section><!-- #section-categories -->
							</div>
						</div><!-- #sidebar -->
                    </div>
                </div>
       		</div>
		</div>
<!-- /.content-wrapper -->
<?php
$footer = App\Model\helpdesk\Theme\Footer::whereId('1')->first();
$footer2 = App\Model\helpdesk\Theme\Footer2::whereId('1')->first();
$footer3 = App\Model\helpdesk\Theme\Footer3::whereId('1')->first();
$footer4 = App\Model\helpdesk\Theme\Footer4::whereId('1')->first();
?>
        <footer id="colophon" class="site-footer" role="contentinfo">
			<div class="container">
				<div class="row col-md-12">
					@if($footer->title == null)
					@else
					<div class="col-md-3">
						<div class="widget-area">
							<section id="section-about" class="section">
								<h2 class="section-line h4 clearfix">{!!$footer->title!!}</h2>
								<div class="textwidget">
									<p>{!!$footer->footer!!}</p>
								</div>
							</section><!-- #section-about -->
						</div>
					</div>
					@endif
					@if($footer2->title == null)
					@else
					<div class="col-md-3">
						<div class="widget-area">
							<section id="section-latest-news" class="section">
								<h2 class="section-title h4 clearfix">{!!$footer2->title!!}</h2>
								<div class="textwidget">
									<p>{!!$footer2->footer!!}</p>
								</div>
							</section><!-- #section-latest-news -->
						</div>
					</div>
					@endif
					@if($footer3->title == null)
					@else
					<div class="col-md-3">
						<div class="widget-area">
							<section id="section-newsletter" class="section">
								<h2 class="section-title h4 clearfix">{!!$footer3->title!!}</h2>
								<div class="textwidget">
                                	<p>{!!$footer3->footer!!}</p>
                                </div>
							</section><!-- #section-newsletter -->
						</div>
					</div>
					@endif
					@if($footer4->title == null)
					@else
                    <div class="col-md-3">
						<div class="widget-area">
							<section id="section-newsletter" class="section">
                            <h2 class="section-title h4 clearfix">{{$footer4->title}}</h2>
                            <div class="textwidget">
                                <p>{!!$footer4->footer!!}</p>
                   			</div>
                       		</section>
                    	</div>
                	</div>
                	@endif
            	</div>
			<div class="clearfix"></div>
					<hr/>
                    <div class="row">
						<div class="site-info col-md-6">
                            <p class="text-muted">Copyright &copy; {!! date('Y') !!}  <a href="{!! $company->website !!}">{!! $company->company_name !!}</a>. All rights reserved. Powered by <a href="http://www.faveohelpdesk.com/">Faveo</a></p>
                        </div>
					</div>
		</footer><!-- #colophon -->
                    <!-- jQuery 2.1.1 -->
                    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
                    <!-- Bootstrap 3.3.2 JS -->
                    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js" type="text/javascript"></script>
                    <!-- Slimscroll -->
					<script src="{{asset("lb-faveo/dist/js/superfish.js")}}" type="text/javascript"></script>
                    <script src="{{asset("lb-faveo/dist/js/mobilemenu.js")}}" type="text/javascript"></script>
                    <script src="{{asset("lb-faveo/dist/js/know.js")}}" type="text/javascript"></script>
                    <script src="{{asset("lb-faveo/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js")}}" type="text/javascript"></script>
                    <script>
                        $(function () {
                        //Add text editor
                        $("textarea").wysihtml5();
                        });
					</script>
                </body>
            </html>