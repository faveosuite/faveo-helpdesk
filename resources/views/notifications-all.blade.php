@extends('themes.default1.agent.layout.agent')

@section('Dashboard')
class="nav-link active"
@stop

@section('dashboard-bar')
class="nav-link active"
@stop

@section('HeadInclude')
<style>
    .notification-item.unread { background-color: var(--bs-tertiary-bg); border-left: 3px solid var(--bs-primary); }
    .notification-item.unread .notification-message { font-weight: 600; }
    .notification-icon { width: 36px; height: 36px; }
</style>
@stop

@section('PageHeader')
<h3>{!! Lang::get('lang.notifications') !!}</h3>
@stop

@section('content')
@if(Session::has('success'))
<div class="alert alert-success alert-dismissible">
    <i class="fa-solid fa-circle-check"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{ Session::get('success') }}
</div>
@endif
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissible">
    <i class="fa-solid fa-ban"></i>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-hidden="true"></button>
    {{ Session::get('fails') }}
</div>
@endif

<div class="card card-light">
    <div class="card-header d-flex flex-wrap align-items-center gap-2">
        <h3 class="card-title mb-0 me-auto">{{ Lang::get('lang.view_all_notifications') }}</h3>
        @if($notificationList->total())
        <button type="button" id="notification-read-all" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-check-double"></i> {{ Lang::get('lang.mark_all_as_read') }}
        </button>
        <a href="{{ url('notifications-list/delete') }}" id="notification-delete-all" class="btn btn-sm btn-danger">
            <i class="fa-solid fa-trash-can"></i> {{ Lang::get('lang.delete_all_notifications') }}
        </a>
        @endif
    </div>

    <div class="card-body p-0">
        @if($notificationList->count())
        <ul class="list-group list-group-flush" id="notification-list">
            @foreach($notificationList as $notification)
            <?php
                $type = $notification->notification->type->type;
                if ($type == 'registration') {
                    $link = route('user.show', $notification->notification->model_id);
                    $icon = 'fa-user-plus text-success';
                } else {
                    $link = route('ticket.thread', $notification->notification->model_id);
                    $icon = $type == 'reply' ? 'fa-reply text-info' : 'fa-ticket text-primary';
                }
            ?>
            <li class="list-group-item notification-item d-flex align-items-center gap-3 py-3 {{ $notification->is_read ? '' : 'unread' }}" data-id="{{ $notification->notification_id }}">
                <span class="notification-icon flex-shrink-0 rounded-circle bg-body-secondary d-flex align-items-center justify-content-center">
                    <i class="fa-solid {{ $icon }}"></i>
                </span>
                <div class="flex-grow-1 min-w-0">
                    <a href="{{ $link }}" class="notification-link notification-message text-body text-decoration-none d-block text-break">
                        {{ $notification->notification->type->message }}
                        @if($type != 'registration')
                        <span class="badge text-bg-light border ms-1">{{ $notification->notification->model->ticket_number }}</span>
                        @endif
                    </a>
                    <small class="text-body-secondary" title="{{ UTC::usertimezone($notification->created_at) }}">
                        <i class="fa-regular fa-clock"></i> {{ $notification->created_at->diffForHumans() }}
                    </small>
                </div>
                <div class="flex-shrink-0 d-flex gap-1">
                    <a href="{{ $link }}" class="notification-link btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="{{ Lang::get('lang.view') }}">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                    <button type="button" class="notification-delete btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="{{ Lang::get('lang.delete') }}">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </li>
            @endforeach
        </ul>
        @else
        <div class="text-center text-body-secondary py-5">
            <i class="fa-solid fa-bell-slash fa-2x mb-2"></i>
            <p class="mb-0">{{ Lang::get('lang.no_notification_available') }}</p>
        </div>
        @endif
    </div>

    @if($notificationList->total())
    <div class="card-footer bg-transparent">
        {{ $notificationList->links('vendor.pagination.datatables') }}
    </div>
    @endif
</div>

<script>
    $(document).ready(function() {
        var token = $('meta[name=_token]').attr('content');

        // mark as read when a notification is opened; keepalive lets the request finish after the page navigates away
        $('#notification-list').on('click', '.notification-link', function() {
            var $item = $(this).closest('.notification-item');
            if ($item.hasClass('unread')) {
                fetch("{{ url('mark-read') }}/" + $item.data('id'), {method: 'POST', headers: {'X-CSRF-Token': token}, credentials: 'same-origin', keepalive: true});
            }
        });

        $('#notification-list').on('click', '.notification-delete', function() {
            var $button = $(this);
            var $item = $button.closest('.notification-item');
            bootstrap.Tooltip.getInstance(this)?.dispose();
            $button.prop('disabled', true);
            $.ajax({
                type: 'POST',
                url: "{{ url('notification-delete') }}/" + $item.data('id'),
                cache: false,
                success: function(response) {
                    if (response == 1) {
                        $item.fadeOut(200, function() {
                            $item.remove();
                            // reload to pull in the next page, or show the empty state
                            if (!$('#notification-list .notification-item').length) {
                                window.location.reload();
                            }
                        });
                    } else {
                        $button.prop('disabled', false);
                    }
                },
                error: function() {
                    $button.prop('disabled', false);
                }
            });
        });

        $('#notification-read-all').on('click', function() {
            var $button = $(this).prop('disabled', true);
            $.ajax({
                type: 'POST',
                url: "{{ url('mark-all-read') }}/{{ Auth::id() }}",
                success: function() {
                    $('#notification-list .notification-item').removeClass('unread');
                },
                complete: function() {
                    $button.prop('disabled', false);
                }
            });
        });

        $('#notification-delete-all').on('click', function(e) {
            if (!confirm("{{ Lang::get('lang.delete_all_notifications_confirm') }}")) {
                e.preventDefault();
            }
        });
    });
</script>
@stop
