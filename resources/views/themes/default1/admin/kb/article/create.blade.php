@extends('themes.default1.agent.layout.kb')

@section('article')
    active
@stop
@section('add-article')
    class="active"
@stop
        <script type="text/javascript" src="{{asset('lb-faveo/dist/js/nicEdit.js')}}"></script>
        <script type="text/javascript">
            bkLib.onDomLoaded(function() { nicEditors.allTextAreas() });
        </script>
@section('content')
{!! html()->form('POST', action('Admin\kb\ArticleController@store'))->open() !!}

<div class="row">
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
    @if(!$category)
    <div class="alert alert-warning alert-dismissible">
        <i class="fa-solid fa-info"></i>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
        Create a Category 
    </div>
    @endif
    	
		<div class="box-body">
			<div class="col-md-9">
			<div class="box box-primary">
			<div class="box-header">
	 			<h4 class="box-title">Add Article</h4>
			</div>
			<div class="box-body">
			<div class="row">

				<div class="col-md-6 mb-3 {{ $errors->has('name') ? 'has-error' : '' }}" >

					{!! html()->label(Lang::get('lang.name'), 'name') !!}
					{!! $errors->first('name', '<spam class="help-block">:message</spam>') !!}
					{!! html()->text('name', null)->class('form-control') !!}
				</div>

				<div class="col-md-6 mb-3 {{ $errors->has('slug') ? 'has-error' : '' }}" >

					{!! html()->label(Lang::get('lang.slug'), 'slug') !!}
					{!! $errors->first('slug', '<spam class="help-block">:message</spam>') !!}
					{!! html()->text('slug', null)->class('form-control') !!}
				</div>
			</div>

				<div class="mb-3 {{ $errors->has('description') ? 'has-error' : '' }}">
				{!! html()->label(Lang::get('lang.description'), 'description') !!}
				{!! $errors->first('description', '<spam class="help-block">:message</spam>') !!}
				<div class="mb-3" style="background-color:white">
					{!! html()->textarea('description', null)->class('form-control color')->id('myNicEditor')->placeholder('Enter the description')->attributes(['size' => '128x20']) !!}
				</div>
				</div>
			</div>
			</div>

		</div>

	<ul style="list-style-type:none;">
	<li>
	<div class="col-md-3">
	<div class="box box-default">
	<div class="box-header with-border">
                  <h3 class="box-title">{{Lang::get('lang.publish')}}</h3>
	</div>
				<div class="box-body">
					<div class="mb-3 {{ $errors->has('type') ? 'has-error' : '' }}">

						{!! html()->label(Lang::get('lang.status'), 'type') !!}
						{!! $errors->first('type', '<spam class="help-block">:message</spam>') !!}
						<div class="row">
							<div class="col-4">
								{!! html()->radio('type', true, '1') !!}{{Lang::get('lang.published')}}
							</div>
							<div class="col-3">
								{!! html()->radio('type', null, '0') !!}{{Lang::get('lang.draft')}}
							</div>
						</div>
					</div>


					<div class="mb-3 {{ $errors->has('status') ? 'has-error' : '' }}">

						{!! html()->label(Lang::get('lang.visibility'), 'status') !!}
						{!! $errors->first('status', '<spam class="help-block">:message</spam>') !!}
						<div class="row">
							<div class="col-3">
								{!! html()->radio('status', true, '1') !!}{{Lang::get('lang.public')}}
								</div>
								<div class="row">
							<div class="col-3">
								{!! html()->radio('status', null, '0') !!}{{Lang::get('lang.private')}}
								</div>
					</div>

				</div>

			</div>
		</div>
		<div class="box-footer" style="background-color:#f5f5f5;">
		<div style="margin-left:140px;">

				{!! html()->submit(Lang::get('lang.publish'))->class('btn btn-primary') !!}
		</div>

		</div>

</li>
<li>
<div class="col-md-3">
	<div class="box box-default">
				<div class="box-header with-border">
                  <h3 class="box-title">{{Lang::get('lang.category')}}</h3>
                </div>
			<div class="box-body" style="height:190px; overflow-y:auto;">

				<div class="mb-3 {{ $errors->has('category_id') ? 'has-error' : '' }}">
		{{-- {!! html()->label('Category', 'category_id') !!} --}}
				{!! $errors->first('category_id', '<spam class="help-block">:message</spam>') !!}
					@while (list($key, $val) = each($category))
					<div class="row">
						<div class="mb-3">
							<div class="col-md-1">
								<input type="checkbox" name="category_id[]" value="<?php echo $val;?>">
							</div>
							<div class="col-md-10">
								<?php echo $key;?>
							</div>
						</div>
					</div>
					@endwhile

				</div>
		</div>
		{!! html()->closeModelForm() !!}
		<div class="box-footer" style="background-color:#f5f5f5;">

				<span class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#j">{{Lang::get('lang.addcategory')}}</span>
				<div class="modal" id="j">
                    <div class="modal-dialog">
                        <div class="modal-content">
                        {!! html()->form('POST', action('Admin\kb\CategoryController@store'))->open() !!}
                            <div class="modal-header">
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h5 class="modal-title">{{Lang::get('lang.addcategory')}}</h4>
                            </div>
                            <div class="modal-body">
                               	@include('themes.default1.admin.kb.category.form')
                            </div>
                            <div class="modal-footer">
                              	<div class="mb-3">
                                    {!! html()->submit('Add') !!}
                                </div>
                            	<button type="button" class="btn btn-secondary pull-left" data-bs-dismiss="modal">Close</button>
                            </div>
                        {!! html()->closeModelForm() !!}
                      	</div>
                     </div>
                    </div>
		</div>
	</div>
</div>
</li>
</ul>
{{-- {!! html()->closeModelForm() !!} --}}
@stop
@section('FooterInclude')

@stop

