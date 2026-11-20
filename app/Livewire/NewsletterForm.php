<?php

namespace App\Livewire;

use App\Services\Engagement\NewsletterService;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;

class NewsletterForm extends Component
{
    #[Validate('required|email:rfc|max:191')]
    public string $email = '';

    /** Honeypot: real visitors never fill this in. */
    public string $website = '';

    #[Locked]
    public string $variant = 'card';

    #[Locked]
    public string $source = 'form';

    public bool $done = false;

    public function mount(string $variant = 'card', string $source = 'form'): void
    {
        $this->variant = $variant;
        $this->source = substr($source, 0, 40);
    }

    public function subscribe(NewsletterService $newsletter): void
    {
        if ($this->website !== '') {
            $this->done = true; // silently drop bots

            return;
        }

        $this->validate();

        $key = 'newsletter:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', 'Too many attempts. Please try again later.');

            return;
        }
        RateLimiter::hit($key, 3600);

        $newsletter->subscribe($this->email, $this->source, request()->ip());

        $this->reset('email');
        $this->done = true;
    }

    public function render()
    {
        return view('livewire.newsletter-form');
    }
}
