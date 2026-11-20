@component('articles.blocks._frame', ['data' => $data, 'title' => $data['title'] ?? 'Team comparison', 'icon' => 'swap'])
    <div class="mb-4 grid grid-cols-[1fr_auto_1fr] items-center gap-3 text-sm font-bold">
        <span class="text-pitch-700 dark:text-pitch-400">{{ $data['team_a'] ?? '' }}</span>
        <span class="text-xs text-slate-400">vs</span>
        <span class="text-right text-navy-700 dark:text-flame-400">{{ $data['team_b'] ?? '' }}</span>
    </div>
    <div class="space-y-3">
        @foreach ($data['rows'] ?? [] as $row)
            @php
                $a = (float) preg_replace('/[^0-9.\-]/', '', (string) ($row['a'] ?? 0));
                $b = (float) preg_replace('/[^0-9.\-]/', '', (string) ($row['b'] ?? 0));
                $total = max(abs($a) + abs($b), 0.0001);
            @endphp
            <div>
                <div class="mb-1 flex justify-between text-sm">
                    <span class="font-bold tabular-nums">{{ $row['a'] ?? '' }}</span>
                    <span class="text-xs text-slate-500">{{ $row['metric'] ?? '' }}</span>
                    <span class="font-bold tabular-nums">{{ $row['b'] ?? '' }}</span>
                </div>
                <div class="flex h-2 gap-1" aria-hidden="true">
                    <div class="flex flex-1 justify-end overflow-hidden rounded-full bg-slate-100 dark:bg-white/10"><div class="rounded-full bg-pitch-500" style="width: {{ round(abs($a) / $total * 100) }}%"></div></div>
                    <div class="flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-white/10"><div class="h-full rounded-full bg-navy-500 dark:bg-flame-500" style="width: {{ round(abs($b) / $total * 100) }}%"></div></div>
                </div>
            </div>
        @endforeach
    </div>
@endcomponent
