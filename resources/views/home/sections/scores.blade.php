<section class="container-page" aria-labelledby="sec-{{ $section->id }}">
    <x-section-header :title="$section->title" :href="route('football.scores')" link-text="Live scores" id="sec-{{ $section->id }}">
        <a href="{{ route('football.fixtures') }}" class="hidden text-sm font-semibold text-slate-600 hover:text-pitch-600 sm:inline dark:text-slate-300">Fixtures</a>
        <a href="{{ route('football.competitions.index') }}" class="hidden text-sm font-semibold text-slate-600 hover:text-pitch-600 sm:inline dark:text-slate-300">Tables</a>
    </x-section-header>
    <livewire:football.live-widget :limit="$section->item_limit" variant="strip" lazy />
</section>
