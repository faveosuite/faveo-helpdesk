@extends('themes.default1.admin.layout.admin')

@section('Emails')
active
@stop

@section('emails-bar')
active
@stop

@section('emails')
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
	<div class="row">
<div class="col-md-12">
<div class="box box-primary">
<div class="box-header">
	<h2 class="box-title">{{Lang::get('lang.templates')}}</h2>
</div>

<div class="box-body table-responsive">

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
        <b>Fail!</b>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
        {{Session::get('fails')}}
    </div>
    @endif

				<table id="example1" class="table table-bordered table-striped">
	<tr>
        <th>{{Lang::get('lang.name')}}</th>
        <th>{{Lang::get('lang.description')}}</th>
	</tr>
	<!-- Foreach @var templates as @var template -->
        
		@foreach($templates as $template)
                <?php if ($template === '.' or $template === '..') continue; ?>
	<tr>
		<!-- Template Name with Link to Edit page along Id -->
		<td><a href="{{route('template.read',[$template,$directory])}}"><?php $parts = explode('.',$template); $names  = $parts[0]; $name = str_replace('-', ' ', $names); $cname = ucfirst($name); echo $cname?></a></td>
        <td>{{ Lang::get('lang.'.$cname) }}</td>
		<!-- template Status : if status==1 active -->
		<!-- Deleting Fields -->
	</tr>
@endforeach

	<!-- Set a link to Create Page -->




</table>
@stop
</div><!-- /.box -->
@section('FooterInclude')
<!-- page script -->
<script type="text/javascript">
$(function() {
    $("#example1").dataTable();
    $('#example2').dataTable({
        "bPaginate": true,
        "bLengthChange": false,
        "bFilter": false,
        "bSort": true,
        "bInfo": true,
        "bAutoWidth": false
    });
});

</script>

@stop
@stop
<!-- /content -->