@extends('themes.default1.agent.layout.agent')

@extends('themes.default1.agent.layout.sidebar')    

@section('category-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('category-menu-parent')
class="nav-item menu-open"
@stop

@section('Tools')
class="nav-link active"
@stop

@section('tool')
class="active"
@stop

@section('kb')
class="nav-link active"
@stop

@section('all-category')
class="nav-link active"
@stop

@section('category')
class="nav-link active"
@stop

@section('PageHeader')
<h3>{{Lang::get('lang.category')}}</h3>
@stop

@section('content')
{!! html()->modelForm($category, 'PATCH', url('category/'.$category->id))->open() !!}
@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa-regular fa-circle-check"></i>
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
        <h3 class="card-title">{!! Lang::get('lang.edit') !!}</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-sm-3 mb-3 {{ $errors->has('name') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.name'), 'name') !!}<span class="text-red"> *</span>

                {!! html()->text('name', null)->class('form-control') !!}
            </div>
            <div class="col-sm-3 mb-3 {{ $errors->has('parent') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.parent'), 'parent') !!}

                {!! html()->select('parent', [''=>'Select a Category','Categories'=>$categories], null)->class('form-control select') !!}
            </div>
            <div class="col-sm-3 mb-3 {{ $errors->has('status') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.status'), 'status') !!}

                <div>
                    <label class="fw-normal me-3">
                        {!! html()->radio('status', (string) old('status', $category->status) === '1', '1')->id('status-active') !!} {{ Lang::get('lang.active') }}
                    </label>
                    <label class="fw-normal me-3">
                        {!! html()->radio('status', (string) old('status', $category->status) === '0', '0')->id('status-inactive') !!} {{ Lang::get('lang.inactive') }}
                    </label>
                </div>
            </div>
            <div class="col-md-12 mb-3 {{ $errors->has('description') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.description'), 'description') !!}<span class="text-red"> *</span>

                {!! html()->textarea('description', null)->class('form-control')->id('description')->placeholder(Lang::get('lang.enter_the_description'))->attributes(['size' => '128x10']) !!}
            </div>
        </div>
    </div>
    <div class="card-footer">
        {!! html()->submit(Lang::get('lang.update'))->class('btn btn-primary') !!}
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