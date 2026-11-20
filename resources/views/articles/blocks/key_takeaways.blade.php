<aside class="rounded-2xl border-l-4 border-flame-500 bg-flame-50 p-6 dark:bg-flame-500/10" aria-label="Key takeaways">
    <h3 class="font-display text-lg font-extrabold text-navy-900 dark:text-white">{{ $data['heading'] ?? 'Key takeaways' }}</h3>
    <ul class="mt-3 space-y-2">
        @foreach ($data['items'] ?? [] as $item)
            <li class="flex gap-3 text-slate-800 dark:text-slate-200">
                <span class="mt-2 size-1.5 shrink-0 rounded-full bg-flame-500" aria-hidden="true"></span>
                <span>{{ $item['text'] ?? '' }}</span>
            </li>
        @endforeach
    </ul>
</aside>
