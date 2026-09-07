@extends('themes.default1.agent.layout.agent')

@section('Dashboard')
class="active"
@stop

@section('dashboard-bar')
active
@stop

@section('dashboard')
class="active"
@stop

@section('content')

      <div class="box box-info">

                <div class="box-header with-border">
                    <h3 class="box-title">{!! Lang::get('lang.line_chart') !!}</h3>
                    <div class="box-tools pull-right">
                      <button class="btn btn-box-tool" data-widget="collapse"><i class="fa-solid fa-minus"></i></button>
                      <button class="btn btn-box-tool" data-widget="remove"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="chart" >
                            <div id="legendDiv"></div>
                            <canvas class="chart-data" id="tickets-graph" width="1000" height="400"></canvas>
                    </div>
                </div><!-- /.box-body -->
            </div><!-- /.box -->
            <hr/>


   <script src="{{asset("lb-faveo/plugins/chartjs/Chart.min.js")}}" type="text/javascript"></script>
         <script type="text/javascript">
    $(function(){
    $.getJSON("reportdata", function (result) {


    var labels=[], open=[], closed=[], reopened=[];
    //,data2=[],data3=[],data4=[];
    for (var i = 0; i < result.length; i++) {


        // $var12 = result[i].day;

        // labels.push($var12);
        labels.push(result[i].date);
        open.push(result[i].open);
        closed.push(result[i].closed);
        reopened.push(result[i].reopened);
      // data4.push(result[i].open);
    }

    var buyerData = {
      labels : labels,
      datasets : [
        {
          label : "Total Tickets" ,
          fillColor : "rgba(240, 127, 110, 0.3)",
          strokeColor : "#f56954",
          pointColor : "#A62121",
          pointStrokeColor : "#E60073",
          pointHighlightFill : "#FF4DC3",
          pointHighlightStroke : "rgba(151,187,205,1)",
          data : open
        }
        ,{
          label : "Open Tickets" ,
          fillColor : "rgba(255, 102, 204, 0.4)",
          strokeColor : "#f56954",
          pointColor : "#FF66CC",
          pointStrokeColor : "#fff",
          pointHighlightFill : "#FF4DC3",
          pointHighlightStroke : "rgba(151,187,205,1)",
          data : closed

        }
        ,{
          label : "Closed Tickets",
          fillColor : "rgba(151,187,205,0.2)",
          strokeColor : "rgba(151,187,205,1)",
          pointColor : "rgba(151,187,205,1)",
          pointStrokeColor : "#0000CC",
          pointHighlightFill : "#0000E6",
          pointHighlightStroke : "rgba(151,187,205,1)",
          data : reopened
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

     var myLineChart = new Chart(document.getElementById("tickets-graph").getContext("2d")).Line(buyerData, {
          showScale: true,
          //Boolean - Whether grid lines are shown across the chart
          scaleShowGridLines: false,
          //String - Colour of the grid lines
          scaleGridLineColor: "rgba(0,0,0,.05)",
          //Number - Width of the grid lines
          scaleGridLineWidth: 1,
          //Boolean - Whether to show horizontal lines (except X axis)
          scaleShowHorizontalLines: true,
          //Boolean - Whether to show vertical lines (except Y axis)
          scaleShowVerticalLines: true,
          //Boolean - Whether the line is curved between points
          bezierCurve: false,
          //Number - Tension of the bezier curve between points
          bezierCurveTension: 0.3,
          //Boolean - Whether to show a dot for each point
          pointDot: true,
          //Number - Radius of each point dot in pixels
          pointDotRadius: 4,
          //Number - Pixel width of point dot stroke
          pointDotStrokeWidth: 1,
          //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
          pointHitDetectionRadius: 20,
          //Boolean - Whether to show a stroke for datasets
          datasetStroke: true,
          //Number - Pixel width of dataset stroke
          datasetStrokeWidth: 1,
          //Boolean - Whether to fill the dataset with a color
          datasetFill: true,
          //String - A legend template
          //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
          maintainAspectRatio: false,
          //Boolean - whether to make the chart responsive to window resizing
          responsive: true,
      legendTemplate : '<ul style="list-style-type: square;">'
                  +'<% for (var i=0; i<datasets.length; i++) { %>'
                    +'<li style="color: <%=datasets[i].pointColor%>;">'
                    +'<span style=\"background-color:<%=datasets[i].pointColor%>\"></span>'
                    +'<% if (datasets[i].label) { %><%= datasets[i].label %><% } %>'
                  +'</li>'
                +'<% } %>'
              +'</ul>'
    });

    document.getElementById("legendDiv").innerHTML = myLineChart.generateLegend();
  });

});

</script>

@stop
