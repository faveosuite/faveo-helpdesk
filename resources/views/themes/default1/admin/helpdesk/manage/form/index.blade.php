@extends('themes.default1.admin.layout.admin')

@section('Manage')
class="nav-link active"
@stop

@section('manage-menu-parent')
class="nav-item menu-open"
@stop

@section('manage-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('forms')
class="nav-link active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h3>{{Lang::get('lang.manage')}}</h3>
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

@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa-solid fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{Session::get('success')}}
</div>
@endif

<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.forms') !!}</h3>
        <div class="card-tools d-flex">
            <a href="{!! url('forms/create') !!}" class="btn btn-secondary btn-tool">
                <span class="fa-solid fa-plus"></span>&nbsp;{!! Lang::get('lang.create_form') !!}
            </a>
        </div>
    </div>

    <div class="card-body">
        <table id="example2" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>{!! Lang::get('lang.form_name') !!}</th>
                    <th>{!! Lang::get('lang.action') !!}</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $forms = App\Model\helpdesk\Form\Forms::all();
                ?>
                @foreach($forms as $form)
                <tr>
                    <td>{!! $form->formname !!}</td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('forms.edit', [$form->id]) }}" id="View" class="btn btn-primary btn-sm">
                                {{ Lang::get('lang.edit') }}
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="{{ route('forms.show', [$form->id]) }}" id="View" class="btn btn-primary btn-sm">
                                {{ Lang::get('lang.view_this_form') }}
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="{{ route('forms.add.child', [$form->id]) }}" id="add-child" class="btn btn-primary btn-sm">
                                {{ Lang::get('lang.add-child') }}
                            </a>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#delete{{$form->id}}">{!! Lang::get('lang.delete_from') !!}
                            </button>
                        </div>
                        <div class="modal fade" id="delete{{$form->id}}">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{!! Lang::get('lang.delete') !!}</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <span>{!! Lang::get('lang.are_you_sure_you_want_to_delete') !!} ?</span>
                                    </div>
                                    <div class="modal-footer justify-content-between">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{!! Lang::get('lang.close') !!}</button>
                                        <a href="{{ route('forms.delete', [$form->id]) }}" id="delete" class="btn btn-danger">
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
    </div>
</div>
@stop