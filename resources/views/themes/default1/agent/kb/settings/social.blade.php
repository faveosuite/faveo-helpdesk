@extends('themes.default1.agent.layout.agent')
@extends('themes.default1.agent.layout.sidebar')    

@section('widget')
    active
@stop
@section('social')
    class="active"
@stop
@section('content')
<!-- open a form -->

	{!! html()->modelForm($social, 'PATCH', url('postsocial'))->acceptsFiles()->open() !!}

<!-- <div class="mb-3 {{ $errors->has('company_name') ? 'has-error' : '' }}"> -->
	<!-- table  -->

<div class="row">
<div class="col-md-12">
<div class="box box-primary">
	<div class="box-header">
        <h3 class="box-title">{{Lang::get('lang.social')}}</h3>  {!! html()->submit(Lang::get('lang.save'))->class('mb-3 btn btn-primary pull-right') !!}
    </div>

    <!-- check whether success or not -->

    @if(Session::has('success'))
    <div class="alert alert-success alert-dismissible">
        <i class="fa  fa-circle-check"></i>
        <b>Success</b>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
        {{Session::get('success')}}
    </div>
    @endif
    <!-- failure message -->
    @if(Session::has('fails'))
    <div class="alert alert-danger alert-dismissible">
        <i class="fa-solid fa-ban"></i>
        <b>Fail!</b>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
        {{Session::get('fails')}}
    </div>
    @endif

		<!-- Name text form Required -->
 		<div class="box-body table-responsive"style="overflow:hidden;">

            <div class="row">

                <div class=" col-4 mb-3 {{ $errors->has('google') ? 'has-error' : '' }}">

                    {!! html()->label('google', 'google') !!}
                    {!! $errors->first('google', '<spam class="help-block">:message</spam>') !!}
			        {!! html()->text('google', null)->class('form-control') !!}

                </div>

                <div class=" col-4 mb-3 {{ $errors->has('twitter') ? 'has-error' : '' }}">

                    {!! html()->label('twitter', 'twitter') !!}
                    {!! $errors->first('twitter', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->text('twitter', null)->class('form-control') !!}

                </div>

                <div class=" col-4 mb-3 {{ $errors->has('facebook') ? 'has-error' : '' }}">

                    {!! html()->label('facebook', 'facebook') !!}
                    {!! $errors->first('facebook', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->text('facebook', null)->class('form-control') !!}

                </div>

        </div>

        <div class="row">

                <div class=" col-4 mb-3 {{ $errors->has('linkedin') ? 'has-error' : '' }}">

                    {!! html()->label('linkedin', 'linkedin') !!}
                    {!! $errors->first('linkedin', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->text('linkedin', null)->class('form-control') !!}

                </div>

                <div class=" col-4 mb-3 {{ $errors->has('stumble') ? 'has-error' : '' }}">

                    {!! html()->label('stumble', 'stumble') !!}
                    {!! $errors->first('stumble', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->text('stumble', null)->class('form-control') !!}

                </div>

                <div class=" col-4 mb-3 {{ $errors->has('deviantart') ? 'has-error' : '' }}">

                    {!! html()->label('deviantart', 'deviantart') !!}
                    {!! $errors->first('deviantart', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->text('deviantart', null)->class('form-control') !!}

                </div>

        </div>

        <div class="row">

                <div class=" col-4 mb-3 {{ $errors->has('flickr') ? 'has-error' : '' }}">

                    {!! html()->label('flickr', 'flickr') !!}
                    {!! $errors->first('flickr', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->text('flickr', null)->class('form-control') !!}

                </div>

                <div class=" col-4 mb-3 {{ $errors->has('skype') ? 'has-error' : '' }}">

                    {!! html()->label('skype', 'skype') !!}
                    {!! $errors->first('skype', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->text('skype', null)->class('form-control') !!}

                </div>

                <div class=" col-4 mb-3 {{ $errors->has('rss') ? 'has-error' : '' }}">

                    {!! html()->label('rss', 'rss') !!}
                    {!! $errors->first('rss', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->text('rss', null)->class('form-control') !!}

                </div>

        </div>

         <div class="row">

                <div class=" col-4 mb-3 {{ $errors->has('youtube') ? 'has-error' : '' }}">

                    {!! html()->label('youtube', 'youtube') !!}
                    {!! $errors->first('youtube', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->text('youtube', null)->class('form-control') !!}

                </div>

                <div class=" col-4 mb-3 {{ $errors->has('vimeo') ? 'has-error' : '' }}">

                    {!! html()->label('vimeo', 'vimeo') !!}
                    {!! $errors->first('vimeo', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->text('vimeo', null)->class('form-control') !!}

                </div>

                <div class=" col-4 mb-3 {{ $errors->has('pinterest') ? 'has-error' : '' }}">

                    {!! html()->label('pinterest', 'pinterest') !!}
                    {!! $errors->first('pinterest', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->text('pinterest', null)->class('form-control') !!}

                </div>

        </div>

        <div class="row">

                <div class=" col-6 mb-3 {{ $errors->has('dribbble') ? 'has-error' : '' }}">

                    {!! html()->label('dribbble', 'dribbble') !!}
                    {!! $errors->first('dribbble', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->text('dribbble', null)->class('form-control') !!}

                </div>

                <div class=" col-6 mb-3 {{ $errors->has('instagram') ? 'has-error' : '' }}">

                    {!! html()->label('instagram', 'instagram') !!}
                    {!! $errors->first('instagram', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->text('instagram', null)->class('form-control') !!}

                </div>


        </div>

</div>
</div>
</div></div>
@stop
