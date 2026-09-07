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

@section('storage')
class="nav-link active"
@stop

@section('PageHeader')
<h1>{{ Lang::get('storage::lang.storage')}}</h1>
@stop

@section('HeadInclude')
@stop
@section('content')

 @if (count($errors) > 0)
<div class="alert alert-danger">
    <strong>Whoops!</strong> There were some problems with your input.<br><br>
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if(Session::has('success'))
<div class="alert alert-success alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    {{Session::get('success')}}
</div>
@endif
<!-- fail message -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissable">
    <i class="fa fa-ban"></i>
    <b>{{Lang::get('message.alert')}}!</b> {{Lang::get('message.failed')}}.
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    {{Session::get('fails')}}
</div>
@endif

<div class="card card-light">

    <div class="card-header">
        <h3 class="card-title"> {{Lang::get('storage::lang.storage')}} </h3>
       
        {!! html()->form('POST', url('storage'))->open() !!}
    </div><!-- /.box-header -->
    <!-- /.box-header -->
    <div class="card-body">
        <div class="row">
            <div class="form-group col-md-8 {{ $errors->has('default') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('storage::lang.default'), 'default') !!}
                {!! html()->select('default', ['database'=>'Database','local'=>'Local'], $default)->class('form-control') !!}             
            </div>
            
            <div class="form-group col-md-6 {{ $errors->has('root') ? 'has-error' : '' }}" id="root" style="display: none;">
                {!! html()->label(Lang::get('storage::lang.root'), 'root') !!}
                {!! html()->select('root', $directories, $root)->class('form-control') !!}             
            </div>
            <div id="common" style="display: none;">
                <div class="form-group col-md-6 {{ $errors->has('key') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('storage::lang.key'), 'key') !!}
                    {!! html()->text('key', null)->class('form-control') !!}             
                </div>
                <div class="form-group col-md-6 {{ $errors->has('region') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('storage::lang.region'), 'region') !!}
                    {!! html()->text('region', null)->class('form-control') !!}             
                </div>
            </div>
            <div id="s3" style="display: none;">
                <div class="form-group col-md-6 {{ $errors->has('secret') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('storage::lang.secret'), 'secret') !!}
                    {!! html()->text('secret', null)->class('form-control') !!}             
                </div>
                <div class="form-group col-md-6 {{ $errors->has('bucket') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('storage::lang.bucket'), 'bucket') !!}
                    {!! html()->text('bucket', null)->class('form-control') !!}             
                </div>
            </div>
            <div id="rackspace" style="display: none;">
                <div class="form-group col-md-6 {{ $errors->has('username') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('storage::lang.username'), 'username') !!}
                    {!! html()->text('username', null)->class('form-control') !!}             
                </div>
                <div class="form-group col-md-6 {{ $errors->has('container') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('storage::lang.container'), 'container') !!}
                    {!! html()->text('container', null)->class('form-control') !!}             
                </div>
                <div class="form-group col-md-6 {{ $errors->has('endpoint') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('storage::lang.endpoint'), 'endpoint') !!}
                    {!! html()->text('endpoint', null)->class('form-control') !!}             
                </div>
                <div class="form-group col-md-6 {{ $errors->has('url_type') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('storage::lang.url_type'), 'url_type') !!}
                    {!! html()->text('url_type', null)->class('form-control') !!}             
                </div>
            </div>


        </div>
        <!-- /.box-body -->
    </div>
    <div class="card-footer">
        {!! html()->submit(Lang::get('storage::lang.save'))->class('btn btn-success') !!}
        {!! html()->closeModelForm() !!}
    </div>
    <!-- /.box -->
</div>
@stop
@section('FooterInclude')
<script>
    $(document).ready(function () {
        var defaults = $("#default").val();
        switches(defaults);
        $("#default").on("change", function () {
            defaults = $("#default").val();
            switches(defaults);
        });
        function switches(defaults) {
            if(defaults=="local"){
                $("#common").hide();
                $("#s3").hide();
                $("#rackspace").hide();
                $("#root").show();
            }
            if(defaults=="s3"){
               $("#root").hide();
               $("#rackspace").hide();
               $("#common").show();
               $("#s3").show();
            }
            if(defaults=="rackspace"){
               $("#root").hide();
                $("#s3").hide();
               $("#common").show();
               $("#rackspace").show();
            }
            if(defaults=="database"){
               $("#root").hide();
                $("#s3").hide();
               $("#common").hide();
               $("#rackspace").hide();
            }
            
        }
    });
</script>
@stop