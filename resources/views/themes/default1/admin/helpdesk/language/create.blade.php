@extends('themes.default1.admin.layout.admin')

@section('Settings')
class="nav-link active"
@stop

@section('settings-menu-parent')
class="nav-item menu-open"
@stop

@section('settings-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('languages')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{!! Lang::get('lang.language') !!}</h3>
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
{!! html()->form('POST', url('language/add'))->acceptsFiles()->open() !!}
@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa-solid fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <span>{{Session::get('success')}}</span>
</div>
@endif
<!-- failure message -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('fails')}}
    @if(Session::has('link'))
    <a href="{{url(Session::get('link'))}}">{{Lang::get('lang.enable_lang')}}</a>
    @endif
    @if(Session::has('link2'))
    <a href="{{url(Session::get('link2'))}}" target="blank">{{Lang::get('lang.read-more')}}</a>
    @endif
</div>
@endif
@if(Session::has('errors'))
<?php //dd($errors); ?>
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <br/>
    @if($errors->first('language-name'))
    <li class="error-message-padding">{!! $errors->first('language-name', ':message') !!}</li>
    @endif
    @if($errors->first('iso-code'))
    <li class="error-message-padding">{!! $errors->first('iso-code', ':message') !!}</li>
    @endif
    @if($errors->first('File'))
    <li class="error-message-padding">{!! $errors->first('File', ':message') !!}</li>
    @endif
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{{Lang::get('lang.add-lang-package')}}</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- username -->
            <div class="col-sm-4 mb-3 {{ $errors->has('language-name') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.language-name'), 'language-name') !!} <span class="text-red"> *</span>
                {!! html()->text('language-name', null)->placeholder('English')->class('form-control') !!}
            </div>
            <div class="col-sm-4 mb-3 {{ $errors->has('iso-code') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.iso-code'), 'iso-code') !!} <span class="text-red"> *</span>
                {!! html()->text('iso-code', null)->placeholder('en')->attribute('maxlength', 2)->class('form-control') !!}
                <small class="form-text text-muted">{{ Lang::get('lang.iso-code-help') }}</small>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-8 mb-3 {{ $errors->has('File') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.file'), 'File') !!} <span class="text-red"> *</span>
                {!! html()->file('File')->attribute('accept', '.zip,application/zip')->class('visually-hidden') !!}
                <label for="File" class="btn btn-olive d-table">{!! Lang::get('lang.upload_file') !!}</label>
                <small class="form-text text-muted d-block mt-1">
                    {{ Lang::get('lang.lang-zip-help') }}
                    <a href="{{ route('download') }}">{{ Lang::get('lang.lang-zip-template') }}</a>
                </small>

                <!-- the picked file, before it is submitted -->
                {{-- the script shows/hides this box through inline display, so d-flex (which is !important) sits on an inner wrapper --}}
                <div id="lang-file-preview" class="mt-2" style="display: none;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-regular fa-file-zipper fa-lg text-secondary"></i>
                        <span id="lang-file-name" class="text-break"></span>
                        <a href="#" id="lang-file-remove" class="text-danger">{{ Lang::get('lang.remove') }}</a>
                    </div>
                </div>
                <div id="lang-file-error" class="text-danger mt-2" style="display: none;"></div>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <button type="submit" id="lang-submit" class="btn btn-primary">
            <span id="lang-submit-spinner" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true" style="display: none;"></span>
            <span id="lang-submit-text">{{ Lang::get('lang.submit') }}</span>
        </button>
    </div>
</div>
{!! html()->form()->close() !!}
<script type="text/javascript">
    $(document).ready(function() {
        // PHP drops anything over upload_max_filesize (and the rule allows 30000 KB), so catch it here instead of after submit
        var maxZipBytes = {{ min(30000 * 1024, \Illuminate\Http\UploadedFile::getMaxFilesize()) }};
        var maxZipError = "{{ Lang::get('validation.max.file', ['attribute' => Lang::get('lang.file'), 'max' => intdiv(min(30000 * 1024, \Illuminate\Http\UploadedFile::getMaxFilesize()), 1024)]) }}";
        var $fileInput = $('input[name="File"]');

        function clearLangFile() {
            $fileInput.val('');
            $('#lang-file-preview').hide();
        }

        function showLangFileError(error) {
            clearLangFile();
            $('#lang-file-error').text(error).show();
        }

        $fileInput.on('change', function() {
            var file = this.files[0];
            $('#lang-file-error').hide();
            if (!file) {
                clearLangFile();
                return;
            }
            if (!/\.zip$/i.test(file.name)) {
                showLangFileError("{{ Lang::get('lang.lang-zip-only') }}");
                return;
            }
            if (file.size > maxZipBytes) {
                showLangFileError(maxZipError);
                return;
            }
            $('#lang-file-name').text(file.name + ' (' + Math.ceil(file.size / 1024) + ' KB)');
            $('#lang-file-preview').show();
        });

        $('#lang-file-remove').on('click', function(e) {
            e.preventDefault();
            clearLangFile();
        });

        $('#lang-submit').closest('form').on('submit', function(e) {
            if (!$fileInput[0].files.length) {
                e.preventDefault();
                $('#lang-file-error').text("{{ Lang::get('lang.lang-zip-required') }}").show();
                return;
            }
            // stop double submits and show that the upload is in progress
            $('#lang-submit').prop('disabled', true);
            $('#lang-submit-spinner').show();
            $('#lang-submit-text').text("{{ Lang::get('lang.uploading') }}");
        });
    });
</script>
@stop