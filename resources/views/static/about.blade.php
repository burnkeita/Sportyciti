@extends('static._layout')

@section('body')
    <p class="lead">SportyCiti is a Ghanaian sports media brand with an international outlook. We cover the Ghana Premier League, the Black Stars and Black Queens, Ghanaians abroad and the world’s biggest competitions — from a perspective that puts Ghanaian sport front and centre.</p>
    <h2>What we do</h2>
    <ul>
        <li><strong>News</strong> — breaking stories and original reporting from Ghana and around the world.</li>
        <li><strong>Live scores and data</strong> — fixtures, results, tables and match centres powered by a licensed sports data provider.</li>
        <li><strong>SportyCiti Insights</strong> — tactical analysis, match previews, statistics and expert opinion.</li>
        <li><strong>Multimedia</strong> — interviews, press conferences, original programmes and photography.</li>
    </ul>
    <h2>Our standards</h2>
    <p>We verify before we publish, attribute our sources and correct mistakes openly. Read our <a href="{{ route('pages.show', 'editorial-policy') }}">editorial standards</a> and <a href="{{ route('pages.show', 'corrections') }}">corrections policy</a>.</p>
    <p>Meet the people behind SportyCiti on our <a href="{{ route('authors.index') }}">editorial team</a> page.</p>
@endsection
