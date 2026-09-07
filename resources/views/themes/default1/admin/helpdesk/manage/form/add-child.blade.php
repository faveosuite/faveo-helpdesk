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
<h3>{{$form->formname}}</h3>
@stop
@section('content')

<div class="card card-light">

    <div class="card-header">
        
        <h3 class="card-title">Add Child</h3>    
    </div>
    
    <div class="card-body">
        <div class="row">
                @foreach($fields as $field)
                <?php
                $form = App\Http\Controllers\Admin\helpdesk\FormController::getForm($field);
                ?>
                <div class="col-md-10">
                {!! $form !!}
                </div>
                @if($field->values()->get()->count()>0)
                <div class="col-md-2">
                    @include('themes.default1.admin.helpdesk.manage.form.childpopup')
                </div>
                @endif
                @endforeach

            </div>

    </div>
</div>
@stop
