{!! html()->modelForm($emails, 'PATCH', url('post-scheduler'))->open() !!}
@if (count($errors) > 0)
<div class="alert alert-danger">
    <strong>{{Lang::get('lang.woops')}}</strong> {{Lang::get('lang.theirisproblem')}}<br><br>
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
@if($warn!=="")
<div class="alert alert-warning alert-dismissible">
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {!!$warn!!}
</div>
@endif
<!-- check whether success or not -->
@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa-solid  fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {!!Session::get('success')!!}
</div>
@endif
<!-- failure message -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {!!Session::get('fails')!!}
</div>
@endif
<!--        <div class="alert  alert-dismissible" style="background: #F3F3F3">
    <i class="fa  fa-circle-info"></i>&nbsp;Please set this command in your cron
    {!! $command !!}
</div>-->

<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{{Lang::get('lang.cron_settings')}}</h3>
    </div>

    <div class="card-body">
       
        <div class="alert  alert-dismissible" style="background: #F3F3F3">
            <i class="fa-solid  fa-circle-info"></i>&nbsp;Please set this command in your cron
            {!! $shared !!}
        </div>

        <div class="alert  alert-dismissible" style="background: #F3F3F3">
            <i class="fa-solid fa-circle-info"></i>&nbsp;{!!Lang::get('lang.crone-url-message')!!}
            <a href="https://support.faveohelpdesk.com/show/how-to-configure-cron-jobs-in-faveo" style="color:black" target="blank">{!!Lang::get('lang.click')!!}</a> {!!Lang::get('lang.check-cron-set')!!}
        </div>
        
        <div class="row">
            
            <div class="col-md-6">
                
                <div class="info-box shadow-sm">
                    <!-- Apply any bg-* class to to the icon to color it -->
                    <span class="info-box-icon bg-info"><i class="fa-solid fa-cloud-download-alt"></i></span>
                    
                    <div class="info-box-content">

                        <div class="row">
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    {!! html()->label(Lang::get('lang.email_fetch'), 'email_fetching') !!}<br>
                                    {!! html()->checkbox('email_fetching', $condition->checkActiveJob()['fetching'], 1)->id('email_fetching') !!}&nbsp;{{Lang::get('lang.fetch_auto-corn')}}
                                </div>

                            </div>
                            <div class="col-md-6" id="fetching">
                                {!! html()->select('fetching-commands', $commands, $condition->getConditionValue('fetching')['condition'])->class('form-control')->id('fetching-command') !!}
                                <div id='fetching-daily-at'>
                                    {!! html()->text('fetching-dailyAt', $condition->getConditionValue('fetching')['at'])->class('form-control') !!}

                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- /.info-box-content -->
            </div><!-- /.info-box -->

        <div class="col-md-6">

            <div class="info-box shadow-sm">
                <!-- Apply any bg-* class to to the icon to color it -->
                <span class="info-box-icon bg-info"><i class="fa-solid fa-cloud-upload-alt"></i></span>
                <div class="info-box-content">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                {!! html()->label(Lang::get('lang.notification-email'), 'notification_cron') !!}<br>
                                {!! html()->checkbox('notification_cron', $condition->checkActiveJob()['notification'], 1)->id('notification_cron') !!}&nbsp;{{Lang::get('lang.cron_notification')}}
                            </div>
                        </div>
                        <div class="col-md-6" id="notification">
                            {!! html()->select('notification-commands', $commands, $condition->getConditionValue('notification')['condition'])->class('form-control')->id('notification-command') !!}
                            <div id='notification-daily-at'>
                                {!! html()->text('notification-dailyAt', $condition->getConditionValue('notification')['at'])->class('form-control') !!}
                            </div>
                        </div>
                    </div>
                </div><!-- /.info-box-content -->
            </div><!-- /.info-box -->
        </div>            
        </div>

        <div class="row">
            <div class="col-md-6">
            <div class="info-box shadow-sm">
                <!-- Apply any bg-* class to to the icon to color it -->
                <span class="info-box-icon bg-info"><i class="fa-solid fa-circle-check"></i></span>
                <div class="info-box-content">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                {!! html()->label(Lang::get('lang.auto_close_workflow'), 'condition') !!}<br>
                                {!! html()->checkbox('condition', $condition->checkActiveJob()['work'], 1)->id('auto_close') !!}
                                       {{Lang::get('lang.enable_workflow')}}
                            </div>
                        </div>
                        <div class="col-md-6" id="workflow">
                            {!! html()->select('work-commands', $commands, $condition->getConditionValue('work')['condition'])->class('form-control')->id('workflow-command') !!}
                            <div id='workflow-daily-at'>
                                {!! html()->text('workflow-dailyAt', $condition->getConditionValue('work')['at'])->class('form-control') !!}
                            </div>
                        </div>
                    </div>
                </div><!-- /.info-box-content -->
            </div><!-- /.info-box -->
        </div>
        </div>

    </div>
    <div class="card-footer">
        {!! html()->submit(Lang::get('lang.submit'))->class('btn btn-primary') !!}
    </div>
