
<div class="box-body" >
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
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
        {{Session::get('fails')}}
    </div>
    @endif

	<div class="row">

		<div class="col-4 mb-3 {{ $errors->has('name') ? 'has-error' : '' }}">

			{!! html()->label('Name', 'name') !!}
			{!! $errors->first('name', '<spam class="help-block">:message</spam>') !!}
			{!! html()->text('name', null)->class('form-control') !!}

		</div>

		{{--  --}}

		<div class="col-4 mb-3 {{ $errors->has('status') ? 'has-error' : '' }}">

			{!! html()->label('Status', 'status') !!}
			{!! $errors->first('status', '<spam class="help-block">:message</spam>') !!}
			<div class="row">
				<div class="col-3">
					{!! html()->radio('status', true, '1') !!}Active
				</div>
				<div class="col-3">
					{!! html()->radio('status', null, '0') !!}Inactive
				</div>
			</div>
		</div>

	</div>
		<div class="mb-3 {{ $errors->has('description') ? 'has-error' : '' }}">
				{!! html()->label('Description', 'description') !!}
				{!! $errors->first('description', '<spam class="help-block">:message</spam>') !!}

					{!! html()->textarea('description', null)->class('form-control')->id('myNicEditor')->placeholder('Enter the description')->attributes(['size' => '50x10']) !!}
		</div>
</div>

