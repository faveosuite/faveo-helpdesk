@extends('themes.default1.layouts.login')
@section('body')
<div class="login-box-body">
        <p class="login-box-msg">Sign in to start your session</p>
        <form action="../../index2.html" method="post">
          <div class="mb-3 has-feedback">
          <!-- email -->
            <input type="text" class="form-control" placeholder="Email"/>
            <span class="fa-solid fa-envelope text-muted form-control-feedback"></span>
          </div>
          <div class="mb-3 has-feedback">
          <!-- password -->
            <input type="password" class="form-control" placeholder="Password"/>
            <span class="fa  fa-lock form-control-feedback"></span>
          </div>
          <div class="row">
            <div class="col-8">
              <div class="checkbox icheck">
                <label>
                  <input type="checkbox"> Remember Me
                </label>
              </div>
            </div><!-- /.col -->
            <div class="col-4">
              <button type="submit" class="btn btn-primary btn-block ">Sign In</button>
            </div><!-- /.col -->
          </div>
        </form>

        <div class="social-auth-links text-center">
          <p>- OR -</p>
          <a href="#" class="btn btn-block btn-social btn-facebook "><i class="fa-solid fa-facebook"></i> Sign in using Facebook</a>
          <a href="#" class="btn btn-block btn-social btn-google-plus "><i class="fa-solid fa-google-plus"></i> Sign in using Google+</a>
        </div><!-- /.social-auth-links -->

        <a href="#">I forgot my password</a><br>
        <a href="register.html" class="text-center">Register a new membership</a>

      </div>
@stop