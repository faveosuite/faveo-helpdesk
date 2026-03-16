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

@section('template')
class="nav-link active"
@stop

@section('PageHeader')
<h1>{!! Lang::get('lang.templates') !!}</h1>
@stop

@section('content')
{!! html()->modelForm($template, 'PATCH', url('templates/'.$template->id))->open() !!}
@if (count($errors) > 0)
<div class="alert alert-danger">
    <i class="fa-solid fa-ban"></i>  
    <strong>{!! Lang::get('lang.alert') !!} !</strong>
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa-solid fa-circle-check"></i>  
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('success')}}
</div>
@endif
<!-- fail lang -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{{Lang::get('lang.alert')}}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('fails')}}
</div>
@endif
<div class="card card-light">
    <div class="card-header">

        <h3 class="card-title">{{Lang::get('lang.edit_template')}}</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12 mb-3 {{ $errors->has('name') ? 'has-error' : '' }}">
                <!-- first name -->
                <p class="lead mb-0">{!! $template->name !!}</p>
            </div>
            <div class="col-md-4 mb-3 {{ $errors->has('type') ? 'has-error' : '' }}">
                <!-- last name -->
                {!! html()->label(Lang::get('lang.template-types'), 'type')->class('required') !!}<span style="color:red;">*</span>
                {!! html()->select('type', [''=>'Select','Type'=>$type], null)->class('form-control') !!}
            </div>
        </div>
        <div class="row">
            <div class="col-md-8 mb-3 {{ $errors->has('subject') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.subject'), 'subject') !!}
                {!! html()->text('subject', null)->class('form-control')->id('subject') !!}
            </div>
            <div class="col-md-3 mb-3" id = "use-subject" style="margin-top: 15px;">
                <br/>
                {!! html()->hidden('variable', '0') !!}
                {!! html()->checkbox('variable', null, '1') !!}
                {!! html()->label(Lang::get('lang.use_subject'), 'subject') !!}
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 mb-3 {{ $errors->has('message') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.content'), 'message')->class('required') !!}<span style="color:red;">*</span>
                {!! html()->textarea('message', null)->class('form-control')->id('textarea') !!}
            </div>
        </div>
    </div>
    <div class="card-footer">
        {!! html()->submit(Lang::get('lang.update'))->class('btn btn-primary') !!}
    </div>
</div>
{!! html()->closeModelForm() !!}

<script>
    $(document).ready(function() {
        $("#subject").keyup(function() {
            var subject = document.getElementById('subject').value;
            if (subject) {
                $("#use-subject").show();
            } else {
                $("#use-subject").hide();
            }
        });
    });
</script>
@stop