@extends('static._layout')

{{-- DRAFT — review with legal counsel before launch. If personalised advertising is enabled for EU/UK
     visitors, a certified consent management platform (CMP) is required by Google. --}}
@section('body')
    <p><em>Last updated: {{ \Illuminate\Support\Carbon::parse('2026-11-15')->format('j F Y') }}</em></p>

    <p>Cookies are small files stored by your browser. We keep cookies to a minimum. This page lists what SportyCiti uses and how to control it.</p>

    <h2>Strictly necessary</h2>
    <p>These make the site work and cannot be switched off.</p>
    <table>
        <thead><tr><th>Name</th><th>Purpose</th><th>Duration</th></tr></thead>
        <tbody>
            <tr><td><code>sportyciti_session</code></td><td>Keeps you signed in and remembers your session; encrypted</td><td>2 hours, or until you close the browser</td></tr>
            <tr><td><code>XSRF-TOKEN</code></td><td>Protects forms against cross-site request forgery</td><td>2 hours</td></tr>
            <tr><td><code>remember_web_*</code></td><td>Keeps you signed in when you tick “Keep me signed in”</td><td>Up to 5 years, or until you sign out</td></tr>
        </tbody>
    </table>

    <h2>Functional</h2>
    <table>
        <thead><tr><th>Name</th><th>Purpose</th><th>Duration</th></tr></thead>
        <tbody>
            <tr><td><code>tz</code> (cookie)</td><td>Your timezone, so fixtures and kick-off times show in your local time</td><td>1 year</td></tr>
            <tr><td><code>theme</code> (browser storage)</td><td>Remembers light or dark mode</td><td>Until you clear it</td></tr>
        </tbody>
    </table>

    <h2>Advertising</h2>
    <p>When advertising partners such as Google AdSense are enabled, they may set cookies to show and measure ads and limit how often you see them. These are controlled by the partner. See <a href="https://policies.google.com/technologies/ads" rel="noopener" target="_blank">how Google uses cookies in advertising</a> and manage your choices at <a href="https://adssettings.google.com" rel="noopener" target="_blank">Google Ad Settings</a>.</p>
    <p>Clicks on direct advertisements on SportyCiti are counted by our own server; this does not use a cookie.</p>

    <h2>Embedded content</h2>
    <p>Videos are embedded using YouTube’s privacy-enhanced mode (<code>youtube-nocookie.com</code>), which does not set YouTube cookies until you play a video.</p>

    <h2>Managing cookies</h2>
    <p>You can block or delete cookies in your browser settings. Blocking strictly necessary cookies will stop sign-in and forms from working.</p>
@endsection
