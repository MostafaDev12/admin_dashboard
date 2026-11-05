<?php

namespace App\Services;

use App\Models\CmsPage;
use Illuminate\Support\Collection;

/**
 * Understands the link formats stored in content. Only used when a relationship is established
 * or a link is rewritten; public rendering relies on the stored relationship instead.
 */
class CmsPageLinks
{
    /** @return array{slug:string,fragment:string}|null Null when the link does not point at a page. */
    public function parse(?string $href): ?array
    {
        $href = trim((string) $href);
        if ($href === '' || $href[0] === '#') {
            return null;
        }
        if (preg_match('~^[a-z][a-z0-9+.-]*:~i', $href) || str_starts_with($href, '//')) {
            $host = parse_url((string) config('app.url'), PHP_URL_HOST);
            if (! preg_match('~^https?://~i', $href) || ! $host || parse_url($href, PHP_URL_HOST) !== $host) {
                return null;
            }
            $href = preg_replace('~^https?://[^/?#]+~i', '', $href);
        }
        [$location, $fragment] = array_pad(explode('#', $href, 2), 2, '');
        $slug = ltrim($location, '/');
        if ($slug === 'index.html') {
            $slug = '';
        }
        // WebsiteController serves these selectors when the query string is omitted.
        if ($slug === 'service-details.html') {
            $slug = 'service-details.html?service=exam';
        } elseif ($slug === 'article-details.html') {
            $slug = 'article-details.html?article=1';
        }

        return ['slug' => $slug, 'fragment' => $fragment];
    }

    /** @param Collection<string, CmsPage> $pagesBySlug */
    public function pageFor(?string $href, Collection $pagesBySlug): ?CmsPage
    {
        $parsed = $this->parse($href);

        return $parsed ? $pagesBySlug->get($parsed['slug']) : null;
    }

    public function href(CmsPage $page, string $fragment = ''): string
    {
        return '/'.$page->slug.($fragment !== '' ? '#'.$fragment : '');
    }

    /** Points a stored link at the page's current URL, leaving it untouched when already correct. */
    public function follow(?string $href, CmsPage $page): string
    {
        $parsed = $this->parse($href);
        if ($parsed && $parsed['slug'] === $page->slug) {
            return (string) $href;
        }

        return $this->href($page, $parsed['fragment'] ?? '');
    }
}
