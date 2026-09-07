@extends('themes.default1.admin.layout.admin')

@section('Settings')
active
@stop

@section('settings-bar')
active
@stop

@section('Approval')
class="active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{!! Lang::get('lang.settings') !!}</h3>
@stop
<!-- /header -->
<!-- breadcrumbs -->
@section('breadcrumbs')
<ol class="breadcrumb">
</ol>
@stop
<!-- /breadcrumbs -->
<!-- content -->
@section('content')
<!-- open a form -->
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">{{Lang::get('lang.approval_settings')}}</h3>
            </div>
            <!-- check whether success or not -->
            <div class="box-body table-responsive"style="overflow:hidden;">
                @if(Session::has('success'))
                <div class="alert alert-success alert-dismissible">
                    <i class="fa-solid fa-circle-check"></i>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                    {!!Session::get('success')!!}
                </div>
                @endif
                <!-- failure message -->
                @if(Session::has('fails'))
                <div class="alert alert-danger alert-dismissible">
                    <i class="fa-solid fa-ban"></i>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                    <b> {!! Lang::get('lang.alert') !!} ! </b>
                    <li class="error-message-padding">{!!Session::get('fails')!!}</li>
                </div>
                @endif

                <div class="row">
                    <!-- Default System Email:  DROPDOWN value from emails table : Required -->
                    <div class="col-md-12">
                        <div class="col-md-3 no-padding">
                            <div class="mb-3">
                                {!! html()->label(Lang::get('lang.close_all_ticket_for_approval'), 'del_noti') !!}
                            </div>
                        </div>
     
                        <div class="col-md-6">
                            <div class="btn-group" id="toggle_event_editing">
                                <button type="button"  class="btn {{$approval_status->status == '0' ? 'btn-info' : 'btn-secondary'}} locked_active">OFF</button>
                                <button type="button"  class="btn {{$approval_status->status == '1' ? 'btn-info' : 'btn-secondary'}} unlocked_inactive">ON</button>
                            </div>
                            <!-- <div class="alert alert-info" id="switch_status"></div> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $('#toggle_event_editing button').click(function () {

        var settings_approval=1;
         var settings_approval=0;
        if ($(this).hasClass('locked_active') ) {
         

            settings_approval = 0
        } if ( $(this).hasClass('unlocked_inactive')) {
          
            settings_approval = 1;
        }

        /* reverse locking status */
        $('#toggle_event_editing button').eq(0).toggleClass('locked_inactive locked_active btn-secondary btn-info');
        $('#toggle_event_editing button').eq(1).toggleClass('unlocked_inactive unlocked_active btn-info btn-secondary');
        $.ajax({
            type: 'post',
            url: '{{route("settingsUpdateApproval.settings")}}',
            data: {settings_approval: settings_approval},
            success: function (result) {
                // with('success', Lang::get('lang.approval_settings-created-successfully'));
                // alert("Hi, testing");
                alert(result);
                location.reload(); 
            }
        });
    });
</script>
@stop