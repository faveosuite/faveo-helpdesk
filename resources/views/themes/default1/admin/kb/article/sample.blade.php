@extends('themes.default1.layouts.master')
@section('head')
<!-- DATA TABLES -->
<link href="{{asset("lb-faveo/plugins/datatables/dataTables.bootstrap.css")}}" rel="stylesheet" type="text/css" />
@stop
@section('header')

<h1>Home</h1>
<ol class="breadcrumb">
    <li><a href="#"><i class="fa-solid fa-dashboard"></i> Home</a></li>
    <li class="active">Dashboard</li>
</ol>
@stop

@section('content')
<section class="app-content">
    <div class="row">
        <div class="col-12">
            @if(Session::has('success'))
            <div class="alert alert-success alert-dismissible">
                <i class="fa-solid fa-ban"></i>
                <b>Alert!</b> Failed.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                <p>{{Session::get('success')}}</p>
            </div>
            @endif

            <!-- -->
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Song List</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Slug</th>
                                <th>Action</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach($songs as $song)
                            <tr>
                                <td>{!! $song->id !!}</td>
                                <td>{!! $song->title !!}</td>
                                <td>{!! $song->slug !!}</td>
                                <td>
                                    <a href="{{ route('songs.show', [$song->slug]) }}" id="show" class="btn btn-primary btn-sm">
                                        Show
                                    </a>

                                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#{{$song->slug}}">Modal Edit</button>
                                    <div class="modal fade" id="{{$song->slug}}">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                {!! html()->modelForm($song, 'PATCH', route('songs.update', [$song->slug]))->open() !!}
                                                <div class="modal-header">
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    <h5 class="modal-title">Edit Song</h4>
                                                </div>
                                                <div class="modal-body">
                                                    @include('themes.default1.admin.songs.form')
                                                </div>
                                                <div class="modal-footer">
                                                    <div class="mb-3">
                                                        {!! html()->submit('Update Song')->class('btn btn-primary') !!}
                                                    </div>
                                                    <button type="button" class="btn btn-secondary pull-left" data-bs-dismiss="modal">Close</button>
                                                </div>
                                                {!! html()->closeModelForm() !!}
                                            </div><!-- /.modal-content -->
                                        </div><!-- /.modal-dialog -->
                                    </div><!-- /.modal -->

                                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#{{$song->slug}}delete">Modal Delete</button>
                                    <div class="modal fade" id="{{$song->slug}}delete">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    <h5 class="modal-title">Delete</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to Delete ?</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary pull-left" data-bs-dismiss="modal">Close</button>
                                                    <a href="{{ route('songs.delete', [$song->slug]) }}" id="delete" class="btn btn-danger btn-sm">
                                                        Delete
                                                    </a>
                                                </div>
                                            </div><!-- /.modal-content -->
                                        </div><!-- /.modal-dialog -->
                                    </div><!-- /.modal -->
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Slug</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                    </table>
                </div><!-- /.box-body -->
            </div>
            <!-- -->
        </div>
    </div>



</section>
@stop
@section('footer')
<script src="{{asset("lb-faveo/plugins/datatables/jquery.dataTables.js")}}" type="text/javascript"></script>
<script src="{{asset("lb-faveo/plugins/datatables/dataTables.bootstrap.js")}}" type="text/javascript"></script>
<!-- page script -->
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