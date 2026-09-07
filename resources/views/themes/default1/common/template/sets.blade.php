@extends('themes.default1.admin.layout.admin')

@section('Emails')
class="nav-link active"
@stop

@section('email-menu-parent')
class="nav-item menu-open"
@stop

@section('email-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('template')
class="nav-link active"
@stop

@section('PageHeader')
<h3>{!! Lang::get('lang.template_set') !!}</h3>
@stop

@section('content')

@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa-solid fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('success')}}
</div>
@endif

@if(Session::has('failed'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <b>{!! Lang::get('lang.alert') !!} !</b> <br>
    <li>{{Session::get('failed')}}</li>
</div>
@endif

<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.list_of_templates_sets') !!}</h3>
        <div class="card-tools d-flex">
            <button class="btn btn-secondary btn-tool" data-bs-toggle="modal" data-bs-target="#create" title="Create" id="2create">
                <i class="fa-solid fa-plus"> </i> {{Lang::get('lang.create')}}
            </button>
        </div>
    </div><!-- /.box-header -->
    <div class="card-body">
        <table id="example1" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>{!! Lang::get('lang.name') !!}</th>
                    <th>{!! Lang::get('lang.status') !!}</th>
                    <th>{!! Lang::get('lang.action') !!}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sets as $set)
                <tr>
                    <td>{!! $set->name !!}</td>
                    <?php
                    $status = DB::table('settings_email')->first();
                    if (strpos($status->template, '_') !== false) {
                        $ratName = str_replace('_', ' ', $status->template);
                    } else {
                        $ratName = $status->template;
                    }
                    ?>
                    <td>
                        @if($ratName == $set->name)
                        <a style='color:green'>Active</a>
                         @else()
                          <a style='color:red'>Inactive</a>
                          @endif

                       
                    <td>
                        <?php
                        $settings = DB::table('settings_email')->whereId(1)->first();
                        if ($set->name == $settings->template) {
                            $dis = "disabled";
                        } else {
                            $dis = "";
                        }
                        ?>
                        @if($set->name == $settings->template)
                       <button class="btn btn-success btn-sm {!! $dis !!}" data-bs-toggle="modal" data-bs-target="">{!! Lang::get('lang.activate_this_set') !!}</button>
                        @else()
                            <a href="{{ route('active.template-set', [$set->name]) }}" class="btn btn-success btn-sm {{ $dis }}">
                                {{ Lang::get('lang.activate_this_set') }}
                            </a>
                        @endif

                        <a href="{{ route('show.templates', [$set->id]) }}" class="btn btn-success btn-sm">
                            {{ Lang::get('lang.show') }}
                        </a>
                        <div class="modal fade" id="{{$set->id}}">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    {!! html()->modelForm($set, 'PATCH', route('template-sets.update', [$set->id]))->acceptsFiles()->open() !!}
                                    <div class="modal-header">
                                        <h5 class="modal-title">{!! Lang::get('lang.edit_details') !!}</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <div class="form-control">
                                                <label for="title">Name:</label><br>
                                                {!! html()->text('name', null)->class('form-control') !!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer justify-content-between">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        {!! html()->submit('Update Details')->class('btn btn-primary') !!}
                                    </div>
                                    {!! html()->closeModelForm() !!}
                                </div> 
                            </div>
                        </div>
                        <?php
                        $settings = DB::table('settings_email')->whereId(1)->first();
                        if ($set->name == $settings->template) {
                            $dis = "disabled";
                        } else {
                            $dis = "";
                        }
                        ?>
                        <button class="btn btn-danger btn-sm {!! $dis !!}" data-bs-toggle="modal" data-bs-target="#delete{{$set->id}}">{!! Lang::get('lang.delete') !!}</button>
                        <div class="modal fade" id="delete{{$set->id}}">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{!! Lang::get('lang.delete') !!}</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <span>{{Lang::get('lang.are_you_sure_you_want_to_delete')}}</span>&nbsp;
                                    </div>
                                    <div class="modal-footer justify-content-between">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <a href="{{ route('sets.delete', [$set->id]) }}" id="delete" class="btn btn-danger btn-sm">
                                            {{ Lang::get('lang.delete') }}
                                        </a>
                                    </div>
                                </div> 
                            </div>
                        </div> 
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div><!-- /.box-body -->
</div>

<div class="modal fade" id="create" class="modal fade in {{ $errors->has('name') ? 'has-error' : '' }}">
    <div class="modal-dialog">
        <div class="modal-content">
            {!! html()->form('POST', route('template-sets.store'))->open() !!}
            <div class="modal-header">
                <h5 class="modal-title">{!! Lang::get('lang.create') !!}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                @foreach ($errors->all() as $error)
                <div class="alert alert-danger alert-dismissible">
                    <i class="fa-solid fa-ban"></i><button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
                    <b>{!! Lang::get('lang.alert') !!} !</b><br>
                    <li style="list-style: none">{{ $error }}</li>
                </div>
                @if($error == "The name field is required.")
                <script type="text/javascript">
                    $(document).ready(function() {
                        $("#2create").click();
                    });
                </script>
                @endif
                @endforeach 
                <div class="mb-3 {{ $errors->has('name') ? 'has-error' : '' }}">
                    <label for="title">{!! Lang::get('lang.name') !!}:<span style="color:red;">*</span></label><br>
                    {!! html()->text('name', null)->class('form-control') !!}
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{!! Lang::get('lang.close') !!}</button>
                {!! html()->submit(Lang::get('lang.create_set'))->class('btn btn-primary') !!}
            </div>
            {!! html()->closeModelForm() !!}
        </div> 
    </div>
</div>  
<!-- set script -->
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