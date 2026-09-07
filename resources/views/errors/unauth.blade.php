@extends('themes.default1.client.layout.client')
@section('content')  
<div id="page" class="hfeed site">
    <article class="hentry error404 text-center">
        <h1 class="error-title"><i class="fa-solid fa-warning text-red"></i><span class="visible-print text-danger"></span></h1>
        <h2 class="entry-title text-muted">{!! Lang::get('lang.unauthorized_access') !!}</h2>
    </article><!-- .hentry -->
</div><!-- #page -->
@stop