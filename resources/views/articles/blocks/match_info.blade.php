{{-- Pulls match details from the sports database when the article is linked to a fixture; otherwise uses verified editor input. --}}
@php $fixture = $article->fixture; @endphp
@if ($fixture)
    <section class="card overflow-hidden" aria-label="Match information">
        <header class="flex items-center gap-2 border-b border-slate-100 bg-slate-50/70 px-5 py-3 dark:border-white/5 dark:bg-white/5">
            <x-svg-icon name="calendar" class="size-4 text-flame-500" />
            <h3 class="font-display text-base font-bold text-navy-900 dark:text-white">{{ $data['title'] ?? 'Match information' }}</h3>
        </header>
        <x-fixture-row :fixture="$fixture" show-competition show-date />
        <dl class="grid grid-cols-2 gap-4 border-t border-slate-100 px-5 py-4 text-sm sm:grid-cols-3 dark:border-white/5">
            <div><dt class="text-xs uppercase text-slate-400">Kick-off</dt><dd class="font-medium"><x-local-time :at="$fixture->kickoff_at" /></dd></div>
            @if ($fixture->venue_name)<div><dt class="text-xs uppercase text-slate-400">Venue</dt><dd class="font-medium">{{ $fixture->venue_name }}</dd></div>@endif
            @if ($fixture->referee)<div><dt class="text-xs uppercase text-slate-400">Referee</dt><dd class="font-medium">{{ $fixture->referee }}</dd></div>@endif
        </dl>
    </section>
@else
    @component('articles.blocks._frame', ['data' => $data, 'title' => $data['title'] ?? 'Match information', 'icon' => 'calendar'])
        <dl class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
            @foreach (['home' => 'Home', 'away' => 'Away', 'competition' => 'Competition', 'date' => 'Date', 'venue' => 'Venue'] as $key => $label)
                @if (! empty($data[$key]))
                    <div><dt class="text-xs uppercase text-slate-400">{{ $label }}</dt><dd class="font-medium">{{ $data[$key] }}</dd></div>
                @endif
            @endforeach
        </dl>
    @endcomponent
@endif
