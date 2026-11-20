<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\Video;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MultimediaController extends Controller
{
    public function videos(Request $request, Seo $seo): View
    {
        $type = array_key_exists((string) $request->query('type'), Video::TYPES) ? $request->query('type') : null;

        $videos = Video::query()->published()->with('thumbnail')
            ->when($type, fn ($q) => $q->where('video_type', $type))
            ->latest('published_at')->paginate(12)->withQueryString();

        $seo->set('Videos — interviews, press conferences and analysis', 'Watch SportyCiti interviews, press conferences, match analysis and original sports programmes.', route('videos.index'));
        $seo->breadcrumb('Home', route('home'))->breadcrumb('Videos');

        return view('multimedia.videos', [
            'featured' => $videos->onFirstPage() && ! $type ? Video::query()->published()->with('thumbnail')->where('is_featured', true)->latest('published_at')->first() : null,
            'videos' => $videos,
            'types' => Video::TYPES,
            'activeType' => $type,
        ]);
    }

    public function video(string $slug, Seo $seo): View
    {
        $video = Video::query()->published()->where('slug', $slug)->with(['thumbnail', 'category'])->firstOrFail();
        $video->increment('view_count');

        $seo->set($video->title, $video->description ? str($video->description)->limit(155)->toString() : null, $video->url(), $video->thumbnailUrl());
        $seo->type = 'video.other';
        $seo->breadcrumb('Home', route('home'))->breadcrumb('Videos', route('videos.index'))->breadcrumb($video->title);
        $seo->schema(array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'VideoObject',
            'name' => $video->title,
            'description' => $video->description ?: $video->title,
            'thumbnailUrl' => $video->thumbnailUrl(),
            'uploadDate' => $video->published_at?->toIso8601String(),
            'embedUrl' => $video->embedUrl(),
            'contentUrl' => $video->fileUrl(),
            'duration' => $video->duration_seconds ? 'PT'.$video->duration_seconds.'S' : null,
        ]));

        return view('multimedia.video', [
            'video' => $video,
            'more' => Video::query()->published()->with('thumbnail')->whereKeyNot($video->id)->latest('published_at')->limit(6)->get(),
        ]);
    }

    public function galleries(Seo $seo): View
    {
        $seo->set('Photo galleries', 'Sports photography from SportyCiti.', route('galleries.index'));
        $seo->breadcrumb('Home', route('home'))->breadcrumb('Galleries');

        return view('multimedia.galleries', [
            'galleries' => Gallery::query()->published()->with(['cover', 'items.media'])->withCount('items')->latest('published_at')->paginate(12),
        ]);
    }

    public function gallery(string $slug, Seo $seo): View
    {
        $gallery = Gallery::query()->published()->where('slug', $slug)->with(['items.media', 'cover'])->firstOrFail();

        $seo->set($gallery->title, $gallery->description ? str($gallery->description)->limit(155)->toString() : null, $gallery->url(), $gallery->coverMedia()?->url('lg'));
        $seo->breadcrumb('Home', route('home'))->breadcrumb('Galleries', route('galleries.index'))->breadcrumb($gallery->title);

        return view('multimedia.gallery', ['gallery' => $gallery]);
    }
}
