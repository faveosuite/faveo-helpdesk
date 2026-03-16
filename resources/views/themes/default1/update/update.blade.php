@extends('themes.default1.admin.layout.admin')
@section('content')
<div class="box box-primary">
    <div class="row">
        <div class="col-md-12">
            <div class="box-body">
                <!-- check whether success or not -->
                @if(Session::has('success'))
                <div class="alert alert-success alert-dismissible">
                    <i class="fa  fa-circle-check"></i>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                    {{Session::get('success')}}
                </div>
                @endif
                <!-- failure message -->
                @if(Session::has('fails'))
                <div class="alert alert-danger alert-dismissible">
                    <i class="fa-solid fa-ban"></i><button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                    <b>{!! Lang::get('lang.alert') !!} !</b>            
                    {{Session::get('fails')}}
                </div>
                @endif

                <p>CURRENT VERSION: {{$current_version}}</p>
                <p>Reading Current Releases List</p>

                <?php
                $updated = false;
                $controller = new \App\Http\Controllers\Update\UpgradeController();
                if ($latest_version > $current_version) {

                    echo '<p>New Update Found: v' . $latest_version . '</p>';
                    $found = true;
                    if (!is_file("$controller->dir/UPDATES/faveo-helpdesk-master.zip")) {
                        if (key_exists("dodownload", $_GET) && $_GET["dodownload"] == true) {
                            $download_url = $controller->downloadLatestCode();
                            if ($download_url != null) {
                                $controller->saveLatestCodeAtTemp($download_url);
                            } else {
                                echo '<p>Error in you network connection.</p>';
                                //exit();
                            }
                        } else {
                            echo '<p>Latest code found. <a href=' . url('file-upgrade?dodownload=true') . '>&raquo; Download Now?</a></p>';
                            //exit();
                        }
                    } else {
                        echo '<p>Update ready. <a href=' . url('file-upgrade?doUpdate=true') . '>&raquo; Install Now?</a></p>';
                        //echo '<p>Update already downloaded.</p>';
                    }
                    if ($request->get('doUpdate') == true) {
                        ?>
                        <div class="col-md-12" style="padding-top: 20px; padding-right: 20px;">
                            <div class="box" style="border-top: 0px solid #3C8DBC;">


                                <div class="box-header" style="padding: 20px; padding-top: 1px; ">
                                    <h4 style="padding: 1px;font-size: 18px;margin-left: -7px; color:#021CA2;"><strong>&nbsp;Updating Status</strong>

                                        <br><br>


                                        </div>
                                        <div class="box-body"  style="background-color: black; height: 410px; overflow: scroll;" id="data">
                                            <?php
                                            $updated = $controller->doUpdate();
                                            if ($updated === true) {
                                                $controller->copyToActualDirectory($latest_version);
                                            } elseif ($found != true) {
                                                echo '<p>&raquo; No update is available.</p>';
                                                exit();
                                            }
                                            ?>
                                        </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else{
                        echo '<p>Could not find latest realeases.</p>';
                    }
                    
                    ?>

                </div>
            </div>
        </div>
    </div>

    <script>
        window.setInterval(function () {
            var elem = document.getElementById('data');
            elem.scrollTop = elem.scrollHeight;
        }, 500);
    </script>

    @stop
    
   