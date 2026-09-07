@extends('themes.default1.client.layout.client')
@section('content')  
<div id="page" class="hfeed site">
    <article class="hentry error404 text-center">
        <h1 class="error-title"><i class="fa-solid fa-frown-o text-info"></i><span class="visible-print text-danger">0</span></h1>
        <h2 class="entry-title text-muted">{!! Lang::get('lang.error_establishing_connection_to_database') !!}</h2>
    </article><!-- .hentry -->
</div><!-- #page -->

<script>
    function goBack() {
        window.history.back();
    }
</script>
@stop