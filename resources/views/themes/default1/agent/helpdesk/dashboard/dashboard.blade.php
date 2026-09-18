@extends('themes.default1.agent.layout.agent')

@section('Dashboard')
class="nav-link active"
@stop

@section('dashboard-bar')
class="nav-link active"
@stop

@section('PageHeader')
<h3>{!! Lang::get('lang.dashboard_reports') !!}</h3>
@stop

@section('dashboard')
class="nav-item d-none d-sm-inline-block active"
@stop

@section('content')
<!-- check whether success or not -->
{{-- Success message --}}
@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
	<i class="fa-solid fa-circle-check"></i>
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

<div class="row">
	
	<div class="col-md-3" style="max-width: 20%;">
		
		<a href="{!! route('inbox.ticket') !!}" class="text-dark" style="cursor: pointer;">

			<div class="info-box shadow-sm">
			
				<span class="info-box-icon bg-info"><i class="fa-regular fa-envelope"></i></span>

			  	<div class="info-box-content">
				
					<span class="info-box-text">{!! Lang::get('lang.inbox') !!}</span>
					
					<span class="info-box-number"><?php echo $tickets->count() ?></span>
			  	</div>
			</div>
		</a>
	</div>

	<div class="col-md-3" style="max-width: 20%;">
		
		<a href="{!! route('unassigned') !!}" class="text-dark" style="cursor: pointer;">

			<div class="info-box shadow-sm">
			
				<span class="info-box-icon bg-warning"><i class="fa-solid fa-user-xmark text-white"></i></span>

			  	<div class="info-box-content">
				
					<span class="info-box-text">{!! Lang::get('lang.unassigned') !!}</span>
					
					<span class="info-box-number">{{$unassigned->count() }} </span>
			  	</div>
			</div>
		</a>
	</div>

	<div class="col-md-3" style="max-width: 20%;">
		
		<a href="{!! route('overdue.ticket') !!}" class="text-dark" style="cursor: pointer;">

			<div class="info-box shadow-sm">
			
				<span class="info-box-icon bg-danger"><i class="fa-solid fa-calendar-xmark"></i></span>

			  	<div class="info-box-content">
				
					<span class="info-box-text">{!! Lang::get('lang.overdue') !!}</span>
					
					<span class="info-box-number">{{ $overdues->count() }}</span>
			  	</div>
			</div>
		</a>
	</div>

	<div class="col-md-3" style="max-width: 20%;">
		
		<a href="{!! route('myticket.ticket') !!}" class="text-dark" style="cursor: pointer;">

			<div class="info-box shadow-sm">
			
				<span class="info-box-icon bg-warning"><i class="fa-solid fa-user text-white"></i></span>

			  	<div class="info-box-content">
				
					<span class="info-box-text">{!! Lang::get('lang.my_tickets') !!}</span>
					
					<span class="info-box-number">{{ $myticket->count() }}</span>
			  	</div>
			</div>
		</a>
	</div>

	<div class="col-md-3" style="max-width: 20%;">
		
		<?php
	  		if (Auth::user()->role == 'admin') {
				$todaytickets = App\Model\helpdesk\Ticket\Tickets::where('status', '=', 1)->whereDate('tickets.duedate','=', \Carbon\Carbon::now()->format('Y-m-d'))->count();
			}else {
				$dept =  App\Model\helpdesk\Agent\Department::where('id', '=', Auth::user()->primary_dpt)->first();
	 			$todaytickets = App\Model\helpdesk\Ticket\Tickets::where('status', '=', 1)->whereDate('tickets.duedate','=', \Carbon\Carbon::now()->format('Y-m-d'))->where('dept_id', '=', $dept->id)->count();
				}
	  	?>
		<a href="{!! route('ticket.duetoday') !!}" class="text-dark" style="cursor: pointer;">

			<div class="info-box shadow-sm">
			
				<span class="info-box-icon bg-danger"><i class="fa-solid fa-eye"></i></span>

			  	<div class="info-box-content">
				
					<span class="info-box-text">{!! Lang::get('lang.duetoday') !!}</span>
					
					<span class="info-box-number">{{ $todaytickets }}</span>
			  	</div>
			</div>
		</a>
	</div>
</div>

