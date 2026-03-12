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

<!-- header -->
@section('PageHeader')
<h3>{!! Lang::get('lang.forms') !!}</h3>
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
<div class="alert alert-success alert-dismissable">
    <i class="fas fa-check-circle"></i>
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    {{Session::get('success')}}
</div>
@endif
@if(Session::has('fails'))
<div class="alert alert-success alert-dismissable">
    <i class="fas fa-ban"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <b>{!! Lang::get('lang.alert') !!} !</b> <br>
    <li class="error-message-padding">{{Session::get('fails')}}</li>
</div>
@endif
<!-- -->    
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.form_name') !!} : {!! $form->formname !!}</h3>
    </div>
    <div class="card-body">
        @foreach($fields as $field)
        <?php
        $form = App\Http\Controllers\Admin\helpdesk\FormController::getForm($field);
        ?>

        {!! $form !!}
          
        @endforeach         
    </div>
</div>
@stop
@section('FooterInclude')

@stop
