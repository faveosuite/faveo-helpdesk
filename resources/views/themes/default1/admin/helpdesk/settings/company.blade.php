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
    @if($errors->first('company_name'))
    <li class="error-message-padding">{!! $errors->first('company_name', ':message') !!}</li>
    @endif
    @if($errors->first('website'))
    <li class="error-message-padding">{!! $errors->first('website', ':message') !!}</li>
    @endif
    @if($errors->first('phone'))
    <li class="error-message-padding">{!! $errors->first('phone', ':message') !!}</li>
    @endif
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

        <div class="row">
            <div class="col-md-2">
                <!-- logo -->
                {!! html()->label(Lang::get('lang.logo'), 'logo') !!}
                <div class="btn bg-olive btn-file" style="color:blue"> {{Lang::get('lang.upload_file')}}
                    {!! html()->file('logo') !!}
                </div>
            </div>
            <div class="col-sm-10">
                <div id="logo-display" style="display: block;">
                    <div class="row">
                        @if($companys->logo != null)
                        <div class="col-sm-2">
                            {!! html()->checkbox('use_logo') !!} <label> {!! Lang::get('lang.use_logo') !!}</label>
                        </div>
                        @endif
                        <?php $company = App\Model\helpdesk\Settings\Company::where('id', '=', '1')->first(); ?>
                        @if($companys->logo != null)
                        <div class="col-md-3 image" data-bs-content="{{Lang::get('lang.click-delete')}}">
                            <img src="{{asset('uploads/company')}}{{'/'}}{{$company->logo}}" alt="User Image" id="company-logo" width="100px" style="border:1px solid #DCD1D1" />
                        </div>
                        @endif
                    </div>
                </div>
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
                    <button type="button" class="btn-close closemodal" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
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