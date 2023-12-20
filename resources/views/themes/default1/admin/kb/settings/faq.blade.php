@extends('themes.default1.admin.layout.kb')
@section('content')
<!-- open a form -->

	{!! html()->modelForm($faq, 'PATCH', url('post-create-faq/'.$faq->id))->acceptsFiles()->open() !!}

<!-- <div class="mb-3 {{ $errors->has('company_name') ? 'has-error' : '' }}"> -->
	<!-- table  -->

<div class="row">
<div class="col-md-12">
<div class="box box-primary">
	<div class="app-content-header">

		<div>
        	<h4>Faqs {!! html()->submit('save')->class('mb-3 btn btn-primary pull-right') !!}</h4>
    	</div>

    </div>

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
 		<div class="box-body table-responsive"style="overflow:hidden;">

            <div class="row">

        <div class="col-md-10 mb-3 {{ $errors->has('faq') ? 'has-error' : '' }}">
        {!! html()->label('Description', 'faq') !!}
        {!! $errors->first('faq', '<spam class="help-block">:message</spam>') !!}

            {!! html()->textarea('faq', null)->class('form-control')->id('faq')->attributes(['size' => '30x5']) !!}

        </div>
            <script language="JavaScript" type="text/javascript">
                CKEDITOR.replace( 'faq',
                {
                        filebrowserUploadUrl : '/uploader/upload.php',

                });

                CKEDITOR.replace( 'faq', { toolbar : 'MyToolbar' } );
            </script>


        </div>
</div>
</div></div>
@stop
</div><!-- /.box -->
@section('FooterInclude')

@stop
@stop
<!-- /content -->

@stop