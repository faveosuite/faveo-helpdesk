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
<div class="box box-primary">
<div class="box-header">
  <h2 class="box-title">{{Lang::get('lang.template_set')}}</h2>
<button class="btn btn-primary pull-right" data-bs-toggle="modal" data-bs-target="#createtemp">{{Lang::get('lang.create_template')}}</button> 
                                   
                                  <div class="modal fade" id="createtemp">
                                       <div class="modal-dialog">
                                          <div class="modal-content">
                                  {!! html()->form('POST', route('template.createnew'))->open() !!}
                    <div class="modal-header">
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h5 class="modal-title">{{Lang::get('lang.create_template')}}</h4>
        </div>
                     <div class="modal-body">
                              <div class="mb-3 {{ $errors->has('title') ? 'has-error' : '' }}">

    {!! html()->label('Template Set Name:', 'folder_name')->attributes(['style' => 'display: block']) !!}

    {!! html()->text('folder_name', null)->class('form-control') !!}

    {!! $errors->first('folder', '<spam class="help-block">:message</spam>') !!}
  
  </div>
                                     </div>
                                                                        <div class="modal-footer">
                                                                            <div class="mb-3">
                                                                                {!! html()->submit('Create Template Set')->class('btn btn-primary') !!}
                                                                            
                                                                            <button type="button" class="btn btn-secondary pull-left" data-bs-dismiss="modal">Close</button>
                                                                        </div></div>
                                                                        {!! html()->closeModelForm() !!}
                                                                    </div> 
                                                                </div>
                                                            </div></div>

<div class="box-body ">

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
    <th width="100px">{{Lang::get('lang.name')}}</th>
                <th width="100px">{{Lang::get('lang.status')}}</th>
                <th width="100px">{{Lang::get('lang.action')}}</th>
  </tr>
  <!-- Foreach @var templates as @var template -->
    @foreach($directories as $dir)
                <?php if ($dir === '.' or $dir === '..' or $dir === 'notifications') continue; ?>
                <?php //dd($directory); ?>
  <tr>  
    <!-- Template Name with Link to Edit page along Id -->
    <td><a href="{{route('template.list',[$dir,$directory])}}"><?php $parts = explode('.',$dir); $names  = $parts[0]; $name = str_replace('_', ' ', $names);$name1 = ucfirst($name); echo $name1?></a></td>
    <!-- template Status : if status==1 active -->
    <!-- Deleting Fields -->
                <?php $status = DB::table('settings_email')->first();  ?>
                <td><input type="radio" disabled="disabled" value="Active"<?php echo ($status->template == $dir)?'checked':'' ?> /></td>
                <td>
                    <a href="{{ route('active.set', [$dir]) }}" class="btn btn-success btn-xs">
                        Activate This Set
                    </a>
<?php 
if($dir == 'default')  {
  $dis = "disabled";  
} else {
  $dis = "";
} ?>
    <button class="btn btn-danger btn-xs {!! $dis !!}"  data-bs-toggle="modal" data-bs-target="#{{$dir}}delete">Delete</button>
                                                            <div class="modal fade" id="{{$dir}}delete">
                                                                <div class="modal-dialog">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                           <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                 <h5 class="modal-title">Delete</h4>
                                      </div>
                                         <div class="modal-body">
                                             <p>Are you sure you want to Delete ?</p>
                                                </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary pull-left" data-bs-dismiss="modal">Close</button>
                                                            <a href="{{ route('templates.delete', [$dir, $directory]) }}" id="delete" class="btn btn-danger btn-sm">
                                                                Delete
                                                            </a>
                                                        </div>
                                                                    </div> 
                                                                </div>
                                                            </div> </td>
  </tr>
@endforeach
</table>
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
</div><!-- /.box -->
</div>
@stop

