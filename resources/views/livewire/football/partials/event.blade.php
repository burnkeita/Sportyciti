<p class="text-sm font-semibold text-navy-900 dark:text-white">{{ $event->player_name ?? '—' }}</p>
<p class="text-xs text-slate-500">
    @if ($event->type === 'subst')
        Substitution{{ $event->assist_name ? ' · ⇄ '.$event->assist_name : '' }}
    @elseif ($event->type === 'goal' && $event->assist_name)
        {{ $event->detail }} · assist {{ $event->assist_name }}
    @else
        {{ $event->detail }}
    @endif
</p>
