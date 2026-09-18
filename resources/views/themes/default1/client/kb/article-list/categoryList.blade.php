@extends('themes.default1.client.layout.client')

@section('title')
Category List -
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
            <li><a href="{!! URL::route('category-list') !!}">{!! Lang::get('lang.category') !!}</a></li>
        </ol>
{{--    </div>--}}
@stop
@section('content')

<div id="content" class="site-content col-md-12">

    <div class="row">

        @foreach($categorys as $category)
        <div class="col-md-6">
            {{-- get the article_id where category_id == current category --}}
            <?php
            $all = App\Model\kb\Relationship::where('category_id', $category->id)->get();
            /* from whole attribute pick the article_id */
            $article_id = $all->pluck('article_id');
            ?>

            <section class="box-categories">

                <h1 class="section-title h4 clearfix">


                    <i class="fa-regular fa-folder-open fa-fw text-muted"></i>

                    <small class="float-end">

                        <a href="{{url('category-list/'.$category->slug)}}"><i class="fa-regular fa-hdd fa-fw"></i>({{count($all)}})</a>
                    </small>

                     <a href="{{url('category-list/'.$category->slug)}}">{{$category->name}}</a>
                </h1>

                <ul class="fa-ul" style="min-height: 150px;">
                    <?php foreach ($article_id as $id) {
                    ?>
                    <?php
                    $article = App\Model\kb\Article::where('id', $id);
                    if (!Auth::user() || Auth::user()->role == 'user') {
                        $article = $article->where('status', 1);
                        $article = $article->where('type', 1);
                    }
                    $article = $article->orderBy('publish_time', 'desc');
                    $article = $article->get();
                    //dd($article);
                    ?>
                    @forelse($article as $arti)
                    <li>

                        <h3 class="h5" style="text-align:left">

                            <i class="fa-li fa-solid fa-list-alt fa-fw text-muted"></i>

                            <a href="{{url('show/'.$arti->slug)}}">{{$arti->name}}</a>

                        </h3>
                    </li>
                    @empty
{{--                    <p>{!! Lang::get('lang.no_article') !!}</p>--}}
                    @endforelse
                <?php } ?>
                </ul>
                <p class="more-link text-center"><a href="{{url('category-list/'.$category->slug)}}" class="btn btn-custom btn-sm" style="background-color: #009aba; hov: #00c0ef; color: #fff ">{!! Lang::get('lang.view_all') !!}</a></p>
            </section>
        </div>
        @endforeach
    </div>

</div>
<!-- end of page content -->
@stop

