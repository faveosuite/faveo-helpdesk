@extends('themes.default1.admin.layout.admin')

@section('Tickets')
active
@stop

@section('manage-bar')
active
@stop

@section('labels')
class="active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>Labels</h3>
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

@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa-solid fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('success')}}
</div>
@endif
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('fails')}}
</div>
@endif
@if(Session::has('errors'))
        <br><br>
        <div class="alert alert-danger alert-dismissible">
            <i class="fa-solid fa-ban"></i>
            <b>{!! Lang::get('lang.alert') !!}!</b>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
            <br/>
            @if($errors->first('title'))
            <li class="error-message-padding">{!! $errors->first('title', ':message') !!}</li>
            @endif
            @if($errors->first('color'))
            <li class="error-message-padding">{!! $errors->first('color', ':message') !!}</li>
            @endif
            @if($errors->first('order'))
            <li class="error-message-padding">{!! $errors->first('order', ':message') !!}</li>
            @endif
        </div>
        @endif
@if(Session::has('warn'))
<div class="alert alert-warning alert-dismissible">
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('warn')}}
</div>
@endif
<div class="box">
    <link rel="stylesheet" href="{{asset('lb-faveo/plugins/colorpicker/bootstrap-colorpicker.min.css')}}">
    <div class="box-header">
        <div class="box-title">
            {!! $label->titleWithColor() !!}
        </div>
        {!! html()->modelForm($label, 'PATCH', url('labels/'.$label->id))->attributes(['id' => 'label-form'])->open() !!}
    </div>
    <div class="box-body">
        <table class="table table-borderless">
            
           <tr>
                <div class="mb-3 {{ $errors->has('title') ? 'has-error' : '' }}">
                <td>{!! html()->label('Title', 'title') !!}<span class="text-red"> *</span></td>
                <td>
                    <div class="mb-3 {{ $errors->has('title') ? 'has-error' : '' }}">
                        {!! html()->text('title', null)->class('form-control') !!}
                    </div>
                </td>
                </div>
            </tr>
             <tr>
                <td>{!! html()->label('Color', 'color') !!}<span class="text-red"> *</span></td>
                <td>
                    <div class="mb-3 {{ $errors->has('color') ? 'has-error' : '' }}">
                    {!! html()->text('color', null)->class('form-control my-colorpicker1 colorpicker-element') !!}
                    </div>
                </td>
            </tr>
            
             <tr>
                <td>{!! html()->label('Order', 'order') !!}<span class="text-red"> *</span></td>
                <td>
                    <div class="mb-3 {{ $errors->has('order') ? 'has-error' : '' }}">
                    {!! html()->number('order', null)->class('form-control') !!}
                    </div>
                </td>
            </tr>
            
             <tr>
                <td>{!! html()->label('Status', 'status') !!}</td>
                <td><p>{!! html()->checkbox('status') !!}  {!!Lang::get('lang.enable')!!}</p></td>
            </tr>
            
        </table>
    </div>
    <div class="box-footer">
        {!! html()->submit('Save')->class('btn btn-success') !!}
        {!! html()->closeModelForm() !!}
    </div>
</div>
@stop
@section('FooterInclude')
<script src="{{asset('lb-faveo/plugins/colorpicker/bootstrap-colorpicker.min.js')}}"></script>
<script>
//Colorpicker
    $(".my-colorpicker1").colorpicker();
    //color picker with addon
    $(".my-colorpicker2").colorpicker();
</script>
<script type="text/javascript">
    $("#label-form").on('submit', function(e){
        if(document.getElementById('status').checked) {
            checked = 1;
        } else {
            checked = 0;
        }
        $('<input />')
          .attr('type', 'hidden')
          .attr('name', "status")
          .attr('value', checked)
          .appendTo('#label-form');
    })
</script>
@stop