
<div class="box-body" >
@if(Session::has('success'))
    <div class="alert alert-success alert-dismissable">
        <i class="fa  fa-check-circle"></i>
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {{Session::get('success')}}
    </div>
    @endif
    <!-- failure message -->
    @if(Session::has('fails'))
    <div class="alert alert-danger alert-dismissable">
        <i class="fa fa-ban"></i>
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {{Session::get('fails')}}
    </div>
    @endif

	<div class="row">

		<div class="col-xs-4 form-group {{ $errors->has('name') ? 'has-error' : '' }}">

			{!! html()->label('Name', 'name') !!}
			{!! $errors->first('name', '<spam class="help-block">:message</spam>') !!}
			{!! html()->text('name', null)->class('form-control') !!}

		</div>

		{{--  --}}

		<div class="col-xs-4 form-group {{ $errors->has('status') ? 'has-error' : '' }}">

			{!! html()->label('Status', 'status') !!}
			{!! $errors->first('status', '<spam class="help-block">:message</spam>') !!}
			<div class="row">
				<div class="col-xs-3">
					{!! html()->radio('status', true, '1') !!}Active
				</div>
				<div class="col-xs-3">
					{!! html()->radio('status', null, '0') !!}Inactive
				</div>
			</div>
		</div>

	</div>
		<div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
				{!! html()->label('Description', 'description') !!}
				{!! $errors->first('description', '<spam class="help-block">:message</spam>') !!}

					{!! html()->textarea('description', null)->class('form-control')->id('myNicEditor')->placeholder('Enter the description')->attributes(['size' => '50x10']) !!}
		</div>
</div>

