<!DOCTYPE html>
<html dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
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
    <link href="{{ assetLink('css', 'favicon') }}" rel="shortcut icon">

    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

    <!-- Widget CSS -->
    <link href="{{ assetLink('css', 'widget') }}" rel="stylesheet" type="text/css" />

    @if(Lang::getLocale() == 'ar')
    <!-- Bootstrap + Client + Common (RTL) -->
    <link href="{{ assetLink('css', 'bootstrap-rtl') }}" rel="stylesheet" type="text/css" />
    <link href="{{ assetLink('css', 'client-rtl') }}" rel="stylesheet" type="text/css" />
    <link href="{{ assetLink('css', 'common-rtl') }}" rel="stylesheet" type="text/css" />
    <link href="{{ assetLink('css', 'edit-rtl') }}" rel="stylesheet" type="text/css" />
    <link href="{{ assetLink('css', 'custom-rtl') }}" rel="stylesheet" type="text/css" />
    <link href="{{ assetLink('css', 'app-3-rtl') }}" rel="stylesheet" type="text/css" />
    @else
    <!-- Bootstrap + Client + Common (LTR) -->
    <link href="{{ assetLink('css', 'bootstrap') }}" rel="stylesheet" type="text/css" />
    <link href="{{ assetLink('css', 'app') }}" rel="stylesheet" type="text/css" />
    <link href="{{ assetLink('css', 'common') }}" rel="stylesheet" type="text/css" />
    <link href="{{ assetLink('css', 'custom') }}" rel="stylesheet" type="text/css" />
    <link href="{{ assetLink('css', 'client-css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ assetLink('css', 'edit') }}" rel="stylesheet" type="text/css" />
    @endif

    <!-- Font Awesome Icons -->
    <link href="{{ assetLink('css', 'font-awesome') }}" rel="stylesheet" type="text/css" />

    <!-- International Telephone Input -->
    <link href="{{ assetLink('css', 'intl-tel-input') }}" rel="stylesheet" type="text/css" />

    <!-- jQuery Rating -->
    <link href="{{ assetLink('css', 'jquery-rating') }}" rel="stylesheet" type="text/css" />

    <!-- Summernote Lite -->
    <link href="{{ assetLink('css', 'summernote-lite') }}" rel="stylesheet" type="text/css" />

    <!-- Google Fonts -->
    <link rel="stylesheet" href="{{ assetLink('external', 'google-fonts') }}">

    <!-- jQuery -->
    <script src="{{ assetLink('js', 'jquery') }}" type="text/javascript"></script>

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
    #logo { font-size: 1.875rem; }

    #dropdown_content { padding-top: 1rem; margin: 0 !important; }

    #user_avatar { border-radius: 35px; width: 70px; height: 70px; }

    #profile_dropdown { border: 1px solid transparent !important; }

    #profile_dropdown:hover { background: transparent !important; }

    .profile_btn { padding: 3px !important; }

    .text-white:hover { color: white !important; }

    .ellipsize_first_name { overflow: hidden; text-overflow: ellipsis; }

    #lang_ul { width: max-content; font-size: unset !important; }

    .navbar-flag { height: 12px; width: 20px; }

    .lang { cursor: pointer; }

    .breadcrumb { background-color: transparent !important; padding: 0 !important; margin-top: 15px !important; margin-inline-start: 17% !important; }

    .text-small { font-size: 14px; }

    .submit-btn { border: none; background: none; color: white; }

    blockquote { font-size: 14px !important; }

    .form-helper { margin-bottom: 50px; display: inline-block; }

    .alert { width: 100% !important; }

    .has-error .form-control { border-color: #dd4b39; }

    .help-block { color: #dd4b39; }

    .text-red { color: red; }

    .nav-item .dropdown-menu { margin-inline-start: -6px; }

    .btn-primary { background-color: #009aba !important; border-color: #00c0ef !important; }

    .breadcrumb-item+.breadcrumb-item::before { color: #ffffff !important; }

    #header-search { margin-inline-end: 90%; width: 100%; }

    #header-search .form-border { z-index: 0; width: 85%; }

    .search-field { border-radius: 10px; }

    .site-social-list { display: flex; justify-content: flex-end; }
</style>

<div id="page" class="hfeed site text-small">

    <header id="masthead" class="site-header" role="banner">

        <div class="container">

            <nav id="navbar" class="site-navigation navbar navbar-expand-lg navbar-light">

                <div id="logo" class="navbar-brand brand site-logo text-center">

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

                <button class="navbar-toggler custom-toggler" onclick="(function() { jQuery('#navbarSupportedContent').toggle(); })()" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">

                    <span class="navbar-toggler-icon"></span>
                </button> <!-- collapse -->

                <div class="collapse navbar-collapse links justify-content-end" id="navbarSupportedContent">

                    <ul class="navbar-nav navbar-menu site-navigate ms-auto">

                        <li @yield('home') class="nav-item"><a href="{{url('/')}}" class="nav-link">{!! Lang::get('lang.home') !!}</a></li>

                        @if($system->first()->status == 1)
                            <li @yield('submit') class="nav-item">
                                <a href="{{URL::route('form')}}" class="nav-link">{!! Lang::get('lang.submit_a_ticket') !!}</a>
                            </li>
                        @endif

                        <li @yield('kb') class="nav-item dropdown">
                            <a href="{!! url('knowledgebase') !!}" class="dropdown-toggle nav-link" id="navbarDropdown" role="button" data-bs-toggle="dropdown"
                               aria-haspopup="true" aria-expanded="false">{!! Lang::get('lang.knowledge_base') !!}
                            </a>
                            <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu">
                                <li><a href="{{route('category-list')}}" class="dropdown-item">{!! Lang::get('lang.categories') !!}</a></li>
                                <li><a href="{{route('article-list')}}" class="dropdown-item">{!! Lang::get('lang.articles') !!}</a></li>
                            </ul>
                        </li>

                        <?php $pages = App\Model\kb\Page::where('status', '1')->where('visibility', '1')->get();
                        ?>

                        @if(count($pages))
                            <li @yield('pages') class="nav-item dropdown">
                                <a class="dropdown-toggle nav-link" id="navbarDropdown" role="button" data-bs-toggle="dropdown"
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
                                <a href="#" class="dropdown-toggle nav-link" id="navbarDropdown" role="button" data-bs-toggle="dropdown"
                                   aria-haspopup="true" aria-expanded="false">{!! Lang::get('lang.my_profile') !!}
                                </a>
                                <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu">
                                    <li>

                                        <div class="banner-wrapper user-data text-center clearfix" id="profile_dropdown">

                                            <img id="user_avatar" src="{{Auth::user()->profile_pic}}" class="avatar" alt="User Image" height="70" width="70"/>

                                            <div><strong>{{trans('lang.hello')}}</strong></div>

                                            <p class="banner-title ellipsize_first_name h4">{{Auth::user()->first_name." ".Auth::user()->last_name}}</p>

                                            <div class="banner-content" id="dropdown_content">

                                                <a href="{{url('auth/logout')}}" class="btn btn-primary btn-sm profile_btn">{!! Lang::get('lang.log_out') !!}</a>

                                                @if(Auth::user())
                                                    @if(Auth::user()->role != 'user')
                                                        <a href="{{url('dashboard')}}" class="btn btn-primary btn-sm profile_btn">{!! Lang::get('lang.dashboard') !!}</a>
                                                    @endif
                                                @endif
                                                @if(Auth::user())
                                                    @if(Auth::user()->role == 'user')
                                                        <a href="{{url('client-profile')}}" class="btn btn-primary btn-sm profile_btn">{!! Lang::get('lang.profile') !!}</a>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </li>

                        @endif

                        <li class="nav-item dropdown">
                            <?php $src = Lang::getLocale().'.png'; ?>
                            <a href="#" class="nav-link" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                <img src="{{asset("lb-faveo/flags/$src")}}" class="navbar-flag mb-1" alt="language flag" />
                            </a>
                            <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu" id="lang_ul">
                                @foreach($langs as $key => $value)
                                    <?php $src = $key.".png"; ?>
                                    <li><a href="#" id="{{$key}}" onclick="changeLang(this.id)" class="lang dropdown-item d-flex align-items-center gap-2">
                                        <img src="{{asset("lb-faveo/flags/$src")}}" width="20" height="13" alt="{{$key}} flag" />&nbsp;{{$value[0]}}&nbsp;
                                        @if(Lang::getLocale() == "ar")
                                            &rlm;
                                        @endif
                                        ({{$value[1]}})</a></li>
                                @endforeach
                            </ul>
                        </li>

                    </ul>
                </div>
            </nav>

            <div id="header-search" class="site-search clearfix"><!-- #header-search -->
                {!! html()->form('POST', route('client.search'))->attributes(['class' => 'search-form clearfix'])->open() !!}
                <div class="form-border">
                    <div class="d-flex gap-2">
                        <div class="d-flex w-100 gap-2">
                            <input type="text" name="s" class="form-control search-field flex-grow-1" title="{{trans('lang.enter_search_term')}}" placeholder="{{trans('lang.have_a_question?_type_your_search_term_here')}}" required>
                            <button type="submit" class="btn btn-primary">{{trans('lang.search')}}</button>
                        </div>
                    </div>
                </div>
                {!! html()->closeModelForm() !!}
            </div>

        </div>
    </header>
    <!-- Left side column. contains the logo and sidebar -->
    <!-- Right side column. Contains the navbar and content of the page -->
    <div class="site-hero clearfix">
        @yield('breadcrumb')
    </div>

    <!-- Main content -->
    <div id="main" class="site-main clearfix">
        <div class="container">
            <div class="content-area">
                <div>
                    <!-- Success message -->
                    @if(Session::has('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fa-solid fa-circle-check me-1"></i>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            {{Session::get('success')}}
                        </div>
                    @endif

                    <!-- Warning message -->
                    @if(Session::has('warning'))
                        <div class="alert alert-warning alert-dismissible fade show">
                            <i class="fa-solid fa-circle-check me-1"></i>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            {!! Session::get('warning') !!}
                        </div>
                    @endif

                    <!-- Failure message -->
                    @if(Session::has('fails'))
                        @if(Session::has('check'))
                                <?php goto a; ?>
                        @endif
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fa-solid fa-ban me-1"></i>
                            <b>{!! Lang::get('lang.alert') !!} !</b>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            {{Session::get('fails')}}
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
            <hr class="border-secondary"/>
            <div class="row">
                <div class="site-info col-md-6">
                    <p class="text-muted">{!! Lang::get('lang.copyright') !!} &copy; {!! date('Y') !!}  <a href="{!! $company->website !!}" target="_blank">{!! $company->company_name !!}</a>. {!! Lang::get('lang.all_rights_reserved') !!}. {!! Lang::get('lang.powered_by') !!} <a href="https://www.faveohelpdesk.com/" target="_blank">Faveo</a></p>
                </div>
                <div class="site-social text-end col-md-6">
                    <?php $socials = App\Model\helpdesk\Theme\Widgets::all(); ?>
                    <ul class="list-inline hidden-print site-social-list">
                        @foreach($socials as $social)
                            @if($social->name == 'facebook')
                                @if($social->value)
                                    <li><a href="{!! $social->value !!}" class="btn btn-social btn-facebook" target="_blank"><i class="fa-brands fa-facebook fa-fw"></i></a></li>
                                @endif
                            @endif
                            @if($social->name == "twitter")
                                @if($social->value)
                                    <li><a href="{{ $social->value }}" class="btn btn-social btn-twitter" target="_blank"><i class="fa-brands fa-twitter fa-fw"></i></a></li>
                                @endif
                            @endif
                            @if($social->name == "google")
                                @if($social->value)
                                    <li><a href="{{$social->value}}" class="btn btn-social btn-google-plus" target="_blank"><i class="fa-brands fa-google-plus fa-fw"></i></a></li>
                                @endif
                            @endif
                            @if($social->name == "linkedin")
                                @if($social->value)
                                    <li><a href="{{$social->value}}" class="btn btn-social btn-linkedin" target="_blank"><i class="fa-brands fa-linkedin fa-fw"></i></a></li>
                                @endif
                            @endif
                            @if($social->name == "vimeo")
                                @if($social->value)
                                    <li><a href="{{$social->value}}" class="btn btn-social btn-vimeo" target="_blank"><i class="fa-brands fa-vimeo-square fa-fw"></i></a></li>
                                @endif
                            @endif
                            @if($social->name == "youtube")
                                @if($social->value)
                                    <li><a href="{{$social->value}}" class="btn btn-social btn-youtube" target="_blank"><i class="fa-brands fa-youtube fa-fw"></i></a></li>
                                @endif
                            @endif
                            @if($social->name == "pinterest")
                                @if($social->value)
                                    <li><a href="{{$social->value}}" class="btn btn-social btn-pinterest" target="_blank"><i class="fa-brands fa-pinterest fa-fw"></i></a></li>
                                @endif
                            @endif
                            @if($social->name == "dribbble")
                                @if($social->value)
                                    <li><a href="{{$social->value}}" class="btn btn-social btn-dribbble" target="_blank"><i class="fa-brands fa-dribbble fa-fw"></i></a></li>
                                @endif
                            @endif
                            @if($social->name == "flickr")
                                @if($social->value)
                                    <li><a href="{{$social->value}}" class="btn btn-social btn-flickr" target="_blank"><i class="fa-brands fa-flickr fa-fw"></i></a></li>
                                @endif
                            @endif
                            @if($social->name == "instagram")
                                @if($social->value)
                                    <li><a href="{{$social->value }}" class="btn btn-social btn-instagram" target="_blank"><i class="fa-brands fa-instagram fa-fw"></i></a></li>
                                @endif
                            @endif
                            @if($social->name == "rss")
                                @if($social->value)
                                    <li><a href="{{$social->value}}" class="btn btn-social btn-rss" target="_blank"><i class="fa-solid fa-rss fa-fw"></i></a></li>
                                @endif
                            @endif
                            @if($social->name == "skype")
                                @if($social->value)
                                    <li><a href="{{$social->value}}" class="btn btn-social btn-twitter" target="_blank"><i class="fa-brands fa-skype fa-fw"></i></a></li>
                                @endif
                            @endif
                            @if($social->name == "stumble")
                                @if($social->value)
                                    <li><a href="{{$social->value}}" class="btn btn-social btn-google-plus" target="_blank"><i class="fa-brands fa-stumbleupon fa-fw"></i></a></li>
                                @endif
                            @endif
                            @if($social->name == "deviantart")
                                @if($social->value)
                                    <li><a href="{{$social->value}}" class="btn btn-social btn-success" target="_blank"><i class="fa-brands fa-deviantart fa-fw"></i></a></li>
                                @endif
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </footer><!-- #colophon -->

    <!-- Popper -->
    <script src="{{ assetLink('js', 'popper') }}" type="text/javascript"></script>

    <!-- Bootstrap 5 -->
    <script src="{{ assetLink('js', 'bootstrap-client-js') }}" type="text/javascript"></script>

    {{-- <script src="{{ assetLink('js', 'client-min-js') }}" type="text/javascript"></script> --}}

    <!-- Autocomplete -->
    <script src="{{ assetLink('js', 'autocomplete') }}" type="text/javascript"></script>

    <!-- Superfish -->
    <script src="{{ assetLink('js', 'superfish') }}" type="text/javascript"></script>

    <!-- App JS -->
    <script src="{{ assetLink('js', 'app') }}" type="text/javascript"></script>

    <!-- Mobile Menu -->
    <script src="{{ assetLink('js', 'jquery-mobilemenu') }}" type="text/javascript"></script>

    <!-- jQuery Rating -->
    <script src="{{ assetLink('js', 'jquery-rating') }}" type="text/javascript"></script>

    <!-- iCheck -->
    <script src="{{ assetLink('js', 'icheck') }}" type="text/javascript"></script>

    <!-- Language Changer. The base url has to be handed over, without it the script
         falls back to a relative path which 404s on nested routes. -->
    <script>window.faveoBaseUrl = @json(url('/'));</script>
    <script src="{{ assetLink('js', 'language-changer') }}" type="text/javascript"></script>

    {{-- Duplicate languagechanger.js removed --}}

    <!-- Custom JS -->
    <script src="{{ assetLink('js', 'custom') }}" type="text/javascript"></script>

    <!-- HTML5 Shiv -->
    <script src="{{ assetLink('js', 'html5shiv') }}" type="text/javascript"></script>

    <!-- Respond -->
    <script src="{{ assetLink('js', 'respond') }}" type="text/javascript"></script>

    <!-- Summernote Lite -->
    <script src="{{ assetLink('js', 'summernote-lite-js') }}" type="text/javascript"></script>

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

</div><!-- #page -->
</body>
</html>