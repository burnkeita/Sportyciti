<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Request-scoped SEO metadata collected by controllers and rendered once in the layout.
 */
class Seo
{
    public string $title = '';

    public ?string $description = null;

    public ?string $canonical = null;

    public ?string $image = null;

    public string $type = 'website';

    public bool $noindex = false;

    /** @var list<array> JSON-LD graphs */
    public array $schemas = [];

    /** @var list<array{name: string, url: ?string}> */
    public array $breadcrumbs = [];

    /** @var array<string, string> additional meta property tags (e.g. article:published_time) */
    public array $properties = [];

    public function set(string $title, ?string $description = null, ?string $canonical = null, ?string $image = null): static
    {
        $this->title = $title;
        $this->description = $description;
        $this->canonical = $canonical;
        $this->image = $image;

        return $this;
    }

    public function breadcrumb(string $name, ?string $url = null): static
    {
        $this->breadcrumbs[] = ['name' => $name, 'url' => $url];

        return $this;
    }

    public function schema(array $schema): static
    {
        $this->schemas[] = $schema;

        return $this;
    }

    public function fullTitle(): string
    {
        $site = Setting::get('site.name', config('app.name'));

        if ($this->title === '' || $this->title === $site) {
            return $site.' — '.Setting::get('site.tagline', 'Ghana & World Sports News, Scores and Insights');
        }

        return $this->title.' | '.$site;
    }

    public function metaDescription(): string
    {
        return $this->description ?: Setting::get('site.description', 'SportyCiti brings you breaking Ghanaian and international sports news, live football scores, fixtures, league tables, transfers and expert analysis.');
    }

    public function shouldNoindex(): bool
    {
        return $this->noindex || ! Setting::get('seo.indexing_enabled', true);
    }

    public function breadcrumbSchema(): ?array
    {
        if (count($this->breadcrumbs) < 2) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($this->breadcrumbs)->values()->map(fn ($crumb, $i) => array_filter([
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $crumb['name'],
                'item' => $crumb['url'],
            ]))->all(),
        ];
    }

    public static function organisationSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'NewsMediaOrganization',
            'name' => Setting::get('site.name', config('app.name')),
            'url' => url('/'),
            'logo' => ['@type' => 'ImageObject', 'url' => asset('images/sportyciti-logo.png')],
            'sameAs' => array_values(array_filter(Setting::get('social.links', []))),
        ];
    }
}
