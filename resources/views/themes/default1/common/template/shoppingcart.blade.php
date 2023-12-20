@extends('themes.default1.layouts.front.master')
@section('title')
pricing
@stop
@section('page-header')
Pricing
@stop
@section('breadcrumb')
<li><a href="{{url('home')}}">Home</a></li>
<li class="active">Pricing</li>
@stop
@section('main-class') 
main
@stop


@section('content')


<div class="row">
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
    
    <div class="col-md-12 col-md-offset-3">
        <div class="pricing-table princig-table-flat">
        {!! html_entity_decode($template) !!}
        </div>
    </div>

</div>

@stop

