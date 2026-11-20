<div>
    @if ($done)
        <p class="rounded-lg px-4 py-3 text-sm font-medium {{ $variant === 'card' ? 'bg-pitch-50 text-pitch-800 dark:bg-pitch-500/10 dark:text-pitch-200' : 'bg-white/10 text-white' }}" role="status">
            Almost there — check your inbox and click the link to confirm your subscription.
        </p>
    @else
        <form wire:submit="subscribe" class="flex flex-col gap-2 sm:flex-row" novalidate>
            <label for="nl-{{ $this->getId() }}" class="sr-only">Email address</label>
            <input id="nl-{{ $this->getId() }}" type="email" wire:model="email" placeholder="you@example.com" autocomplete="email" required maxlength="191"
                   class="input flex-1 {{ $variant !== 'card' ? '!bg-white/10 !text-white !ring-white/20 placeholder:!text-white/60' : '' }}">
            <div class="hidden" aria-hidden="true">
                <label>Leave this field empty <input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
            </div>
            <button type="submit" class="{{ $variant === 'banner' ? 'btn bg-white text-pitch-700 hover:bg-pitch-50' : 'btn-accent' }}" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="subscribe">Subscribe</span>
                <span wire:loading wire:target="subscribe">Subscribing…</span>
            </button>
        </form>
        @error('email')
            <p class="mt-2 text-sm {{ $variant === 'card' ? 'text-red-600' : 'text-red-200' }}" role="alert">{{ $message }}</p>
        @enderror
        <p class="mt-2 text-xs {{ $variant === 'card' ? 'text-slate-500' : 'text-white/70' }}">Free. Unsubscribe any time. See our <a href="{{ route('pages.show', 'privacy') }}" class="underline">privacy policy</a>.</p>
    @endif
</div>
