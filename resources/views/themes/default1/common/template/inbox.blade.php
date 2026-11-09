@extends('themes.default1.admin.layout.admin')
@section('content')



<div class="box box-primary">

    <div class="box-header">

        <h4>{{Lang::get('lang.templates')}}
        <a href="{{url('templates/create')}}" class="btn btn-primary pull-right   ">{{Lang::get('lang.create')}}</a></h4>
    </div>

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
        <i class="fa-solid fa-ban"></i>
        <b>{{Lang::get('message.alert')}}!</b> {{Lang::get('message.success')}}.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
        {{Session::get('success')}}
    </div>
    @endif
    <!-- fail message -->
    @if(Session::has('fails'))
    <div class="alert alert-danger alert-dismissible">
        <i class="fa-solid fa-ban"></i>
        <b>{{Lang::get('message.alert')}}!</b> {{Lang::get('message.failed')}}.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
        {{Session::get('fails')}}
    </div>
    @endif
    <div id="response"></div>

    <div class="box-body">
        <div class="row">
            
            <div class="col-md-12">
                {{-- This used to call Datatable::table()->render(), the helper from
                     bllim/datatables. That package is long gone — the project moved to
                     yajra/laravel-datatables (config/app.php aliases 'DataTables', and
                     TemplateController already imports it) — so the facade resolved to
                     nothing and the page died with 'Class "Datatable" not found'.
                     Rebuilt with the same server-side pattern every other list in this
                     theme uses, e.g. admin/helpdesk/manage/workflow/index.blade.php. --}}
                <table id="templateTable" class="table table-bordered w-100 d-table">
                    <thead>
                        <tr>
                            <th>{{Lang::get('lang.name')}}</th>
                            <th>{{Lang::get('lang.type')}}</th>
                            <th>{{Lang::get('lang.action')}}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <script type="text/javascript">
                    jQuery(document).ready(function () {
                        jQuery('#templateTable').dataTable({
                            "sPaginationType": "full_numbers",
                            "bProcessing": true,
                            "bServerSide": true,
                            "ajax": {
                                url: "{{url('get-templates')}}"
                            },
                            "columns": [
                                {data: "name"},
                                {data: "type"},
                                {data: "action", orderable: false, searchable: false}
                            ]
                        });
                    });
                </script>
                <script>
                    $('#delete').click(function () {
                        $.ajax({
                            url: "addons-delete",
                            type: "GET",
                            data: $('#check:checked').serialize(),
                            beforeSend: function () {
                                $('#gif').show();
                            },
                            success: function (data) {
                                $('#gif').hide();
                                $('#response').html(data);
                                location.reload();
                            }
                        });
                    });
                </script>
            </div>
        </div>

    </div>

</div>



@stop

@section('icheck')
<script>
    $(function () {


        //Enable check and uncheck all functionality
        $(".checkbox-toggle").click(function () {
            var clicks = $(this).data('clicks');
            if (clicks) {
                //Uncheck all checkboxes
                $(".mailbox-messages input[type='checkbox']").iCheck("uncheck");
                $(".fa", this).removeClass("fa-check-square-o").addClass('fa-square-o');
            } else {
                //Check all checkboxes
                $(".mailbox-messages input[type='checkbox']").iCheck("check");
                $(".fa", this).removeClass("fa-square-o").addClass('fa-check-square-o');
            }
            $(this).data("clicks", !clicks);
        });


    });
</script>
@stop