@php $columns = array_values(array_filter([$data['col1'] ?? null, $data['col2'] ?? null, $data['col3'] ?? null, $data['col4'] ?? null], fn ($c) => filled($c))); @endphp
@component('articles.blocks._frame', ['data' => $data, 'title' => $data['title'] ?? 'Statistics', 'icon' => 'table'])
    <div class="-mx-5 overflow-x-auto px-5">
        <table class="w-full min-w-[28rem] text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wider text-slate-500 dark:border-white/10">
                    <th scope="col" class="py-2 pr-4 font-semibold">{{ $data['label_heading'] ?? '' }}</th>
                    @foreach ($columns as $col)
                        <th scope="col" class="px-2 py-2 text-right font-semibold">{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                @foreach ($data['rows'] ?? [] as $row)
                    <tr>
                        <th scope="row" class="py-2 pr-4 text-left font-medium text-navy-900 dark:text-white">{{ $row['label'] ?? '' }}</th>
                        @foreach (array_keys($columns) as $i)
                            <td class="px-2 py-2 text-right tabular-nums">{{ $row['v'.($i + 1)] ?? '' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endcomponent
