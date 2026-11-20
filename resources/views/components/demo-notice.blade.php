@props(['compact' => false])
<div {{ $attributes->merge(['class' => 'flex items-start gap-2 rounded-lg border border-dashed border-flame-400 bg-flame-50 text-flame-800 dark:border-flame-500/50 dark:bg-flame-500/10 dark:text-flame-200 '.($compact ? 'px-3 py-1.5 text-xs' : 'px-4 py-3 text-sm')]) }} role="note">
    <strong class="shrink-0 font-bold uppercase tracking-wide">Demo data</strong>
    <span>
        @if ($compact)
            Fictional teams and results for development — not real matches.
        @else
            These scores, fixtures and tables are <strong>fictional demonstration data</strong> generated for development. They do not represent real teams, matches or results. Live data appears once a licensed sports data provider is configured.
        @endif
    </span>
</div>
