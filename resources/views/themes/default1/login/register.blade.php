@extends('themes.default1.layouts.register')
@section('body')
	
	<!-- openn a form -->

          <body class="login-page">
    <div class="login-box">
      
      <div class="login-box-body">
        <p class="login-box-msg">Registration</p>
        <!-- form open -->
        {!! html()->form('POST', action('Auth\AuthController@postRegister'))->open() !!}
           
          <!-- fullname -->
           <div class="mb-3 has-feedback {{ $errors->has('full_name') ? 'has-error' : '' }}">
			
			{!! html()->text('full_name', null)->placeholder('Full Name')->class('form-control') !!}
			{!! $errors->first('full_name', '<spam class="help-block">:message</spam>') !!}
			<span class="glyphicon glyphicon-user form-control-feedback"></span>
          
          </div>

          <!-- Email -->
          <div class="mb-3 has-feedback {{ $errors->has('email') ? 'has-error' : '' }}">
			
			{!! html()->text('email', null)->placeholder('Email')->class('form-control') !!}
			{!! $errors->first('email', '<spam class="help-block">:message</spam>') !!}
			<span class="fa-regular fa-envelope text-muted form-control-feedback"></span>
          
          </div>


          <div class="mb-3 has-feedback {{ $errors->has('password') ? 'has-error' : '' }}">
            {!! html()->password('password')->placeholder('Password')->class('form-control') !!}
			{!! $errors->first('password', '<spam class="help-block">:message</spam>') !!}
            <span class="fa-solid fa-lock form-control-feedback"></span>
          </div>

          <div class="mb-3 has-feedback {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
            {!! html()->password('password_confirmation')->placeholder('Retype Password')->class('form-control') !!}
			{!! $errors->first('password_confirmation', '<spam class="help-block">:message</spam>') !!}
            <span class="fa-solid fa-right-to-bracket form-control-feedback"></span>
          </div>

          
          
          
          <div class="row">
            <div class="col-8">    
              <div class="checkbox icheck">
                <label>
                  <input type="checkbox"> I agree to the <a href="#">terms</a>
                </label>
              </div>                        
            </div><!-- /.col -->
            <div class="col-4">
              <button type="submit" class="btn btn-primary btn-block ">Register</button>
            </div><!-- /.col -->
          </div>

        
       
        <a href="{{url('auth/login')}}" class="text-center">I already have a membership</a>

      </div><!-- /.login-box-body -->
    </div><!-- /.login-box -->
    {!! html()->closeModelForm() !!}


        
@stop


