<div>
    <button type="button" wire:click="toggle" aria-pressed="{{ $saved ? 'true' : 'false' }}"
            class="btn {{ $saved ? 'bg-pitch-500 text-white hover:bg-pitch-600' : 'bg-slate-100 text-navy-900 hover:bg-slate-200 dark:bg-white/10 dark:text-white dark:hover:bg-white/15' }} px-3 py-2">
        <x-svg-icon :name="$saved ? 'bookmark-solid' : 'bookmark'" class="size-4" />
        <span>{{ $saved ? 'Saved' : 'Save' }}</span>
    </button>
</div>
