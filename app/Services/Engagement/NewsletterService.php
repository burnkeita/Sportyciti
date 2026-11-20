<?php

namespace App\Services\Engagement;

use App\Mail\ConfirmNewsletterSubscription;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Double opt-in newsletter subscription. Responses never reveal whether an
 * address was already subscribed (prevents email enumeration).
 */
class NewsletterService
{
    public function subscribe(string $email, string $source = 'form', ?string $ip = null): NewsletterSubscriber
    {
        $email = Str::lower(trim($email));
        $subscriber = NewsletterSubscriber::firstOrNew(['email' => $email]);

        if ($subscriber->exists && $subscriber->status === 'confirmed') {
            return $subscriber;
        }

        $subscriber->fill([
            'status' => 'pending',
            'source' => $source,
            'ip_hash' => $ip ? hash_hmac('sha256', $ip, (string) config('app.key')) : null,
        ]);
        $subscriber->token = Str::random(48);
        $subscriber->save();

        Mail::to($email)->queue(new ConfirmNewsletterSubscription($subscriber));

        return $subscriber;
    }
}
