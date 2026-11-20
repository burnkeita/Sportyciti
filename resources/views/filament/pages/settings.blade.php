<x-filament-panels::page>
    @if (method_exists($this, 'getStatus'))
        <x-filament::section heading="Provider status">
            {{-- Inline styles: custom Tailwind classes are not part of Filament's compiled stylesheet. --}}
            <dl style="display:grid;grid-template-columns:repeat(auto-fit,minmax(11rem,1fr));gap:1rem">
                @foreach ($this->getStatus() as $label => $value)
                    <div>
                        <dt style="font-size:.75rem;opacity:.65">{{ $label }}</dt>
                        <dd style="margin-top:.25rem;font-size:.9rem;font-weight:600">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-filament::section>
    @endif

    <form wire:submit="save" style="display:flex;flex-direction:column;gap:1.5rem">
        {{ $this->form }}

        <div>
            <x-filament::button type="submit" wire:loading.attr="disabled">
                Save settings
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
