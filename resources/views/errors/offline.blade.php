@extends('themes.default1.client.layout.client')
@section('content')  
<div id="page" class="hfeed site">
    <article class="hentry error404 text-center">
        <h1 class="error-title"><i class="fa-solid fa-frown-o text-info"></i></h1>
        <h2 class="entry-title text-muted">{!! Lang::get('lang.the_board_is_offline') !!}</h2>
    </article><!-- .hentry -->
</div><!-- #page -->

<script>
    function goBack() {
        window.history.back();
    }
</script>
@stop