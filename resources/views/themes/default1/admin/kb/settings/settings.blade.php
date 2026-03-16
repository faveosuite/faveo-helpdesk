@extends('themes.default1.admin.layout.kb')
@section('settings')
    class="active"
@stop
<script type="text/javascript" src="{{asset('dist/js/SetnicEdit.js')}}"></script>
<script type="text/javascript">
    bkLib.onDomLoaded(function() { nicEditors.allTextAreas() });
</script>
@section('content')
<!-- open a form -->
    {!! html()->modelForm($settings, 'PATCH', url('postsettings/'.$settings->id))->acceptsFiles()->open() !!}

            <div class="box-header">
                <h3 class="box-title">{{Lang::get('lang.settings')}}</h3>  {!! html()->submit(Lang::get('lang.save'))->class('mb-3 btn btn-primary pull-right') !!}
            </div>
            <div class="box-body">
              <!-- Custom Tabs -->
              <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                  <li class="active"><a href="#tab_1" data-bs-toggle="tab">{{Lang::get('lang.system')}}</a></li>
                  <li><a href="#tab_2" data-bs-toggle="tab">{{Lang::get('lang.smtp')}}</a></li>
                </ul>
                <div class="tab-content">
                  <div class="tab-pane active" id="tab_1">
                     {{-- For Form --}}
    <!-- check whether success or not -->
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
        <!-- Name text form Required -->
            <div class="row">
                <div class="col-md-3 mb-3 {{ $errors->has('company_name') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.companyname'), 'company_name') !!}
                    {!! $errors->first('company_name', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->text('company_name', $settings->company_name)->class('form-control') !!}
                </div>
                <div class="col-md-3 mb-3 {{ $errors->has('website') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.website'), 'website') !!}
                    {!! $errors->first('website', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->text('website', $settings->website)->class('form-control') !!}
                </div>
                <div class="col-md-3 mb-3 {{ $errors->has('phone') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.phone'), 'phone') !!}
                    {!! $errors->first('phone', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->text('phone', $settings->phone)->class('form-control') !!}
                </div>
                {{--  <div class="col-md-3 mb-3 {{ $errors->has('phone') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.language'), 'language') !!}
                        {!! html()->select('language', ['en'=>'English','ch'=>'Chinese'], null)->class('form-control select') !!}
                </div>
 --}}
                    <div class="col-md-12 mb-3 {{ $errors->has('address') ? 'has-error' : '' }}">
                {!! html()->label(Lang::get('lang.address'), 'address') !!}
                {!! $errors->first('address', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->textarea('address', null)->class('form-control')->id('address')->placeholder('Enter the address')->attributes(['size' => '128x10']) !!}
                </div>
                    <div class="col-md-3 mb-3">
                           {!! html()->label(Lang::get('lang.logo'), 'logo') !!}
                           {!! html()->file('logo') !!}
                        @if($settings->logo)
                           <img src="{{asset('lb-faveo/dist/image/'.$settings->logo)}}" />
                           <a href="{{url('delete-logo/'.$settings->id)}}">{{Lang::get('lang.delete')}}</a>
                        @endif
                    </div>
                    <div class="col-md-3 mb-3">
                        {!! html()->label(Lang::get('lang.numberofelementstodisplay'), 'pagination') !!}
                        {!! $errors->first('pagination', '<spam class="help-block">:message</spam>') !!}
                        {!! html()->text('pagination', $settings->pagination)->class('form-control') !!}
                    </div>
                    <div class="col-md-3 mb-3">
                        {!! html()->label(Lang::get('lang.timezone'), 'timezone') !!}
                        {!! html()->select('timezone', $time->pluck('location','location'), null)->class('form-control select') !!}
                    </div>
                     </div>
                  </div><!-- /.tab-pane -->
        <div class="tab-pane" id="tab_2">
            <div class="row">
                <div class="col-md-4 mb-3 {{ $errors->has('port') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.portnumber'), 'port') !!}
                    {!! $errors->first('port', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->text('port', $settings->port)->class('form-control') !!}
                </div>
                <div class="col-md-4 mb-3 {{ $errors->has('host') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.host'), 'host') !!}
                    {!! $errors->first('host', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->text('host', $settings->host)->class('form-control') !!}
                </div>
                <div class="col-md-4 mb-3 {{ $errors->has('encryption') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.encryption'), 'encryption') !!}
                    {!! $errors->first('encryption', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->text('encryption', $settings->encryption)->class('form-control') !!}
                </div>
                <div class="col-md-4 mb-3 {{ $errors->has('email') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.settingsemail'), 'email') !!}
                    {!! $errors->first('email', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->text('email', $settings->email)->class('form-control') !!}
                </div>
                <div class="col-md-4 mb-3 {{ $errors->has('password') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('lang.password'), 'password') !!}
                    {!! $errors->first('password', '<spam class="help-block">:message</spam>') !!}
                    {!! html()->password('password')->class('form-control') !!}
                </div>
                <div class="col-md-4 mb-3">
                        {!! html()->label(Lang::get('lang.dateformat'), 'dateformat') !!}
                        {!! html()->select('dateformat', $date->pluck('format','format'), null)->class('form-control select') !!}
                    </div>
            </div>
                  </div><!-- /.tab-pane -->
                </div><!-- /.tab-content -->
              </div><!-- nav-tabs-custom -->
            </div><!-- /.col -->
@stop
@section('FooterInclude')

@stop

<!-- /content -->
