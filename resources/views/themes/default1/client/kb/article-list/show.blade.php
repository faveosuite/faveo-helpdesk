@extends('themes.default1.client.layout.client')

@section('title')
    @if(isset($category->name))
        {!! $category->name !!} -
    @endif
@stop

@section('kb')
    class = "nav-item active"
@stop

@section('breadcrumb')

<?php
//dd($arti);
$all = App\Model\kb\Relationship::where('article_id','=', $arti->id)->get();
//dd($all);
/* from whole attribute pick the article_id */
$category_id = $all->pluck('category_id')->toArray();
?>
@section('breadcrumb')
    {{--<div class="site-hero clearfix">--}}
    <ol class="breadcrumb float-sm-right ">
        <style>
            .words {
                margin-right: 10px; /* Adjust the value to increase or decrease the gap between list items */
            }
        </style>
        <li class="breadcrumb-item"> <i class="fa-solid fa-home"> </i> {!! Lang::get('lang.you_are_here') !!} : &nbsp;</li>
        <?php $category = App\Model\kb\Category::where('id', $category_id)->first(); ?>
        <li><a class="words" href="{!! URL::route('home') !!}">{!! Lang::get('lang.knowledge_base') !!}</a></li>
        <li class="words">></li>

        <li><a  class="words" href="{{url('article-list')}}">{!! Lang::get('lang.article_list') !!}</a></li>
        <li class="words">></li>
        <li><a class="words" href="{{url('category-list/'.$category->slug)}}">{{$category->name}}</a></li>
        <li class="words"> > </li>
        <li class="active">{{$arti->name}}</li> </ol>
@stop
@section('title')
    {!! $arti->name !!} -
@stop	
@section('content')

<div id="content" class="site-content col-md-9">
 
    <article class="hentry">

        <header class="entry-header">

            <h1 class="entry-title">{{$arti->name}}</h1>

            <div class="entry-meta text-muted">

                <span class="date">
                    <i class="fa-regular fa-clock fa-fw"></i> 
                    <time datetime="2013-09-19T20:01:58+00:00">{{$arti->created_at->format('l, d-m-Y')}}</time>
                </span>

                <span class="category">
                    <i class="fa-solid fa-folder-open fa-fw"></i> 
                    <a href="{{url('category-list/'.$category->slug)}}">{{$category->name}}</a>
                </span>
            </div><!-- .entry-meta -->
        </header><!-- .entry-header -->

        <div class="entry-content clearfix">

            <p>{!!$arti->description!!}</p>

        </div><!-- .entry-content -->

    </article><!-- .hentry -->

    <?php
    use Illuminate\Support\Facades\Auth;



    $comments = App\Model\kb\Comment::where('article_id', $arti->id)
        ->where('status', '1')
        ->orWhere(function ($query) {
            $query->where('article_id', Auth::id()); // Add this line to include the authenticated user's comment
        })
        ->get();

?>

    <div id="comments" class="comments-area">
        @foreach($comments as $comment)
        <ol class="comment-list">
            <li class="comment">
                <article class="comment-body">
                    <footer class="comment-meta">
                        <div class="comment-author">
                            <img src="{{asset("lb-faveo/media/images/avatar_1.png")}}" alt="" height="50" width="50" class="avatar" />
                            <b class="fn"><a href="#" rel="external" class="url">{!! $comment->name !!}</a></b>
                        </div><!-- .comment-author -->

                        <div class="comment-metadata">
                            <small class="date text-muted">
                                <time datetime="2013-10-23T01:50:50+00:00">{!! $comment->created_at->format('l, d-m-Y') !!}</time>
                            </small>
                        </div><!-- .comment-metadata -->
                    </footer><!-- .comment-meta -->

                    <div class="comment-content">

                        <p>{!! $comment->comment !!}</p>
                    </div><!-- .comment-content -->

                    <div class="comment-reply text-end">
                        {{-- <a class="btn btn-custom btn-sm" href="#">Reply</a> --}}
                    </div><!-- .comment-reply -->
                </article><!-- .comment-body -->
            </li><!-- .comment -->
        </ol><!-- .comment-list -->
        @endforeach

{{--        <div id="respond" class="comment-respond form-border">--}}

