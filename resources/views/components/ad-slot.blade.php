@props(['placement', 'class' => ''])
@php
    $ads = app(\App\Services\Ads\AdService::class);
    $ad = $ads->forPlacement($placement);
    if ($ad) {
        $ads->recordImpression($ad);
    }
    $adsenseClient = \App\Models\Setting::get('ads.adsense_client');
@endphp
@if ($ad)
    <aside class="{{ $class }} flex flex-col items-center" aria-label="Advertisement">
        <span class="mb-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">Advertisement</span>
        @switch($ad->type)
            @case('image')
                <a href="{{ route('ads.click', $ad) }}" target="_blank" rel="sponsored noopener" class="block max-w-full">
                    <picture>
                        @if ($ad->mobileImageUrl())
                            <source media="(max-width: 640px)" srcset="{{ $ad->mobileImageUrl() }}">
                        @endif
                        <img src="{{ $ad->imageUrl() }}" alt="{{ $ad->alt_text ?: 'Advertisement: '.($ad->advertiser ?: $ad->name) }}" loading="lazy" decoding="async" class="mx-auto h-auto max-w-full rounded">
                    </picture>
                </a>
                @break
            @case('adsense')
                @if ($adsenseClient && $ad->adsense_slot)
                    <ins class="adsbygoogle block w-full" data-ad-client="{{ $adsenseClient }}" data-ad-slot="{{ $ad->adsense_slot }}" data-ad-format="auto" data-full-width-responsive="true"></ins>
                    <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
                @endif
                @break
            @case('html')
                {{-- Admin-authored HTML/JS from trusted ad partners (restricted by the "manage advertisements" permission). --}}
                <div class="w-full overflow-hidden">{!! $ad->html_code !!}</div>
                @break
        @endswitch
    </aside>
@endif
