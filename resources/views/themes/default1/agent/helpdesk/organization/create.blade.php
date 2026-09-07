@extends('themes.default1.agent.layout.agent')

@section('Users')
class="nav-link active"
@stop

@section('user-bar')
class="nav-link active"
@stop

@section('user')
class="active"
@stop

@section('organizations')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{!! Lang::get('lang.organization') !!}</h3>
@stop
<!-- /header -->
<!-- breadcrumbs -->
@section('breadcrumbs')
<ol class="breadcrumb">
</ol>
@stop
<!-- /breadcrumbs -->
<!-- content -->
@section('content')
<!-- open a form -->
{!! html()->form('POST', route('organizations.store'))->open() !!}

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
    @if($errors->first('phone'))
    <li class="error-message-padding">{!! $errors->first('phone', ':message') !!}</li>
    @endif
    @if($errors->first('website'))
    <li class="error-message-padding">{!! $errors->first('website', ':message') !!}</li>
    @endif
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{{Lang::get('lang.create')}}</h3>
    </div>
    <div class="card-body">  
        <!-- name : text : Required -->
        <div class="row">
            <div class="col-sm-4 mb-3 {{ $errors->has('name') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.name'), 'name') !!} <span class="text-red"> *</span>
                {!! html()->text('name', null)->class('form-control') !!}
            </div>
            <!-- phone : Text : -->
            <div class="col-sm-4 mb-3 {{ $errors->has('phone') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.phone'), 'phone') !!}
                {!! html()->text('phone', null)->class('form-control') !!}
            </div>
            <!-- website : Text :  -->
            <div class="col-sm-4 mb-3 {{ $errors->has('website') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.website'), 'website') !!}
                {!! html()->text('website', null)->class('form-control') !!}
            </div>
        </div>
        <!-- Internal Notes : Textarea -->
        <div class="row">
            <div class="col-sm-6 mb-3">
                {!! html()->label(Lang::get('lang.address'), 'address') !!}
                {!! html()->textarea('address', null)->class('form-control') !!}
            </div>
            <div class="col-sm-6 mb-3">
                {!! html()->label(Lang::get('lang.internal_notes'), 'internal_notes') !!}
                {!! html()->textarea('internal_notes', null)->class('form-control') !!}
            </div>
        </div>
    </div>
    <div class="card-footer">
        {!! html()->submit(Lang::get('lang.submit'))->class('btn btn-primary') !!}
    </div>
</div>
<script type="text/javascript">
    $(function () {
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