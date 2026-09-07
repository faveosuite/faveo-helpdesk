@extends('themes.default1.admin.layout.kb')

@section('pages')
    active
@stop
@section('add-pages')
    class="active"
@stop
<script type="text/javascript" src="{{asset('lb-faveo/dist/js/nicEdit.js')}}"></script>
<script type="text/javascript">
    bkLib.onDomLoaded(function() { nicEditors.allTextAreas() });
</script>
@section('content')
{!! html()->form('POST', action('Admin\kb\PageController@store'))->open() !!}


    <div class="box-body">
    <div class="row">
    
    <div class="col-md-9">
    <div class="box box-primary">
    <div class="box-header">  
        <h3 class="box-title">Add Pages</h3>
    </div>
    <div class="box-body">  
    <div class="row">
        <div class="col-md-6 mb-3 {{ $errors->has('name') ? 'has-error' : '' }}">

            {!! html()->label(Lang::get('lang.name'), 'name') !!}
            {!! $errors->first('name', '<spam class="help-block">:message</spam>') !!}
            {!! html()->text('name', null)->class('form-control') !!}

        </div>

        <div class="col-md-6 mb-3 {{ $errors->has('slug') ? 'has-error' : '' }}">

            {!! html()->label(Lang::get('lang.slug'), 'slug') !!}
            {!! $errors->first('slug', '<spam class="help-block">:message</spam>') !!}
            {!! html()->text('slug', null)->class('form-control') !!}

        </div>
    </div>


                <div class="mb-3 {{ $errors->has('description') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.description'), 'description') !!}
                    {!! $errors->first('description', '<spam class="help-block">:message</spam>') !!}

                    <div class="mb-3" style="background-color:white">
                    {!! html()->textarea('description', null)->class('form-control color')->id('myNicEditor')->placeholder('Enter the description')->attributes(['size' => '110x15']) !!}
                </div>
                </div>

            </div>
            </div>
        </div>

            <div class="col-md-3">
    <div class="box box-default">
    <div class="box-header with-border">
                  <h3 class="box-title">{{Lang::get('lang.publish')}}</h3>
    </div>
                <div class="box-body">
                    <div class="mb-3 {{ $errors->has('status') ? 'has-error' : '' }}">

                        {!! html()->label(Lang::get('lang.status'), 'status') !!}
                        {!! $errors->first('status', '<spam class="help-block">:message</spam>') !!}
                        <div class="row">
                            <div class="col-4">
                                {!! html()->radio('status', true, '1') !!}{{Lang::get('lang.published')}}
                            </div>
                            <div class="col-3">
                                {!! html()->radio('status', null, '0') !!}{{Lang::get('lang.draft')}}
                            </div>
                        </div>
                    </div>


                    <div class="mb-3 {{ $errors->has('visibility') ? 'has-error' : '' }}">

                        {!! html()->label(Lang::get('lang.visibility'), 'visibility') !!}
                        {!! $errors->first('visibility', '<spam class="help-block">:message</spam>') !!}
                        <div class="row">
                            <div class="col-3">
                                {!! html()->radio('visibility', true, '1') !!}{{Lang::get('lang.public')}}
                                </div>
                                <div class="row">
                            <div class="col-3">
                                {!! html()->radio('visibility', null, '0') !!}{{Lang::get('lang.private')}}
                                </div>
                    </div>

                </div>

            </div>
       </div>

        <div class="box-footer" style="background-color:#f5f5f5;">
        <div style="margin-left:140px;">

                {!! html()->submit(Lang::get('lang.publish'))->class('btn btn-primary') !!}
        </div>

        </div>

    </div>
</div>
</div>
</div>
@stop
@section('FooterInclude')

@stop

<!-- /content -->
