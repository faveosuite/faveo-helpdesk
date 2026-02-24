<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <?php
        $title = App\Model\helpdesk\Settings\System::where('id', '=', '1')->first();
        if (isset($title->name)) {
            $title_name = $title->name;
        } else {
            $title_name = "SUPPORT CENTER";
        }
        ?>
        <title> @yield('title') {!! strip_tags($title_name) !!} </title>
        <!-- faveo favicon -->
        <link href="{{asset("lb-faveo/media/images/favicon.ico")}}"  rel="shortcut icon" >

        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

       <link href="{{asset("lb-faveo/css/widgetbox.min.css")}}" rel="stylesheet" type="text/css" />
        <!-- Bootstrap 4.3.1 -->
        <link href="{{asset("lb-faveo/css/bootstrap5.min.css")}}" rel="stylesheet" type="text/css" />
        <!-- Font Awesome Icons -->
        <link href="{{asset("lb-faveo/css/font-awesome-5.min.css")}}" rel="stylesheet" type="text/css" />

        <link href="{{asset("lb-faveo/css/intlTelInput.min.css")}}" rel="stylesheet" type="text/css" />
        <!-- Theme style -->
{{--           <link href="{{asset("lb-faveo/css/client.min.css")}}" rel="stylesheet" type="text/css" />--}}

      <link href="{{asset("lb-faveo/css/app.3.0.css")}}" rel="stylesheet" type="text/css">

        <link href="{{asset("lb-faveo/css/custom.css")}}" rel="stylesheet" type="text/css">

        <link href="{{asset("lb-faveo/css/edit.css")}}" rel="stylesheet" type="text/css">

        <link href="{{asset("lb-faveo/css/jquery.rating.css")}}" rel="stylesheet" type="text/css" />

        <link href="{{asset("lb-faveo/plugins/summernote/summernote-lite.min.css")}}" rel="stylesheet" type="text/css" />

        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

       <script src="{{asset("lb-faveo/js/jquery-3.6.3.min.js")}}" type="text/javascript"></script>

        @yield('HeadInclude')

        <style>
            .note-editor .dropdown-toggle::after {
                all: unset;
            }

            .note-editor .note-dropdown-menu, .note-editor .note-modal-footer {
                box-sizing: content-box;
            }
        </style>
    </head>
    <body>

        <style>

           #dropdown_content{ padding-top: 1rem;margin: 0 !important;}

            #user_avatar{ border-radius: 35px;width: 70px;height: 70px;}

            #profile_dropdown {border: 1px solid transparent !important; }

            #profile_dropdown:hover{background: transparent !important;}

            .profile_btn{padding: 3px !important;}

            .text-white:hover{color: white !important;}

            .ellipsize_first_name {overflow: hidden;text-overflow: ellipsis;}

            .lang_dropdown-menu {right : -1px !important;left : auto !important;}

            #lang_ul{width: max-content;font-size: unset !important;}

            .lang{cursor: pointer;}

            .breadcrumb{background-color: transparent !important;padding: 0 !important;margin-top: 15px !important;margin-left: 17% !important;}

            .text-small{font-size: 14px;}

            .submit-btn { border: none;background: none;color: white; }

            blockquote {font-size: 14px !important;}

            .form-helper {margin-bottom: 50px;display: inline-block;}

            .alert { width: 100% !important; }

            .has-error .form-control { border-color : #dd4b39; }

            .help-block { color : #dd4b39; }

             .text-red { color: red; }

              .nav-item .dropdown-menu{right: unset !important; left: unset!important;;margin-left: -6px;}

              .btn-primary { background-color:#009aba !important;border-color:#00c0ef !important; }

            .breadcrumb-item+.breadcrumb-item::before {color: #ffffff !important;}
        </style>

        <div id="page" class="hfeed site text-small">

            <header id="masthead" class="site-header" role="banner">

                <div class="container">

                    <nav id="navbar" class="site-navigation navbar navbar-expand-lg navbar-light">

                        <div id="logo" class="navbar-brand brand site-logo text-center" style="font-size: 30px;">

                            <?php
                            $company = App\Model\helpdesk\Settings\Company::where('id', '=', '1')->first();
                            $system = App\Model\helpdesk\Settings\System::where('id', '=', '1')->first();
                            ?>
                            @if($system->url)
                            <a href="{!! $system->url !!}" rel="home">
                            @else
                            <a href="{{url('/')}}" rel="home">
                                @endif
                                @if($company->use_logo == 1)
                                <img src="{{asset('uploads/company')}}{{'/'}}{{$company->logo}}" alt="User Image" width="200px" height="200px"/>
                                @else
                                @if($system->name)
                                {!! $system->name !!}
                                @else
                                <b>SUPPORT</b> CENTER
                                @endif
                                @endif
                            </a>
                        </div>

                        <button class="navbar-toggler custom-toggler" onclick="javascript:(function() { jQuery('#navbarSupportedContent').toggle(); })()" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">

                            <span class="navbar-toggler-icon"></span>
                        </button> <!-- collapse -->

                        <div class="collapse navbar-collapse links justify-content-end" id="navbarSupportedContent">

                            <ul class="navbar-nav navbar-menu site-navigate ml-auto">

                                <li @yield('home') class="nav-item"><a href="{{url('/')}}" class="nav-link">{!! Lang::get('lang.home') !!}</a></li>

                                @if($system->first()->status == 1)
                                <li @yield('submit') class="nav-item">
                                    <a href="{{URL::route('form')}}" class="nav-link">{!! Lang::get('lang.submit_a_ticket') !!}</a>
                                </li>
                                @endif

                                <li @yield('kb') class="nav-item dropdown">
                                    <a href="{!! url('knowledgebase') !!}" class="dropdown-toggle nav-link"  id="navbarDropdown" role="button" data-toggle=""
                                        aria-haspopup="true" aria-expanded="false">{!! Lang::get('lang.knowledge_base') !!}
                                    </a>
                                    <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu">
                                        <li><a href="{{route('category-list')}}" class="dropdown-item">{!! Lang::get('lang.categories') !!}</a></li>
                                        <li><a href="{{route('article-list')}}" class="dropdown-item">{!! Lang::get('lang.articles') !!}</a></li>
                                    </ul>
                                </li>

                                 <?php
                                    if(!Auth::check() || Auth::user()->role == 'user')
                                 $pages = App\Model\kb\Page::where('status', '1')->where('visibility', '1')->get();
                                    else
                                        $pages = App\Model\kb\Page::where('status', '1')->get();
                                ?>
                                 @if(count($pages))
                                <li @yield('pages') class="nav-item dropdown">
                                    <a class="dropdown-toggle nav-link"  id="navbarDropdown" role="button" data-toggle=""
                                        aria-haspopup="true" aria-expanded="false">{!! Lang::get('lang.pages') !!}
                                    </a>
                                    <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu">
                                        @foreach($pages as $page)
                                        <li><a href="{{route('pages',$page->slug)}}" class="dropdown-item">{{$page->name}}</a></li>
                                         @endforeach
                                    </ul>
                                </li>
                                @endif

                                @if(Auth::user())
                                <li @yield('myticket') class="nav-item">
                                    <a href="{{url('mytickets')}}" class="nav-link">{!! Lang::get('lang.my_tickets') !!}</a>
                                </li>

                                <li @yield('profile') class="nav-item dropdown">
                                    <a href="#" class="dropdown-toggle nav-link"  id="navbarDropdown" role="button" data-toggle=""
                                        aria-haspopup="true" aria-expanded="false">{!! Lang::get('lang.my_profile') !!}
                                    </a>
                                    <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu">
                                        <li>

                                            <div class="banner-wrapper user-data text-center clearfix" id="profile_dropdown">

                                                <img id="user_avatar" src="{{Auth::user()->profile_pic}}"class="avatar" alt="User Image" height="70" width="70"/>

                                                <div><strong>{{trans('lang.hello')}}</strong></div>

                                                <p class="banner-title ellipsize_first_name h4">{{Auth::user()->first_name." ".Auth::user()->last_name}}</p>

                                                <div class="banner-content" id="dropdown_content">

                                                    <a href="{{url('auth/logout')}}" class="btn btn-custom btn-sm text-white profile_btn" style="background-color: #009aba; hov: #00c0ef; color: #fff "">{!! Lang::get('lang.log_out') !!}</a>

                                                    @if(Auth::user())
                                                    @if(Auth::user()->role != 'user')
                                                        <a href="{{url('dashboard')}}" class="btn btn-custom btn-sm text-white profile_btn" style="background-color: #009aba; hov: #00c0ef; color: #fff ">{!! Lang::get('lang.dashboard') !!}</a>
                                                    @endif
                                                    @endif
                                                    @if(Auth::user())
                                                    @if(Auth::user()->role == 'user')
                                                    <a href="{{url('client-profile')}}" class="btn btn-custom btn-sm text-white profile_btn" style="background-color: #009aba; hov: #00c0ef; color: #fff ">{!! Lang::get('lang.profile') !!}</a>
                                                    @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </li>
                         </ul>
                                </li>
                                @else
                                @if(isset($errors))
                                        <li class="nav-item">
                                                <?php if (is_object($errors) && ($errors->first('email') || $errors->first('password'))) : ?>
                                            <a href="#" class="nav-link sfHover" data-bs-toggle="collapse" data-bs-target="#login-form">
                                                {!! Lang::get('lang.login') !!}
{{--                                                <i class="sub-indicator fa fa-chevron-circle-down fa-fw text-muted"></i>--}}
                                            </a>
                                            <?php else : ?>
                                            <a href="#" class="nav-link collapsed" data-bs-toggle="collapse" data-bs-target="#login-form">
                                                {!! Lang::get('lang.login') !!}
{{--                                                <i class="sub-indicator fa fa-chevron-circle-down fa-fw text-muted"></i>--}}
                                            </a>
                                            <?php endif; ?>
                                        </li>

                                    @endif
                                    @endif
                                    <li class="nav-item dropdown">
                                    <?php $src = Lang::getLocale().'.png'; ?>
                                        <a href="#" class="dropdown-toggle nav-link" id="navbarDropdown" role="button" data-toggle="" aria-haspopup="true"
                                            aria-expanded="false">
                                            <img src="{{asset("lb-faveo/flags/$src")}}"></img>
                                        </a>
                                        <ul class="dropdown-menu" style="right: -1px !important;left: auto !important;" role="menu" aria-labelledby="dropdownMenu" id="lang_ul">
                                            @foreach($langs as $key => $value)
                                                <?php $src = $key.".png"; ?>
                                                <li><a href="#" id="{{$key}}" onclick="changeLang(this.id)" class="lang dropdown-item">
                                                    <img src="{{asset("lb-faveo/flags/$src")}}"></img>&nbsp;{{$value[0]}}&nbsp;
                                            @if(Lang::getLocale() == "ar")
                                                &rlm;
                                            @endif
                                                ({{$value[1]}})</a></li>
                                            @endforeach
                                        </ul>
                                    </li>
                                </ul>
{{--                            <span class="switcher-toggle desk_none" onclick="myFunction()">--}}
{{--                          <span class="icon"></span>--}}
{{--                        </span>--}}

                            <?php
                                    $loginFormClass = "login-form collapse fade clearfix";
                                    if(isset($errors) && ($errors->first('email') || $errors->first('password')))
                                    {
                                        $loginFormClass .= " show";
                                    }
                                ?>
                            <div id="login-form" class="{{$loginFormClass}}">
                                 <div class="row">
                                    <div class="col-md-12">
                                        {!! html()->form('POST', route('post.login'))->open() !!}
                                        @if(Session::has('errors'))
                                        @if(Session::has('check'))
                                        <?php goto b; ?>
                                        @endif
                                        @if(Session::has('error'))
                                        <div class="alert alert-danger alert-dismissable">

                                            {!! Session::get('error') !!}

                                        </div>
                                         @endif
                                        <?php b: ?>
                                        @endif
                                        <div class="form-group has-feedback @if(isset($errors)) {!! $errors->has('email') ? 'has-error' : '' !!} @endif">
                                            {!! html()->text('email', null)->placeholder(Lang::get('lang.e-mail'))->class('form-control') !!}
                                        </div>
                                        <div class="form-group has-feedback @if(isset($errors)) {!! $errors->has('password') ? 'has-error' : '' !!} @endif">
                                            {!! html()->password('password')->placeholder(Lang::get('lang.password'))->class('form-control') !!}
                                            <?php \Illuminate\Support\Facades\Event::dispatch('auth.login.form'); ?>
                                            <a href="{{url('password/email')}}" style="font-size: .8em" class="pull-left">{!! Lang::get('lang.forgot_password') !!}</a>
                                        </div>
                                        <div class="form-group pull-left">
                                         <input type="checkbox" name="remember"> {!! Lang::get("lang.remember") !!}
                                        </div>
                                    </div>
                                    <div class="col-md-12 text-center">
                                            <button type="submit" class="btn btn-custom" style="background-color: #009aba; hov: #00c0ef; color: #fff ">{!! Lang::get('lang.login') !!}</button>
                                        {!! html()->closeModelForm() !!}
                                    </div>

                                <div class="col-md-12 text-center">
                                     {{Lang::get('lang.or')}}
                                    <ul class="list-unstyled">
                                        <a href="{{url('auth/register')}}" style="font-size: 1.2em">{!! Lang::get('lang.create_account') !!}</a>
                                    </ul>
                                </div>
                                </div>
                                    <div>
                                        @include('themes.default1.client.layout.social-login')
                                    </div>
                            </div><!-- #login-form -->
                        </div>
                    </nav>

                    <div id="header-search" class="site-search clearfix" style="margin-right: 90%; width: 100%"><!-- #header-search -->
                        {!! html()->form('POST', route('client.search'))->attributes(['class' => 'search-form clearfix'])->open() !!}
                        <div class="form-border" style="z-index: 0;width: 85%;">
                            <div class="form-inline ">
                                <div class="form-group input-group " style="width: 98% ">
                                    <input type="text" name="s" class="search-field form-control" title="Enter search term" placeholder="{{trans('lang.have_a_question?_type_your_search_term_here')}}" required="" style="width: 80%">
                                    <span class="input-group-btn">
                                        <button type="submit" class="btn btn-custom btn-md " style="margin-left: 20% ;background-color: #009aba; hov: #00c0ef; color: #fff ">{{trans('lang.search')}}</button>
                                    </span>
                                </div>
                            </div><!----><!----></div>
                                <style>
                                    .search-field {
                                        border-radius: 10px; /* You can adjust the value to your desired radius */
                                    }
                                </style>
                            </div>
                        </div>
                        {!! html()->closeModelForm() !!}
                    </div>
                </div>
            </header>
            <!-- Left side column. contains the logo and sidebar -->
            <div class="site-hero clearfix">
{{--                {!! \DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs::render() !!}--}}
                @yield('breadcrumb')
            </div>



            <div id="main" class="site-main clearfix">
                <div class="container">
                    <div class="content-area">
                        <div>
                            <!-- Success message -->
                            @if(Session::has('success'))
                                <div class="alert alert-success alert-dismissable" style="padding-right:20px">
                                    <i class="fa fa-check-circle"></i>
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    {{ Session::get('success') }}
                                </div>
                            @endif

                            <!-- Warning message -->
                            @if(Session::has('warning'))
                                <div class="alert alert-warning alert-dismissable" style="padding-right:20px">
                                    <i class="fa fa-check-circle"></i>
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                    {!! Session::get('warning') !!}
                                </div>
                            @endif

                            <!-- Failure message -->
                            @if(Session::has('fails'))
                                @if(Session::has('check'))
                                        <?php goto a; ?>
                                @endif
                                <div class="alert alert-danger alert-dismissable" style="padding-right:20px">
                                    <i class="fa fa-ban"></i>
                                    <b>{!! Lang::get('lang.alert') !!} !</b>
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    {{ Session::get('fails') }}
                                </div>
                                    <?php a: ?>
                            @endif
                        </div>
                        <div class="row">
                            @yield('content')
                            @yield('check')
                            @yield('category')
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rest of the code... -->

            <!-- /.content-wrapper -->
            <?php
            $footer1 = App\Model\helpdesk\Theme\Widgets::where('name', '=', 'footer1')->first();
            $footer2 = App\Model\helpdesk\Theme\Widgets::where('name', '=', 'footer2')->first();
            $footer3 = App\Model\helpdesk\Theme\Widgets::where('name', '=', 'footer3')->first();
            $footer4 = App\Model\helpdesk\Theme\Widgets::where('name', '=', 'footer4')->first();
            ?>
            <footer id="colophon" class="site-footer" role="contentinfo">
                <div class="container">
                    <div class="row">
                        @if($footer1->title == null)
                        @else
                        <div class="col-md-3">
                            <div class="widget-area">
                                <section id="section-about" class="section">
                                    <h2 class="section-title h4 clearfix">{!!$footer1->title!!}</h2>
                                    <div class="textwidget">
                                        <p>{!!$footer1->value!!}</p>
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
                                        <p>{!! $footer2->value !!}</p>
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
                                        <p>{!! $footer3->value !!}</p>
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
                                        <p>{!! $footer4->value !!}</p>
                                    </div>
                                </section>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="clearfix"></div>
                    <hr style="color:#E5E5E5"/>
                    <div class="row">
                        <div class="site-info col-md-6">
                            <p class="text-muted">{!! Lang::get('lang.copyright') !!} &copy; {!! date('Y') !!}  <a href="{!! $company->website !!}" target="_blank">{!! $company->company_name !!}</a>. {!! Lang::get('lang.all_rights_reserved') !!}. {!! Lang::get('lang.powered_by') !!} <a href="https://www.faveohelpdesk.com/"  target="_blank">Faveo</a></p>
                        </div>
                        <div class="site-social text-right col-md-6">
                            <?php $socials = App\Model\helpdesk\Theme\Widgets::all(); ?>
                            <ul class="list-inline hidden-print" style="display: flex;float: right;">
                                @foreach($socials as $social)
                                @if($social->name == 'facebook')
                                @if($social->value)
                                <li><a href="{!! $social->value !!}" class="btn btn-social btn-facebook" target="_blank"><i class="fab fa-facebook fa-fw"></i></a></li>
                                @endif
                                @endif
                                @if($social->name == "twitter")
                                @if($social->value)
                                <li><a href="{{ $social->value }}" class="btn btn-social btn-twitter" target="_blank"><i class="fab fa-twitter fa-fw"></i></a></li>
                                @endif
                                @endif
                                @if($social->name == "google")
                                @if($social->value)
                                <li><a href="{{$social->value}}" class="btn btn-social btn-google-plus" target="_blank"><i class="fab fa-google-plus fa-fw"></i></a></li>
                                @endif
                                @endif
                                @if($social->name == "linkedin")
                                @if($social->value)
                                <li><a href="{{$social->value}}" class="btn btn-social btn-linkedin" target="_blank"><i class="fab fa-linkedin fa-fw"></i></a></li>
                                @endif
                                @endif
                                @if($social->name == "vimeo")
                                @if($social->value)
                                <li><a href="{{$social->value}}" class="btn btn-social btn-vimeo" target="_blank"><i class="fab fa-vimeo-square fa-fw"></i></a></li>
                                @endif
                                @endif
                                @if($social->name == "youtube")
                                @if($social->value)
                                <li><a href="{{$social->value}}" class="btn btn-social btn-youtube" target="_blank"><i class="fab fa-youtube fa-fw"></i></a></li>
                                @endif
                                @endif
                                @if($social->name == "pinterest")
                                @if($social->value)
                                <li><a href="{{$social->value}}" class="btn btn-social btn-pinterest" target="_blank"><i class="fab fa-pinterest fa-fw"></i></a></li>
                                @endif
                                @endif
                                @if($social->name == "dribbble")
                                @if($social->value)
                                <li><a href="{{$social->value}}" class="btn btn-social btn-dribbble" target="_blank"><i class="fab fa-dribbble fa-fw"></i></a></li>
                                @endif
                                @endif
                                @if($social->name == "flickr")
                                @if($social->value)
                                <li><a href="{{$social->value}}" class="btn btn-social btn-flickr" target="_blank"><i class="fab fa-flickr fa-fw"></i></a></li>
                                @endif
                                @endif
                                @if($social->name == "instagram")
                                @if($social->value)
                                <li><a href="{{$social->value }}" class="btn btn-social btn-instagram" target="_blank"><i class="fab fa-instagram fa-fw"></i></a></li>
                                @endif
                                @endif
                                @if($social->name == "rss")
                                @if($social->value)
                                <li><a href="{{$social->value}}" class="btn btn-social btn-rss" target="_blank"><i class="fas fa-rss fa-fw"></i></a></li>
                                @endif
                                @endif
                                @if($social->name == "skype")
                                @if($social->value)
                                <li><a href="{{$social->value}}" class="btn btn-social btn-twitter" target="_blank"><i class="fab fa-skype fa-fw"></i></a></li>
                                @endif
                                @endif
                                @if($social->name == "stumble")
                                @if($social->value)
                                <li><a href="{{$social->value}}" class="btn btn-social btn-google-plus" target="_blank"><i class="fab fa-stumbleupon fa-fw"></i></a></li>
                                @endif
                                @endif
                                @if($social->name == "deviantart")
                                @if($social->value)
                                <li><a href="{{$social->value}}" class="btn btn-social btn-success" target="_blank"><i class="fab fa-deviantart fa-fw"></i></a></li>
                                @endif
                                @endif
                                @endforeach
                            </ul>
                        </div>
                    </div></div>
            </footer><!-- #colophon -->

            <script src="{{asset("lb-faveo/js/popper.min.js")}}" type="text/javascript"></script>
            <!-- Bootstrap 3.3.2 JS -->
            <script src="{{asset("lb-faveo/js/bootstrap5.min.js")}}" type="text/javascript"></script>

{{--          <script src="{{asset("lb-faveo/js/client.min.js")}}" type="text/javascript"></script>--}}

           <script src="{{asset("lb-faveo/js/autocomplete.js")}}" type="text/javascript"></script>

           <script src="{{asset("lb-faveo/js/superfish.min.js")}}" type="text/javascript"></script>

           <script src="{{asset("lb-faveo/js/app.js")}}" type="text/javascript"></script>-

            <script src="{{asset("lb-faveo/js/jquery.mobilemenu.js")}}" type="text/javascript"></script>

            <script src="{{asset("lb-faveo/js/jquery.rating.pack.js")}}" type="text/javascript"></script>

            <script src="{{asset("lb-faveo/plugins/iCheck/icheck.min.js")}}" type="text/javascript"></script>

            <script src="{{asset("lb-faveo/js/languagechanger.js")}}" type="text/javascript"></script>

            <script src="{{asset("lb-faveo/js/languagechanger.js")}}" type="text/javascript"></script>

            <script src="{{asset("lb-faveo/js/custom.js")}}" type="text/javascript"></script>

          <script src="{{asset("lb-faveo/js/html5shiv.min.js")}}" type="text/javascript"></script>

          <script src="{{asset("lb-faveo/js/respond.min.js")}}" type="text/javascript"></script>

            <script src="{{asset("lb-faveo/plugins/summernote/summernote-lite.min.js")}}" type="text/javascript"></script>

            <script>
$(function () {
//Enable check and uncheck all functionality
    $(".checkbox-toggle").click(function () {
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
    $(".mailbox-star").click(function (e) {
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

        </div>
        </div>
    </body>
</html>