@extends('themes.default1.layouts.login')
@section('body')

<body class="login-page">
    <div class="login-box">
      
      <div class="login-box-body">
        <p class="login-box-msg">Sign in to start your session</p>
        <!-- form open -->
        {!! html()->form('POST', route('post.login'))->open() !!}
          <!-- Email -->
          <div class="mb-3 has-feedback {{ $errors->has('email') ? 'has-error' : '' }}">
			
			{!! html()->text('email', null)->placeholder('Email')->class('form-control') !!}
			{!! $errors->first('email', '<spam class="help-block">:message</spam>') !!}
			<span class="fa-regular fa-envelope text-muted form-control-feedback"></span>
          
          </div>

          <div class="mb-3 has-feedback {{ $errors->has('password') ? 'has-error' : '' }}">
            {!! html()->password('password')->placeholder('Password')->class('form-control') !!}
			{!! $errors->first('password', '<spam class="help-block">:message</spam>') !!}
            <span class="fa-solid fa-lock text-muted form-control-feedback"></span>
          </div>
          <div class="row">
            <div class="col-8">    
              <div class="checkbox icheck">
                <label>
                  <input type="checkbox" name="remember"> Remember Me
                </label>
              </div>                        
            </div><!-- /.col -->
            <div class="col-4">
              <button type="submit" class="btn btn-primary btn-block ">Sign In</button>
            </div><!-- /.col -->
          </div>
        </form>

        
        <a href="{{url('password/email')}}">I forgot my password</a><br>
        <a href="{{url('auth/register')}}" class="text-center">Register a new membership</a>

      </div><!-- /.login-box-body -->
    </div><!-- /.login-box -->

@stop
