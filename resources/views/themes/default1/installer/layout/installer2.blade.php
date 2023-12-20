<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta name="viewport" content="width=device-width">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Faveo HELPDESK</title>
        <link rel="shortcut icon" href="{{asset("lb-faveo/media/images/favicon.ico")}}">
        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
        <link rel="stylesheet" href="{{asset("lb-faveo/css/load-styles.css")}}" type="text/css" media="all">
        <link rel="stylesheet" href="{{asset("lb-faveo/css/css.css")}}" type="text/css" media="all">
        <link rel="stylesheet" href="{{asset("lb-faveo/css/admin.css")}}" type="text/css" media="all">
        <link rel="stylesheet" href="{{asset("lb-faveo/css/wc-setup.css")}}" type="text/css" media="all">
        <link rel="stylesheet" href="{{asset("lb-faveo/css/activation.css")}}" type="text/css" media="all">
        <link rel="stylesheet" href="{{asset("lb-faveo/css/style.css")}}" type="text/css" media="all">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
        <link href="{{asset("lb-faveo/css/ggpopover.css")}}" rel="stylesheet"/>
        <link rel="stylesheet" href="{{asset("lb-faveo/css/prism.css")}}">
        <link rel="stylesheet" href="{{asset("lb-faveo/css/chosen.css")}}">
    </head>
    <body class="wc-setup wp-core-ui">
        <center><h1 id="wc-logo"><a href="http://www.faveohelpdesk.com">
            <img src="{{asset("lb-faveo/media/installer/faveo.png")}}" alt="faveo" width="
            250px"></a></h1></center>
   
    <div class="wc-setup-content">
        @yield('content')
    </div>
    
    
    <p style="text-align: center;"> Copyright &copy; 2015 - <?php echo date('Y')?> · Ladybird Web Solution Pvt Ltd. All Rights Reserved. Powered by <a target="_blank" href="http://www.faveohelpdesk.com">Faveo </a></p>
    


    
    <script src="{{asset("lb-faveo/js/ggpopover.js")}}"></script>
    <script type="text/javascript">
        $('[data-bs-toggle="popover"]').ggpopover();
    </script>
    <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js" type="text/javascript"></script> -->
    <script src="{{asset("lb-faveo/js/chosen.jquery.js")}}" type="text/javascript"></script>
    <script src="{{asset("lb-faveo/js/prism.js")}}" type="text/javascript" charset="utf-8"></script>
    <script type="text/javascript">
        var config = {
            '.chosen-select': {},
            '.chosen-select-deselect': {
                allow_single_deselect: true
            },
            '.chosen-select-no-single': {
                disable_search_threshold: 10
            },
            '.chosen-select-no-results': {
                no_results_text: 'Oops, nothing found!'
            },
            '.chosen-select-width': {
                width: "95%"
            }
        }
        for (var selector in config) {
            $(selector).chosen(config[selector]);
        }
    </script>
    </body>
</html>