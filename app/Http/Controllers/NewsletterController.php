<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use App\Services\Engagement\NewsletterService;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsletterController extends Controller
{
    /** Non-JS fallback for the Livewire newsletter form. */
    public function subscribe(Request $request, NewsletterService $newsletter): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:191'],
            'website' => ['nullable', 'prohibited'], // honeypot
        ]);

        $newsletter->subscribe($data['email'], 'form', $request->ip());

        return back()->with('status', 'Thanks! Check your inbox to confirm your subscription.');
    }

    public function confirm(string $token, Seo $seo): View
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->firstOrFail();
        $subscriber->confirm();
        $seo->set('Subscription confirmed');
        $seo->noindex = true;

        return view('pages.message', [
            'title' => 'You’re subscribed',
            'message' => 'Thanks for confirming. The SportyCiti newsletter will now arrive in your inbox.',
        ]);
    }

    public function unsubscribe(string $token, Seo $seo): View
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->firstOrFail();
        $subscriber->unsubscribe();
        $seo->set('Unsubscribed');
        $seo->noindex = true;

        return view('pages.message', [
            'title' => 'You’ve been unsubscribed',
            'message' => 'You will no longer receive SportyCiti newsletters. You can re-subscribe at any time.',
        ]);
    }
}
