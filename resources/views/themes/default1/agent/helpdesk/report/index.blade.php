@extends('themes.default1.agent.layout.agent')

@section('sidebar')
<li class="nav-header">{!! Lang::get('lang.Report') !!}</li>
<li class="nav-item">
    <a href="" class="nav-link active">
        <i class="nav-icon fa-solid fa-chart-area"></i> <p>{!! Lang::get('lang.help_topic') !!}</p>
    </a>
</li>
@stop 

@section('Report')
class="nav-link active"
@stop

@section('dashboard-bar')
active
@stop

@section('PageHeader')
<h3>{!! Lang::get('lang.report') !!}</h3>
@stop

@section('dashboard')
class="active"
@stop

@section('content')
<!-- check whether success or not -->
{{-- Success message --}}
@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa-solid  fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('success')}}
</div>
@endif
{{-- failure message --}}
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('fails')}}
</div>
@endif

<div class="card card-light">
    
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.help_topic') !!}</h3>
    </div>
    
    <div class="card-body">
        
        <form id="foo">
            <input type="hidden" name="duration" value="" id="duration">
            <input type="hidden" name="default" value="false" id="default">
         
            <div  class="mb-3">
         
                <div class="row">
         
                    <div class='col-sm-2'>
                        {!! html()->label(Lang::get('lang.help_topic'), 'helptopic') !!}
                        <select name="help_topic" id="help_topic" class="form-control">
                            <?php $helptopics = App\Model\helpdesk\Manage\Help_topic::where('status', '=', '1')->get([ 'id', 'topic']); ?>
                            @foreach($helptopics as $helptopic)
                            <option value="{!! $helptopic->id !!}">{!! $helptopic->topic !!}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class='col-sm-2 mb-3' id="start_date">
                        {!! html()->label(Lang::get('lang.start_date').':', 'date') !!}
                        {!! html()->text('start_date', null)->class('form-control')->id('datepicker4') !!}
                    </div>
                    <?php
                    $start_date = App\Model\helpdesk\Ticket\Tickets::where('id', '=', '1')->first();
                    if ($start_date != null) {
                        $created_date = $start_date->created_at;
                        $created_date = explode(' ', $created_date);
                        $created_date = $created_date[0];
                        $start_date = date("m/d/Y", strtotime($created_date . ' -1 months'));
                    } else {
                        $start_date = date("m/d/Y", strtotime(date("m/d/Y") . ' -1 months'));
                    }
                    ?>
                    <script type="text/javascript">
                        $(function() {
                            var timestring1 = "{!! $start_date !!}";
                            var timestring2 = "{!! date('m/d/Y') !!}";
                            $('#datepicker4').datetimepicker({
                                format: 'DD/MM/YYYY',
                                minDate: moment(timestring1).startOf('day'),
                                maxDate: moment(timestring2).startOf('day')
                            });
                        });
                    </script>

                    <div class='col-sm-2 mb-3' id="end_date">
                        {!! html()->label(Lang::get('lang.end_date').':', 'start_time') !!}
                        {!! html()->text('end_date', null)->class('form-control')->id('datetimepicker3') !!}
                    </div>
                    <script type="text/javascript">
                        $(function() {
                            var timestring1 = "{!! $start_date !!}";
                            var timestring2 = "{!! date('m/d/Y') !!}";
                            $('#datetimepicker3').datetimepicker({
                                format: 'DD/MM/YYYY',
                                minDate: moment(timestring1).startOf('day'),
                                maxDate: moment(timestring2).startOf('day')
                            });
                        });
                    </script>

                    <div class='col-sm-1'>
                        <label>{!! Lang::get('lang.status') !!}</label>
                        <div class="btn-group">
                            <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                {!! Lang::get('lang.select') !!}
                            </button>
                            <div class="dropdown-menu" role="menu">
                                <a href="#" id="stop" class="dropdown-item">
                                    <input type="checkbox" name="open" id="open"> {!! lang::get('lang.created') !!} {!! lang::get('lang.tickets') !!}
                                </a>

                                <a href="#" id="stop" class="dropdown-item">
                                    <input type="checkbox" name="closed" id="closed"> {!! lang::get('lang.closed') !!} {!! lang::get('lang.tickets') !!}
                                </a>

                                <a href="#" id="stop" class="dropdown-item">
                                    <input type="checkbox" name="reopened" id="reopened"> {!! lang::get('lang.reopened') !!} {!! lang::get('lang.tickets') !!}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class='col-sm-1'>
                        {!! html()->label('Filter:', 'filter')->attributes(['style' => 'visibility:hidden;']) !!}<br>
                        <input type="submit" class="btn btn-primary" value="{{trans('lang.submit')}}" id="submit">
                    </div>
                    <br/>
                    <div class="col-md-4">

                        {!! html()->label('Filter:', 'filter')->attributes(['style' => 'visibility:hidden;']) !!}<br>

                        <a class="btn btn-primary" href="#" id="pdf">{!! Lang::get('lang.generate_pdf') !!}</a>

                        <div class="float-end">
                            <div class="btn-group">
                                <button type="button" class="btn btn-secondary" id="click_day">{{trans('lang.day')}}</button>
                                <button type="button" class="btn btn-secondary" id="click_week">{{trans('lang.week')}}</button>
                                <button type="button" class="btn btn-secondary" id="click_month">{{trans('lang.month')}}</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <style>
                        #legend-holder { float: left; width: 32px; height: 16px;}
                    </style>
                    <div class="col-md-2"><span id="legend-holder" style="background-color: #6C96DF;"></span>&nbsp; <span> <span id="total-created-tickets1" ></span> {!! Lang::get('lang.tickets') !!} {!! Lang::get('lang.created') !!}</span></div> 
                    <div class="col-md-2"><span id="legend-holder" style="background-color: #6DC5B2;"></span>&nbsp; <span> <span id="total-reopen-tickets1"></span> {!! Lang::get('lang.tickets') !!} {!! Lang::get('lang.reopen') !!}</span></div> 
                    <div class="col-md-2"><span id="legend-holder" style="background-color: #E3B870;"></span>&nbsp; <span> <span id="total-closed-tickets1"></span> {!! Lang::get('lang.tickets') !!} {!! Lang::get('lang.closed') !!}</span></div> 
                </div>
            </div>
        </form>
        <div class="chart">
            <canvas class="chart-data" id="tickets-graph" width="1000" height="250"></canvas>   
        </div>
    </div>

    <div class="card-footer">
        <div class="row">
            <div class="col-sm-3">
                <div class="description-block border-right">
                    <h3>
                        <span class="description-percentage text-yellow" >
                            <i class="fa-solid fa-file-lines"> </i> 
                            <small class="text-yellow"><i class="fa-solid fa-random"> </i></small> 
                            <span id="total-inprogress-tickets"> </span> 
                        </span>
                    </h3>
                    <span class="">{!! Lang::get('lang.Currnet_In_Progress') !!}</span>
                </div>
                <!-- /.description-block -->
            </div>
            <!-- /.col -->
            <div class="col-sm-3">
                <div class="description-block border-right">
                    <h3>
                        <span class="description-percentage text-blue" ><i class="fa-solid fa-file-lines"> </i> <small class="text-blue"><i class="fa-solid fa-plus"> </i>
                        </small><span id="total-created-tickets"> </span> </span>
                    </h3>
                    <span class="">{!! Lang::get('lang.Total_Created') !!}</span>
                </div>
                <!-- /.description-block -->
            </div>
            <!-- /.col -->
            <div class="col-sm-3">
                <div class="description-block border-right">
                    <h3>
                        <span class="description-percentage text-yellow" ><i class="fa-solid fa-file-lines"></i> <small class="text-yellow"><i class="fa-solid fa-arrows-rotate"> </i>
                        </small> <span id="total-reopen-tickets"> </span> </span>
                    </h3>
                    <span class="">{!! Lang::get('lang.Total_Reopened') !!}</span>
                </div>
                <!-- /.description-block -->
            </div>
            <!-- /.col -->
            <div class="col-sm-3">
                <div class="description-block">
                    <h3>
                        <span class="description-percentage text-green" ><i class="fa-solid fa-file-lines"> </i> <small class="text-green"><i class="fa-solid fa-xmark"> </i></small> <span id="total-closed-tickets"> </span> </span>
                    </h3>
                    <span class="">{!! Lang::get('lang.Total_Closed') !!}</span>
                </div>
                <!-- /.description-block -->
            </div>
            <!-- /.col -->
        </div>  
    </div>
