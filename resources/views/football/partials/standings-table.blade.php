@php
    $view = $view ?? 'all';
    $compact = $compact ?? false;
    $highlight = $highlight ?? [];
@endphp
@foreach ($standings as $group => $rows)
    <div class="card overflow-hidden {{ ! $loop->last ? 'mb-6' : '' }}">
        @if ($group !== '')
            <h3 class="border-b border-slate-100 bg-slate-50/80 px-4 py-2.5 text-sm font-bold text-navy-900 dark:border-white/5 dark:bg-white/5 dark:text-white">{{ $group }}</h3>
        @endif
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <caption class="sr-only">{{ $caption ?? 'League table' }}{{ $group !== '' ? ' — '.$group : '' }}</caption>
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase tracking-wider text-slate-500 dark:border-white/5">
                        <th scope="col" class="w-10 py-2.5 pl-4 text-left font-semibold">#</th>
                        <th scope="col" class="py-2.5 text-left font-semibold">Team</th>
                        <th scope="col" class="px-2 py-2.5 text-center font-semibold" title="Played">P</th>
                        @unless ($compact)
                            <th scope="col" class="hidden px-2 py-2.5 text-center font-semibold sm:table-cell" title="Won">W</th>
                            <th scope="col" class="hidden px-2 py-2.5 text-center font-semibold sm:table-cell" title="Drawn">D</th>
                            <th scope="col" class="hidden px-2 py-2.5 text-center font-semibold sm:table-cell" title="Lost">L</th>
                            <th scope="col" class="hidden px-2 py-2.5 text-center font-semibold md:table-cell" title="Goals for">GF</th>
                            <th scope="col" class="hidden px-2 py-2.5 text-center font-semibold md:table-cell" title="Goals against">GA</th>
                        @endunless
                        <th scope="col" class="px-2 py-2.5 text-center font-semibold" title="Goal difference">GD</th>
                        <th scope="col" class="px-3 py-2.5 text-center font-semibold" title="Points">Pts</th>
                        @unless ($compact)
                            <th scope="col" class="hidden py-2.5 pr-4 text-left font-semibold lg:table-cell">Form</th>
                        @endunless
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @foreach ($rows as $row)
                        @php
                            $split = $view === 'all' ? null : ($row->{$view} ?? null);
                            $p = $split['played'] ?? $row->played; $w = $split['won'] ?? $row->won; $d = $split['drawn'] ?? $row->drawn; $l = $split['lost'] ?? $row->lost;
                            $gf = $split['goals_for'] ?? $row->goals_for; $ga = $split['goals_against'] ?? $row->goals_against;
                            $pts = $split ? ($w * 3 + $d) : $row->points;
                        @endphp
                        <tr class="{{ in_array($row->team_id, $highlight, true) ? 'bg-pitch-50 dark:bg-pitch-500/10' : 'hover:bg-slate-50 dark:hover:bg-white/5' }}">
                            <td class="py-2.5 pl-4 font-semibold tabular-nums text-slate-500" @if ($row->description) title="{{ $row->description }}" @endif>
                                <span class="inline-flex items-center gap-1.5">
                                    @if ($row->description)<span class="size-1.5 rounded-full {{ str_contains(strtolower($row->description), 'relegation') ? 'bg-red-500' : 'bg-pitch-500' }}"></span>@endif
                                    {{ $row->rank }}
                                </span>
                            </td>
                            <td class="py-2.5">
                                <a href="{{ $row->team->url() }}" class="flex items-center gap-2 font-semibold text-navy-900 hover:text-pitch-700 dark:text-white">
                                    <x-team-logo :team="$row->team" size="size-5" />
                                    <span class="truncate">{{ $compact ? $row->team->displayName() : $row->team->name }}</span>
                                </a>
                            </td>
                            <td class="px-2 py-2.5 text-center tabular-nums">{{ $p }}</td>
                            @unless ($compact)
                                <td class="hidden px-2 py-2.5 text-center tabular-nums sm:table-cell">{{ $w }}</td>
                                <td class="hidden px-2 py-2.5 text-center tabular-nums sm:table-cell">{{ $d }}</td>
                                <td class="hidden px-2 py-2.5 text-center tabular-nums sm:table-cell">{{ $l }}</td>
                                <td class="hidden px-2 py-2.5 text-center tabular-nums md:table-cell">{{ $gf }}</td>
                                <td class="hidden px-2 py-2.5 text-center tabular-nums md:table-cell">{{ $ga }}</td>
                            @endunless
                            <td class="px-2 py-2.5 text-center tabular-nums">{{ ($gf - $ga) > 0 ? '+' : '' }}{{ $gf - $ga }}</td>
                            <td class="px-3 py-2.5 text-center font-extrabold tabular-nums text-navy-900 dark:text-white">{{ $pts }}</td>
                            @unless ($compact)
                                <td class="hidden py-2.5 pr-4 lg:table-cell">
                                    @if ($row->form && $view === 'all')
                                        <span class="flex gap-1">@foreach (str_split(strrev(substr($row->form, 0, 5))) as $r)<x-form-badge :result="$r" />@endforeach</span>
                                    @endif
                                </td>
                            @endunless
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach
