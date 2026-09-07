@extends('themes.default1.layouts.login')
@section('body')


<div class="row">
    <div class="col-12">  
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
            <i class="fa-solid fa-ban"></i><button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
            <b>{!! Lang::get('lang.alert') !!} !</b>            
            {{Session::get('fails')}}
        </div>
        @endif
        <h3>Database Update Required</h3>
        <p>{{ucfirst(Config::get('app.name'))}} has been updated! Before we send you on your own way,
            we have to update your database to the newest version.</p>
        <p>The update process may take a little while, so please be patient.</p>
        <p><a href="{{$url}}" class="btn btn-secondary">Update {{ucfirst(Config::get('app.name'))}} Database</a></p>
    </div>
</div>

@stop