@extends('themes.default1.admin.layout.admin')

@section('Tickets')
class="nav-link active"
@stop

@section('ticket-menu-parent')
class="nav-item menu-open"
@stop

@section('ticket-menu-open')
class="nav nav-treeview menu-open"
@stop

@section('status')
class="nav-link active"
@stop

@section('PageHeader')
<h3>{!! Lang::get('lang.settings') !!}</h3>
@stop

@section('breadcrumbs')
<ol class="breadcrumb">
</ol>
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
    <b>{!! Lang::get('lang.alert') !!} !</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <p>{{Session::get('failed')}}</p>                
</div>
@endif
<div class="card card-light">
    <div class="card-header">
        <h3 class="card-title">{!! Lang::get('lang.list_of_status') !!}</h3>
    </div><!-- /.box-header -->
    <div class="card-body">
        <table id="example1" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>{!! Lang::get('lang.name') !!}</th>
                    <th>{!! Lang::get('lang.display_order') !!}</th>
                    <th>{!! Lang::get('lang.action') !!}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statuss as $status)
                <?php if ($status->name == 'Deleted') continue; ?>
                <tr>
                    <td>{!! $status->name !!}</td>
                    <td>{!! $status->sort !!}</td>
                    <td>
                        <a href="{!! route('status.edit',$status->id) !!}"><button class="btn btn-primary btn-sm">{!! Lang::get('lang.edit_details') !!}</button></a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div><!-- /.box-body -->
</div>
@stop