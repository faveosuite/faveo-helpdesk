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

@section('add-category')
class="nav-link active"
@stop

@section('category')
class="nav-link active"
@stop

@section('category-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('category-menu-parent')
class="nav-item menu-open"
@stop

@section('PageHeader')
<h1>{{Lang::get('lang.category')}}</h1>
@stop

@section('content')
{!! html()->form('POST', route('category.store'))->open() !!}
@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa-solid fa-circle-check"></i>
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
    @if($errors->first('parent'))
    <li class="error-message-padding">{!! $errors->first('parent', ':message') !!}</li>
    @endif
    @if($errors->first('status'))
    <li class="error-message-padding">{!! $errors->first('status', ':message') !!}</li>
    @endif
    @if($errors->first('description'))
    <li class="error-message-padding">{!! $errors->first('description', ':message') !!}</li>
    @endif          
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.addcategory') !!}</h3> 
    </div>
    <div class="card-body">
        
        <div class="row">
            <div class="col-sm-3 {{ $errors->has('name') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.name'), 'name') !!}<span class="text-red"> *</span>
                {!! html()->text('name', null)->class('form-control') !!}
            </div>
            <div class="col-sm-3 {{ $errors->has('parent') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.parent'), 'parent') !!}
                {!! html()->select('parent', [''=>'Select a Category','Categories'=>$category], null)->class('form-control select') !!}
            </div>
            <div class="col-sm-3 {{ $errors->has('status') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.status'), 'status') !!}
                <div class="row">
                    <div class="col-md-4">
                        {!! html()->radio('status', true, '1') !!} {{ Lang::get('lang.active')}}
                    </div>
                    <div class="col-md-6">
                        {!! html()->radio('status', null, '0') !!} {{ Lang::get('lang.inactive')}}
                    </div>
                </div>
            </div>
            <div class="col-md-12 {{ $errors->has('description') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.description'), 'description') !!}<span class="text-red"> *</span>
                {!! html()->textarea('description', null)->class('form-control')->id('description')->placeholder(Lang::get('lang.enter_the_description')) !!}
            </div>
        </div>
    </div>
    <div class="card-footer">
        {!! html()->submit(Lang::get('lang.submit'))->class('btn btn-primary') !!}
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
