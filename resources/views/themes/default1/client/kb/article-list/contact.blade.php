@extends('themes.default1.client.layout.client')

@section('contact')
    class = "active"
@stop

@section('check')
<!-- Start of Page Container -->
<div style="padding-top: 60px;">

    @if($settings->address)
    <h2>Our Address</h2>
    {!! $settings->address !!}
    @endif
</div>
@stop
@section('content')
<div id="content" class="site-content col-md-9">
    <article class="type-page hentry clearfix">
        <h1 class="post-title">
            <a href="#">Contact us</a>
        </h1>
        <hr>
        <p></p>
    </article>
    {!! html()->form('POST', action('Client\kb\UserController@postContact'))->open() !!}
    @if(Session::has('success'))
    <div class="alert alert-success alert-dismissable">
        <i class="fa  fa-check-circle"></i>
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {{Session::get('success')}}
    </div>
    @endif
    <!-- failure message -->
    @if(Session::has('fails'))
    <div class="alert alert-danger alert-dismissable">
        <i class="fa fa-ban"></i>
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {{Session::get('fails')}}
    </div>
    @endif

    <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">

        {!! html()->label('Name', 'name') !!}
        {!! $errors->first('name', '<spam class="help-block">:message</spam>') !!}
        {!! html()->text('name', null)->class('form-control') !!}

    </div>

    <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">

        {!! html()->label('Email', 'email') !!}
        {!! $errors->first('email', '<spam class="help-block">:message</spam>') !!}
        {!! html()->text('email', null)->class('form-control') !!}

    </div>

    <div class="form-group {{ $errors->has('subject') ? 'has-error' : '' }}">

        {!! html()->label('Subject', 'subject') !!}
        {!! $errors->first('subject', '<spam class="help-block">:message</spam>') !!}
        {!! html()->text('subject', null)->class('form-control') !!}

    </div>

    <div class="form-group {{ $errors->has('message') ? 'has-	error' : '' }}">
        {!! html()->label('Messege', 'message')->attributes(['style' => 'display: block']) !!}
        {!! $errors->first('message', '<spam class="help-block">:message</spam>') !!}
        {!! html()->textarea('message', null)->class('form-control')->id('message')->attributes(['size' => '30x7']) !!}

    </div>
    <div>

        {!! html()->submit('Send Message')->class('form-group btn btn-primary') !!}

    </div>

    {!! html()->closeModelForm() !!}


</div>

@stop