</div>
<script>
    $(document).ready(function () {
        var checked = $("#email_fetching").is(':checked');
        check(checked, 'email_fetching');
        $("#email_fetching").on('click', function () {
            checked = $("#email_fetching").is(':checked');
            check(checked);
        });
        var command = $("#fetching-command").val();
        showDailyAt(command);
        $("#fetching-command").on('change', function () {
            command = $("#fetching-command").val();
            showDailyAt(command);
        });
        function check(checked, id) {
            if (checked) {
                $("#fetching").show();
            } else {
                $("#fetching").hide();
            }
        }
        function showDailyAt(command) {
            if (command === 'dailyAt') {
                $("#fetching-daily-at").show();
            } else {
                $("#fetching-daily-at").hide();
            }
        }
    });
    $(document).ready(function () {
        var checked = $("#notification_cron").is(':checked');
        check(checked, 'notification_cron');
        $("#notification_cron").on('click', function () {
            checked = $("#notification_cron").is(':checked');
            check(checked);
        });
        var command = $("#notification-command").val();
        showDailyAt(command);
        $("#notification-command").on('change', function () {
            command = $("#notification-command").val();
            showDailyAt(command);
        });
        function check(checked, id) {
            if (checked) {
                $("#notification").show();
            } else {
                $("#notification").hide();
            }
        }
        function showDailyAt(command) {
            if (command === 'dailyAt') {
                $("#notification-daily-at").show();
            } else {
                $("#notification-daily-at").hide();
            }
        }
    });
    $(document).ready(function () {
        var checked = $("#auto_close").is(':checked');
        check(checked, 'auto_close');
        $("#auto_close").on('click', function () {
            checked = $("#auto_close").is(':checked');
            check(checked);
        });
        var command = $("#workflow-command").val();
        showDailyAt(command);
        $("#workflow-command").on('change', function () {
            command = $("#workflow-command").val();
            showDailyAt(command);
        });
        function check(checked, id) {
            if (checked) {
                $("#workflow").show();
            } else {
                $("#workflow").hide();
            }
        }
        function showDailyAt(command) {
            if (command == 'dailyAt') {
                $("#workflow-daily-at").show();
            } else {
                $("#workflow-daily-at").hide();
            }
        }
    });
//follow up
     $(document).ready(function () {
        var checked = $("#notification_cron1").is(':checked');
        check(checked, 'notification_cron1');
        $("#notification_cron1").on('click', function () {
            checked = $("#notification_cron1").is(':checked');
            check(checked);
        });
        var command = $("#notification-command1").val();
        showDailyAt(command);
        $("#notification-command1").on('change', function () {
            command = $("#notification-command1").val();
            showDailyAt(command);
        });
        function check(checked, id) {
            if (checked) {
                $("#notification1").show();
            } else {
                $("#notification1").hide();
            }
        }
        function showDailyAt(command) {
            if (command === 'dailyAt') {
                $("#notification-daily-at1").show();
            } else {
                $("#notification-daily-at1").hide();
            }
        }
    });
</script>
