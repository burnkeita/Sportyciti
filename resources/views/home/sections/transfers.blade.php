@if ($items->isNotEmpty())
    <section class="container-page" aria-labelledby="sec-{{ $section->id }}">
        <x-section-header :title="$section->title" :href="route('football.transfers.index')" id="sec-{{ $section->id }}" />
        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($items as $transfer)
                @include('football.partials.transfer-card', ['transfer' => $transfer])
            @endforeach
        </div>
    </section>
@endif
