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
<h3>{!! Lang::get('lang.status_settings') !!}</h3>
@stop

@section('breadcrumbs')
<ol class="breadcrumb">
</ol>
@stop

<style type="text/css">
    .select2-container--default .select2-selection--single {
    border: 1px solid #ced4da !important;
    border-radius: 0.25rem !important;
    height: 35px !important;
    }
    .select2-selection__rendered{margin-top: 2px !important;}

    .select2-selection__arrow { top: 5px !important; }
</style>

@section('content')
{!! html()->modelForm($status, 'PATCH', route('statuss.update', [$status->id]))->acceptsFiles()->open() !!}
 @if(Session::has('errors'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    <br/>
    @foreach ($errors->all() as $error)
    <li class="error-message-padding">{{ $error }}</li>
    @endforeach 
</div>
@endif
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
        <h3 class="card-title">{!! Lang::get('lang.edit_details') !!}</h3>
    </div><!-- /.box-header -->
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3 {{ $errors->has('name') ? 'has-error' : '' }}">
                    <label>{!! Lang::get('lang.name') !!}: <span class="text-red"> *</span></label><br>
                    {!! html()->text('name', null)->class('form-control') !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3 {{ $errors->has('sort') ? 'has-error' : '' }}">
                    <label>{!! Lang::get('lang.display_order') !!}: <span class="text-red"> *</span></label><br>
                    <input type="number" name="sort" min="1" class="form-control" value="{!! $status->sort !!}">
                </div>  
            </div>
            <div class="col-md-2" id="ticket-status-icon-container">
                <div class="mb-3 {{ $errors->has('icon_class') ? 'has-error' : '' }}">
                    <i class=></i>
                    <label>{!! Lang::get('lang.icon_class') !!}: <span class="text-red"> *</span></label><br>
                    <select class="form-control icons"  name="icon_class" style="font-family: 'FontAwesome', sans-serif;" required>
                         <option <?php if ($status->icon_class == "fa-solid fa-pen-to-square") echo 'selected="selected"' ?> value="fa-solid fa-pen-to-square">&#xf044</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-folder-open") echo 'selected="selected"' ?> value="fa-solid fa-folder-open">&#xf07c</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-circle-minus") echo 'selected="selected"' ?> value="fa-solid fa-circle-minus">&#xf056</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-triangle-exclamation") echo 'selected="selected"' ?> value="fa-solid fa-triangle-exclamation">&#xf071</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-bars") echo 'selected="selected"' ?> value="fa-solid fa-bars">&#xf0c9</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-bell") echo 'selected="selected"' ?> value="fa-solid fa-bell">&#xf0f3</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-bookmark") echo 'selected="selected"' ?> value="fa-solid fa-bookmark">&#xf02e</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-bug") echo 'selected="selected"' ?> value="fa-solid fa-bug">&#xf188</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-bullhorn") echo 'selected="selected"' ?> value="fa-solid fa-bullhorn">&#xf0a1</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-calendar") echo 'selected="selected"' ?> value="fa-solid fa-calendar">&#xf133</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-cart-plus") echo 'selected="selected"' ?> value="fa-solid fa-cart-plus">&#xf217</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-check") echo 'selected="selected"' ?> value="fa-solid fa-check">&#xf00c</option>
                        <option <?php if ($status->icon_class == "fa-regular fa-circle-check") echo 'selected="selected"' ?> value="fa-regular fa-circle-check">&#xf058</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-circle-check") echo 'selected="selected"' ?> value="fa-solid fa-circle-check">&#xf058</option>
                        <option <?php if ($status->icon_class == "fa-regular fa-check-square") echo 'selected="selected"' ?> value="fa-regular fa-check-square">&#xf14a</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-check-square") echo 'selected="selected"' ?> value="fa-solid fa-check-square">&#xf14a</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-circle-notch") echo 'selected="selected"' ?> value="fa-solid fa-circle-notch">&#xf1ce</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-clock") echo 'selected="selected"' ?> value="fa-solid fa-clock">&#xf017</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-xmark") echo 'selected="selected"' ?> value="fa-solid fa-xmark">&#xf00d</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-code") echo 'selected="selected"' ?> value="fa-solid fa-code">&#xf121</option>
                        <option <?php if ($status->icon_class == "fa-regular fa-hand-paper") echo 'selected="selected"' ?> value="fa-regular fa-hand-paper">&#xf256</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-hourglass-half") echo 'selected="selected"' ?> value="fa-solid fa-hourglass-half">&#xf252</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-gear") echo 'selected="selected"' ?> value="fa-solid fa-gear">&#xf013</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-gears") echo 'selected="selected"' ?> value="fa-solid fa-gears">&#xf085</option>
                        <option <?php if ($status->icon_class == "fa-regular fa-comment") echo 'selected="selected"' ?> value="fa-regular fa-comment">&#xf075</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-comment") echo 'selected="selected"' ?> value="fa-solid fa-comment">&#xf075</option>
                        <option <?php if ($status->icon_class == "fa-regular fa-comment-dots") echo 'selected="selected"' ?> value="fa-regular fa-comment-dots">&#xf4ad</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-comment-dots") echo 'selected="selected"' ?> value="fa-solid fa-comment-dots">&#xf4ad</option>
                        <option <?php if ($status->icon_class == "fa-regular fa-comments") echo 'selected="selected"' ?> value="fa-regular fa-comments">&#xf086</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-comments") echo 'selected="selected"' ?> value="fa-solid fa-comments">&#xf086</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-pen-to-square") echo 'selected="selected"' ?> value="fa-solid fa-pen-to-square">&#xf044</option>
                        <option <?php if ($status->icon_class == "fa-regular fa-envelope") echo 'selected="selected"' ?> value="fa-regular fa-envelope">&#xf0e0</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-right-left") echo 'selected="selected"' ?> value="fa-solid fa-right-left">&#xf362</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-exclamation") echo 'selected="selected"' ?> value="fa-solid fa-exclamation">&#xf12a</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-triangle-exclamation") echo 'selected="selected"' ?> value="fa-solid fa-triangle-exclamation">&#xf071</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-arrow-up-right-from-square") echo 'selected="selected"' ?> value="fa-solid fa-arrow-up-right-from-square">&#xf35d</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-eye") echo 'selected="selected"' ?> value="fa-solid fa-eye">&#xf06e</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-rss") echo 'selected="selected"' ?> value="fa-solid fa-rss">&#xf09e</option>
                        <option <?php if ($status->icon_class == "fa-regular fa-flag") echo 'selected="selected"' ?> value="fa-regular fa-flag">&#xf024</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-bolt") echo 'selected="selected"' ?> value="fa-solid fa-bolt">&#xf0e7</option>
                        <option <?php if ($status->icon_class == "fa-regular fa-folder") echo 'selected="selected"' ?> value="fa-regular fa-folder">&#xf07b</option>
                        <option <?php if ($status->icon_class == "fa-regular fa-folder-open") echo 'selected="selected"' ?> value="fa-regular fa-folder-open">&#xf07c</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-users") echo 'selected="selected"' ?> value="fa-solid fa-users">&#xf0c0</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-info") echo 'selected="selected"' ?> value="fa-solid fa-info">&#xf129</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-life-ring") echo 'selected="selected"' ?> value="fa-solid fa-life-ring">&#xf1cd</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-chart-line") echo 'selected="selected"' ?> value="fa-solid fa-chart-line">&#xf201</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-location-arrow") echo 'selected="selected"' ?> value="fa-solid fa-location-arrow">&#xf124</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-lock") echo 'selected="selected"' ?> value="fa-solid fa-lock">&#xf023</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-share") echo 'selected="selected"' ?> value="fa-solid fa-share">&#xf064</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-reply") echo 'selected="selected"' ?> value="fa-solid fa-reply">&#xf3e5</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-reply-all") echo 'selected="selected"' ?> value="fa-solid fa-reply-all">&#xf122</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-xmark") echo 'selected="selected"' ?> value="fa-solid fa-xmark">&#xf00d</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-trash") echo 'selected="selected"' ?> value="fa-solid fa-trash">&#xf1f8</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-user") echo 'selected="selected"' ?> value="fa-solid fa-user">&#xf007</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-user-plus") echo 'selected="selected"' ?> value="fa-solid fa-user-plus">&#xf234</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-user-secret") echo 'selected="selected"' ?> value="fa-solid fa-user-secret">&#xf21b</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-user-xmark") echo 'selected="selected"' ?> value="fa-solid fa-user-xmark">&#xf235</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-users") echo 'selected="selected"' ?> value="fa-solid fa-users">&#xf0c0</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-wrench") echo 'selected="selected"' ?> value="fa-solid fa-wrench">&#xf0ad</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-circle-notch") echo 'selected="selected"' ?> value="fa-solid fa-circle-notch">&#xf1ce</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-arrows-rotate") echo 'selected="selected"' ?> value="fa-solid fa-arrows-rotate">&#xf021</option>
                        <option <?php if ($status->icon_class == "fa-solid fa-spinner") echo 'selected="selected"' ?> value="fa-solid fa-spinner">&#xf110</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <!-- gender -->
            {!! html()->label(Lang::get('lang.resolved_status'), 'gender') !!}
            <div class="callout callout-default font-oblique">{!! Lang::get('lang.status_msg3') !!}</div>
            <div class="row">
                <div class="col-sm-3">
                    {!! html()->radio('state', true, 'closed') !!} {{Lang::get('lang.yes')}}
                </div>
                <div class="col-sm-3">
                    {!! html()->radio('state', null, 'open') !!} {{Lang::get('lang.no')}}
                </div>
            </div>
        </div>
        <div class="mb-3">
            <!-- Email user -->
            {!! html()->label(Lang::get('lang.deleted_status'), 'gender') !!}
            <div class="callout callout-default font-oblique">{!! Lang::get('lang.status_msg2') !!}</div>
            <div class="row">
                <div class="col-sm-3">
                    {!! html()->radio('delete', null, 'yes') !!} {{Lang::get('lang.yes')}}
                </div>
                <div class="col-sm-3">
                    {!! html()->radio('delete', null, 'no') !!} {{Lang::get('lang.no')}}
                </div>
            </div>        
        </div>
        <div class="mb-3">
            <!-- gender -->
            {!! html()->label(Lang::get('lang.notify_user'), 'gender') !!}
            <div class="callout callout-default font-oblique">{!! Lang::get('lang.status_msg1') !!}</div>
            <div class="row">
                <div class="col-sm-3">
                    {!! html()->radio('email_user', null, 'yes') !!} {{Lang::get('lang.yes')}}
                </div>
                <div class="col-sm-3">
                    {!! html()->radio('email_user', null, 'no') !!} {{Lang::get('lang.no')}}
                </div>
            </div>        
        </div>
    </div>
    <div class="card-footer">
        {!! html()->submit(Lang::get('lang.update'))->class('btn btn-primary') !!}
    </div>
    {!! html()->closeModelForm() !!}
</div> 
<script src="{{asset("lb-faveo/plugins/select2/select2.full.min.js")}}" type="text/javascript"></script>
<script type="text/javascript">
    function format(option){
    var icon = $(option.element).attr('value');
        return '<i class="'+icon+'" ></i> ';
    }
    $('.icons').select2({
            templateResult: format,
            templateSelection: format,
            escapeMarkup: function (m) {
                                        return m;
                                        }
    })
</script>
@stop