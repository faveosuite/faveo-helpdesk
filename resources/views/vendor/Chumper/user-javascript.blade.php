<?php
$segments = \Request::segments();
$segment = "";
foreach($segments as $seg){
    $segment.="/".$seg;
}
?>
<script type="text/javascript">
    jQuery(document).ready(function () {
        var show = 'all';
        var searchTerm = '';
        oTable = myFunction(show, searchTerm);
        
        $("select[name=type_of_profile]").change(function () {
            //alert($('select[name=type_of_profile]').val());
            $("#chumper").DataTable().destroy();
            myFunction();
        });

        function myFunction(show)
        {
            return jQuery('#chumper').dataTable({
                "sDom": "<'row'<'col-sm-6'l><'col-sm-6'>r>"+
                        "t"+
                        "<'row'<'col-sm-6'i><'col-sm-6'p>>",
                "sPaginationType": "full_numbers",
                "bProcessing": true,
                "bServerSide": true,
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
                    url: "{{url('user-list')}}",
                    data: function (d) {
                        d.profiletype = show;
                        d.searchTerm = searchTerm;
                    }
                },
                "columns":[
                    {data: "user_name"},
                    {data: "email"},
                    {data: "mobile"},
                    {data: "active"},
                    {data: "updated_at"},
                    {data: "role"},
                    {data: "Actions"},
                ],
                
            });
        }

        $('.all').on('click', function(){
            show = 'all';
            classname = '.'+show;
            filterTable(show);
            toggleActiveClass(classname);
        });

        $('.active-users').on('click', function(){
            show = 'active-users';
            classname = '.'+show;
            filterTable(show);
            toggleActiveClass(classname);
            
        });

        $('.inactive').on('click', function(){
            show = 'inactive';
            classname = '.'+show;
            filterTable(show);
            toggleActiveClass(classname);
        });

        $('.agents').on('click', function(){
            show = 'agents';
            classname = '.'+show;
            filterTable(show);
            toggleActiveClass(classname);
        });

        $('.users').on('click', function(){
            show = 'users';
            classname = '.'+show;
            filterTable(show);
            toggleActiveClass(classname);
        });

        $('.banned').on('click', function(){
            show = 'banned';
            classname = '.'+show;
            filterTable(show);
            toggleActiveClass(classname);
        });

        $('.deleted').on('click', function(){
            show = 'deleted';
            classname = '.'+show;
            filterTable(show);
            toggleActiveClass(classname);
        });

        document.getElementById('search-text').onkeypress = function(e){
            if (!e) e = window.event;
            var keyCode = e.keyCode || e.which;
            if (keyCode == '13'){ 
                searchTerm = $('input[name=search]').val();
                $("#chumper").DataTable().destroy();
                myFunction(show, searchTerm);
            }
        }

        function filterTable(show) {
            $("#chumper").DataTable().destroy();
            myFunction(show, searchTerm);
        }

        function toggleActiveClass(classname) {
            $('.active').removeClass('active');
            $(classname).parent('li').addClass('active');
        }
    });
</script>