<?php

namespace App\Mail;

use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmNewsletterSubscription extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public NewsletterSubscriber $subscriber) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Confirm your SportyCiti newsletter subscription');
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.newsletter-confirm', with: [
            'confirmUrl' => route('newsletter.confirm', $this->subscriber->token),
            'unsubscribeUrl' => route('newsletter.unsubscribe', $this->subscriber->token),
        ]);
    }
}
