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


@section('content')
<style>
    /* Select2 sized and aligned like the Bootstrap 5 inputs next to it */
    #ticket-status-icon-container .select2-container { width: 100% !important; }
    #ticket-status-icon-container .select2-selection--single {
        height: calc(2.25rem + 2px);
        border: 1px solid var(--bs-border-color, #ced4da);
        border-radius: var(--bs-border-radius, .375rem);
    }
    #ticket-status-icon-container .select2-selection__rendered { line-height: 2.25rem; padding-left: .75rem; padding-right: 2rem; }
    #ticket-status-icon-container .select2-selection__arrow { height: 2.25rem; right: .5rem; }
    .status-icon-option i { width: 1.5rem; text-align: center; margin-right: .5rem; }
</style>
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
            <div class="col-md-4" id="ticket-status-icon-container">
                <div class="mb-3 {{ $errors->has('icon_class') ? 'has-error' : '' }}">
                    <label for="icon_class">{!! Lang::get('lang.icon_class') !!}: <span class="text-red"> *</span></label>
                    <?php
                    $icons = [
                        'fa-solid fa-pen-to-square',
                        'fa-solid fa-folder-open',
                        'fa-solid fa-circle-minus',
                        'fa-solid fa-triangle-exclamation',
                        'fa-solid fa-bars',
                        'fa-solid fa-bell',
                        'fa-solid fa-bookmark',
                        'fa-solid fa-bug',
                        'fa-solid fa-bullhorn',
                        'fa-solid fa-calendar',
                        'fa-solid fa-cart-plus',
                        'fa-solid fa-check',
                        'fa-regular fa-circle-check',
                        'fa-solid fa-circle-check',
                        'fa-regular fa-check-square',
                        'fa-solid fa-check-square',
                        'fa-solid fa-circle-notch',
                        'fa-solid fa-clock',
                        'fa-solid fa-xmark',
                        'fa-solid fa-code',
                        'fa-regular fa-hand-paper',
                        'fa-solid fa-hourglass-half',
                        'fa-solid fa-gear',
                        'fa-solid fa-gears',
                        'fa-regular fa-comment',
                        'fa-solid fa-comment',
                        'fa-regular fa-comment-dots',
                        'fa-solid fa-comment-dots',
                        'fa-regular fa-comments',
                        'fa-solid fa-comments',
                        'fa-regular fa-envelope',
                        'fa-solid fa-right-left',
                        'fa-solid fa-exclamation',
                        'fa-solid fa-arrow-up-right-from-square',
                        'fa-solid fa-eye',
                        'fa-solid fa-rss',
                        'fa-regular fa-flag',
                        'fa-solid fa-bolt',
                        'fa-regular fa-folder',
                        'fa-regular fa-folder-open',
                        'fa-solid fa-users',
                        'fa-solid fa-info',
                        'fa-solid fa-life-ring',
                        'fa-solid fa-chart-line',
                        'fa-solid fa-location-arrow',
                        'fa-solid fa-lock',
                        'fa-solid fa-share',
                        'fa-solid fa-reply',
                        'fa-solid fa-reply-all',
                        'fa-solid fa-trash',
                        'fa-solid fa-user',
                        'fa-solid fa-user-plus',
                        'fa-solid fa-user-secret',
                        'fa-solid fa-user-xmark',
                        'fa-solid fa-wrench',
                        'fa-solid fa-arrows-rotate',
                        'fa-solid fa-spinner',
                    ];
                    $selectedIcon = old('icon_class', $status->icon_class);
                    // keep an icon saved before this list was trimmed selectable
                    if ($selectedIcon && !in_array($selectedIcon, $icons, true)) {
                        array_unshift($icons, $selectedIcon);
                    }
                    ?>
                    <select class="form-control icons" id="icon_class" name="icon_class" required>
                        @foreach($icons as $icon)
                        {{-- the readable name is what the search box matches --}}
                        <option value="{{ $icon }}" @if($icon === $selectedIcon) selected @endif>{{ ucfirst(str_replace('-', ' ', preg_replace('/^fa-(solid|regular) fa-/', '', $icon))) }}{{ str_starts_with($icon, 'fa-regular') ? ' ('.Lang::get('lang.outline').')' : '' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <?php
        // pre-select the saved values (or the submitted ones after a validation error)
        $resolved = old('state', $status->state) == 'closed';
        $deleted = old('delete', $status->state == 'deleted' ? 'yes' : 'no') == 'yes';
        $notify = old('email_user', (int) $status->email_user) == 1;
        ?>
        <div class="mb-3">
            <!-- gender -->
            {!! html()->label(Lang::get('lang.resolved_status'), 'gender') !!}
            <div class="callout callout-default font-oblique">{!! Lang::get('lang.status_msg3') !!}</div>
            <div>
                <label class="fw-normal me-4">{!! html()->radio('state', $resolved, 'closed')->id('state-yes') !!} {{Lang::get('lang.yes')}}</label>
                <label class="fw-normal">{!! html()->radio('state', !$resolved, 'open')->id('state-no') !!} {{Lang::get('lang.no')}}</label>
            </div>
        </div>
        <div class="mb-3">
            <!-- Email user -->
            {!! html()->label(Lang::get('lang.deleted_status'), 'gender') !!}
            <div class="callout callout-default font-oblique">{!! Lang::get('lang.status_msg2') !!}</div>
            <div>
                <label class="fw-normal me-4">{!! html()->radio('delete', $deleted, 'yes')->id('delete-yes') !!} {{Lang::get('lang.yes')}}</label>
                <label class="fw-normal">{!! html()->radio('delete', !$deleted, 'no')->id('delete-no') !!} {{Lang::get('lang.no')}}</label>
            </div>        
        </div>
        <div class="mb-3">
            <!-- gender -->
            {!! html()->label(Lang::get('lang.notify_user'), 'gender') !!}
            <div class="callout callout-default font-oblique">{!! Lang::get('lang.status_msg1') !!}</div>
            <div>
                <label class="fw-normal me-4">{!! html()->radio('email_user', $notify, '1')->id('email_user-yes') !!} {{Lang::get('lang.yes')}}</label>
                <label class="fw-normal">{!! html()->radio('email_user', !$notify, '0')->id('email_user-no') !!} {{Lang::get('lang.no')}}</label>
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
        if (!option.id) {
            return option.text;
        }
        return $('<span class="status-icon-option"><i></i></span>').find('i').addClass(option.id).end().append(document.createTextNode(option.text));
    }
    $('.icons').select2({
            templateResult: format,
            templateSelection: format,
            width: '100%'
    })
</script>
@stop