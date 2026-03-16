@extends('themes.default1.admin.layout.admin')

@section('Tickets')
class="nav-link active"
@stop

@section('ticket-menu-parent')
class="nav-item menu-open"
@stop

@section('ticket-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('ratings')
class="nav-link active"
@stop

@section('HeadInclude')
@stop

<!-- header -->
@section('PageHeader')
<h3>Create Ratings</h3>
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
{!! html()->form('POST', route('rating.store'))->open() !!}
 @if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa-solid fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('success')}}
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
    @if($errors->first('display_order'))
    <li class="error-message-padding">{!! $errors->first('display_order', ':message') !!}</li>
    @endif
    @if($errors->first('rating_scale'))
    <li class="error-message-padding">{!! $errors->first('rating_scale', ':message') !!}</li>
    @endif
    @if($errors->first('rating_area'))
    <li class="error-message-padding">{!! $errors->first('rating_area', ':message') !!}</li>
    @endif
    @if($errors->first('restrict'))
    <li class="error-message-padding">{!! $errors->first('restrict', ':message') !!}</li>
    @endif
    @if($errors->first('allow_modification'))
    <li class="error-message-padding">{!! $errors->first('allow_modification', ':message') !!}</li>
    @endif
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{{Lang::get('lang.create')}}</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3 {{ $errors->has('name') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.rating_label'), 'name') !!}<span style="color:red;">*</span>
                {!! html()->text('name', null)->class('form-control') !!}
            </div>
            <div class="col-md-6 mb-3 {{ $errors->has('display_order') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.display_order'), 'display_order') !!}<span style="color:red;">*</span>
                {!! html()->text('display_order', null)->class('form-control') !!}
            </div>
        </div>
        <div class="mb-3 {{ $errors->has('rating_scale') ? 'has-error' : '' }}">
            {!! html()->label(Lang::get('lang.rating_scale'), 'rating_scale') !!}<span style="color:red;">*</span>
            <div class="callout callout-default font-oblique">{!! Lang::get('lang.rating-msg1') !!}</div>
            {!! html()->select('rating_scale', ['1' => '1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8'], null)->class('form-control') !!}
        </div>
        <div class="mb-3 {{ $errors->has('rating_area') ? 'has-error' : '' }}">
            {!! html()->label(Lang::get('lang.rating_area'), 'rating_area') !!}<span style="color:red;">*</span>
            {!! html()->select('rating_area', ['Helpdesk Area' => 'Helpdesk Area','Comment Area'=>'Comment Area'], null)->class('form-control') !!}
        </div>
        <div class="mb-3 {{ $errors->has('restrict') ? 'has-error' : '' }}">
            <!-- gender -->
            {!! html()->label(Lang::get('lang.rating_restrict'), 'gender') !!}<span style="color:red;">*</span>
            <div class="callout callout-default font-oblique">{!! Lang::get('lang.rating-msg2') !!}</div>
            {!! html()->select('restrict', ['General' => 'general','Support'=>'support'], null)->class('form-control') !!}
        </div>
        <div class="mb-3 {{ $errors->has('allow_modification') ? 'has-error' : '' }}">
            <!-- Email user -->
            {!! html()->label(Lang::get('lang.rating_change'), 'allow_modification') !!}<span style="color:red;">*</span>
            <div class="callout callout-default font-oblique">{!! Lang::get('lang.rating-msg3') !!}</div>
            <div class="row">
                <div class="col-sm-2">
                    {!! html()->radio('allow_modification', null, '1') !!} {{Lang::get('lang.yes')}}
                </div>
                <div class="col-sm-2">
                    {!! html()->radio('allow_modification', null, '0') !!} {{Lang::get('lang.no')}}
                </div>
            </div>        
        </div>
    </div>
    <div class="card-footer">
        {!! html()->submit(Lang::get('lang.submit'))->class('btn btn-primary') !!}
    </div>
</div>
@stop