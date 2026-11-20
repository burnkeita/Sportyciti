<?php

namespace App\Services\Content;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer as SymfonySanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Sanitises editorial HTML before it is stored. Allows safe formatting, images,
 * tables, and iframes only from approved embed hosts (YouTube, X/Twitter, Instagram).
 */
class HtmlSanitizer
{
    private SymfonySanitizer $sanitizer;

    public function __construct()
    {
        $config = (new HtmlSanitizerConfig)
            ->allowSafeElements()
            ->allowElement('iframe', ['src', 'width', 'height', 'title', 'allow', 'allowfullscreen', 'loading', 'frameborder'])
            ->allowElement('figure', ['class'])
            ->allowElement('figcaption')
            ->allowElement('blockquote', ['class', 'cite', 'data-theme'])
            ->allowAttribute('class', ['p', 'div', 'span', 'blockquote', 'table', 'figure', 'img', 'a', 'ul', 'ol', 'li', 'h2', 'h3', 'h4'])
            ->allowAttribute('data-align', '*')
            ->allowLinkSchemes(['https', 'http', 'mailto'])
            ->allowMediaSchemes(['https', 'http'])
            ->allowRelativeLinks()
            ->allowRelativeMedias()
            ->allowMediaHosts(array_merge(
                ['www.youtube.com', 'youtube.com', 'www.youtube-nocookie.com', 'player.vimeo.com', 'www.instagram.com', 'platform.twitter.com'],
                $this->ownHosts(),
                ['i.ytimg.com', 'media.api-sports.io'],
            ))
            ->forceAttribute('a', 'rel', 'noopener noreferrer')
            ->withMaxInputLength(500_000);

        $this->sanitizer = new SymfonySanitizer($config);
    }

    public function sanitize(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $clean = $this->sanitizer->sanitize($html);

        // Lazy-load images and embeds for performance.
        $clean = preg_replace('/<img(?![^>]*\bloading=)/i', '<img loading="lazy" decoding="async"', $clean);
        $clean = preg_replace('/<iframe(?![^>]*\bloading=)/i', '<iframe loading="lazy"', $clean);

        return $clean;
    }

    /** @return list<string> */
    private function ownHosts(): array
    {
        $hosts = [parse_url((string) config('app.url'), PHP_URL_HOST), parse_url((string) config('filesystems.disks.public.url'), PHP_URL_HOST)];

        return array_values(array_filter(array_unique($hosts)));
    }
}
