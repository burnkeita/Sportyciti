@extends('static._layout')

{{-- DRAFT — review with legal counsel before launch. --}}
@section('body')
    <p><em>Last updated: {{ \Illuminate\Support\Carbon::parse('2026-11-15')->format('j F Y') }}</em></p>

    <p>These terms apply to your use of sportyciti.com. By using the site you agree to them. If you do not agree, please do not use the site.</p>

    <h2>1. Our content</h2>
    <p>Articles, photographs, videos, graphics and the SportyCiti name and logo belong to SportyCiti or its licensors. You may read and share links to our content for personal, non-commercial use. You may not copy, republish, sell or systematically scrape our content, or use it to train automated systems, without our written permission.</p>

    <h2>2. Sports data</h2>
    <p>Scores, fixtures, tables and statistics are supplied by licensed third-party data providers and may occasionally be delayed, incomplete or inaccurate. They are provided for information only. Do not rely on them for any decision where accuracy is critical.</p>

    <h2>3. Accounts</h2>
    <ul>
        <li>You must give accurate information and keep your password secure. You are responsible for activity on your account.</li>
        <li>You must be at least 13 years old to create an account.</li>
        <li>We may suspend or close accounts that break these terms.</li>
    </ul>

    <h2>4. Community rules for comments</h2>
    <p>Comments are moderated and may be edited or removed. You keep ownership of what you post, but you give SportyCiti a free, worldwide licence to publish and display it on our services. Do not post anything that:</p>
    <ul>
        <li>is abusive, threatening, harassing, hateful or discriminatory;</li>
        <li>is defamatory, knowingly false or infringes someone else’s rights;</li>
        <li>shares another person’s personal information;</li>
        <li>is spam, advertising, or links to unlawful content;</li>
        <li>impersonates another person or organisation.</li>
    </ul>

    <h2>5. Sponsored content and advertising</h2>
    <p>Sponsored articles are always labelled. Advertisers are responsible for their advertisements; inclusion of an advertisement is not an endorsement. We do not accept gambling or betting advertising.</p>

    <h2>6. Links to other websites</h2>
    <p>We are not responsible for the content or practices of websites we link to.</p>

    <h2>7. Liability</h2>
    <p>We work hard to be accurate, but the site is provided “as is”. To the extent the law allows, we are not liable for loss arising from your use of the site or reliance on its content. Nothing in these terms limits liability that cannot be limited by law.</p>

    <h2>8. Changes and governing law</h2>
    <p>We may update these terms and will post changes on this page. These terms are governed by the laws of the Republic of Ghana, and the courts of Ghana have jurisdiction over any dispute.</p>

    <h2>9. Contact</h2>
    <p>Questions about these terms: <a href="mailto:{{ \App\Models\Setting::get('site.contact_email', 'newsroom@sportyciti.com') }}">{{ \App\Models\Setting::get('site.contact_email', 'newsroom@sportyciti.com') }}</a>. Corrections: see our <a href="{{ route('pages.show', 'corrections') }}">corrections policy</a>.</p>
@endsection
