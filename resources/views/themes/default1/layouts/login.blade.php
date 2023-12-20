<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>SUPPORT CENTER</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    
    <link rel="shortcut icon" href="{{ assetLink('css' , 'favicon') }}">
    
    <link href="{{ assetLink('css', 'bootstrap') }}" rel="stylesheet" type="text/css" />
    <!-- Font Awesome Icons -->
    <link href="{{ assetLink('css' , 'font-awesome')}}" rel="stylesheet" type="text/css" />
    <!-- Theme style -->
    <link href="{{ assetLink('css' , 'client')}}" rel="stylesheet" type="text/css" />

    <link href="{{ assetLink('css' , 'widget')}}" rel="stylesheet" type="text/css" />
    <link href="{{ assetLink('css' , 'common')}}" rel="stylesheet" type="text/css" />
    
  </head>
  <body>

     <style type="text/css">
      
      .site-hero {
          padding: 35px 0;
          padding-top: 1px !important;
          background: rgb(0, 154, 186) !important;
      }
      .breadcrumb {
            width: 80%;
            margin: 20px 0% !important;
        }

        .form-helper {margin-bottom: 50px;display: inline-block;}

      .alert { width: 100% !important; }

      .has-error .form-control { border-color : #dd4b39; }

       .help-block { color : #dd4b39; }

       .text-red { color: red; }

       .btn-primary { background-color:#009aba !important;border-color:#00c0ef !important; }
    </style>

    <div id="page" class="hfeed site">
    
    <header id="masthead" class="site-header" role="banner">
      
      <div class="container">

        <div id="logo" class="col-md-12 site-logo text-center">
          
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
              
              <img src="{{asset('uploads/company')}}{{'/'}}{{$company->logo}}" alt="User Image" width="200px" />
            @else
              @if($system->name)
                <h1>{!! $system->name !!}</h1>
              @else
                <h1><b>SUPPORT</b> CENTER</h1>
              @endif
            @endif
          </a>
        </div>

        <div id="header-search" class="site-search clearfix"></div>

      </div>
    </header>

    <div class="site-hero clearfix">

      <div class="container">
      
        <ol class="breadcrumb breadcrumb-custom">
      
          <li class="text">You are here :</li>
      
          <li class="active" id="active_breadcrumb"></li>
        </ol>
      </div>
    </div>

    <div id="main" class="site-main clearfix">
      
      <div class="container">
  
        <div class="content-area">

           @yield('body')
        </div>
      </div>
    </div>

    <footer id="colophon" class="site-footer" role="contentinfo">
      
      <div class="container">

        <div class="col-sm-12 text-center">
          
          <div class="site-info">

            <p class="text-muted">{!! Lang::get('lang.copyright') !!} &copy; {!! date('Y') !!}  <a href="{!! $company->website !!}">{!! $company->company_name !!}</a>. {!! Lang::get('lang.all_rights_reserved') !!}. {!! Lang::get('lang.powered_by') !!} <a href="http://www.faveohelpdesk.com/"  target="_blank">Faveo</a></p>
          </div>
        </div>
      </div>
    </footer> 
  </div>

    <script src="{{ assetLink('js' , 'client-js')}}" type="text/javascript"></script>

    <script src="{{ assetLink('js' , 'bootstrap')}}" type="text/javascript"></script>
            
    <script src="{{ assetLink('js' , 'client-min-js')}}" type="text/javascript"></script>

    <script type="text/javascript">

      var locale = window.location.pathname.split('/');

      if(locale[locale.length - 3] === 'password'){

        document.getElementById('active_breadcrumb').innerText = 'Reset Password';
      } else {

        document.getElementById('active_breadcrumb').innerText = 'License';
      }
    </script>
  </body>
</html>
