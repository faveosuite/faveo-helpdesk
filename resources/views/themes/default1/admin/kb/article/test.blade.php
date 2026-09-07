@extends('themes.default1.article.layout')
@section('content')
    <link href="{{asset('dist/css/bootstrap.css')}}" rel="stylesheet">
    <link href="{{asset('dist/css/dataTables.bootstrap.css')}}" rel="stylesheet">
    <!-- Data tables CDN -->
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.3/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="{{asset('dist/js/jquery-1.11.1.min.js')}}"></script>
    <script type="text/javascript" src="//cdn.datatables.net/1.10.0/js/jquery.dataTables.js"></script>
    <script type="text/javascript" language="javascript" src="//cdn.datatables.net/plug-ins/505bef35b56/integration/bootstrap/3/dataTables.bootstrap.js"></script>
  </head>
<body style="font-family: 'Segoe UI'">
<div class="box box-primary">
<div class="box-header">
    <h2 class="box-title">Articles</h2></div>

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



    <table id="allBlogs"  class="table table-hover">
        <thead>
        <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Action</th>
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

    <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.1/js/bootstrap.min.js"></script>
    <script src="{{asset('dist/js/blogs.js')}}"></script>
    </body>
@stop