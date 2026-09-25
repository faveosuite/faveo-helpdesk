@extends('themes.default1.agent.layout.agent')

@section('Users')
class="nav-link active"
@stop

@section('user-bar')
class="nav-link active"
@stop

@section('user')
class="active"
@stop

@section('user-directory')
class="nav-link active"
@stop

<!-- header -->
@section('PageHeader')
<h3>Export User</h3>
@stop
<!-- /header -->
<!-- content -->
@section('content')
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />
{{-- Response is a file download, so the page stays: release the submit guard after a few seconds. --}}
{!! html()->form('POST', url('user-export'))->attribute('data-submit-guard-timeout', '4000')->open() !!}
@if (count($errors) > 0)
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <b>{!! Lang::get('lang.alert') !!} !</b>
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa-solid fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('success')}}
</div>
@endif
<!-- fail message -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i><button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <b>{!! Lang::get('lang.alert') !!} !</b>
    {{Session::get('fails')}}
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{{Lang::get('lang.user')}}</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div id="reportrange" class="form-control" style="cursor: pointer;">
                    <i class="fa-solid fa-calendar"></i>&nbsp;
                    <span id="date"></span>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer">
        {!! html()->hidden('date', null)->id('hidden') !!}
        {!! html()->submit('Export')->class('btn btn-primary')->id('submit') !!}
    </div>
</div>
{!! html()->closeModelForm() !!}
@stop
@section('FooterInclude')
<script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
<script type="text/javascript">
$(function () {

    var start = moment().subtract(29, 'days');
    var end = moment();

    function cb(start, end) {
        $('#reportrange span').html(start.format('YYYY-MM-DD') + ' : ' + end.format('YYYY-MM-DD'));
    }

    $('#reportrange').daterangepicker({
        startDate: start,
        endDate: end,
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cb);

    cb(start, end);

});
$("#submit").on('click', function () {
    var date = $("#date").text();
    $("#hidden").val(date);
});


</script>
@stop