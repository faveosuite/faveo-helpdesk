@extends('themes.default1.admin.layout.admin')

@section('Settings')
active
@stop

@section('settings-bar')
active
@stop

@section('clean')
class="active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{{Lang::get('lang.delete_dummy_data')}}</h3>
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
<div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">{!! Lang::get('lang.clean-data-box-title')!!}</h3>
        </div><!-- /.box-header -->
        <div class="box-body">
          	<ul>
				<li>{!! Lang::get('lang.clean-dummy-ticket-conversation') !!}</li>
				<li>{!! Lang::get('lang.clean-dummy-notification') !!}</li>
				<li>{!! Lang::get('lang.clean-dummy-users') !!}</li>
				<li>{!! Lang::get('lang.clean-other-data') !!}</li>
				<li>{!! Lang::get('lang.clean-user-created-data') !!}</li>
			</ul>
        </div><!-- /.box-body -->
        <div class="box-footer">
        <input type="checkbox" id="clean-confirm">&nbsp;{!! Lang::get('lang.clean-confirm') !!}</input>
        </div>
        <div class="box-footer">
          <input type="button" class="btn btn-sm btn-primary" disabled="disabled" data-bs-toggle="modal" data-bs-target="#loadingpopup" value="{!! Lang::get('lang.clean-data-btn') !!}" id="clean-btn"></input>
        </div>
</div>
<div class="modal fade" id="loadingpopup" style="padding:200px;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div id="head">
                    <button type="button" class="btn-close" id="close" data-bs-dismiss="modal" aria-label="Close" style="display:none;"><span aria-hidden="true">×</span></button>
                    <div class="col-md-5"></div><div class="col-md-2"><img src="{{asset("lb-faveo/media/images/gifloader.gif")}}" ></div><div class="col-md-5"></div>
                    <br/>
                    <br/>
                    <br/>
                    <center><h3 style="color:#80DE02;">{!! Lang::get('lang.cleaning-database') !!}</h3></center>
                    <br/>
                    <center><h4>{!! Lang::get('lang.cleaning-in-progress') !!}</h4></center>
                    <center><h4>{!! Lang::get('lang.do-not-refresh') !!}</h4></center>
                    <br/>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@section('FooterInclude')
<script type="text/javascript">
    $('#clean-confirm').on('ifChecked', function () {
        document.getElementById("clean-btn").disabled = false;
    });
    $('#clean-confirm').on('ifUnchecked', function () {
        document.getElementById("clean-btn").disabled = true;
    });

    $('#clean-btn').on('click', function(){
        $.ajax({
            type: "POST",
            url: "{!! route('post-clean-database') !!}",
            dataType: "json",
            data: {
                "_token": "{{ csrf_token() }}",
            },
            headers: {
                'X-CSRF-Token': $('meta[name="_token"]').attr('content')
            },
            beforeSend: function () {
                $('.loader').css('display','block');
            },
            success: function (json) {
                $('.loader').css('display','none');
                var link = "{{route('setting')}}";
                window.location = link;
            },
            error: function (json) {
                
            }
        });
    })
</script>
@stop