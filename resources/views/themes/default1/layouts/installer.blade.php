<html>
<head>
        <meta charset="UTF-8">
        <title>Faveo HELPDESK | Insatller</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        {{-- lb-faveo/downloads/bootstrap.min.css, lb-faveo/downloads/ionicons.min.css and
             lb-faveo/dist/css/AdminLTE.min.css were linked here but are not shipped: each
             one was a 404 on every render, so the links were removed. --}}
        {{-- {{ HTML::style('ep-content/themes/ep-admin/default1/css/bootstrap.min.css'); }} --}}

        <!-- font Awesome -->
        {{-- <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" /> --}}
        {{-- {{ HTML::style('ep-content/themes/ep-admin/default1/css/font-awesome.min.css'); }} --}}

        {{-- {{ HTML::style('ep-content/themes/ep-admin/default1/admin/css/ionicons.min.css'); }} --}}

        {{-- {{ HTML::style('ep-content/themes/ep-admin/default1/admin/css/AdminLTE.css'); }} --}}

        <!-- Bootstrap time Picker -->
        {{-- <link href="{{asset("downloads/ionicons.min.css")}}" rel="stylesheet" type="text/css" /> --}}
        {{-- {{ HTML::style('ep-content/themes/ep-admin/default1/css/daterangepicker/daterangepicker-bs3.css'); }} --}}

        <!-- daterange picker -->
        {{-- <link href="{{asset("downloads/ionicons.min.css")}}" rel="stylesheet" type="text/css" /> --}}
        {{-- {{ HTML::style('ep-content/themes/ep-admin/default1/css/timepicker/bootstrap-timepicker.min.css'); }} --}}

<style type="text/css">
a:link    {color:#000;}  /* unvisited link  */
a:visited {color:#000;}  /* visited link    */
a:hover   {color:#000;}  /* mouse over link */
a:active  {color:#000;}

</style>
<style type="text/css">
    #access2{
        float: left;
        /*position: fixed;*/
        width: 100px;
        background: #E60000;
        font-weight: bold;
        padding: 10px;
        border: 0 none;
        border-radius: 3px;
    }
    
    #access1{
        float: left;
        /*position: fixed;*/
        width: 100px;
        background: #27AE60;
        font-weight: bold;
        padding: 10px;
        border: 0 none;
        border-radius: 3px;
    }
    
    #access{
        float: right;
        /*position: fixed;*/
        width: 100px;
        background: #27AE60;
        font-weight: bold;
        padding: 10px;
        border: 0 none;
        border-radius: 3px;
        color:black;
    }

    #access5{
        float: right;
        /*position: fixed;*/
        width: 100px;
        background: #27AE60;
        font-weight: bold;
        padding: 10px;
        border: 0 none;
        border-radius: 3px;
    }
    
    #inputfield{
        /*padding: 10px;*/
        border: 0 none;
        border-radius: 3px;
        }
</style>
<style type="text/css">

    .ok span, .warning span, .error span {
            font-weight: bolder;
        }

    ok span {
            color: green;
        }

    .warning span {
            color: orange;
        }

    .error span {
            color: red;
        }

</style>

</head>
<body style="background-color:#d2d6de;">
<div class="login-box">
    @yield('content')
    <p id="footer">&copy;<?php echo date('Y')?>. Powered by <a href="http://www.faveohelpdesk.com">Faveo </a></p>
</div>
</body>
</html>
