@extends('themes.default1.client.layout.client')

@section('title')
Knowledge Base -
@stop
@section('breadcrumb')
    {{--<div class="site-hero clearfix">--}}
    <ol class="breadcrumb float-sm-right ">
        <li class="breadcrumb-item"> <i class="fa-solid fa-home"> </i> {!! Lang::get('lang.you_are_here') !!} : &nbsp;</li>
        <li><a href="{!! URL::route('home') !!}">{!! Lang::get('lang.knowledge_base') !!}</a></li>
    </ol>

@stop
@section('kb')
class = "nav-item active"
@stop
@section('content')

@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa-solid  fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('success')}}
</div>
@endif
<!-- failure message -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('fails')}}
</div>
@endif

<div id="content" class="site-content col-md-9">
    <div class="row">
        <?php $categories = App\Model\kb\Category::all();
        ?>
        @foreach($categorys as $category)
        {{-- get the article_id where category_id == current category --}}
        <?php
        $all = App\Model\kb\Relationship::all();
        /* from whole attribute pick the article_id */
        $page = App\Model\kb\Relationship::where('category_id', '=', $category->id)->paginate('3');
        /* from whole attribute pick the article_id */
        $article_id = $page->pluck('article_id');
        $count = count($article_id);
        ?>
        <div class="col-md-6">

            <section class="box-categories">

                <h1 class="section-title h4 clearfix">

                    <i class="line" style="border-color: rgb(0, 154, 186);"></i>

                    <i class="fa-regular fa-folder-open fa-fw text-muted"></i>

                    <small class="float-end">

                        <a href="{{url('category-list/'.$category->slug)}}"><i class="fa-regular fa-hdd fa-fw"></i>({{count($all)}})</a>
                    </small>

                    <a href="{{url('category-list/'.$category->slug)}}">{{$category->name}}</a>
                </h1>

                <ul class="fa-ul" style="min-height: 150px;">
                    <?php
                    foreach ($article_id as $id) {
                        //$format = App\Model\helpdesk\Settings\System::where('id','1')->first()->date_time_format;
                        $tz = App\Model\helpdesk\Settings\System::where('id', '1')->first()->time_zone;
                        $tz = \App\Model\helpdesk\Utility\Timezones::where('id', $tz)->first()->name;
                        date_default_timezone_set($tz);
                        $date = \Carbon\Carbon::now()->toDateTimeString();
                        //dd($date);

                        $article = App\Model\kb\Article::where('id', '=', $id);
                        if (Auth::check()) {
                            if (\Auth::user()->role == 'user') {
                                $article = $article->where('status', '1');
                            }
                        } else {
                            $article = $article->where('status', '1');
                        }
                        $article = $article->where('type', '1');
                        $article = $article->orderBy('publish_time','desc')->get();
                        ?>
                        @forelse($article as $arti)
                        <li>

                            <h3 class="h5" style="text-align:left">

                                <i class="fa-li fa-solid fa-list-alt fa-fw text-muted"></i>

                                <a href="{{url('show/'.$arti->slug)}}">{{$arti->name}}</a>

                            </h3>
                        </li>
                        @empty
{{--                        <p>{!! Lang::get('lang.no_article') !!}</p>--}}
                        @endforelse
                    <?php } ?>
                </ul>
                <p class="more-link text-center"><a href="{{url('category-list/'.$category->slug)}}" class="btn btn-custom btn-sm" style="background-color: #009aba; hov: #00c0ef; color: #fff ">{!! Lang::get('lang.view_all') !!}</a></p>
            </section>
        </div>
        @endforeach
    </div>

    <section class="section">

        <div class="banner-wrapper banner-horizontal clearfix" style="background: none;">

            <h4 style="font-size: 15px;" class="banner-title h3">{!! Lang::get('lang.need_more_support') !!}?</h4>

            <div class="banner-content">

                <p>{!! Lang::get('lang.if_you_did_not_find_an_answer_please_raise_a_ticket_describing_the_issue') !!}.</p>
            </div>

            <p><a  style="background-color: #009aba; hov: #00c0ef; color: #fff " href="{!! URL::route('form') !!}" class="btn btn-custom">{!! Lang::get('lang.submit_a_ticket') !!}</a></p>
        </div>
    </section>
</div>
@stop

@section('category')

<div id="sidebar" class="site-sidebar col-md-3">

    <div class="col-sm-12">

        <div class="widget-area">

            <section id="section-categories" class="categories">

                <h2 class="section-title h4 clearfix">

                    <b>   <i class="line"></i>{!! Lang::get('lang.categories') !!}</b>

                    <small class="float-end"><i class="fa-regular fa-hdd fa-fw"></i></small>
                </h2>

                <ul class="nav nav-pills nav-stacked nav-categories">

                    @foreach($categorys as $category)
                    <?php
                        $num = \App\Model\kb\Relationship::where('category_id', '=', $category->id)->get();
                        $article_id = $num->pluck('article_id');
                        $numcount = count($article_id);
                    ?>

                    <li class="d-flex justify-content-between align-items-center">

                        <a  href="{{url('category-list/'.$category->slug)}}" class="list-group-item list-group-item-action" style="padding: 5px;">

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