<div class="card">

	<div class="card-header">
		
		<h3 class="card-title">{!! Lang::get('lang.report') !!}</h3>
	</div>

	<div class="card-body">
		
		<form id="foo">
			
			<div class="mb-3">
			
				<div class="row">
					
					<div class='col-sm-2'>
						{!! html()->label(trans('lang.start_date'), 'date') !!}
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
						$(function () {
							var timestring1 = "{!! $start_date !!}";
							var timestring2 = "{!! date('m/d/Y') !!}";
							$('#datepicker4').datetimepicker({
								format: 'DD/MM/YYYY',
								minDate: moment(timestring1).startOf('day'),
								maxDate: moment(timestring2).startOf('day')
							});
							//                $('#datepicker').datepicker()
						});
					</script>

					<div class='col-sm-2'>

						{!! html()->label(trans('lang.end_date'), 'start_time') !!}
						{!! html()->text('end_date', null)->class('form-control')->id('datetimepicker3') !!}
					</div>

					<script type="text/javascript">
						$(function () {
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
						{!! html()->label('Filter:', 'filter')->attributes(['style' => 'visibility:hidden;']) !!}<br>
						<button type="submit" class="btn btn-primary">{{trans('lang.submit')}}</button>
					</div>
				</div>

				<div class="row mt-2">
				
					<style>
						#legend-holder { border: 1px solid #ccc; float: left; width: 25px; height: 25px; margin: 1px; }
					</style>

					<div class="col-md-4">
						<span id="legend-holder" style="background-color: #6C96DF;"></span>&nbsp; 
						<span class="lead"> <span id="total-created-tickets" ></span> {!! Lang::get('lang.tickets') !!} {!! Lang::get('lang.created') !!}</span>
					</div> 
					
					<div class="col-md-4">
						<span id="legend-holder" style="background-color: #6DC5B2;"></span>&nbsp; 
						<span class="lead"> <span id="total-reopen-tickets" class="lead"></span> {!! Lang::get('lang.tickets') !!} {!! Lang::get('lang.reopen') !!}</span>
					</div> 

					<div class="col-md-4">
						<span id="legend-holder" style="background-color: #E3B870;"></span>&nbsp; 
						<span class="lead"> <span id="total-closed-tickets" class="lead"></span> {!! Lang::get('lang.tickets') !!} {!! Lang::get('lang.closed') !!}</span>
					</div> 
				</div>
			</div>
		</form>
		
		<div class="chart">
			<canvas class="chart-data" id="tickets-graph" width="1000" height="250"></canvas>   
		</div>
	</div>
</div>

<div class="card">

	<div class="card-header">

		<h3 class="card-title">{!! Lang::get('lang.statistics') !!}</h3>
	</div>

	<div class="card-body">
		<table class="table table-hover table-bordered">
			<?php
//            dd($department);
			$flattened = $department->flatMap(function ($values) {
				return $values->keyBy('status');
			});
			$statuses = $flattened->keys();
			?>
			<tr>
				<th>{{trans('lang.department')}}</th>
				@forelse($statuses as $status)
				 <th>{{trans('lang.'.strtolower($status))}}</th>
				@empty 
				
				@endforelse

			</tr>
			@foreach($department as $name=>$dept)
			<tr>
				<td>{!! $name !!}</td>
				@forelse($statuses as $status)
				@if($dept->get($status))
				 <th>{!! $dept->get($status)->count !!}</th>
				@else 
					<th></th>
				 @endif
				@empty 
				
				@endforelse
			</tr>
			@endforeach 
		</table>
	</div>
</div>

<div id="refresh"> 
	<script src="{{asset("lb-faveo/plugins/chartjs/Chart.min.js")}}" type="text/javascript"></script>
</div>

<script type="text/javascript">
						$(document).ready(function () {
							$.getJSON("agen", function (result) {
								var labels = [], open = [], closed = [], reopened = [], open_total = 0, closed_total = 0, reopened_total = 0;
								//,data2=[],data3=[],data4=[];
								for (var i = 0; i < result.length; i++) {
									// $var12 = result[i].day;
									// labels.push($var12);
									labels.push(result[i].date);
									open.push(result[i].open);
									closed.push(result[i].closed);
									reopened.push(result[i].reopened);
									// data4.push(result[i].open);
									open_total += parseInt(result[i].open);
									closed_total += parseInt(result[i].closed);
									reopened_total += parseInt(result[i].reopened);
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
								document.getElementById("legendDiv").innerHTML = myLineChart.generateLegend();
							});
							$('#click me').click(function () {
								$('#foo').submit();
							});
							$('#foo').submit(function (event) {
								// get the form data
								// there are many ways to get this data using jQuery (you can use the class or id also)
								var date1 = $('#datepicker4').val();
								var date2 = $('#datetimepicker3').val();
								var formData = date1.split("/").join('-');
								var dateData = date2.split("/").join('-');
								//$('#foo').serialize();
								// process the form
								$.ajax({
									type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
									url: 'chart-range/' + dateData + '/' + formData, // the url where we want to POST
									data: formData, // our data object
									dataType: 'json', // what type of data do we expect back from the server

									success: function (result2) {
										//  $.getJSON("agen", function (result) {
										var labels = [], open = [], closed = [], reopened = [], open_total = 0, closed_total = 0, reopened_total = 0;
										//,data2=[],data3=[],data4=[];
										for (var i = 0; i < result2.length; i++) {
											// $var12 = result[i].day;
											// labels.push($var12);
											labels.push(result2[i].date);
											open.push(result2[i].open);
											closed.push(result2[i].closed);
											reopened.push(result2[i].reopened);
											// data4.push(result[i].open);
											open_total += parseInt(result2[i].open);
											closed_total += parseInt(result2[i].closed);
											reopened_total += parseInt(result2[i].reopened);
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
												// ,{
												//       label : "Reopened Tickets",
												//         fillColor : "rgba(102,255,51,0.2)",
												//       strokeColor : "rgba(151,187,205,1)",
												//        pointColor : "rgba(46,184,0,1)",
												//         pointStrokeColor : "#fff",
												//         pointHighlightFill : "#fff",
												//         pointHighlightStroke : "rgba(151,187,205,1)",
												//        data : data3
												//     }
											]
										};
										$("#total-created-tickets").html(open_total);
										$("#total-reopen-tickets").html(reopened_total);
										$("#total-closed-tickets").html(closed_total);
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
										document.getElementById("legendDiv").innerHTML = myLineChart1.generateLegend();
									}
								});
								// using the done promise callback
								// stop the form from submitting the normal way and refreshing the page
								event.preventDefault();
							});
						});
</script>
<script type="text/javascript">
	jQuery(document).ready(function () {
		// Close a ticket
		$('#close').on('click', function (e) {
			$.ajax({
				type: "GET",
				url: "agen",
				beforeSend: function () {

				},
				success: function (response) {

				}
			})
			return false;
		});
	});
</script>
@stop