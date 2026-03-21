@extends('themes.default1.agent.layout.agent')

@section('Users')
class="active"
@stop

@section('user-bar')
active
@stop

@section('user')
class="active"
@stop

<!-- header -->
@section('PageHeader')
<h3>Export User</h3>
@stop
<!-- /header -->
<!-- content -->
@section('content')
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />
{!! html()->form('POST', url('user-export'))->open() !!}
<div class="box box-primary">
    @if (count($errors) > 0)
    <div class="alert alert-danger">
        <strong>Whoops!</strong> There were some problems with your input.<br><br>
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(Session::has('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
        {{Session::get('success')}}
    </div>
    @endif
    <!-- fail message -->
    @if(Session::has('fails'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
        {{Session::get('fails')}}
    </div>
    @endif
    <div class="box-header with-border">
        <h3 class="box-title">
            Users
        </h3>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-6">
                <div id="reportrange" class="pull-right" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                    <i class="glyphicon glyphicon-calendar fa-solid fa-calendar"></i>&nbsp;
                    <span id="date"></span> <b class="caret"></b>
                </div>
            </div>
            <div class="col-md-6">
                {!! html()->hidden('date', null)->id('hidden') !!}
                {!! html()->submit('Export')->class('btn btn-success')->id('submit') !!}
            </div>
        </div>
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