{{--            <h3 id="reply-title" class="comment-reply-title section-title">--}}
{{--                <i class="line" style="border-color: rgb(0, 154, 186);"<></i>{!! Lang::get('lang.leave_a_reply') !!}--}}
{{--            </h3>--}}

            {!! html()->form('POST', url('postcomment/'.$arti->slug))->attributes(['id' => 'comment-form'])->open() !!}
            {!! csrf_field() !!}

            <div id="respond" class="comment-respond form-border">
                <h3 id="reply-title" class="comment-reply-title section-title">
                    <i class="line" style="border-color: rgb(0, 154, 186);"></i>{!! Lang::get('lang.leave_a_reply') !!}
                </h3>

                @if(Auth::check())
                    <div class="row">

                        <div class="col-md-4" style="border:#f4f4f4;">
                            <div data-v-43e70d45="" class="banner-wrapper user-data text-center clearfix" id="ban_ner" style="border-width: 5px 1px 1px; border-style: solid; border-color: rgb(0, 154, 186); border-image: initial; width: 90%" >
                            <img id="user_avatar" src="{{Auth::user()->profile_pic}}" class="avatar" alt="User Image" style="margin-left: 5% ">
                                <div STYLE="margin-left:5%"><strong>{{trans('lang.hello')}}</strong></div>
                            <p class="banner-title ellipsize_first_name h4" STYLE="margin-left: 5%">{{Auth::user()->first_name." ".Auth::user()->last_name}}</p>
                            <div class="banner-content" id="dropdown_content">
                                <p data-v-43e70d45="">If you are not? </p>
                                <a href="{{url('auth/logout')}}" class="btn btn-custom btn-sm text-white profile_btn" STYLE="width: 50%;height: 200%;margin-left: 7%; background-color: #009aba; hov: #00c0ef; color: #fff">{!! Lang::get('lang.log_out') !!}</a>

                            </div>
                            </div>
                        </div>

                        <div class="col-md-10" style="width: 65%">
                            <div class="mb-3 {{ $errors->has('comment') ? 'has-error' : '' }}">
                                {!! html()->label(Lang::get('lang.message'), 'comment')->class('label') !!}
                                {!! html()->textarea('comment', null)->class('form-control')->id('comment')->attributes(['size' => '30x8']) !!}
                                {!! $errors->first('comment', '<spam class="help-block">:message</spam>') !!}
                            </div>
                            <button type="submit" class="btn btn-custom btn-lg float-end" style="background-color: #009aba; hov: #00c0ef; color: #fff">
                                {{ Lang::get('lang.post_message') }}
                            </button>
                        </div>
            </div><script>
                        function submitComment(userId, comment) {
                            // Prepare the form data
                            var formData = new FormData();
                            formData.append('user_id', userId);
                            formData.append('comment', comment);

                            // If the user is not authenticated, include the name and email fields
                            if (!userId) {
                                var name = $("#name").val();
                                var email = $("#email").val();
                                formData.append('name', name);
                                formData.append('email', email);
                            }

                            // Perform the AJAX request to submit the comment
                            $.ajax({
                                type: "POST",
                                url: "{{ url('postcomment/'.$arti->slug) }}",
                                data: formData,
                                processData: false,
                                contentType: false,
                                success: function(response) {
                                    // Handle the success response
                                    if (response.success) {
                                        alert("Comment posted successfully!");
                                        // You can redirect the user to a specific page or perform any other necessary action here
                                    } else {
                                        alert("An error occurred while processing your request. Please try again.");
                                    }
                                },
                                error: function(xhr, status, error) {
                                    // Handle the error response
                                    alert("An error occurred while processing your request. Please try again.");
                                }
                            });
                        }

                        // Remove the error styling and message when the user types in the comment field
                        $("#comment").on("input", function() {
                            $(this).removeClass("has-error");
                            $(this).next(".help-block").remove();
                        });




                    </script></div>
                    {!! html()->closeModelForm() !!}
                @else
                    {!! html()->form('POST', url('postcomment/'.$arti->slug))->attributes(['id' => 'comment-form'])->open() !!}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3 {{ $errors->has('name') ? 'has-error' : '' }}">
                                {!! html()->label(Lang::get('lang.name'), 'name')->class('label') !!}
                                {!! html()->text('name', null)->class('form-control')->id('comment-name') !!}
                                {!! $errors->first('name', '<span class="help-block">:message</span>') !!}
                            </div>

                            <div class="mb-3 {{ $errors->has('email') ? 'has-error' : '' }}">
                                {!! html()->label(Lang::get('lang.email'), 'email')->class('label') !!}
                                {!! html()->text('email', null)->class('form-control')->id('comment-email') !!}
                                {!! $errors->first('email', '<span class="help-block">:message</span>') !!}
                            </div>

                            <div class="mb-3 {{ $errors->has('website') ? 'has-error' : '' }}">
                                {!! html()->label(Lang::get('lang.website'), 'website')->class('label') !!}
                                {!! html()->text('website', null)->class('form-control') !!}
                                {!! $errors->first('website', '<span class="help-block">:message</span>') !!}
                            </div>

                        </div>

                        <div class="col-md-10" style="width: 65%">
                            <div class="mb-3 {{ $errors->has('comment') ? 'has-error' : '' }}">
                                {!! html()->label(Lang::get('lang.message'), 'comment')->class('label') !!}
                                {!! html()->textarea('comment', null)->class('form-control')->id('comment')->attributes(['size' => '30x8']) !!}
                                {!! $errors->first('comment', '<spam class="help-block">:message</spam>') !!}
                            </div>
                            <button type="submit" class="btn btn-custom btn-lg float-end" style="background-color: #009aba; hov: #00c0ef; color: #fff">
                                {{ Lang::get('lang.post_message') }}
                            </button>
                        </div>


                    </div>
                    <script>
                        $(document).ready(function() {
                            $("#comment-form").submit(function(event) {
                                event.preventDefault(); // Prevent the form from submitting

                                // Remove any existing error messages and reset label colors
                                $(".help-block").remove();
                                $(".mb-3").removeClass("has-error");

                                // Perform your custom validation here
                                var nameElement = $("#comment-name");
                                var emailElement = $("#comment-email");
                                var commentElement = $("#comment-comment");

                                if (nameElement.length > 0) {
                                    var name = nameElement.val().trim();
                                }

                                if (emailElement.length > 0) {
                                    var email = emailElement.val().trim();
                                }

                                if (commentElement.length > 0) {
                                    var comment = commentElement.val().trim();
                                }


                                // Flag to track if there are any errors
                                var hasErrors = false;

                                // Check if the name field is empty
                                if (name === "") {
                                    $("#comment-name").parent().addClass("has-error");
                                    $("#comment-name").after('<span class="help-block">The name field is required.</span>');

                                    hasErrors = true;
                                }

                                // Check if the email field is empty or invalid
                                if (email === "") {
                                    $("#comment-email").parent().addClass("has-error");
                                    $("#comment-email").after('<span class="help-block">The email field is required.</span>');

                                    hasErrors = true;
                                }

                                // Check if the comment field is empty
                                if (comment === "") {
                                    $("#comment-comment").parent().addClass("has-error");
                                    $("#comment-comment").after('<span class="help-block">The comment field is required.</span>');
                                    hasErrors = true;
                                }

                                // If there are no errors, submit the form
                                if (!hasErrors) {
                                    $("#comment-form")[0].submit();
                                }
                            });

                            // Remove the error styling and message when the user types in a field
                            $(".form-control").on("input", function() {
                                $(this).parent().removeClass("has-error");
                            });
                        });

                    </script>



                    </div>
                @endif
            </div><!-- #respond -->
            {!! html()->closeModelForm() !!}



    </div>
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
                        
                        <?php $categorys = App\Model\kb\Category::all(); ?>
                        @foreach($categorys as $category)
                        <?php
                        $num = \App\Model\kb\Relationship::where('category_id','=', $category->id)->get();
                        $article_id = $num->pluck('article_id');
                        $numcount = count($article_id);
                        ?>

                        <li class="d-flex justify-content-between align-items-center">

                            <a href="{{url('category-list/'.$category->slug)}}" class="list-group-item list-group-item-action" style="padding: 5px;">
                                <span class="badge bg-pill float-end" style="margin-top: 2px;">{{$numcount}}</span>{{$category->name}}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </section>
            </div>
        </div>
    </div>
@stop