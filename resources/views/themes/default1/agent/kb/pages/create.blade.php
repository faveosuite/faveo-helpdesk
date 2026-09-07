@extends('themes.default1.agent.layout.agent')

@extends('themes.default1.agent.layout.sidebar')    

@section('Tools')
class="nav-link active"
@stop

@section('tool')
class="active"
@stop

@section('kb')
class="nav-link active"
@stop

@section('add-pages')
class="nav-link active"
@stop

@section('pages')
class="nav-link active"
@stop

@section('page-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('page-menu-parent')
class="nav-item menu-open"
@stop

@section('PageHeader')
<h1>{{Lang::get('lang.pages')}}</h1>
@stop

@section('content')

{!! html()->form('POST', route('page.store'))->open() !!}

@if(Session::has('errors'))
<?php //dd($errors); ?>
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <br/>
    @if($errors->first('name'))
    <li class="error-message-padding">{!! $errors->first('name', ':message') !!}</li>
    @endif
    @if($errors->first('slug'))
    <li class="error-message-padding">{!! $errors->first('slug', ':message') !!}</li>
    @endif
    @if($errors->first('description'))
    <li class="error-message-padding">{!! $errors->first('description', ':message') !!}</li>
    @endif
    @if($errors->first('status'))
    <li class="error-message-padding">{!! $errors->first('status', ':message') !!}</li>
    @endif
    @if($errors->first('visibility'))
    <li class="error-message-padding">{!! $errors->first('visibility', ':message') !!}</li>
    @endif
</div>
@endif

<div class="row">
    
    <div class="col-sm-9">
        
        <div class="card card-light">

            <div class="card-header">  
                <h3 class="card-title">{!! Lang::get('lang.addpages') !!}</h3>
            </div>

            <div class="card-body"> 
                <div class="row">
                    <div class="col-md-6 mb-3 {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('lang.name'), 'name') !!}<span class="text-red"> *</span>

                        {!! html()->text('name', null)->class('form-control') !!}
                    </div>
                    
                    <div class="mb-3 col-sm-12 {{ $errors->has('description') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('lang.description'), 'description') !!}
                        <span class="text-red"> *</span>
                        <div class="mb-3" style="background-color:white">
                            {!! html()->textarea('description', null)->class('form-control color')->id('myNicEditor')->placeholder(Lang::get('lang.enter_the_description'))->attributes(['size' => '110x15']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>  

    <div class="col-sm-3">
        
        <div class="card card-light">
            
            <div class="card-header">
                <h3 class="card-title">{{Lang::get('lang.publish')}}</h3>
            </div>
            
            <div class="card-body">
                <div class="mb-3 {{ $errors->has('status') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.status'), 'status') !!}
                    <div class="row">
                        <div class="col-sm-5">
                            {!! html()->radio('status', true, '1') !!} {{ Lang::get('lang.published') }}
                        </div>
                        <div class="col-sm-5">
                            {!! html()->radio('status', null, '0') !!} {{ Lang::get('lang.draft') }}
                        </div>
                    </div>
                </div>
                <div class="mb-3 {{ $errors->has('visibility') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.visibility'), 'visibility') !!}
                    <div class="row">
                        <div class="col-sm-5">
                            {!! html()->radio('visibility', true, '1') !!} {{Lang::get('lang.public')}}
                        </div>
                        <div class="col-sm-5">
                            {!! html()->radio('visibility', null, '0') !!} {{Lang::get('lang.private')}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                {!! html()->submit(Lang::get('lang.publish'))->class('btn btn-primary') !!}
            </div>
        </div>
    </div>    
</div>
<script type="text/javascript">
    $(function() {
        $("textarea").summernote({
            height: 300,
            tabsize: 2,
            toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']]
          ]
        });
    });
</script>
@stop