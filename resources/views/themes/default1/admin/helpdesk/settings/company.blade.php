@extends('themes.default1.admin.layout.admin')
<link href="{{asset("lb-faveo/css/faveo-css.css")}}" rel="stylesheet" type="text/css" />
@section('Settings')
class="nav-link active"
@stop

@section('settings-menu-parent')
class="nav-item menu-open"
@stop

@section('settings-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('company')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{{ Lang::get('lang.settings') }}</h3>
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
{!! html()->modelForm($companys, 'PATCH', url('postcompany/'.$companys->id))->acceptsFiles()->open() !!}
<!-- check whether success or not -->
@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa-solid fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {!!Session::get('success')!!}
</div>
@endif
<!-- failure message -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {!!Session::get('fails')!!}
</div>
@endif

@if(Session::has('errors'))
<?php //dd($errors); ?>
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <br/>
    {{-- every field, logo included: a rejected upload used to fail with no message --}}
    @foreach($errors->all() as $error)
    <li class="error-message-padding">{{ $error }}</li>
    @endforeach
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{{Lang::get('lang.company_settings')}}</h3>
    </div>
    <!-- Name text form Required -->
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <!-- comapny name -->
                <div class="mb-3 {{ $errors->has('company_name') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.name'), 'company_name') !!} <span class="text-red"> *</span>
                    {!! html()->text('company_name', $companys->company_name)->class('form-control') !!}
                </div>
            </div>
            <div class="col-md-4">
                <!-- website -->
                <div class="mb-3 {{ $errors->has('website') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.website'), 'website') !!}
                    {!! html()->input('url', 'website', $companys->website)->class('form-control') !!}
                </div>
            </div>
            <div class="col-md-4">
                <!-- phone -->
                <div class="mb-3 {{ $errors->has('phone') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.phone'), 'phone') !!}
                    {!! html()->text('phone', $companys->phone)->class('form-control') !!}
                </div>
            </div>
        </div>

         <div class="{{ $errors->has('address') ? 'has-error' : '' }}">
            {!! html()->label(Lang::get('lang.address'), 'address') !!}
            {!! html()->textarea('address', $companys->address)->class('form-control')->attributes(['size' => '30x5']) !!}
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <!-- logo -->
                {!! html()->label(Lang::get('lang.logo'), 'logo')->class('d-block') !!}
                {!! html()->file('logo')->attribute('accept', 'image/*')->class('visually-hidden') !!}
                <label for="logo" class="btn btn-olive">{{ Lang::get('lang.upload_file') }}</label>

                <!-- preview of the picked file, before it is submitted -->
                {{-- the script shows/hides these boxes through inline display, so d-flex (which is !important) sits on an inner wrapper --}}
                <div id="logo-preview" class="mt-2" style="display: none;">
                    <div class="d-flex align-items-center gap-3">
                        <img id="logo-preview-img" src="" alt="" class="img-thumbnail" style="max-width: 160px; max-height: 80px; object-fit: contain;" />
                        <div>
                            <div id="logo-preview-name" class="text-break"></div>
                            <a href="#" id="logo-preview-remove" class="text-danger">{{ Lang::get('lang.remove') }}</a>
                        </div>
                    </div>
                </div>
                <div id="logo-preview-error" class="text-danger mt-2" style="display: none;"></div>

                <!-- the saved logo -->
                @if($companys->logo != null)
                <div id="logo-display" class="mt-2">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ asset('uploads/company/'.$companys->logo) }}" alt="{{ Lang::get('lang.logo') }}" id="company-logo" class="img-thumbnail" style="max-width: 160px; max-height: 80px; object-fit: contain;" />
                        <div>
                            <div class="form-check mb-2">
                                {!! html()->checkbox('use_logo')->class('form-check-input') !!}
                                <label class="form-check-label" for="use_logo">{!! Lang::get('lang.use_logo') !!}</label>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger image">
                                <i class="fa-solid fa-trash"></i> {{ Lang::get('lang.delete-logo') }}
                            </button>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="card-footer">
        {!! html()->submit(Lang::get('lang.submit'))->class('btn btn-primary') !!}
    </div>
    <!-- Modal -->   
    <div class="modal fade" id="myModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel"></h4>
                    <button type="button" class="btn-close closemodal" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="custom-alert-body" >
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-primary yes" data-bs-dismiss="modal"></button>
                    <button type="button" class="btn btn-secondary no"></button>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        // PHP rejects anything over upload_max_filesize, so catch it here instead of after submit
        var maxLogoBytes = {{ \Illuminate\Http\UploadedFile::getMaxFilesize() }};
        var $logoInput = $('input[name="logo"]');

        function clearLogoPreview() {
            $logoInput.val('');
            $('#logo-preview').hide();
            $('#logo-preview-img').attr('src', '');
        }

        $logoInput.on('change', function() {
            var file = this.files[0];
            $('#logo-preview-error').hide();
            if (!file) {
                clearLogoPreview();
                return;
            }
            var error = '';
            if (!/^image\//.test(file.type)) {
                error = "{{ Lang::get('validation.image', ['attribute' => Lang::get('lang.logo')]) }}";
            } else if (file.size > maxLogoBytes) {
                error = "{{ Lang::get('validation.max.file', ['attribute' => Lang::get('lang.logo'), 'max' => intdiv(\Illuminate\Http\UploadedFile::getMaxFilesize(), 1024)]) }}";
            }
            if (error) {
                clearLogoPreview();
                $('#logo-preview-error').text(error).show();
                return;
            }
            $('#logo-preview-img').attr('src', URL.createObjectURL(file));
            $('#logo-preview-name').text(file.name + ' (' + Math.ceil(file.size / 1024) + ' KB)');
            $('#logo-preview').css('display', 'flex');
        });

        $('#logo-preview-remove').on('click', function(e) {
            e.preventDefault();
            clearLogoPreview();
        });

        $(".image").on("click", function() {
            $('#myModal').modal('show');
            $("#myModalLabel").html("{!! Lang::get('lang.delete-logo') !!}");
            $(".yes").html("{!! Lang::get('lang.yes') !!}");
            $(".no").html("{{Lang::get('lang.cancel')}}");
            $("#custom-alert-body").html("{{Lang::get('lang.confirm')}}");
        });
        $('.no,.closemodal').on("click", function() {
            $('#myModal').modal('hide');
        });
        $('.yes').on('click', function() {
            var src = $('#company-logo').attr('src').split('/');
            var file = src[src.length - 1];

            var path = "uploads/company/" + file;
            // alert(path); 
            $.ajax({
                type: "GET",
                url: "{{route('delete.logo')}}",
                dataType: "html",
                data: {data1: path},
                success: function(data) {
                    if (data == "true") {
                        var msg = "Logo deleted succesfully."
                        $("#logo-display").css("display", "none");
                        $('#myModal').modal('hide');
                    } else {
                        $('#myModal').modal('hide');
                    }
                }
            });
        });
    });
</script>
@stop