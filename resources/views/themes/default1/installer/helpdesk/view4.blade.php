@extends('themes.default1.installer.layout.installer')

@section('license')
done
@stop

@section('environment')
done
@stop

@section('database')
active
@stop

@section('content') 

<h1 style="text-align: center;">Database Setup</h1>
This test will check prerequisites required to install Faveo<br/>
<?php
/**
 * Faveo HELPDESK Probe
 *
 * Copyright (c) 2014 Ladybird Web Solution.
 *
 */
// -- Please provide valid database connection parameters ------------------------------
$default = Session::get('default');
$host = Session::get('host');
$username = Session::get('username');
$password = Session::get('password');
$databasename = Session::get('databasename');
$dummy_install = Session::get('dummy_data_installation');
$port = Session::get('port');
define('DB_HOST', $host); // Address of your MySQL server (usually localhost)
define('DB_USER', $username); // Username that is used to connect to the server
define('DB_PASS', $password); // User's password
define('DB_NAME', $databasename); // Name of the database you are connecting to
define('DB_PORT', $port); // Name of the database you are connecting to
define('PROBE_VERSION', '4.2');
define('PROBE_FOR', '<b>Faveo</b> HELPDESK 1.0 and Newer');
define('STATUS_OK', 'Ok');
define('STATUS_WARNING', 'Warning');
define('STATUS_ERROR', 'Error');

class TestResult {

    var $message;
    var $status;

    function __construct($message, $status = STATUS_OK) {
        $this->message = $message;
        $this->status = $status;
    }

}

// TestResult
if (DB_HOST && DB_USER && DB_NAME) {
    ?>
    <?php
    $mysqli_ok = true;
    $results = [];
    // error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
    error_reporting(0);
try {
    if ($default == 'mysql') {
        if(DB_PORT != '' && is_numeric(DB_PORT)) {
            $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        } else {
            $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        }
        if ($connection) {
            $results[] = new TestResult('Connected to database as ' . DB_USER . '@' . DB_HOST . DB_PORT, STATUS_OK);
            if (mysqli_select_db($connection, DB_NAME)) {
                $results[] = new TestResult('Database "' . DB_NAME . '" selected', STATUS_OK);
                $mysqli_version = mysqli_get_server_info($connection);
                if (version_compare($mysqli_version, '8') >= 0) {
                    $results[] = new TestResult('MySQL version is ' . $mysqli_version, STATUS_OK);
                    // $have_inno = check_have_inno($connection);
                    $sql = "SHOW TABLES FROM " . DB_NAME;
                    $res = mysqli_query($connection, $sql);
                    if (mysqli_fetch_array($res) === null) {
                        $results[] = new TestResult('Database is empty');
                        $mysqli_ok = true;
                    } else {
                        $results[] = new TestResult('Faveo installation requires an empty database, your database already has tables and data in it.', STATUS_ERROR);
                        $mysqli_ok = false;
                    }
                } else {
                    $results[] = new TestResult('Your MySQL version is ' . $mysqli_version . '. We recommend upgrading to at least MySQL8!', STATUS_ERROR);
                    $mysqli_ok = false;
                } // if
            } else {
                $results[] = new TestResult('Failed to select database. ' . mysqli_connect_error(), STATUS_ERROR);
                $mysqli_ok = false;
            } // if
        } else {
            $results[] = new TestResult('Failed to connect to database. ' . mysqli_connect_error(), STATUS_ERROR);
            $mysqli_ok = false;
        } // if
    }
}catch (\Exception $exception) {
    $results[] = new TestResult('Failed to connect to database. ' . $exception->getMessage(), STATUS_ERROR);
    $mysqli_ok = false;
}

    // elseif($default == 'pgsql') {
    //     if ($connection2 = pg_connect("'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASS.")) {
    //         $results[] = new TestResult('Connected to database as ' . DB_USER . '@' . DB_HOST, STATUS_OK);
    //     } else {
    //         $results[] = new TestResult('Failed to connect to database. <br> PgSQL said: ' . mysqli_error(), STATUS_ERROR);
    //         $mysqli_ok = false;
    //     }
    // } elseif($default == 'sqlsrv') {
    // }
    // ---------------------------------------------------
    //  Validators
    // ---------------------------------------------------
// dd($results);
    ?><p class="setup-actions step"><?php
    foreach ($results as $result) {
        print '<br><span class="' . strtolower($result->status) . '">' . $result->status . '</span> &mdash; ' . $result->message . '';
    } // foreach
    ?> </p>
    <!-- </ul> -->
<?php } else { ?>
    <br/>
    <ul>
        <li><p>Unable to test database connection. Please make sure your database server is up and running and PHP is working with session.</p></li>
    </ul>
    <p>If you have fixed all the errors. <a href="{{ URL::route('configuration') }}">Click here</a> to continue the installation process.</p>
    <?php $mysqli_ok = null; ?>
<?php } ?>

