<?php
$load_old_state = false;
if (Session::has('current_page')) {
    if (Session::get('current_page') == \Request::fullUrl()) {
        $load_old_state = true;
    } else {
        Session::put('current_page', \Request::fullUrl());
    }
} else {
    Session::put('current_page', \Request::fullUrl());
}

$segments = \Request::segments();
$segment = "";
foreach ($segments as $seg) {
    $segment.="/".$seg;
}
$inputs = json_encode(Request::all());
$path = public_path();
?>
<script type="text/javascript">
    jQuery(document).ready(function () {
        var oTable = myFunction();
        function myFunction()
        {
            return jQuery('#chumper').dataTable({
                "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                       "<'row'<'col-sm-12'tr>>" +
                       "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 d-flex justify-content-end'p>>",
                "pagingType": "full_numbers",
                "processing": true,
                "serverSide": true,
                "stateSave": true,
                "stateDuration": -1,
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
                        "sFirst":    "&laquo;",   // «
                        "sPrevious": "&lsaquo;",  // ‹
                        "sNext":     "&rsaquo;",  // ›
                        "sLast":     "&raquo;"    // »
                    },

                    "oAria": {
                        "sSortAscending":  "{!! Lang::get('datatables.oAria.sSortAscending') !!}",
                        "sSortDescending": "{!! Lang::get('datatables.oAria.sortDescending') !!}"
                    },
                },
                "stateLoadParams": function (settings, data) {
                    if ('{{$load_old_state}}') {
                        return true;
                    } else {
                        return false;
                    }
                },
                "fnDrawCallback": function( oSettings ) {
                    $("#chumper").css({"opacity": "1"});
                    $('#blur-bg').css({"opacity": "0.7", "z-index": "99999"});
                    $('.loader1').css('display', 'none');
                    t_id = [];
                    showAssign(t_id);
                },
                "fnPreDrawCallback": function(oSettings, json) {
                    $('.loader1').css('display', 'block');
                    $("#chumper").css({"opacity":"0.3"});
                    $('#blur-bg').css({"opacity": "0.7", "z-index": "99999"});

                },

                "headerCallback": function headerCallback(thead, data, start, end, display) {
                    $(thead).find('th').first().css('text-align','center');
                    $(thead).find('th').first().css('padding','3px 5px 3px 5px');
                    if ($(thead).find('th').first().find('i').hasClass('fa-check-square-o')) {
                        $(thead).find('th').first().find('i').removeClass('fa-check-square-o');
                        $(thead).find('th').first().find('i').addClass('fa-square-o');
                    }
                },
                "lengthMenu": [[10, 25, 50, 100, 500], [10, 25, 50, 100, 500]],
                "ajax": {
                    url: "{{url('get-filtered-tickets')}}",
                    data: function (d) {
                        d.options = "{{$inputs}}";
                    }
                },
                "aaSorting": [[5, "desc"]],
                "columnDefs": [
                    {"defaultContent": "-",
                        "targets": "_all"},
                    { "orderable": false, "searchable": false, "targets": 0},
                    { "searchable": false, "targets": 5 },
                    { "visible": true, "targets": 5 },
                    {
                        "aTargets": [0],
                        "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                            var str = sData;
                            var start = str.indexOf('#');
                            var color = hexToRgbA(str.substr(start, 7));
                                $(nTd).css("border-left", "5px solid "+color);
                        }
                    }
                ],
                "columns":[
                    {data: "id"},
                    {data: "title"},
                    {data: "ticket_number"},
                    {data: "c_uname"},
                    {data: "a_uname"},
                    {data: "updated_at"},
                ],
                "fnCreatedRow": function (nRow, aData, iDataIndex) {
                    var str = aData['id'];
                    var length = aData['ticket_number'].indexOf('*') - aData['ticket_number'].indexOf('$');
                    var p = aData['ticket_number'].substr(aData['ticket_number'].indexOf('$')+1, length-1);
                    $("td", nRow).attr('title', "{!! Lang::get('lang.ticket-has-x-priority', ['priority' => '"+p+"']) !!}");
                    if (str.search("#000") == -1) {
                        $("td", nRow).css({"background-color": "#F3F3F3", "font-weight": "600", "border-bottom": "solid 0.5px #ddd", "border-right": "solid 0.5px #F3F3F3"});
                        $("td", nRow).mouseenter(function () {
                            $("td", nRow).css({"background-color": "#DEDFE0", "font-weight": "600", "border-bottom": "solid 0.5px #ddd", "border-right": "solid 0.5px #DEDFE0"});
                        });
                        $("td", nRow).mouseleave(function () {
                            $("td", nRow).css({"background-color": "#F3F3F3", "font-weight": "600", "border-bottom": "solid 0.5px #ddd", "border-right": "solid 0.5px #F3F3F3"});
                        });
                    } else {
                        $("td", nRow).css({"background-color": "white", "border-bottom": "solid 0.5px #ddd"});
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

        function hexToRgbA(hex){
            var c;
            if(/^#([A-Fa-f0-9]{3}){1,2}$/.test(hex)){
                c= hex.substring(1).split('');
                if(c.length== 3){
                    c= [c[0], c[0], c[1], c[1], c[2], c[2]];
                }
                c= '0x'+c.join('');
                return 'rgba('+[(c>>16)&255, (c>>8)&255, c&255].join(',')+', 0.67)';
            }
            throw new Error('Bad Hex');
        }
    });
</script>