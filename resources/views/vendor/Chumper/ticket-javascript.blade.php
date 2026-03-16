<?php
$segments = \Request::segments();
$segment = "";
foreach($segments as $seg){
    $segment.="/".$seg;
}
?>
<script type="text/javascript">
    jQuery(document).ready(function () {
        var clicked = 1;
        var create = false;
        var last = true;
        var sort = [[6, "desc"]];
        oTable = myFunction();
        $("select[name=label]").change(function () {
            $("#chumper").dataTable().fnDestroy();
            myFunction();
        });
        $("select[name=tag]").change(function () {
            $("#chumper").dataTable().fnDestroy();
            myFunction();
        });

        function myFunction()
        {
            return jQuery('#chumper').dataTable({
                "sDom": "<'row'<'col-sm-6'l><'col-sm-6'f>r>"+
                        "t"+
                        "<'row'<'col-sm-6'i><'col-sm-6'p>>",
                "sPaginationType": "full_numbers",
                "bProcessing": true,
                "lengthMenu": [[10, 25, 50, 100, 500], [10, 25, 50, 100, 500]],
                "oLanguage": {
                    "sEmptyTable": "{!! Lang::get('datatables.sEmptyTable') !!}",
                    "sInfo": "{!! Lang::get('datatables.sInfo') !!}",
                    "sInfoEmpty": "{!! Lang::get('datatables.sInfoEmpty') !!}",
                    "sInfoFiltered": "{!! Lang::get('datatables.sInfoFiltered') !!}",
                    "sInfoPostFix": "{!! Lang::get('datatables.sInfoPostFix') !!}",
                    "sInfoThousands": "{!! Lang::get('datatables.sInfoThousands') !!}",
                    "sLengthMenu": "{!! Lang::get('datatables.sLengthMenu') !!}",
                    "sLoadingRecords": "{!! Lang::get('datatables.sLoadingRecords') !!}",
                    "sProcessing": "{!! Lang::get('datatables.sProcessing') !!}",
                    "sSearch": "{!! Lang::get('datatables.sSearch') !!}",
                    "sZeroRecords": "{!! Lang::get('datatables.sZeroRecords') !!}",
                    "oPaginate": {
                        "sFirst": "{!! Lang::get('datatables.oPaginate.sFirst') !!}",
                        "sLast": "{!! Lang::get('datatables.oPaginate.sLast') !!}",
                        "sNext": "{!! Lang::get('datatables.oPaginate.sNext') !!}",
                        "sPrevious": "{!! Lang::get('datatables.oPaginate.sPrevious') !!}"
                    },
                    "oAria": {
                        "sSortAscending": "{!! Lang::get('datatables.oAria.sSortAscending') !!}",
                        "sSortDescending": "{!! Lang::get('datatables.oAria.sortDescending') !!}"
                    },
                },
                "ajax": {
                    url: "{{url('filter')}}",
                    data: function (d) {
                        d.labels = $('select[name=label]').val();
                        d.tags = $('select[name=tag]').val();
                        d.segment = "{{$segment}}";
                    }
                },
                "aaSorting": sort,
                "columnDefs": [
                    {"defaultContent": "-",
                        "targets": "_all"},
                    { "visible": last, "targets": 6 },
                    {"visible": create, "targets":7},
                ],
                "columns":[
                    {data: "id"},
                    {data: "title"},
                    {data: "ticket_number"},
                    {data: "priority"},
                    {data: "user_name"},
                    {data: "assign_user_name"},
                    {data: "updated_at"},
                ],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {
                    var str = aData['ticket_number'];
                    if (str.search("#000") == -1) {
                        $("td", nRow).css({"background-color": "#F3F3F3", "font-weight": "600", "border-bottom": "solid 0.5px #ddd", "border-right": "solid 0.5px #F3F3F3"});
                        $("td", nRow).mouseenter(function () {
                            $("td", nRow).css({"background-color": "#DEDFE0", "font-weight": "600", "border-bottom": "solid 0.5px #ddd", "border-right": "solid 0.5px #DEDFE0"});
                        });
                        $("td", nRow).mouseleave(function () {
                            $("td", nRow).css({"background-color": "#F3F3F3", "font-weight": "600", "border-bottom": "solid 0.5px #ddd", "border-right": "solid 0.5px #F3F3F3"});
                        });
                    } else {
                        $("td", nRow).css({"background-color": "white", "border-bottom": "solid 0.5px #ddd", "border-right": "solid 0.5px white"});
                        $("td", nRow).mouseenter(function () {
                            $("td", nRow).css({"background-color": "#DEDFE0", "border-bottom": "solid 0.5px #ddd", "border-right": "solid 0.5px #DEDFE0"});
                        });
                        $("td", nRow).mouseleave(function () {
                            $("td", nRow).css({"background-color": "white", "border-bottom": "solid 0.5px #ddd", "border-right": "solid 0.5px white"});
                        });
                    }
                }
            });
        }
        $('a.toggle-vis').on( 'click', function (e)
        {
            clicked = clicked+1;
            if((clicked%2)== 0){
                last = false;
                create = true;
                sort = [[7, "desc"]]
                $('a.toggle-vis').html('<i class="fa-solid fa-clock-o" style="color:green;"> </i>{!!Lang::get("lang.last_activity")!!}');
            } else {
                last = true;
                create = false;
                sort = [[6, "desc"]]
                $('a.toggle-vis').html('<i class="fa-solid fa-plus-square-o" style="color:green;"> </i>{!!Lang::get("lang.created-at")!!}');

            }
            $("#chumper").dataTable().fnDestroy();
            myFunction();
        });
    });

</script>