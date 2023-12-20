@extends('themes.default1.admin.layout.admin')

@section('Settings')
class="active"
@stop

@section('settings-bar')
active
@stop

@section('access')
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

	{!! html()->modelForm($accesses, 'PATCH', url('postaccess/'.$accesses->id))->open() !!}


	<div class="row">
		<div class="col-md-12">
			<div class="box box-primary">
				<div class="box-header">
                <h3 class="box-title">{{Lang::get('lang.access')}}</h3> <div class="pull-right">
                {!! html()->submit(Lang::get('lang.save'))->class('btn btn-primary') !!}
              </div>
            </div>


<!-- check whether success or not -->

@if(Session::has('success'))
    <div class="alert alert-success alert-dismissible">
        <i class="fa  fa-circle-check"></i>
        <b>Success!</b>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
        {!!Session::get('success')!!}
    </div>
    @endif
    <!-- failure message -->
    @if(Session::has('fails'))
    <div class="alert alert-danger alert-dismissible">
        <i class="fa-solid fa-ban"></i>
        <b>Fail!</b>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
        {!!Session::get('fails')!!}
    </div>
    @endif


		<!-- Password Expiration Policy: DROPDOWN	  -->
          <div class="box-body table-responsive"style="overflow:hidden;">
             <div class="row">
               <div class="col-md-4">
               <div class="mb-3">
                {!! html()->label(Lang::get('lang.expiration_policy'), 'password_expire') !!}
				{!! html()->select('password_expire', ['1 month','2 month','3 month'], null)->class('form-control select') !!}

			</div>
		</div>



		<!-- Reset Token Expiration: TEXT- minutes    -->
			<div class="col-md-4">
             <div class="mb-3">
				{!! html()->label(Lang::get('lang.reset_token_expiration'), 'reset_ticket_expire') !!}
				{!! html()->text('reset_ticket_expire', $accesses->reset_ticket_expire)->class('form-control') !!}

			</div>
			</div>

		<!-- Agent Excessive Logins:	TEXT failed login attempt(s) allowed before a lock-out is enforced

		 								TEXT minutes locked out -->

		 		<!-- *************************    TODO    ************************** -->



		<!-- Agent Session Timeout: TEXT - minutes (0 to disable).  -->


			<div class="col-md-4">
			    <div class="mb-3">
				{!! html()->label(Lang::get('lang.agent_session_timeout'), 'agent_session') !!}
				{!! html()->text('agent_session', $accesses->agent_session)->class('form-control') !!}

			</div>
			</div>
			</div>
			<!-- Allow Password Resets:	 CHECKBOX  -->
			<div class="row">
			<div class="col-md-4">
				<div class="mb-3">
				{!! html()->label(Lang::get('lang.allow_password_resets'), 'password_reset') !!}
				{!! html()->checkbox('password_reset', null, 1) !!}

			</div>
			</div>
            </div>

		<!-- Registration Method:	DROPDOWN  -->

			<div class="row">
			<div class="col-md-6">
                <div class="mb-3">
				{!! html()->label(Lang::get('lang.registration_method'), 'reg_method') !!}
				{!! html()->select('reg_method', ['public','private','dissabled'], null)->class('form-control select') !!}

			</div>
			</div>


		<!-- User Excessive Logins:	TEXT failed login attempt(s) allowed before a lock-out is enforced

								TEXT	minutes locked out -->

		<!--*************************************    TODO   ******************************************  -->



		<!-- User Session Timeout:	TEXT  -->


			<div class="col-md-6">
                 <div class="mb-3">
				{!! html()->label(Lang::get('lang.user_session_timeout'), 'user_session') !!}
				{!! html()->text('user_session', $accesses->user_session)->class('form-control') !!}

			</div>
			</div>
			</div>
			        <!-- Bind Agent Session to IP:	CHECKBOX  -->

			<div class="row">
			<div class="col-md-4">
				<div class="mb-3">
				{!! html()->checkbox('bind_agent_ip', true, 1) !!} &nbsp;
				{!! html()->label(Lang::get('lang.bind_agent_session_IP'), 'bind_agent_ip') !!}


			</div>
			</div>
			</div>
			<!-- Registration Required:	CHECKBOX- Require registration and login to create tickets  -->

			<div class="row">
			<div class="col-md-4">
                  <div class="mb-3">
                  {!! html()->checkbox('reg_require', true, 1)->class('form-control') !!}&nbsp;
				{!! html()->label(Lang::get('lang.registration_required'), 'reg_require') !!}


			</div>
			</div>
          </div>


		<!-- Client Quick Access: CHECKBOX -->

			<div class="row">
			<div class="col-md-4">
			<div class="mb-3">
			{!! html()->checkbox('quick_access', true, 1) !!}&nbsp;
				{!! html()->label(Lang::get('lang.client_quick_access'), 'quick_access') !!}


			</div>
			</div>
          </div>



		</div>
		</div>
	</div>
	</div>

@stop
