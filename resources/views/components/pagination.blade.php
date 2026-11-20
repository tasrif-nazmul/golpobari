@if ($paginator->hasPages())
<nav class="flex items-center justify-between gap-3 px-1 py-3 text-sm" aria-label="Pagination">
    <p class="text-stone-500">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</p>
    <div class="flex gap-1.5">
        @if ($paginator->onFirstPage())<span class="a-btn a-btn-ghost a-btn-sm opacity-40">‹ Prev</span>@else<a class="a-btn a-btn-ghost a-btn-sm" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹ Prev</a>@endif
        @if ($paginator->hasMorePages())<a class="a-btn a-btn-ghost a-btn-sm" href="{{ $paginator->nextPageUrl() }}" rel="next">Next ›</a>@else<span class="a-btn a-btn-ghost a-btn-sm opacity-40">Next ›</span>@endif
    </div>
</nav>
@endif
