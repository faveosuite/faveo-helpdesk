{{-- Server-side pagination styled like the DataTables "full_numbers" pager, so
     Laravel-paginated lists match the jQuery DataTables tables used across Faveo.

     Safe to use as the application-wide default view:
       - renders nothing when there is only one page (same as Laravel's own views),
       - works for a LengthAwarePaginator (paginate) AND the simple Paginator
         (simplePaginate) — the simple one has no total()/lastPage(), so it falls
         back to Previous/Next only instead of erroring,
       - outputs the standard Bootstrap 5 .pagination/.page-item/.page-link markup
         (plus DataTables' .dt-* wrappers), so existing CSS keeps applying. --}}
@if($paginator->hasPages())
<?php
    $isLengthAware = $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
    $current = $paginator->currentPage();
    $pages   = [];
    $info    = '';
    $last    = null;

    if ($isLengthAware) {
        $last = $paginator->lastPage();
        // same window as DataTables: at most 7 numbers, with ellipses around the current page
        if ($last <= 7) {
            $pages = range(1, $last);
        } elseif ($current <= 4) {
            $pages = array_merge(range(1, 5), ['...', $last]);
        } elseif ($current >= $last - 3) {
            $pages = array_merge([1, '...'], range($last - 4, $last));
        } else {
            $pages = [1, '...', $current - 1, $current, $current + 1, '...', $last];
        }
        $info = $paginator->total()
            ? str_replace(['_START_', '_END_', '_TOTAL_'], [$paginator->firstItem(), $paginator->lastItem(), $paginator->total()], Lang::get('datatables.sInfo'))
            : Lang::get('datatables.sInfoEmpty');
    }

    $prevDisabled = $paginator->onFirstPage();
    $nextDisabled = !$paginator->hasMorePages();
?>
<div class="dt-container w-100">
    {{-- flex-wrap keeps info left / pager right on wide tables and stacks them in narrow panes --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="dt-info" role="status" aria-live="polite">{{ $info }}</div>
        <div class="dt-paging">
            <nav aria-label="pagination">
                <ul class="pagination justify-content-end mb-0 flex-wrap">
                    {{-- First (only when the last page is known) --}}
                    @if($isLengthAware)
                    <li class="dt-paging-button page-item {{ $prevDisabled ? 'disabled' : '' }}">
                        <a class="page-link" aria-label="{{ Lang::get('datatables.oPaginate.sFirst') }}" @if(!$prevDisabled) href="{{ $paginator->url(1) }}" @endif>&laquo;</a>
                    </li>
                    @endif
                    {{-- Previous --}}
                    <li class="dt-paging-button page-item {{ $prevDisabled ? 'disabled' : '' }}">
                        <a class="page-link" aria-label="{{ Lang::get('datatables.oPaginate.sPrevious') }}" @if(!$prevDisabled) href="{{ $paginator->url($current - 1) }}" @endif>&lsaquo;</a>
                    </li>
                    {{-- Numbered pages (length-aware only) --}}
                    @foreach($pages as $page)
                        @if($page === '...')
                        <li class="dt-paging-button page-item disabled"><a class="page-link">&#x2026;</a></li>
                        @else
                        <li class="dt-paging-button page-item {{ $page == $current ? 'active' : '' }}">
                            <a class="page-link" href="{{ $paginator->url($page) }}" @if($page == $current) aria-current="page" @endif>{{ $page }}</a>
                        </li>
                        @endif
                    @endforeach
                    {{-- Next --}}
                    <li class="dt-paging-button page-item {{ $nextDisabled ? 'disabled' : '' }}">
                        <a class="page-link" aria-label="{{ Lang::get('datatables.oPaginate.sNext') }}" @if(!$nextDisabled) href="{{ $paginator->url($current + 1) }}" @endif>&rsaquo;</a>
                    </li>
                    {{-- Last (only when the last page is known) --}}
                    @if($isLengthAware)
                    <li class="dt-paging-button page-item {{ $nextDisabled ? 'disabled' : '' }}">
                        <a class="page-link" aria-label="{{ Lang::get('datatables.oPaginate.sLast') }}" @if(!$nextDisabled) href="{{ $paginator->url($last) }}" @endif>&raquo;</a>
                    </li>
                    @endif
                </ul>
            </nav>
        </div>
    </div>
</div>
@endif
