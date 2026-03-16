@extends('themes.default1.admin.layout.admin')

@section('Manage')
class="nav-link active"
@stop

@section('manage-menu-parent')
class="nav-item menu-open"
@stop

@section('manage-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('priority')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{!! Lang::get('lang.priority') !!}</h3>
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

<form action="{!!URL::route('priority.create1')!!}" method="post" role="form">
{{ csrf_field() }}
    @if(Session::has('errors'))
    <?php //dd($errors); ?>
    <div class="alert alert-danger alert-dismissible">
        <i class="fa-solid fa-ban"></i>
        <b>Alert!</b>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
        <br/>
        @if($errors->first('priority'))
        <li class="error-message-padding">{!! $errors->first('priority', ':message') !!}</li>
        @endif
        @if($errors->first('priority_desc'))
        <li class="error-message-padding">{!! $errors->first('priority_desc', ':message') !!}</li>
        @endif
        @if($errors->first('priority_color'))
        <li class="error-message-padding">{!! $errors->first('priority_color', ':message') !!}</li>
        @endif
        @if($errors->first('status'))
        <li class="error-message-padding">{!! $errors->first('status', ':message') !!}</li>
        @endif
    </div>
    @endif
    <div class="card card-light">
        <div class="card-header">
            <h3 class="card-title">{{Lang::get('lang.create')}}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="mb-3 col-md-6 {{ $errors->has('priority') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.priority'), 'priority') !!} <span class="text-red"> *</span>
                    <input type="text" class="form-control" name="priority" value="" >
                </div>
                <!-- Grace Period text form Required -->
                <div class="mb-3 col-md-6 {{ $errors->has('priority_desc') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.priority_desc'), 'priority_desc') !!}<span class="text-red"> *</span>
                    <input type="text" name="priority_desc" class="form-control">
                </div> 
            </div>
            <!-- Priority Color -->
            <div class="row">
                <div class="mb-3 col-sm-6 {{ $errors->has('priority_color') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.priority_color'), 'priority_color') !!}<span class="text-red"> *</span>
                    <input class="form-control my-colorpicker1 colorpicker-element" id="colorpicker" type="text" name="priority_color">
                </div>

                <div class="mb-3 col-sm-3 {{ $errors->has('status') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.status'), 'status') !!}&nbsp;<span class="text-red"> *</span><br/>
                    <input type="radio"  name="status" value="1" checked>&nbsp;&nbsp;{{Lang::get('lang.active')}}&nbsp;&nbsp;
                    <input type="radio"  name="status" value="0" >&nbsp;&nbsp;{{Lang::get('lang.inactive')}}
                </div> 

                <div class="mb-3 col-sm-3 {{ $errors->has('ispublic') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.visibility'), 'ispublic') !!}&nbsp;<span class="text-red"> *</span><br/>
                    <input type="radio"  name="ispublic" value="1" checked>{{Lang::get('lang.public')}}
                    <input type="radio"  name="ispublic" value="0" >&nbsp;&nbsp;{{Lang::get('lang.private')}}
                </div>
            </div>  
            <!-- Admin Note  : Textarea :  -->
            <div>
                {!! html()->label(Lang::get('lang.admin_notes'), 'admin_note') !!}
                {!! html()->textarea('admin_note', null)->class('form-control')->attributes(['size' => '30x5']) !!}        
            </div>
        </div>
        <div class="card-footer">
            {!! html()->submit(Lang::get('lang.submit'))->class('btn btn-primary') !!}
        </div>
    </div>
    <script>
        $(function () {

            $("#colorpicker").colorpicker();
        });
    </script>

    <!-- close form -->
    {!! html()->closeModelForm() !!}
    @stop