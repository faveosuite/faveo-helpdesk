@extends('themes.default1.admin.layout.kb')
@section('pages')
    active
@stop
@section('all-pages')
    class="active"
@stop
@section('content')

    <link href="{{asset('lb-faveo/dist/css/dataTables.bootstrap.css')}}" rel="stylesheet">
    
<div class="box box-primary">
<div class="box-header">
    <h2 class="box-title">{{Lang::get('lang.pages')}}</h2></div>

<div class="box-body table-responsive no-padding">

<!-- check whether success or not -->

@if(Session::has('success'))
    <div class="alert alert-success alert-dismissible">
        <i class="fa  fa-circle-check"></i>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
        {{Session::get('success')}}
    </div>
    @endif
    <!-- failure message -->
    @if(Session::has('fails'))
    <div class="alert alert-danger alert-dismissible">
        <i class="fa-solid fa-ban"></i>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
        {{Session::get('fails')}}
    </div>
    @endif



    <section class="app-content">
<div class="row">
<div class="col-12">

<div id="example1_wrapper" class="dataTables_wrapper d-flex gap-2 dt-bootstrap table table-hover overflow-hidden">
<div class="row">
<div class="col-sm-12">
    <table id="allBlogs"  class="table table-bordered table-striped dataTable" role="grid" aria-describedby="example1_info">
        <thead>
        <tr>
            <th>{{Lang::get('lang.name')}}</th>
            <th>{{Lang::get('lang.created')}}</th>
            <th>{{Lang::get('lang.action')}}</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            </tr>
        </tbody>
    </table>
    </div>
    </div>
    </div>
    </div>
    </div>
    </section>
    </div>
    </div>

    <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.1/js/bootstrap.min.js"></script>
    <script src="{{asset('lb-faveo/dist/js/page.js')}}"></script>
@stop