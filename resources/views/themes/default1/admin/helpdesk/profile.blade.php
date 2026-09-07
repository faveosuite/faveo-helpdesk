@extends('themes.default1.admin.layout.admin')

@section('content')

<div class="box box-primary">

	<div class="app-content-header">

	 	<h4>Profile</h4>

	</div>

	<div class="box-body">

					@if(Session::has('success'))
                    <div class="alert alert-success alert-dismissible">
                        <i class="fa-solid fa-ban"></i>
                        <b>Alert!</b> Success.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                        {{Session::get('success')}}
                    </div>
                    @endif
                    <!-- fail message -->
                    @if(Session::has('fails'))
                    <div class="alert alert-danger alert-dismissible">
                        <i class="fa-solid fa-ban"></i>
                        <b>Alert!</b> Failed.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                        {{Session::get('fails')}}
                    </div>
                    @endif


		<div class="row">
	        <div class="col-md-3">
	        	<img src="{{asset("dist/img/user8-128x128.jpg")}}" width="250px; "/>
	        </div>
	        <div class="col-md-6">
	        <a href="{{url('agent-profile-edit')}}"><i class="fa-solid fa-fw fa-pen-to-square"> </i></a>
	        	<h3><b>User Information</b></h3>
	        	<h2>{{ $user->user_name }}</h2>
	        	<h4>{{ $user->primary_dpt }}</h4>
	        	<h4>{{ $user->assign_group }}</h4>
	        	<h4>{{ $user->ext }}{{ $user->phone_number }}</h4>
				<h3><b>Contact Information</b></h3>
	        	<h4>{{ $user->mobile }}</h4>
	        	<h4>{{ $user->company }}</h4>
	        	<h4>{{ $user->agent_tzone }}</h4>
	        	<h4>{{ $user->role }}</h4>
	        </div>
        </div>
	</div>
</div>

@stop
