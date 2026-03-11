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
<h1>{!! Lang::get('lang.status_settings') !!}</h1>
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
<div class="alert alert-danger alert-dismissable">
    <i class="fas fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <br/>
    @foreach ($errors->all() as $error)
    <li class="error-message-padding">{{ $error }}</li>
    @endforeach 
</div>
@endif
@if(Session::has('success'))
<div class="alert alert-success alert-dismissable">
    <i class="fas fa-check-circle"></i>
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    {{Session::get('success')}}
</div>
@endif
@if(Session::has('failed'))
<div class="alert alert-danger alert-dismissable">
    <i class="fas fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!} !</b>
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
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
                <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    <label>{!! Lang::get('lang.name') !!}: <span class="text-red"> *</span></label><br>
                    {!! html()->text('name', null)->class('form-control') !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group {{ $errors->has('sort') ? 'has-error' : '' }}">
                    <label>{!! Lang::get('lang.display_order') !!}: <span class="text-red"> *</span></label><br>
                    <input type="number" name="sort" min="1" class="form-control" value="{!! $status->sort !!}">
                </div>  
            </div>
            <div class="col-md-2" id="ticket-status-icon-container">
                <div class="form-group {{ $errors->has('icon_class') ? 'has-error' : '' }}">
                    <i class=></i>
                    <label>{!! Lang::get('lang.icon_class') !!}: <span class="text-red"> *</span></label><br>
                    <select class="form-control icons"  name="icon_class" style="font-family: 'FontAwesome', sans-serif;" required>
                         <option <?php if ($status->icon_class == "fas fa-edit") echo 'selected="selected"' ?> value="fas fa-edit">&#xf044</option>
                        <option <?php if ($status->icon_class == "fas fa-folder-open") echo 'selected="selected"' ?> value="fas fa-folder-open">&#xf07c</option>
                        <option <?php if ($status->icon_class == "fas fa-minus-circle") echo 'selected="selected"' ?> value="fas fa-minus-circle">&#xf056</option>
                        <option <?php if ($status->icon_class == "fas fa-exclamation-triangle") echo 'selected="selected"' ?> value="fas fa-exclamation-triangle">&#xf071</option>
                        <option <?php if ($status->icon_class == "fas fa-bars") echo 'selected="selected"' ?> value="fas fa-bars">&#xf0c9</option>
                        <option <?php if ($status->icon_class == "fas fa-bell") echo 'selected="selected"' ?> value="fas fa-bell">&#xf0f3</option>
                        <option <?php if ($status->icon_class == "fas fa-bookmark") echo 'selected="selected"' ?> value="fas fa-bookmark">&#xf02e</option>
                        <option <?php if ($status->icon_class == "fas fa-bug") echo 'selected="selected"' ?> value="fas fa-bug">&#xf188</option>
                        <option <?php if ($status->icon_class == "fas fa-bullhorn") echo 'selected="selected"' ?> value="fas fa-bullhorn">&#xf0a1</option>
                        <option <?php if ($status->icon_class == "fas fa-calendar") echo 'selected="selected"' ?> value="fas fa-calendar">&#xf133</option>
                        <option <?php if ($status->icon_class == "fas fa-cart-plus") echo 'selected="selected"' ?> value="fas fa-cart-plus">&#xf217</option>
                        <option <?php if ($status->icon_class == "fas fa-check") echo 'selected="selected"' ?> value="fas fa-check">&#xf00c</option>
                        <option <?php if ($status->icon_class == "far fa-check-circle") echo 'selected="selected"' ?> value="far fa-check-circle">&#xf058</option>
                        <option <?php if ($status->icon_class == "fas fa-check-circle") echo 'selected="selected"' ?> value="fas fa-check-circle">&#xf058</option>
                        <option <?php if ($status->icon_class == "far fa-check-square") echo 'selected="selected"' ?> value="far fa-check-square">&#xf14a</option>
                        <option <?php if ($status->icon_class == "fas fa-check-square") echo 'selected="selected"' ?> value="fas fa-check-square">&#xf14a</option>
                        <option <?php if ($status->icon_class == "fas fa-circle-notch") echo 'selected="selected"' ?> value="fas fa-circle-notch">&#xf1ce</option>
                        <option <?php if ($status->icon_class == "fas fa-clock") echo 'selected="selected"' ?> value="fas fa-clock">&#xf017</option>
                        <option <?php if ($status->icon_class == "fas fa-times") echo 'selected="selected"' ?> value="fas fa-times">&#xf00d</option>
                        <option <?php if ($status->icon_class == "fas fa-code") echo 'selected="selected"' ?> value="fas fa-code">&#xf121</option>
                        <option <?php if ($status->icon_class == "far fa-hand-paper") echo 'selected="selected"' ?> value="far fa-hand-paper">&#xf256</option>
                        <option <?php if ($status->icon_class == "fas fa-hourglass-half") echo 'selected="selected"' ?> value="fas fa-hourglass-half">&#xf252</option>
                        <option <?php if ($status->icon_class == "fas fa-cog") echo 'selected="selected"' ?> value="fas fa-cog">&#xf013</option>
                        <option <?php if ($status->icon_class == "fas fa-cogs") echo 'selected="selected"' ?> value="fas fa-cogs">&#xf085</option>
                        <option <?php if ($status->icon_class == "far fa-comment") echo 'selected="selected"' ?> value="far fa-comment">&#xf075</option>
                        <option <?php if ($status->icon_class == "fas fa-comment") echo 'selected="selected"' ?> value="fas fa-comment">&#xf075</option>
                        <option <?php if ($status->icon_class == "far fa-comment-dots") echo 'selected="selected"' ?> value="far fa-comment-dots">&#xf4ad</option>
                        <option <?php if ($status->icon_class == "fas fa-comment-dots") echo 'selected="selected"' ?> value="fas fa-comment-dots">&#xf4ad</option>
                        <option <?php if ($status->icon_class == "far fa-comments") echo 'selected="selected"' ?> value="far fa-comments">&#xf086</option>
                        <option <?php if ($status->icon_class == "fas fa-comments") echo 'selected="selected"' ?> value="fas fa-comments">&#xf086</option>
                        <option <?php if ($status->icon_class == "fas fa-edit") echo 'selected="selected"' ?> value="fas fa-edit">&#xf044</option>
                        <option <?php if ($status->icon_class == "far fa-envelope") echo 'selected="selected"' ?> value="far fa-envelope">&#xf0e0</option>
                        <option <?php if ($status->icon_class == "fas fa-exchange-alt") echo 'selected="selected"' ?> value="fas fa-exchange-alt">&#xf362</option>
                        <option <?php if ($status->icon_class == "fas fa-exclamation") echo 'selected="selected"' ?> value="fas fa-exclamation">&#xf12a</option>
                        <option <?php if ($status->icon_class == "fas fa-exclamation-triangle") echo 'selected="selected"' ?> value="fas fa-exclamation-triangle">&#xf071</option>
                        <option <?php if ($status->icon_class == "fas fa-external-link-alt") echo 'selected="selected"' ?> value="fas fa-external-link-alt">&#xf35d</option>
                        <option <?php if ($status->icon_class == "fas fa-eye") echo 'selected="selected"' ?> value="fas fa-eye">&#xf06e</option>
                        <option <?php if ($status->icon_class == "fas fa-rss") echo 'selected="selected"' ?> value="fas fa-rss">&#xf09e</option>
                        <option <?php if ($status->icon_class == "far fa-flag") echo 'selected="selected"' ?> value="far fa-flag">&#xf024</option>
                        <option <?php if ($status->icon_class == "fas fa-bolt") echo 'selected="selected"' ?> value="fas fa-bolt">&#xf0e7</option>
                        <option <?php if ($status->icon_class == "far fa-folder") echo 'selected="selected"' ?> value="far fa-folder">&#xf07b</option>
                        <option <?php if ($status->icon_class == "far fa-folder-open") echo 'selected="selected"' ?> value="far fa-folder-open">&#xf07c</option>
                        <option <?php if ($status->icon_class == "fas fa-users") echo 'selected="selected"' ?> value="fas fa-users">&#xf0c0</option>
                        <option <?php if ($status->icon_class == "fas fa-info") echo 'selected="selected"' ?> value="fas fa-info">&#xf129</option>
                        <option <?php if ($status->icon_class == "fas fa-life-ring") echo 'selected="selected"' ?> value="fas fa-life-ring">&#xf1cd</option>
                        <option <?php if ($status->icon_class == "fas fa-chart-line") echo 'selected="selected"' ?> value="fas fa-chart-line">&#xf201</option>
                        <option <?php if ($status->icon_class == "fas fa-location-arrow") echo 'selected="selected"' ?> value="fas fa-location-arrow">&#xf124</option>
                        <option <?php if ($status->icon_class == "fas fa-lock") echo 'selected="selected"' ?> value="fas fa-lock">&#xf023</option>
                        <option <?php if ($status->icon_class == "fas fa-share") echo 'selected="selected"' ?> value="fas fa-share">&#xf064</option>
                        <option <?php if ($status->icon_class == "fas fa-reply") echo 'selected="selected"' ?> value="fas fa-reply">&#xf3e5</option>
                        <option <?php if ($status->icon_class == "fas fa-reply-all") echo 'selected="selected"' ?> value="fas fa-reply-all">&#xf122</option>
                        <option <?php if ($status->icon_class == "fas fa-times") echo 'selected="selected"' ?> value="fas fa-times">&#xf00d</option>
                        <option <?php if ($status->icon_class == "fas fa-trash") echo 'selected="selected"' ?> value="fas fa-trash">&#xf1f8</option>
                        <option <?php if ($status->icon_class == "fas fa-user") echo 'selected="selected"' ?> value="fas fa-user">&#xf007</option>
                        <option <?php if ($status->icon_class == "fas fa-user-plus") echo 'selected="selected"' ?> value="fas fa-user-plus">&#xf234</option>
                        <option <?php if ($status->icon_class == "fas fa-user-secret") echo 'selected="selected"' ?> value="fas fa-user-secret">&#xf21b</option>
                        <option <?php if ($status->icon_class == "fas fa-user-times") echo 'selected="selected"' ?> value="fas fa-user-times">&#xf235</option>
                        <option <?php if ($status->icon_class == "fas fa-users") echo 'selected="selected"' ?> value="fas fa-users">&#xf0c0</option>
                        <option <?php if ($status->icon_class == "fas fa-wrench") echo 'selected="selected"' ?> value="fas fa-wrench">&#xf0ad</option>
                        <option <?php if ($status->icon_class == "fas fa-circle-notch") echo 'selected="selected"' ?> value="fas fa-circle-notch">&#xf1ce</option>
                        <option <?php if ($status->icon_class == "fas fa-sync") echo 'selected="selected"' ?> value="fas fa-sync">&#xf021</option>
                        <option <?php if ($status->icon_class == "fas fa-spinner") echo 'selected="selected"' ?> value="fas fa-spinner">&#xf110</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group">
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
        <div class="form-group">
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
        <div class="form-group">
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