<figure class="relative rounded-2xl bg-navy-900 p-6 text-white sm:p-8">
    <svg class="absolute left-5 top-4 size-10 text-flame-500/40" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9 7H5a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h2v3l3-3V9a2 2 0 0 0-1-2zm10 0h-4a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h2v3l3-3V9a2 2 0 0 0-1-2z"/></svg>
    <blockquote class="relative pl-6 font-display text-xl font-semibold leading-snug sm:text-2xl">“{{ $data['quote'] ?? '' }}”</blockquote>
    @if (! empty($data['name']))
        <figcaption class="mt-4 pl-6 text-sm text-slate-300">— <span class="font-semibold text-white">{{ $data['name'] }}</span>@if (! empty($data['role'])), {{ $data['role'] }}@endif</figcaption>
    @endif
</figure>
