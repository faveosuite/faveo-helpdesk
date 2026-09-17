@extends('themes.default1.client.layout.client')

@section('title')
    Pages List -
@stop
@section('breadcrumb')
    {{--<div class="site-hero clearfix">--}}
    <ol class="breadcrumb float-sm-right ">
        <style>
            .words {
                margin-right: 10px; /* Adjust the value to increase or decrease the gap between list items */
            }
        </style>
        <li class="breadcrumb-item"> <i class="fa-solid fa-home"> </i> {!! Lang::get('lang.you_are_here') !!} : &nbsp;</li>
        <li><a class="words" href="{!! URL::route('home') !!}">{!! Lang::get('lang.knowledge_base') !!}</a></li>
        <li class="words">></li>
        <li><a class="words" href="{{url('pages/'.$page->slug)}}">{!! Lang::get('lang.pages') !!}</a></li>
        <li class="words" style="margin-right: 10px">></li>
        <li>  {{$page->name}}</li>

    </ol>
    </div>
@stop
@section('pages')
class = "nav-item  active"
@stop

@section('content')

    <div id="content" class="site-content col-sm-12">

        <article class="hentry">

            <header class="entry-header">

                <h1 class="entry-title">{{$page->name}}</h1>
            </header>

            <div class="entry-content clearfix">

                {!! $page->description !!}
            </div>
        </article>
    </div>
@stop