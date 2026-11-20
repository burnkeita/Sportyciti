@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-between gap-3">
        @if ($paginator->onFirstPage())
            <span class="btn-ghost cursor-not-allowed opacity-50" aria-disabled="true"><x-svg-icon name="chevron-left" class="size-4" /> Newer</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn-ghost"><x-svg-icon name="chevron-left" class="size-4" /> Newer</a>
        @endif

        <ul class="hidden items-center gap-1 sm:flex">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="px-2 text-slate-400">{{ $element }}</span></li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="grid size-10 place-items-center rounded-lg bg-navy-900 text-sm font-bold text-white dark:bg-pitch-500">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="grid size-10 place-items-center rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/10" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach
        </ul>
        <span class="text-sm text-slate-500 sm:hidden">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn-ghost">Older <x-svg-icon name="chevron-right" class="size-4" /></a>
        @else
            <span class="btn-ghost cursor-not-allowed opacity-50" aria-disabled="true">Older <x-svg-icon name="chevron-right" class="size-4" /></span>
        @endif
    </nav>
@endif
