@extends('static._layout')

{{-- DRAFT prepared to reflect what the platform actually collects. Must be reviewed by legal counsel
     (Ghana Data Protection Act, 2012 (Act 843); GDPR/UK GDPR if targeting EU/UK readers) before launch. --}}
@php $contact = \App\Models\Setting::get('site.contact_email', 'newsroom@sportyciti.com'); @endphp

@section('body')
    <p><em>Last updated: {{ \Illuminate\Support\Carbon::parse('2026-11-15')->format('j F Y') }}</em></p>

    <p>SportyCiti (“we”, “us”) publishes sports news and information at sportyciti.com. This policy explains what personal data we collect, why we collect it, how long we keep it and the rights you have. We process personal data in line with Ghana’s Data Protection Act, 2012 (Act 843).</p>

    <h2>1. Who is responsible for your data</h2>
    <p>SportyCiti is the data controller for personal data collected through this website. Contact us about privacy at <a href="mailto:privacy@sportyciti.com">privacy@sportyciti.com</a>.</p>

    <h2>2. What we collect and why</h2>
    <table>
        <thead><tr><th>Data</th><th>When</th><th>Purpose</th><th>Basis</th></tr></thead>
        <tbody>
            <tr><td>Name, email address, password (stored only as a secure hash)</td><td>You create an account</td><td>Provide your account, saved articles, followed sections and commenting</td><td>Performing our agreement with you</td></tr>
            <tr><td>Email address</td><td>You subscribe to the newsletter</td><td>Send the newsletter you confirmed (double opt-in)</td><td>Your consent, which you can withdraw at any time</td></tr>
            <tr><td>Comments you post and a one-way hash of your IP address</td><td>You comment</td><td>Publish and moderate comments; prevent abuse and spam</td><td>Our legitimate interest in a safe community</td></tr>
            <tr><td>Saved articles and followed sections</td><td>You use those features</td><td>Personalise your feed</td><td>Performing our agreement with you</td></tr>
            <tr><td>Aggregated article view counts</td><td>You read articles</td><td>Understand which stories are read. Counts are not linked to your identity</td><td>Legitimate interest</td></tr>
            <tr><td>Session and security cookies, your timezone</td><td>You visit the site</td><td>Keep the site working and secure; show kick-off times in your local time</td><td>Necessary for the service</td></tr>
            <tr><td>Sign-in time and IP address of staff accounts</td><td>Staff sign in to the newsroom</td><td>Security and audit trail</td><td>Legitimate interest / legal obligation</td></tr>
        </tbody>
    </table>
    <p>We do not sell personal data. We do not knowingly collect data from children under 13; if you believe a child has created an account, contact us and we will delete it.</p>

    <h2>3. Who we share it with</h2>
    <ul>
        <li><strong>Service providers</strong> who host the website, send email and store backups for us, under contracts requiring them to protect your data.</li>
        <li><strong>Advertising partners</strong> such as Google AdSense, where enabled. They may use cookies as described in our <a href="{{ route('pages.show', 'cookies') }}">cookie policy</a>.</li>
        <li><strong>Authorities</strong> where we are legally required to do so.</li>
    </ul>
    <p>Some providers may process data outside Ghana. Where they do, we take steps to ensure your data receives an adequate level of protection.</p>

    <h2>4. How long we keep it</h2>
    <ul>
        <li>Account data: while your account is active, and deleted within 30 days of a deletion request (except where we must keep it by law).</li>
        <li>Newsletter: until you unsubscribe; unconfirmed sign-ups are removed after 30 days.</li>
        <li>Comments: while the article is published or until you ask us to remove them.</li>
        <li>Security and audit logs: up to 12 months. Backups are overwritten on a rolling basis.</li>
    </ul>

    <h2>5. Security</h2>
    <p>We use encrypted connections (HTTPS), hash passwords, encrypt sensitive settings, restrict staff access by role with two-factor authentication, and keep audit logs of administrative changes. No system is perfectly secure, but we work to protect your data and will notify affected users and the authorities of a breach where the law requires.</p>

    <h2>6. Your rights</h2>
    <p>Subject to the law, you may ask us to:</p>
    <ul>
        <li>confirm whether we hold your personal data and give you a copy;</li>
        <li>correct inaccurate data or delete data;</li>
        <li>stop using your data for direct marketing (every newsletter has an unsubscribe link);</li>
        <li>object to or restrict other processing.</li>
    </ul>
    <p>Email <a href="mailto:privacy@sportyciti.com">privacy@sportyciti.com</a>. We will respond within a reasonable time and in any event within the period the law allows. If you are not satisfied, you may complain to Ghana’s Data Protection Commission.</p>

    <h2>7. Changes</h2>
    <p>We will post any changes to this policy on this page and update the date above. For significant changes we will notify registered users by email.</p>

    <p>General enquiries: <a href="mailto:{{ $contact }}">{{ $contact }}</a></p>
@endsection
