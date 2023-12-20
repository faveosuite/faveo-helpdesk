@extends('themes.default1.client.layout.client')

<?php $category = App\Model\kb\Category::where('id', '=', $id)->first(); ?>
@section('title')
{!! $category->name !!} -
@stop

@section('kb')
class = "nav-item active"
@stop
@section('breadcrumb')
    {{--    <div class="site-hero clearfix">--}}
    <ol class="breadcrumb float-sm-right ">
        <style>
            .words {
                margin-right: 10px; /* Adjust the value to increase or decrease the gap between list items */
            }
        </style>
        <li class="breadcrumb-item"> <i class="fa-solid fa-home"> </i> {!! Lang::get('lang.you_are_here') !!} : &nbsp;</li>
        <li><a class="words" href="{!! URL::route('home') !!}">{!! Lang::get('lang.knowledge_base') !!}</a></li>
        <li class="words">></li>
        <li><a class="words" href="{!! URL::route('category-list') !!}">{!! Lang::get('lang.category') !!}</a></li>
        <li class="words">></li>
        <li><a  class="words"href="{!! URL::route('category-list') !!}">{!! Lang::get('lang.category_list') !!}</a></li>

    </ol>
    {{--    </div>--}}
@stop

@section('content')
<div id="content" class="site-content col-md-9">

    <header class="archive-header">
        <h1 class="archive-title">{!! $category->name !!}</h1>
    </header>

    <div class="archive-list archive-article">
        <?php foreach ($article_id as $id) { ?>
        <?php
        $article = App\Model\kb\Article::where('id', $id);
        if (!Auth::user() || Auth::user()->role == 'user') {
            $article = $article->where('status', 1);
            $article = $article->where('type', 1);
        }
        $article = $article->orderBy('publish_time', 'desc');
        $article = $article->get();
        ?>
        @forelse($article as $arti)

             <article class="hentry">

                <header class="entry-header">

                    <i class="fa-solid fa-list-alt fa-2x fa-fw float-start text-muted"></i>

                    <h2 class="entry-title h4">

                        <a href="{{url('show/'.$arti->slug)}}" onclick="toggle_visibility('foo');">{{$arti->name}}</a>
                    </h2>
                </header>

                <?php $str = $arti->description; ?>
                <?php $excerpt = App\Http\Controllers\Client\kb\UserController::getExcerpt($str, $startPos = 0, $maxLength = 400); ?>

                <blockquote class="blockquote archive-description" id="block" style="margin-bottom: 10px; margin-top: 10px;">

                    <?php $content = trim(preg_replace("/<img[^>]+\>/i", "", $excerpt), " \t.") ?>
                    <p>{!! strip_tags($content) !!}</p>

                    <p><a href="{{url('show/'.$arti->slug)}}">{!! Lang::get('lang.read_more') !!}</a></p>
                </blockquote>

                <footer class="entry-footer">

                    <div class="entry-meta text-muted">

                        <span style="margin-right:0px;"><i class="fa-regular fa-clock fa-fw"></i>

                            <span>{{$arti->created_at->format('l, d-m-Y')}}</span>
                        </span>
                    </div>
                </footer>
            </article>
            @empty
            <p>No articles available</p>
        @endforelse
        <?php
        }
        //echo $all->render();
        ?>
    </div>
</div>
<script type="text/javascript">
<!--
    function toggle_visibility(id) {
        var e = document.getElementById(id);
        if (e.style.display == 'block')
            e.style.display = 'none';
        else
            e.style.display = 'block';
    }
//-->
</script>

@stop

@section('category')

    <div id="sidebar" class="site-sidebar col-md-3">

        <div class="col-sm-12">
            <div class="widget-area">

                <section id="section-categories" class="section">

                    <h2 class="section-title h4 clearfix">

                        <b>   <i class="line" style="border-color: rgb(0, 154, 186);"></i>{!! Lang::get('lang.categories') !!}</b>
                        <small class="float-end"><i class="fa-regular fa-hdd fa-fw"></i></small>
                    </h2>

                    <ul class="nav nav-pills nav-stacked nav-categories">

                        @foreach($categorys as $category)
                        <?php
                        $num = \App\Model\kb\Relationship::where('category_id','=', $category->id)->get();
                        $article_id = $num->pluck('article_id');
                        $numcount = count($article_id);
                        ?>

                        <li class="d-flex justify-content-between align-items-center">

                            <a href="{{url('category-list/'.$category->slug)}}" class="list-group-item list-group-item-action" style="padding: 5px;">

                                <span class="badge bg-pill float-end" style="margin-top: 2px;">{{$numcount}}</span>

                                {{$category->name}}
                            </a>
                        </li>
                         @endforeach
                    </ul>
                </section>
            </div>
        </div>
    </div>
@stop