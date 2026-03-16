@extends('themes.default1.admin.layout.admin')

@section('Emails')
class="nav-link active"
@stop

@section('email-menu-parent')
class="nav-item menu-open"
@stop

@section('email-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('template')
class="nav-link active"
@stop

@section('PageHeader')
<h1>{!! Lang::get('lang.templates') !!}</h1>
@stop

@section('content')
 @if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa-solid fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('success')}}
</div>
@endif
@if(Session::has('failed'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <p>{{Session::get('failed')}}</p>                
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.edit_templates') !!}</h3>
    </div><!-- /.box-header -->
    <div class="card-body">
       
        <table id="example1" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>{!! Lang::get('lang.type') !!}</th>
                    <th>{!! Lang::get('lang.description') !!}</th>
                    <th>{!! Lang::get('lang.action') !!}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($templates as $template)
                <tr>
                    <?php $type = App\Model\Common\TemplateType::where('id','=',$template->type)->first(); ?>
                    <td>{!! $type->name !!}</td>
                    <td>{!! $template->name !!}</td>
                    <td>
                        <a href="{{ route('templates.edit', [$template->id]) }}" class="btn btn-success btn-sm">
                            {{ Lang::get('lang.edit_templates') }}
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div><!-- /.box-body -->
</div>

<!-- status script -->
<script type="text/javascript">
$(function() {
    $("#example1").dataTable();
    $('#example2').dataTable({
        "bPaginate": true,
        "bLengthChange": false,
        "bFilter": false,
        "bSort": true,
        "bInfo": true,
        "bAutoWidth": false
    });
});
</script>
@stop