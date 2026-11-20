@component('articles.blocks._frame', ['data' => $data, 'title' => ($data['player'] ?? 'Player').' — statistics', 'icon' => 'user'])
    @if (! empty($data['team']) || ! empty($data['context']))
        <p class="mb-4 text-sm text-slate-500">{{ collect([$data['team'] ?? null, $data['context'] ?? null])->filter()->implode(' · ') }}</p>
    @endif
    <dl class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach ($data['rows'] ?? [] as $row)
            <div class="rounded-xl bg-slate-50 p-3 text-center dark:bg-white/5">
                <dd class="font-display text-2xl font-extrabold text-navy-900 tabular-nums dark:text-white">{{ $row['value'] ?? '' }}</dd>
                <dt class="mt-0.5 text-xs text-slate-500">{{ $row['metric'] ?? '' }}</dt>
            </div>
        @endforeach
    </dl>
@endcomponent
