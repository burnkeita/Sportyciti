@php
    $rows = collect($data['rows'] ?? [])->map(fn ($r) => ['label' => $r['label'] ?? '', 'value' => (float) ($r['value'] ?? 0)]);
    $max = max($rows->max('value') ?: 0, 0.0001);
    $suffix = $data['suffix'] ?? '';
@endphp
@component('articles.blocks._frame', ['data' => $data, 'title' => $data['title'] ?? 'Chart', 'icon' => 'chart'])
    <figure>
        <div class="space-y-2.5" role="img" aria-label="{{ $data['title'] ?? 'Bar chart' }}: {{ $rows->map(fn ($r) => $r['label'].' '.$r['value'].$suffix)->implode(', ') }}">
            @foreach ($rows as $row)
                <div class="grid grid-cols-[minmax(6rem,10rem)_1fr_auto] items-center gap-3 text-sm">
                    <span class="truncate font-medium text-slate-700 dark:text-slate-300">{{ $row['label'] }}</span>
                    <span class="h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-white/10">
                        <span class="block h-full rounded-full bg-gradient-to-r from-pitch-500 to-pitch-400" style="width: {{ round($row['value'] / $max * 100, 1) }}%"></span>
                    </span>
                    <span class="w-14 text-right font-bold tabular-nums">{{ rtrim(rtrim(number_format($row['value'], 2), '0'), '.') }}{{ $suffix }}</span>
                </div>
            @endforeach
        </div>
        @if (! empty($data['caption']))
            <figcaption class="mt-3 text-xs text-slate-500">{{ $data['caption'] }}</figcaption>
        @endif
    </figure>
@endcomponent
