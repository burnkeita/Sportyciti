@php
    $socialIcons = ['facebook' => 'facebook', 'x' => 'x-social', 'instagram' => 'instagram', 'youtube' => 'youtube', 'tiktok' => 'tiktok', 'whatsapp' => 'whatsapp'];
@endphp
<footer class="mt-16 bg-navy-900 text-slate-300">
    <div class="container-page grid gap-10 py-12 md:grid-cols-2 lg:grid-cols-12">
        <div class="lg:col-span-4">
            <x-logo inverted />
            <p class="mt-4 max-w-sm text-sm leading-relaxed text-slate-400">
                Ghana’s home for sports news, live scores, analysis and insight — with the world’s biggest competitions covered from a Ghanaian perspective.
            </p>
            @if ($socialLinks)
                <div class="mt-5 flex gap-2">
                    @foreach ($socialLinks as $network => $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener me" class="grid size-9 place-items-center rounded-full bg-white/10 text-white transition hover:bg-pitch-500" aria-label="SportyCiti on {{ ucfirst($network) }}">
                            <x-svg-icon :name="$socialIcons[$network] ?? 'link'" class="size-4" />
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="lg:col-span-2">
            <h2 class="mb-3 text-sm font-bold uppercase tracking-wider text-white">Football</h2>
            <ul class="space-y-2 text-sm">
                <li><a class="hover:text-white" href="{{ route('football.scores') }}">Live scores</a></li>
                <li><a class="hover:text-white" href="{{ route('football.fixtures') }}">Fixtures</a></li>
                <li><a class="hover:text-white" href="{{ route('football.results') }}">Results</a></li>
                <li><a class="hover:text-white" href="{{ route('football.competitions.index') }}">Tables</a></li>
                <li><a class="hover:text-white" href="{{ route('football.transfers.index') }}">Transfers</a></li>
            </ul>
        </div>

        <div class="lg:col-span-2">
            <h2 class="mb-3 text-sm font-bold uppercase tracking-wider text-white">Sections</h2>
            <ul class="space-y-2 text-sm">
                @foreach ($navCategories as $category)
                    <li><a class="hover:text-white" href="{{ $category->url() }}">{{ $category->name }}</a></li>
                @endforeach
                <li><a class="hover:text-white" href="{{ route('videos.index') }}">Videos</a></li>
                <li><a class="hover:text-white" href="{{ route('galleries.index') }}">Galleries</a></li>
            </ul>
        </div>

        <div class="lg:col-span-4">
            <h2 class="mb-3 text-sm font-bold uppercase tracking-wider text-white">The SportyCiti briefing</h2>
            <p class="mb-3 text-sm text-slate-400">The day’s essential sports stories, straight to your inbox.</p>
            <livewire:newsletter-form variant="footer" source="footer" />
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="container-page flex flex-col gap-3 py-5 text-xs text-slate-400 md:flex-row md:items-center md:justify-between">
            <p>&copy; {{ now()->year }} {{ $siteName }}. All rights reserved.</p>
            <nav aria-label="Legal" class="flex flex-wrap gap-x-4 gap-y-2">
                <a class="hover:text-white" href="{{ route('pages.show', 'about') }}">About</a>
                <a class="hover:text-white" href="{{ route('pages.show', 'editorial-policy') }}">Editorial standards</a>
                <a class="hover:text-white" href="{{ route('pages.show', 'corrections') }}">Corrections</a>
                <a class="hover:text-white" href="{{ route('pages.show', 'privacy') }}">Privacy</a>
                <a class="hover:text-white" href="{{ route('pages.show', 'cookies') }}">Cookies</a>
                <a class="hover:text-white" href="{{ route('pages.show', 'terms') }}">Terms</a>
                <a class="hover:text-white" href="{{ route('pages.show', 'advertise') }}">Advertise</a>
            </nav>
        </div>
    </div>
</footer>
