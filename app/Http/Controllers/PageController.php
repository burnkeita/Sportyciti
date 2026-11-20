<?php

namespace App\Http\Controllers;

use App\Support\Seo;
use Illuminate\View\View;

class PageController extends Controller
{
    private const TITLES = [
        'about' => 'About SportyCiti',
        'contact' => 'Contact us',
        'advertise' => 'Advertise with SportyCiti',
        'privacy' => 'Privacy policy',
        'terms' => 'Terms of use',
        'cookies' => 'Cookie policy',
        'editorial-policy' => 'Editorial standards',
        'corrections' => 'Corrections policy',
    ];

    public function show(string $page, Seo $seo): View
    {
        $title = self::TITLES[$page];
        $seo->set($title, null, route('pages.show', $page));
        $seo->breadcrumb('Home', route('home'))->breadcrumb($title);

        return view("static.{$page}", ['title' => $title]);
    }
}
