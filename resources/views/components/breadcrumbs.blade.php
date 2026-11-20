@php $crumbs = app(\App\Support\Seo::class)->breadcrumbs; @endphp
@if (count($crumbs) > 1)
    <nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => 'text-xs text-slate-500 dark:text-slate-400']) }}>
        <ol class="flex flex-wrap items-center gap-1.5">
            @foreach ($crumbs as $crumb)
                <li class="flex items-center gap-1.5">
                    @if (! $loop->first)<x-svg-icon name="chevron-right" class="size-3 opacity-60" />@endif
                    @if ($crumb['url'] && ! $loop->last)
                        <a href="{{ $crumb['url'] }}" class="hover:text-pitch-600 hover:underline">{{ $crumb['name'] }}</a>
                    @else
                        <span @if ($loop->last) aria-current="page" class="line-clamp-1 font-medium text-slate-700 dark:text-slate-300" @endif>{{ $crumb['name'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
