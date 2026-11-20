@php
    // Lines from back to front, e.g. "4-3-3" → [4,3,3]; goalkeeper added automatically.
    $lines = array_values(array_filter(array_map('intval', explode('-', (string) ($data['formation'] ?? '4-4-2')))));
    $players = array_values($data['players'] ?? []);
    $rows = [[$players[0] ?? ['name' => 'GK']]];
    $i = 1;
    foreach ($lines as $count) {
        $rows[] = array_slice($players, $i, $count) + array_fill(0, $count, ['name' => '']);
        $i += $count;
    }
    $rows = array_reverse($rows); // attackers at top
@endphp
@component('articles.blocks._frame', ['data' => $data + ['verified' => $data['verified'] ?? true], 'title' => trim(($data['team'] ?? '').' '.($data['formation'] ?? '')).(! empty($data['label']) ? ' — '.$data['label'] : ''), 'icon' => 'table'])
    <figure>
        <div class="relative mx-auto aspect-[3/4] max-w-sm overflow-hidden rounded-xl bg-gradient-to-b from-pitch-600 to-pitch-700 p-4" role="img" aria-label="Formation diagram: {{ $data['formation'] ?? '' }}">
            <div class="pointer-events-none absolute inset-3 rounded border-2 border-white/40" aria-hidden="true"></div>
            <div class="pointer-events-none absolute inset-x-3 top-1/2 h-0.5 bg-white/40" aria-hidden="true"></div>
            <div class="pointer-events-none absolute left-1/2 top-1/2 size-20 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-white/40" aria-hidden="true"></div>
            <div class="relative flex h-full flex-col justify-around">
                @foreach ($rows as $row)
                    <div class="flex justify-around">
                        @foreach ($row as $p)
                            <div class="flex w-16 flex-col items-center text-center">
                                <span class="grid size-8 place-items-center rounded-full bg-white text-xs font-extrabold text-navy-900 shadow">{{ $p['number'] ?? '' }}</span>
                                <span class="mt-1 line-clamp-2 text-[10px] font-semibold leading-tight text-white drop-shadow">{{ $p['name'] ?? '' }}</span>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
        @if (! empty($data['notes']))
            <figcaption class="mt-3 text-sm text-slate-600 dark:text-slate-300">{{ $data['notes'] }}</figcaption>
        @endif
    </figure>
@endcomponent
