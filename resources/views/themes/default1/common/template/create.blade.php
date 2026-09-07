@extends('themes.default1.admin.layout.admin')
@section('content')
<div class="box box-primary">

    <div class="app-content-header">
        {!! html()->form('POST', route('templates.store'))->open() !!}
        <h4>{{Lang::get('lang.templates')}}	{!! html()->submit(Lang::get('lang.save'))->class('mb-3 btn btn-primary pull-right') !!}</h4>

    </div>

    <div class="box-body">

        <div class="row">

            <div class="col-md-12">

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
                <div class="alert alert-success alert-dismissible">
                    <i class="fa-solid fa-ban"></i>
                    <b>{{Lang::get('lang.alert')}}!</b> {{Lang::get('lang.success')}}.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                    {{Session::get('success')}}
                </div>
                @endif
                <!-- fail lang -->
                @if(Session::has('fails'))
                <div class="alert alert-danger alert-dismissible">
                    <i class="fa-solid fa-ban"></i>
                    <b>{{Lang::get('lang.alert')}}!</b> {{Lang::get('lang.failed')}}.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                    {{Session::get('fails')}}
                </div>
                @endif

                <div class="row">

                    <div class="col-md-6 mb-3 {{ $errors->has('name') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! html()->label(Lang::get('lang.name'), 'name')->class('required') !!}
                        {!! html()->text('name', null)->class('form-control') !!}

                    </div>

                    <div class="col-md-6 mb-3 {{ $errors->has('type') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! html()->label(Lang::get('lang.template-types'), 'type')->class('required') !!}
                        {!! html()->select('type', [''=>'Select','Type'=>$type], null)->class('form-control') !!}

                    </div>
                                        

                </div>
<!--                <div class="row">
                    <div class="col-md-12 mb-3 {{ $errors->has('subject') ? 'has-error' : '' }}">
         
                        {!! html()->label(Lang::get('lang.subject'), 'subject') !!}
                        {!! html()->text('subject', null)->class('form-control') !!}

                    </div>
                </div>-->

                <div class="row">
                    <div class="col-md-12 mb-3 {{ $errors->has('message') ? 'has-error' : '' }}">
                       
                        
                        {!! html()->label(Lang::get('lang.content'), 'message')->class('required') !!}
                        {!! html()->textarea('message', null)->class('form-control')->id('textarea') !!}
                       
                    </div>


                </div>

            </div>

        </div>

    </div>

</div>


{!! html()->closeModelForm() !!}
@stop