<?php if ($mysqli_ok !== null) { ?>
    <?php if ($mysqli_ok) { ?>

        <div class="woocommerce-message woocommerce-tracker" >
            <p id="pass">Database connection successful. This system can run Faveo</p>
        </div>

        <script src="{{asset("lb-faveo/js/ajax-jquery.min.js")}}"></script>

        <span id="wait"></span>

        {!! html()->form('POST', url()->current())->attributes(['id' => 'form'])->open() !!}
        {{-- <input type="hidden" name="_token" value="{{ csrf_token() }}"> --}}
        <!-- <b>default</b><br> -->
        <input type="hidden" name="default" value="{{ $default }}"/>
        <!-- <b>Host</b><br> -->
        <input type="hidden"  name="host" value="{{ $host }}"/>
        <!-- <b>Database Name</b><br> -->
        <input type="hidden" name="databasename" value="{{ $databasename }}"/>
        <!-- <b>User Name</b><br> -->
        <input type="hidden" name="username" value="{{ $username }}"/>
        <!-- <b>User Password</b><br> -->
        <input type="hidden" name="password" value="{{ $password }}"/>
        <!-- <b>Port</b><br> -->
        <input type="hidden" name="port" value="{{ $port }}"/>
        <!-- Dummy data installation -->
        <input type="hidden" name="dummy_install" value="{{ $dummy_install }}"/>

        <input type="submit" style="display:none;">

        </form>

        <div id="show" style="display:none;">
            <div class="row">
                <div class="col-md-2">
                </div>
                <div class="col-md-9" style="text-align: center"id='loader' >
                    <img src="{{asset("lb-faveo/media/images/gifloader.gif")}}"><br/><br/><br/>
                </div>
            </div>
        </div>

        <div style="border-bottom: 1px solid #eee;">
            <p class="setup-actions step" id="retry">
                <a href="{{ URL::route('account') }}" class="pull-right" id="next" style="text-color:black"><input type="submit" id="submitme" class="button-primary button button-large button-next" value="Continue"> </a>
                <a href="{{ URL::route('configuration') }}" class="button button-large button-next" style="float: left">Previous</a>
            </p>
        </div>

        <br/>

        <script type="text/javascript">
        // submit a ticket
        $(document).ready(function () {
            $("#form").submit();
        });
        // Edit a ticket
        $('#form').on('submit', function () {
            $.ajax({
                type: "GET",
                url: "{!! url('create/env') !!}",
                dataType: "json",
                data: $(this).serialize(),
                beforeSend: function () {
                    $("#conn").hide();
                    $("#show").show();
                    $("#wait").show();
                },
                success: function (response) {
                    var data=response.result;
                    console.log(data);
                    var message = data.success;
                    var next = data.next;
                    var api = data.api;
                    $('#submitme').attr('disabled','disabled');
                    $('#wait').append('<ul><li>'+message+'</li><li class="seco">'+next+'...</li></ul>');
                    callApi(api);
                },
                error: function(response){
                    var errorMsg = (response.responseJSON && response.responseJSON.result) ? response.responseJSON.result.error : 'An unexpected server error occurred. Please check server logs.';
                    $('#wait').append('<ul><li style="color:red">'+errorMsg+'</li></ul>');
                    $('#loader').hide();
                    $('#next').find('#submitme').hide();
                    $('#retry').append('<input type="button" id="submitm" class="button-primary button button-large button-next" value="Retry" onclick="reload()">');
                    
                }
            })
            return false;
        });

        function callApi(api) {
            $.ajax({
                type: "GET",
                url: api,
                dataType: "json",
                data: $(this).serialize(),
                success: function (response) {
                    var data=response.result;
                    console.log(data);
                    var message = data.success;
                    var next = data.next;
                    var api = data.api;
                    $("#wait").find('.seco').remove();
                    $('#wait ul').append('<li>'+message+'</li><li class="seco">'+next+'...</li>');
                    if (message == 'Database has been setup successfully.') {
                        $('#loader').hide();
                        $('#next').find('#submitme').show();
                        $('#submitme').removeAttr('disabled');
                        $('.seco').hide();
                    } else {
                        callApi(api);
                    }
                },
                error: function(response){
                    console.log(response);
                    var errorMsg = (response.responseJSON && response.responseJSON.result) ? response.responseJSON.result.error : 'An unexpected server error occurred. Please check server logs.';
                    $('#wait ul').append('<li style="color:red">'+errorMsg+'</li>');
                    $('#loader').hide();
                    $('#next').find('#submitme').hide();
                    $('#retry').append('<input type="button" id="submitm" class="button-primary button button-large button-next" value="Retry" onclick="reload()">');
                }
            });
        }
        function reload(){
            $('#retry').find('#submitm').remove();
            $('#loader').show();
            $('#wait').find('ul').remove();
            $.ajax({
                type: "GET",
                url: "{!! url('create/env') !!}",
                dataType: "json",
                data: $(this).serialize(),
                beforeSend: function () {
                    $("#conn").hide();
                    $("#show").show();
                    $("#wait").show();
                },
                success: function (response) {
                    var data=response.result;
                    console.log(data);
                    var message = data.success;
                    var next = data.next;
                    var api = data.api;
                    $('#submitme').attr('disabled','disabled');
                    $('#wait').append('<ul><li>'+message+'</li><li class="seco">'+next+'...</li></ul>');
                    callApi(api);
                },
                error: function(response){
                    var errorMsg = (response.responseJSON && response.responseJSON.result) ? response.responseJSON.result.error : 'An unexpected server error occurred. Please check server logs.';
                    $('#wait').append('<ul><li style="color:red">'+errorMsg+'</li></ul>');
                    $('#loader').hide();
                    $('#next').find('#submitme').hide();
                    $('#retry').append('<input type="button" id="submitm" class="button-primary button button-large button-next" value="Retry" onclick="reload()">');
                    
                }
            })
            
        }
        </script>

    <?php } else { ?>
        <div class="woocommerce-message woocommerce-tracker" >
            <p id="fail">Database connection unsuccessful. This system does not meet Faveo system requirements</p>
        </div>
        <p>This either means that the username and password information is incorrect or we can&rsquo;t contact the database server. This could mean your host&rsquo;s database server is down.</p>
        <ul>
            <li>Are you sure you have the correct username and password?</li>
            <li>Are you sure that you have typed the correct hostname?</li>
            <li>Are you sure that the database server is running?</li>
        </ul>
        <p>If you&rsquo;re unsure what these terms mean you should probably contact your host. If you still need help you can always visit the <a href="http://www.ladybirdweb.com/support">Faveo Support </a>.</p>


        <div  style="border-bottom: 1px solid #eee;">
            @if(Cache::has('step4')) <?php Cache::forget('step4') ?> @endif
            <p class="setup-actions step">
                <input type="button" id="submitme" class="button-danger button button-large button-next" style="background-color: #d43f3a;color:#fff;" value="continue" disabled>
                <a href="{{URL::route('configuration')}}" class="button button-large button-next" style="float: left;">Previous</a>
            </p>
        </div>
        <br/><br/>
    <?php } // if  ?>
    <div id="legend">
        {{-- <ul> --}}
        <p class="setup-actions step">
            <span class="ok">Ok</span> &mdash; All Ok <br/>
            <span class="warning">Warning</span> &mdash; Not a deal breaker, but it's recommended to have this installed for some features to work<br/>
            <span class="error">Error</span> &mdash; Faveo HELPDESK require this feature and can't work without it<br/>
        </p>
        {{-- </ul> --}}
    </div>
<?php } // if  ?>

@stop