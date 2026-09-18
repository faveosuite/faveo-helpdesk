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
<form action="{!!URL::route('priority.edit1')!!}" method="post" role="form">
{{ csrf_field() }}
    <input type="hidden" name="priority_id" value="{{$tk_priority->priority_id}}">
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
        @if($errors->first('ispublic'))
        <li class="error-message-padding">{!! $errors->first('ispublic', ':message') !!}</li>
        @endif
    </div>
    @endif
    <div class="card card-light">
        <div class="card-header">
            <h3 class="card-title">{{Lang::get('lang.edit')}}</h3>
        </div>
        <div class="card-body">
            
            <div class="row">

                 <div class="mb-3 col-md-6 {{ $errors->has('priority') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.priority'), 'priority') !!}<span class="text-red"> *</span>
                    <input type="text" class="form-control" name="priority" value="{{ ($tk_priority->priority) }}" >
                </div>

                <div class="mb-3 col-md-6 {{ $errors->has('priority_desc') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.priority_desc'), 'priority_desc') !!} <span class="text-red"> *</span>
                    <input type="text" class="form-control" name="priority_desc" value="{{ ($tk_priority->priority_desc) }}">
                </div>
            </div>
            <!-- Priority Color -->
            <div class="row">

                <div class="mb-3 col-sm-6 {{ $errors->has('priority_color') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.priority_color'), 'priority_color') !!}<span class="text-red"> *</span>
                    <input class="form-control my-colorpicker1 colorpicker-element" id="colorpicker" value="{{ ($tk_priority->priority_color) }}" type="text" name="priority_color">
                </div>

                <div class="mb-3 col-sm-3 {{ $errors->has('status') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.status'), 'status') !!}<span class="text-red"> *</span><br/>
                    <input type="radio"  name="status" value="1" {{$tk_priority->status == '1' ? 'checked' : ''}}>&nbsp;&nbsp;{{Lang::get('lang.active')}}&nbsp;&nbsp;
                    <input type="radio"  name="status"  value="0" {{$tk_priority->status == '0' ? 'checked' : ''}}>&nbsp;&nbsp;{{Lang::get('lang.inactive')}}
                </div>

                <div class="mb-3 col-sm-3 {{ $errors->has('ispublic') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.visibility'), 'visibility') !!}&nbsp;<span class="text-red"> *</span><br/>
                    <input type="radio"  name="ispublic" value="1" {{$tk_priority->ispublic == '1' ? 'checked' : ''}} >&nbsp;&nbsp;{{Lang::get('lang.public')}}&nbsp;&nbsp;
                    <input type="radio"  name="ispublic"  value="0" {{$tk_priority->ispublic == '0' ? 'checked' : ''}}>&nbsp;&nbsp;{{Lang::get('lang.private')}}
                </div>
            </div>  
            <!-- Admin Note : Textarea : -->
            <div>
                {!! html()->label(Lang::get('lang.admin_notes'), 'admin_note') !!}
                {!! html()->textarea('admin_note', null)->class('form-control')->attributes(['size' => '30x5']) !!}
            </div>

            <div>
                <input type="checkbox" name="default_priority" @if($tk_priority->is_default == $tk_priority->priority_id) checked disabled @endif> {{ Lang::get('lang.make-default-priority')}}
            </div>
        </div>
        <div class="card-footer">
            {!! html()->submit(Lang::get('lang.update'))->class('btn btn-primary') !!}
        </div>
    </div>
    <!-- close form -->
    {!! html()->closeModelForm() !!}
    <script>
        $(function () {

            $("#colorpicker").colorpicker();
        });
    </script>

    @stop
