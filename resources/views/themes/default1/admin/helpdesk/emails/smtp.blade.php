@extends('themes.default1.admin.layout.admin')

@section('Emails')
class="active"
@stop

@section('emails-bar')
active
@stop

@section('smtp')
class="active"
@stop

@section('HeadInclude')
@stop

<!-- /breadcrumbs -->
<!-- content -->
@section('content')
{!! html()->modelForm($settings, 'PATCH', url('post-smtp'))->open() !!}
<div class="box box-primary">
    <div class="box-header">
        <h3 class="box-title">{!! Lang::get('lang.outgoing_emails') !!}</h3>
    </div>
    <!-- Ban Status : Radio form : Required -->
    <div class="box-body">
        @if(Session::has('success'))
        <div class="alert alert-success alert-dismissible">
            <i class="fa  fa-circle-check"></i>
            <b>Success!</b>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
            {{Session::get('success')}}
        </div>
        @endif
        <!-- failure message -->
        @if(Session::has('fails'))
        <div class="alert alert-danger alert-dismissible">
            <i class="fa-solid fa-ban"></i>
            <b>Alert!</b> Failed.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
            {{Session::get('fails')}}
        </div>
        @endif
        <div class="row">
            <!-- email Address : Text form : Required -->
            <div class="col-md-3 mb-3 {{ $errors->has('driver') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.driver'), 'driver') !!}
                {!! $errors->first('driver', '<spam class="help-block">:message</spam>') !!}
                <select name="driver" class="form-control">
                    <option <?php if ($settings->driver == "mail") {
    echo "selected='selected'";
} ?> value="mail">mail</option>
                    <option <?php if ($settings->driver == "smtp") {
    echo "selected='selected'";
} ?>  value="smtp">smtp</option>
                </select>
            </div>

            <div class="col-md-3 mb-3 {{ $errors->has('host') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.host'), 'host') !!}
                {!! $errors->first('host', '<spam class="help-block">:message</spam>') !!}
                {!! html()->text('host', null)->class('form-control') !!}
            </div>

            <div class="col-md-3 mb-3 {{ $errors->has('port') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.port'), 'port') !!}
                {!! $errors->first('port', '<spam class="help-block">:message</spam>') !!}
                {!! html()->text('port', null)->class('form-control') !!}
            </div>

            <div class="col-md-3 mb-3 {{ $errors->has('encryption') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.encryption'), 'encryption') !!}
                {!! $errors->first('encryption', '<spam class="help-block">:message</spam>') !!}
                <select name="encryption" class="form-control">
                    <option <?php if ($settings->encryption == "ssl") {
    echo "selected='selected'";
} ?>  value="ssl">SSL</option>
                    <option <?php if ($settings->encryption == "tls") {
    echo "selected='selected'";
} ?> value="tls">TLS</option>
                </select>
            </div>

            <div class="col-md-4 mb-3 {{ $errors->has('name') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.name'), 'name') !!}
                {!! $errors->first('name', '<spam class="help-block">:message</spam>') !!}
                {!! html()->text('name', null)->class('form-control') !!}
            </div>

            <div class="col-md-4 mb-3 {{ $errors->has('email') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.email'), 'email') !!}
                {!! $errors->first('email', '<spam class="help-block">:message</spam>') !!}
                {!! html()->text('email', null)->class('form-control') !!}
            </div>

            <div class="col-md-4 mb-3 {{ $errors->has('password') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.password'), 'password') !!}
                {!! $errors->first('password', '<spam class="help-block">:message</spam>') !!}
                @if($settings->password)
                <input type="password" name="password" class="form-control" value="{!! Crypt::decrypt($settings->password) !!}">
                @else
                <input type="password" name="password" class="form-control">
                @endif
            </div>
        </div>

    </div>
</div>
@stop