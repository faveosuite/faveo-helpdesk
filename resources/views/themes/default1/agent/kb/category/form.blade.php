
<div>
	@if(Session::has('success'))
	<div class="alert alert-success alert-dismissible">
	  <i class="fa-solid fa-circle-check"></i>
	  <b>Success</b>
	  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
	  {{Session::get('success')}}
	</div>
	@endif
	<!-- failure message -->
	@if(Session::has('fails'))
	<div class="alert alert-danger alert-dismissible">
	  <i class="fa-solid fa-ban"></i>
	  <b>Fail!</b>
	  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
	  {{Session::get('fails')}}
	</div>
	@endif

	<div class="row">

		<div class="col-sm-7 mb-3 {{ $errors->has('name') ? 'has-error' : '' }}">

			{!! html()->label(Lang::get('lang.name'), 'name') !!}
			{!! $errors->first('name', '<spam class="help-block">:message</spam>') !!}
			{!! html()->text('name', null)->class('form-control') !!}
		</div>

		<div class="col-sm-5 mb-3 {{ $errors->has('status') ? 'has-error' : '' }}">

			{!! html()->label(Lang::get('lang.status'), 'status') !!}
			{!! $errors->first('status', '<spam class="help-block">:message</spam>') !!}
			
            <div>
                <label class="fw-normal me-3">
                    {!! html()->radio('status', true, '1')->id('modal-status-active') !!} {!! Lang::get('lang.active') !!}
                </label>
                <label class="fw-normal me-3">
                    {!! html()->radio('status', null, '0')->id('modal-status-inactive') !!} {!! Lang::get('lang.inactive') !!}
                </label>
            </div>
	</div>

	<div class="mb-3 col-sm-12 {{ $errors->has('description') ? 'has-error' : '' }}">
		{!! html()->label(Lang::get('lang.description'), 'description') !!}
		{!! $errors->first('description', '<spam class="help-block">:message</spam>') !!}

		{!! html()->textarea('description', null)->class('form-control')->id('myNicEditor')->placeholder(Lang::get('lang.enter_the_description'))->attributes(['size' => '50x10']) !!}
	</div>
</div>

