@extends('themes.default1.admin.layout.admin')

@section('Tickets')
class="nav-link active"
@stop

@section('ticket-menu-parent')
class="nav-item menu-open"
@stop

@section('ticket-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('ratings')
class="nav-link active"
@stop

@section('PageHeader')
<h1>{!! Lang::get('lang.settings') !!}</h1>
@stop

@section('header')
@stop

@section('content')
@if(Session::has('success'))
<div class="alert alert-success alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <span>{{Session::get('success')}}</span>                
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.current_ratings') !!}</h3>
        <div class="card-tools">
            <a class="btn btn-default btn-tool" href="{{ route('rating.create') }}" title="{!! Lang::get('lang.create') !!}">
                <i class="fas fa-plus"></i> {!! Lang::get('lang.create') !!}
            </a>
        </div><!-- /.box-header -->
    </div>
    <div class="card-body">
        <table id="example1" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>{!! Lang::get('lang.name') !!}</th>
                    <th>{!! Lang::get('lang.display_order') !!}</th>
                    <th>{!! Lang::get('lang.rating_area') !!}</th>
                    <th>{!! Lang::get('lang.action') !!}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ratings as $rating)
                <tr>
                    <td>{!! $rating->name !!}</td>
                    <td>{!! $rating->display_order !!}</td>
                    <td>{!! $rating->rating_area !!}</td>
                    <td>
                        <a href="{{ route('rating.edit', [$rating->id]) }}" class="btn btn-primary btn-sm">
                            {{ trans('lang.edit_ratings') }}
                        </a>
                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#delete{{$rating->id}}">{{trans('lang.delete')}}</button>
                        <div class="modal fade" id="delete{{$rating->id}}">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">{!! Lang::get('lang.delete') !!}</h4>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <span>{!! Lang::get('lang.are_you_sure_you_want_to_delete') !!} ?</span>
                                    </div>
                                    <div class="modal-footer justify-content-between">
                                        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">{!! Lang::get('lang.close') !!}</button>
                                        <a href="{{ route('ratings.delete', [$rating->id]) }}" id="delete" class="btn btn-danger btn-sm">
                                            {{ Lang::get('lang.delete') }}
                                        </a>
                                    </div>
                                </div> 
                            </div>
                        </div> 
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div><!-- /.box-body -->
</div>
@stop
@section('footer')

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

<script type="text/javascript">
<?php if (count($errors) > 0) { ?>
        $('#create').modal('show');
<?php } ?>
</script>  

@stop