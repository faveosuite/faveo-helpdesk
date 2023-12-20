@extends('themes.default1.agent.layout.agent')

@section('Tools')
class="nav-link active"
@stop

@section('tools-bar')
active
@stop

@section('tool')
class="active"
@stop

@section('tools')
class="nav-link active"
@stop

@section('PageHeader')
<h1>{{Lang::get('lang.canned_response')}}</h1>
@stop

<!-- content -->
@section('content')
<!-- open a form -->
{!! html()->form('PATCH', route('canned.store'))->open() !!}
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.create') !!} </h3>
    </div>
    <div class="card-body">
        @if(Session::has('errors'))
        <?php //dd($errors); ?>
        <div class="alert alert-danger alert-dismissible">
            <i class="fa-solid fa-ban"></i>
            <b>{!! Lang::get('lang.alert') !!}!</b>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
            <br/>
            @if($errors->first('title'))
            <li class="error-message-padding">{!! $errors->first('title', ':message') !!}</li>
            @endif
            @if($errors->first('message'))
            <li class="error-message-padding">{!! $errors->first('message', ':message') !!}</li>
            @endif
        </div>
        @endif
        <div class="row">
            <!-- username -->
            <div class="col-sm-6 mb-3 {{ $errors->has('title') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.title'), 'title') !!}    <span class="text-red"> *</span>           
                {!! html()->text('title', null)->class('form-control') !!}
            </div>
            <!-- firstname -->
            <div class="col-sm-12 mb-3 {{ $errors->has('message') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.message'), 'message') !!}<span class="text-red"> *</span>
                {!! html()->textarea('message', null)->class('form-control') !!}
            </div>
        </div>
    </div>
    <div class="card-footer">
        {!! html()->submit(Lang::get('lang.submit'))->class('btn btn-primary') !!}
    </div>
</div>
<script>
    $(function() {
        //Add text editor
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
