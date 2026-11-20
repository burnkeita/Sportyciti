@if (! empty($data['items']))
    <section class="border-t border-slate-200 pt-6 dark:border-white/10" aria-label="References">
        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Sources & references</h3>
        <ol class="mt-3 list-decimal space-y-1.5 pl-5 text-sm text-slate-600 dark:text-slate-300">
            @foreach ($data['items'] as $ref)
                <li>
                    @if (! empty($ref['url']))
                        <a href="{{ $ref['url'] }}" target="_blank" rel="nofollow noopener" class="underline hover:text-pitch-700">{{ $ref['title'] ?? $ref['url'] }}</a>
                    @else
                        {{ $ref['title'] ?? '' }}
                    @endif
                </li>
            @endforeach
        </ol>
    </section>
@endif
