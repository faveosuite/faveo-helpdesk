<li class="nav-item dropdown notifications-menu">
    <a href="#" class="nav-link" data-bs-toggle="dropdown">

        <i class="fa-solid fa-arrows-rotate"></i>

        <span class="badge bg-warning navbar-badge" id="count">{!! $notification->count() !!}</span>
    </a>

    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">

        <span class="dropdown-header">You have {!! $notification->count() !!} update(s).</span>

        <ul class="menu list-unstyled">

            @if($notification->count()>0)
            @foreach($notification as $notify)
            @if($notify->value)

            <li class="ms-2">{!! ucfirst($notify->value) !!}</li>
            <li class="clearfix"></li>
            @endif
            @endforeach
            @endif
        </ul>
    </div>
</li>


