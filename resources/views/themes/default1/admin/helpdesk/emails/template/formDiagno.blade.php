@extends('themes.default1.admin.layout.admin')

@section('Emails')
class="nav-link active"
@stop

@section('email-menu-parent')
class="nav-item menu-open"
@stop

@section('email-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('diagnostics')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{{Lang::get('lang.email_diagnostic')}}</h3>
@stop
<!-- /header -->
<!-- content -->
@section('content')
<!-- check whether success or not -->
<form method="POST" action="{!! route('postdiagno') !!}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    @if(Session::has('success') && !Session::has('fails'))
    <div class="alert alert-success alert-dismissible">
        <i class="fa-solid fa-circle-check"></i>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
        {{Session::get('success')}}
    </div>
    @endif
    <!-- failure message -->
    @if(Session::has('fails'))
    <div class="alert alert-warning alert-dismissible">
        <i class="fa-solid fa-ban"></i>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
        <b>{!! Lang::get('lang.alert') !!} !</b><br/>
        <li class="error-message-padding">{{Session::get('fails')}}</li>
    </div>
    @endif
    @if(Session::has('errors'))
    <?php //dd($errors); ?>
    <div class="alert alert-danger alert-dismissible">
        <i class="fa-solid fa-ban"></i>
        <b>{!! Lang::get('lang.alert') !!} !</b>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
        <br/>
        @if($errors->first('from'))
        <li class="error-message-padding">{!! $errors->first('from', ':message') !!}</li>
        @endif
        @if($errors->first('to'))
        <li class="error-message-padding">{!! $errors->first('to', ':message') !!}</li>
        @endif
        @if($errors->first('subject'))
        <li class="error-message-padding">{!! $errors->first('subject', ':message') !!}</li>
        @endif
        @if($errors->first('message'))
        <li class="error-message-padding">{!! $errors->first('message', ':message') !!}</li>
        @endif
    </div>
    @endif
    <div class="card card-light">
        <div class="card-header">
            <h3 class="card-title">{{Lang::get('lang.send-mail-to-diagnos')}}</h3>	
        </div>
        <div class="card-body">

            <div class="row mb-3 no-padding {!! $errors->has('from') ? 'has-error' : '' !!}">
                <div class="col-md-2">
                    <label>{!! Lang::get('lang.from') !!} <span class="text-red">*</span> :</label>
                </div>
                <div class="col-md-4">
                    {!! $errors->first('fetching_encryption', '<spam class="help-block">:message</spam>') !!}
                    <select name="from" class="form-control" id="from">
                        <option value="">{!! Lang::get('lang.choose_an_email') !!}</option>
                        <optgroup label="{!! Lang::get('lang.email') !!}">
                            @foreach($emails as $email)
                            <?php
                            if ($email->email_address == $email->email_name) {
                                $email1 = $email->email_address;
                            } else {
                                $email1 = $email->email_name . ' ( ' . $email->email_address . ' ) ';
                            }
                            ?>
                            <option value="{!! $email->id !!}"> {{ $email1 }} </option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
            </div>
            <div class="row mb-3 no-padding {!! $errors->has('to') ? 'has-error' : '' !!}">
                <div class="col-md-2">
                    <label>{!! Lang::get('lang.to') !!} <span class="text-red">*</span> :</label>
                </div>
                <div class="col-md-4">
                    {!! html()->text('to', null)->class('form-control') !!}
                </div>
            </div>
            <div class="row mb-3 no-padding {!! $errors->has('subject') ? 'has-error' : '' !!}">
                <div class="col-md-2">
                    <label>{!! Lang::get('lang.subject') !!} <span class="text-red">*</span> :</label>
                </div>
                <div class="col-md-8">
                    {!! html()->text('subject', null)->class('form-control') !!}
                </div>
            </div>
            <div class="row mb-3 no-padding {!! $errors->has('message') ? 'has-error' : '' !!}">
                <div class="col-md-2">
                    <label>{!! Lang::get('lang.message') !!} <span class="text-red">*</span> :</label>
                </div>
                <div class="col-md-10">
                    <textarea name="message" id="message" class="form-control" style="height:200px;"></textarea>
                </div>
            </div>
        </div>
        <div class="card-footer">
            {!! html()->submit(Lang::get('lang.send'))->class('btn btn-primary') !!}
        </div>
    </div>
</form>
@stop