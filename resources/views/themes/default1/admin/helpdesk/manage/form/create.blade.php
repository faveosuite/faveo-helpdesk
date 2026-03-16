@extends('themes.default1.admin.layout.admin')

@section('Manage')
active
@stop

@section('manage-bar')
active
@stop

@section('forms')
class="active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')

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
{!! html()->form('POST', action('Admin\helpdesk\FormController@store'))->open() !!}
<div class="box box-primary">
    <div class="box-header">
        
        <h2 class="box-title"style="margin-left:-10px">{{Lang::get('lang.create')}}</h2>{!! html()->submit(Lang::get('lang.save'))->class('pull-right btn btn-primary') !!}
    </div>
    <div class="box-body">
        
        <!-- title: text -->
        <div class="box-body table-responsive no-padding"style="overflow:hidden">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3 {{ $errors->has('title') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('lang.title'), 'title') !!}
                        {!! $errors->first('title', '<spam class="help-block">:message</spam>') !!}
                        {!! html()->text('title', null)->class('form-control') !!}
                    </div>
                </div>
                <!-- declare table head Label -->
                <div class="col-md-6">
                    <div class="mb-3 {{ $errors->has('label') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('lang.label'), 'label') !!}
                        {!! $errors->first('label', '<spam class="help-block">:message</spam>') !!}
                        {!! html()->text('label', null)->class('form-control') !!}
                    </div>
                </div>
                <!-- declare table head type -->
                <div class="col-md-4">
                    <div class="mb-3 {{ $errors->has('type') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('lang.type'), 'type') !!}
                        {!! $errors->first('type', '<spam class="help-block">:message</spam>') !!}
                        {!! html()->select('type', [''=>'Select a Type','types'=>$type->pluck('type','id')], null)->class('form-control') !!}
                    </div>
                </div>
                <!-- declare table head Vissibility -->
                <div class="col-md-4">
                    <div class="mb-3 {{ $errors->has('visibility') ? 'has-error' : '' }}">
                        {!! html()->label(Lang::get('lang.visibility'), 'visibility') !!}
                        {!! $errors->first('visibility', '<spam class="help-block">:message</spam>') !!}
                        {!! html()->select('visibility', [''=>'Select a Visibility','visibilities' =>$visibility->pluck('visibility','id')], null)->class('form-control') !!}
                    </div>
                </div>
                <!-- declare table head variable -->
                <div class="col-md-4">
                    <div class="mb-3">
                        {!! html()->label(Lang::get('lang.variable'), 'variable') !!}
                        {!! html()->text('variable', null)->class('form-control') !!}
                    </div>
                </div>
                <!-- instruction: textarea -->
                <div class="col-md-6">
                    <div class="mb-3">
                        {!! html()->label(Lang::get('lang.instruction'), 'instruction') !!}
                        {!! html()->textarea('instruction', null)->class('form-control')->attributes(['size' => '10x5']) !!}
                    </div>
                </div>

                <!-- /table -->

                <!-- txt area -->
                <div class="col-md-6">
                    <div class="mb-3">
                        {!! html()->label(Lang::get('lang.internal_notes'), 'internal_notes') !!}
                        {!! html()->textarea('internal_notes', null)->class('form-control')->attributes(['size' => '10x5']) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
@stop