</div><!-- /.box -->

<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.tabular') !!}</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered" id="tabular">
        </table>
    </div>
</div>

<form action="{!! route('help.topic.pdf') !!}" method="POST" id="form_pdf">
    <input type="hidden" name="pdf_form" value="" id="pdf_form">
    <input type="hidden" name="pdf_form_help_topic" value="" id="pdf_form_help_topic">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
    <input type="submit" style="display:none;">
</form>

<div id="refresh"> 
    <script src="{{asset("lb-faveo/plugins/chartjs/Chart.min.js")}}" type="text/javascript"></script>
</div>
<script type="text/javascript">
    var result1a;
//    var help_topic_global;
                        $(document).ready(function() {
                            $.getJSON("help-topic-report", function(result) {
                                var labels = [], open = [], closed = [], reopened = [], open_total = 0, closed_total = 0, reopened_total = 0;
                                //,data2=[],data3=[],data4=[];+
                                var tableRef = document.getElementById('tabular');
                                while (tableRef.rows.length > 0)
                                {
                                    tableRef.deleteRow(0);
                                }
                                var header = tableRef.createTHead();
                                var row = header.insertRow(0);
                                var body = tableRef.createTBody();
                                window.result1a = result;
//                                window.help_topic_global = document.getElementById('help_topic');
                                for (var i = 0; i < result.length; i++) {
                                    var row1 = body.insertRow(0);
//                                    if(i % 4 === 0) {
                                    labels.push(result[i].date);
//                                    } else {
//                                        labels.push("");
//                                    }

                                    open.push(result[i].open);
                                    if (i == 1) {
                                        var cell = row.insertCell(0);
                                        cell.innerHTML = "<b>{!! Lang::get('lang.reopened') !!}</b>";
                                        var cell = row.insertCell(0);
                                        cell.innerHTML = "<b>{!! Lang::get('lang.closed') !!}</b>";
                                        var cell = row.insertCell(0);
                                        cell.innerHTML = "<b>{!! Lang::get('lang.created') !!}</b>";
                                        var cell = row.insertCell(0);
                                        cell.innerHTML = "<b>{!! Lang::get('lang.date') !!}</b>";
                                    }
                                    var cell1 = row1.insertCell(0);
                                    cell1.innerHTML = "<b>" + result[i].reopened + "</b>";
                                    var cell1 = row1.insertCell(0);
                                    cell1.innerHTML = "<b>" + result[i].closed + "</b>";
                                    var cell1 = row1.insertCell(0);
                                    cell1.innerHTML = "<b>" + result[i].open + "</b>";
                                    var cell1 = row1.insertCell(0);
                                    cell1.innerHTML = "<b>" + result[i].date + "</b>";

                                    closed.push(result[i].closed);
                                    reopened.push(result[i].reopened);
                                    // data4.push(result[i].open);
                                    open_total += parseInt(result[i].open);
                                    closed_total += parseInt(result[i].closed);
                                    reopened_total += parseInt(result[i].reopened);
                                    
                                    $inprog = result[i].inprogress;
                                }
                                var buyerData = {
                                    labels: labels,
                                    datasets: [
                                        {
                                            label: "Open Tickets",
                                            fillColor: "rgba(93, 189, 255, 0.05)",
                                            strokeColor: "rgba(2, 69, 195, 0.9)",
                                            pointColor: "rgba(2, 69, 195, 0.9)",
                                            pointStrokeColor: "#c1c7d1",
                                            pointHighlightFill: "#fff",
                                            pointHighlightStroke: "rgba(220,220,220,1)",
                                            data: open
                                        }, {
                                            label: "Closed Tickets",
                                            fillColor: "rgba(255, 206, 96, 0.08)",
                                            strokeColor: "rgba(221, 129, 0, 0.94)",
                                            pointColor: "rgba(221, 129, 0, 0.94)",
                                            pointStrokeColor: "rgba(60,141,188,1)",
                                            pointHighlightFill: "#fff",
                                            pointHighlightStroke: "rgba(60,141,188,1)",
                                            data: closed

                                        }, {
                                            label: "Reopened Tickets",
                                            fillColor: "rgba(104, 255, 220, 0.06)",
                                            strokeColor: "rgba(0, 149, 115, 0.94)",
                                            pointColor: "rgba(0, 149, 115, 0.94)",
                                            pointStrokeColor: "rgba(60,141,188,1)",
                                            pointHighlightFill: "#fff",
                                            pointHighlightStroke: "rgba(60,141,188,1)",
                                            data: reopened
                                        }
                                    ]
                                };
                                $("#total-created-tickets").html(open_total);
                                $("#total-reopen-tickets").html(reopened_total);
                                $("#total-closed-tickets").html(closed_total);
                                $("#total-inprogress-tickets").html($inprog);
                                var myLineChart = new Chart(document.getElementById("tickets-graph").getContext("2d")).Line(buyerData, {
                                    showScale: true,
                                    //Boolean - Whether grid lines are shown across the chart
                                    scaleShowGridLines: true,
                                    //String - Colour of the grid lines
                                    scaleGridLineColor: "rgba(0,0,0,.05)",
                                    //Number - Width of the grid lines
                                    scaleGridLineWidth: 1,
                                    //Boolean - Whether to show horizontal lines (except X axis)
                                    scaleShowHorizontalLines: true,
                                    //Boolean - Whether to show vertical lines (except Y axis)
                                    scaleShowVerticalLines: true,
                                    //Boolean - Whether the line is curved between points
                                    bezierCurve: true,
                                    //Number - Tension of the bezier curve between points
                                    bezierCurveTension: 0.3,
                                    //Boolean - Whether to show a dot for each point
                                    pointDot: true,
                                    //Number - Radius of each point dot in pixels
                                    pointDotRadius: 1,
                                    //Number - Pixel width of point dot stroke
                                    pointDotStrokeWidth: 1,
                                    //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
                                    pointHitDetectionRadius: 10,
                                    //Boolean - Whether to show a stroke for datasets
                                    datasetStroke: true,
                                    //Number - Pixel width of dataset stroke
                                    datasetStrokeWidth: 1,
                                    //Boolean - Whether to fill the dataset with a color
                                    datasetFill: true,
                                    //String - A legend template
                                    //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
                                    maintainAspectRatio: true,
                                    //Boolean - whether to make the chart responsive to window resizing
                                    responsive: true

                                });
//                                document.getElementById("legendDiv").innerHTML = myLineChart.generateLegend();
                            });
                            $('#click_day').click(function() {
                                $('#click_week').removeClass('btn-primary');
                                $('#click_week').addClass('btn-secondary');
                                $('#click_month').removeClass('btn-primary');
                                $('#click_month').addClass('btn-secondary');
                                $('#click_day').removeClass('btn-secondary');
                                $('#click_day').addClass('btn-primary');
                                $("#duration").val("day");
                                document.getElementById("open").checked = false;
                                document.getElementById("closed").checked = false;
                                document.getElementById("reopened").checked = false;
                                $('#foo').submit();
                            });
                            $('#click_week').click(function() {
                                $('#click_day').removeClass('btn-primary');
                                $('#click_day').addClass('btn-secondary');
                                $('#click_month').removeClass('btn-primary');
                                $('#click_month').addClass('btn-secondary');
                                $('#click_week').removeClass('btn-secondary');
                                $('#click_week').addClass('btn-primary');
                                $("#duration").val("week");
                                document.getElementById("open").checked = false;
                                document.getElementById("closed").checked = false;
                                document.getElementById("reopened").checked = false;
                                $('#foo').submit();
                            });
                            $('#click_month').click(function() {
                                $('#click_week').removeClass('btn-primary');
                                $('#click_week').addClass('btn-secondary');
                                $('#click_day').removeClass('btn-primary');
                                $('#click_day').addClass('btn-secondary');
                                $('#click_month').removeClass('btn-secondary');
                                $('#click_month').addClass('btn-primary');
                                $("#duration").val("month");
                                document.getElementById("open").checked = false;
                                document.getElementById("closed").checked = false;
                                document.getElementById("reopened").checked = false;
                                $('#foo').submit();
                            });
                            $('#submit').click(function() {
                                $('#click_week').removeClass('btn-primary');
                                $('#click_week').addClass('btn-secondary');
                                $('#click_day').removeClass('btn-primary');
                                $('#click_day').addClass('btn-secondary');
                                $('#click_month').removeClass('btn-primary');
                                $('#click_month').addClass('btn-secondary');
                                $("#duration").val('');
                            });

                            $('#foo').submit(function(event) {
                                // get the form data
                                // there are many ways to get this data using jQuery (you can use the class or id also)
                                var date1 = $('#datepicker4').val();
                                var date2 = $('#datetimepicker3').val();
                                var help_topic = $('#help_topic').val();
                                var duration = $('#duration').val();
                                if (!duration) {
                                    if (!date1) {
                                        $('#start_date').addClass("has-error");
                                    } else {
                                        $('#start_date').removeClass("has-error");
                                    }
                                    if (!date2) {
                                        $('#end_date').addClass("has-error");
                                    } else {
                                        $('#end_date').removeClass("has-error");
                                    }
                                    if (!date1 || !date2) {
                                        return false;
                                    }

                                    var formData = date1.split("/").join('-');
                                    var dateData = date2.split("/").join('-');
                                } else {
                                    var formData = null;
                                    var dateData = null;
                                    $('#datepicker4').val('');
                                    $('#datetimepicker3').val('');
                                }
                                var data = $('#foo').serialize();
                                // process the form
                                $.ajax({
                                    type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
                                    url: 'help-topic-report/' + dateData + '/' + formData + '/' + help_topic, // the url where we want to POST
                                    dataType: 'json', // what type of data do we expect back from the server
                                    data: data, // our data object

                                    success: function(result2) {
                                        window.result1a = result2;
                                        var tableRef = document.getElementById('tabular');
                                        while (tableRef.rows.length > 0)
                                        {
                                            tableRef.deleteRow(0);
                                        }

                                        var labels = [], open = [], closed = [], reopened = [], open_total = 0, closed_total = 0, reopened_total = 0;
                                        var header = tableRef.createTHead();
                                        var row = header.insertRow(0);
                                        var body = tableRef.createTBody();

                                        for (var i = 0; i < result2.length; i++) {
                                            labels.push(result2[i].date);
                                            var date123 = result2[i].date;

                                            var row1 = body.insertRow(0);

                                            if (result2[i].reopened) {
                                                reopened.push(result2[i].reopened);
                                                reopened_total += parseInt(result2[i].reopened);
                                                if (i == 1) {
                                                    var cell = row.insertCell(0);
                                                    cell.innerHTML = "<b>{!! Lang::get('lang.reopened') !!}</b>";
                                                }
                                                var cell1 = row1.insertCell(0);
                                                cell1.innerHTML = "<b>" + result2[i].reopened + "</b>";
                                            } else {
                                                reopened.push("");
                                                reopened_total += 0;
                                            }

                                            if (result2[i].closed) {
                                                closed.push(result2[i].closed);
                                                closed_total += parseInt(result2[i].closed);
                                                if (i == 1) {
                                                    var cell = row.insertCell(0);
                                                    cell.innerHTML = "<b>{!! Lang::get('lang.closed') !!}</b>";
                                                }
                                                var cell1 = row1.insertCell(0);
                                                cell1.innerHTML = "<b>" + result2[i].closed + "</b>";
                                            } else {
                                                closed.push("");
                                                closed_total += 0;
                                            }

                                            if (result2[i].open) {
                                                open.push(result2[i].open);
                                                open_total += parseInt(result2[i].open);
                                                if (i == 1) {
                                                    var cell = row.insertCell(0);
                                                    cell.innerHTML = "<b>{!! Lang::get('lang.created') !!}</b>";
                                                }
                                                var cell1 = row1.insertCell(0);
                                                cell1.innerHTML = "<b>" + result2[i].open + "</b>";
                                            } else {
                                                open.push("");
                                                open_total += 0;
                                            }

                                            if (i == 1) {
                                                var cell = row.insertCell(0);
                                                cell.innerHTML = "<b>{!! Lang::get('lang.date') !!}</b>";
                                            }

                                            var cell1 = row1.insertCell(0);
                                            cell1.innerHTML = "<b>" + result2[i].date + "</b>";
                                            $inprog = result2[i].inprogress;
                                        }
                                        $("#head123").html("</tr>");

                                        var buyerData = {
                                            labels: labels,
                                            datasets: [
                                                {
                                                    label: "Open Tickets",
                                                    fillColor: "rgba(93, 189, 255, 0.05)",
                                                    strokeColor: "rgba(2, 69, 195, 0.9)",
                                                    pointColor: "rgba(2, 69, 195, 0.9)",
                                                    pointStrokeColor: "#c1c7d1",
                                                    pointHighlightFill: "#fff",
                                                    pointHighlightStroke: "rgba(220,220,220,1)",
                                                    data: open
                                                }
                                                , {
                                                    label: "Closed Tickets",
                                                    fillColor: "rgba(255, 206, 96, 0.08)",
                                                    strokeColor: "rgba(221, 129, 0, 0.94)",
                                                    pointColor: "rgba(221, 129, 0, 0.94)",
                                                    pointStrokeColor: "rgba(60,141,188,1)",
                                                    pointHighlightFill: "#fff",
                                                    pointHighlightStroke: "rgba(60,141,188,1)",
                                                    data: closed
                                                }
                                                , {
                                                    label: "Reopened Tickets",
                                                    fillColor: "rgba(104, 255, 220, 0.06)",
                                                    strokeColor: "rgba(0, 149, 115, 0.94)",
                                                    pointColor: "rgba(0, 149, 115, 0.94)",
                                                    pointStrokeColor: "rgba(60,141,188,1)",
                                                    pointHighlightFill: "#fff",
                                                    pointHighlightStroke: "rgba(60,141,188,1)",
                                                    data: reopened
                                                }
                                            ]
                                        };
                                        $("#total-created-tickets").html(open_total);
                                        $("#total-reopen-tickets").html(reopened_total);
                                        $("#total-closed-tickets").html(closed_total);
                                        $("#total-inprogress-tickets").html($inprog);
                                        
                                        var myLineChart = new Chart(document.getElementById("tickets-graph").getContext("2d")).Line(buyerData, {
                                            showScale: true,
                                            //Boolean - Whether grid lines are shown across the chart
                                            scaleShowGridLines: true,
                                            //String - Colour of the grid lines
                                            scaleGridLineColor: "rgba(0,0,0,.05)",
                                            //Number - Width of the grid lines
                                            scaleGridLineWidth: 1,
                                            //Boolean - Whether to show horizontal lines (except X axis)
                                            scaleShowHorizontalLines: false,
                                            //Boolean - Whether to show vertical lines (except Y axis)
                                            scaleShowVerticalLines: false,
                                            //Boolean - Whether the line is curved between points
                                            bezierCurve: true,
                                            //Number - Tension of the bezier curve between points
                                            bezierCurveTension: 0.3,
                                            //Boolean - Whether to show a dot for each point
                                            pointDot: true,
                                            //Number - Radius of each point dot in pixels
                                            pointDotRadius: 1,
                                            //Number - Pixel width of point dot stroke
                                            pointDotStrokeWidth: 1,
                                            //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
                                            pointHitDetectionRadius: 10,
                                            //Boolean - Whether to show a stroke for datasets
                                            datasetStroke: true,
                                            //Number - Pixel width of dataset stroke
                                            datasetStrokeWidth: 1,
                                            //Boolean - Whether to fill the dataset with a color
                                            datasetFill: true,
                                            //String - A legend template
                                            //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
                                            maintainAspectRatio: true,
                                            //Boolean - whether to make the chart responsive to window resizing
                                            responsive: true

                                        });
                                        myLineChart.options.responsive = false;
                                        $("#tickets-graph").remove();
                                        $(".chart").html("<canvas id='tickets-graph' width='1000' height='250'></canvas>");
                                        var myLineChart1 = new Chart(document.getElementById("tickets-graph").getContext("2d")).Line(buyerData, {
                                            showScale: true,
                                            //Boolean - Whether grid lines are shown across the chart
                                            scaleShowGridLines: true,
                                            //String - Colour of the grid lines
                                            scaleGridLineColor: "rgba(0,0,0,.05)",
                                            //Number - Width of the grid lines
                                            scaleGridLineWidth: 1,
                                            //Boolean - Whether to show horizontal lines (except X axis)
                                            scaleShowHorizontalLines: true,
                                            //Boolean - Whether to show vertical lines (except Y axis)
                                            scaleShowVerticalLines: false,
                                            //Boolean - Whether the line is curved between points
                                            bezierCurve: true,
                                            //Number - Tension of the bezier curve between points
                                            bezierCurveTension: 0.3,
                                            //Boolean - Whether to show a dot for each point
                                            pointDot: true,
                                            //Number - Radius of each point dot in pixels
                                            pointDotRadius: 1,
                                            //Number - Pixel width of point dot stroke
                                            pointDotStrokeWidth: 1,
                                            //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
                                            pointHitDetectionRadius: 10,
                                            //Boolean - Whether to show a stroke for datasets
                                            datasetStroke: true,
                                            //Number - Pixel width of dataset stroke
                                            datasetStrokeWidth: 1,
                                            //Boolean - Whether to fill the dataset with a color
                                            datasetFill: true,
                                            //String - A legend template
                                            //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
                                            maintainAspectRatio: true,
                                            //Boolean - whether to make the chart responsive to window resizing
                                            responsive: true
                                        });
//                                        document.getElementById("legendDiv").innerHTML = myLineChart1.generateLegend();
                                    }
                                });
                                // using the done promise callback
                                // stop the form from submitting the normal way and refreshing the page
                                event.preventDefault();
                            });
                            $("#pdf").on('click', function(){
                                document.getElementById("pdf_form").value = JSON.stringify(result1a);
                                document.getElementById("pdf_form_help_topic").value = $('#help_topic :selected').val();
                                document.getElementById("form_pdf").submit();
//                                $("#form_pdf").submit(function(){
//                                    alert('saasdas');
//                                });
                            });
                        });
</script>
<script>
  $(function () {
//    $("#tabular").DataTable();
    $('#tabular').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": false,
      "ordering": true,
      "info": true,
      "autoWidth": false
    });
  });
</script>
<script type="text/javascript">
    jQuery(document).ready(function() {
        // Close a ticket
        $('#close').on('click', function(e) {
            $.ajax({
                type: "GET",
                url: "agen",
                beforeSend: function() {

                },
                success: function(response) {

                }
            })
            return false;
        });
    });
</script>
@stop