@extends('static._layout')

@section('body')
    @php $email = \App\Models\Setting::get('site.contact_email', 'newsroom@sportyciti.com'); @endphp
    <p>We’d love to hear from you — story tips, feedback, corrections and partnership enquiries are all welcome.</p>
    <ul>
        <li><strong>Newsroom & story tips:</strong> <a href="mailto:{{ $email }}">{{ $email }}</a></li>
        <li><strong>Corrections:</strong> see our <a href="{{ route('pages.show', 'corrections') }}">corrections policy</a></li>
        <li><strong>Advertising:</strong> see <a href="{{ route('pages.show', 'advertise') }}">advertise with SportyCiti</a></li>
        <li><strong>Privacy requests:</strong> <a href="mailto:privacy@sportyciti.com">privacy@sportyciti.com</a></li>
    </ul>
@endsection
