<x-mail::message>
# Confirm your subscription

Thanks for signing up for the SportyCiti briefing — breaking news, big-match previews and our best analysis.

Please confirm that this is your email address:

<x-mail::button :url="$confirmUrl" color="success">
Confirm subscription
</x-mail::button>

If you didn’t request this, you can ignore this email and you won’t be subscribed.

Thanks,<br>
The SportyCiti newsroom

<small>[Unsubscribe]({{ $unsubscribeUrl }})</small>
</x-mail::message>
