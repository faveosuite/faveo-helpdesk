@unless ($breadcrumbs->isEmpty())

<ol class="breadcrumb float-sm-end ">
    <li class="breadcrumb-item"> <i class="fa-solid fa-home"> </i> {!! Lang::get('lang.you_are_here') !!} : &nbsp;</li>
    @foreach($breadcrumbs as $breadcrumb)
    @if (!$loop->last)
    <li class="breadcrumb-item"><a href="{{ $breadcrumb->url }}">{{ $breadcrumb->title }}</a></li>
    @else
    <li class="breadcrumb-item active">{{ str_replace("&nbsp;", "", $breadcrumb->title) }}</li>
    @endif
    @endforeach

</ol>
@endunless