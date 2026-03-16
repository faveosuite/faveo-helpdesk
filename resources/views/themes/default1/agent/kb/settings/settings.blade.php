@extends('themes.default1.agent.layout.agent')
@extends('themes.default1.agent.layout.sidebar')    

@section('Tools')
class="nav-link active"
@stop

@section('tool')
class="active"
@stop

@section('kb')
class="nav-link active"
@stop

@section('settings')
class="nav-link active"
@stop

@section('content')
<!-- open a form -->
{!! html()->modelForm($settings, 'PATCH', url('postsettings/'.$settings->id))->acceptsFiles()->open() !!}

<!-- check whether success or not -->
@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa  fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('success')}}
</div>
@endif
<!-- failure message -->
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! lang::get('lang.alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('fails')}}
</div>
@endif
@if(Session::has('errors'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <br/>
    @if($errors->first('pagination'))
    <li class="error-message-padding">{!! $errors->first('pagination', ':message') !!}</li>
    @endif         
</div>
@endif
<div class="card card-light">
    
    <div class="card-header">
        <h3 class="card-title">{{Lang::get('lang.kb-settings')}}</h3> 
    </div>
    
    <div class="card-body">
        
        <div class="row">
            <div class="col-md-3">
                {!! html()->label(Lang::get('lang.numberofelementstodisplay'), 'pagination') !!} <span class="text-red"> *</span>
                <input type="number" class="form-control" name='pagination' value="{!! $settings->pagination !!}" min="2" required>
            </div>
        </div>
    </div>

     <div class="card-footer">
        {!! html()->submit(Lang::get('lang.submit'))->class('btn btn-primary') !!}
    </div>
</div>
@stop
