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
    public function canonicalize(array $content, array $schema, ?Collection $pages = null): array
    {
        $pages = $pages ?? CmsPage::all()->keyBy('slug');
        foreach ($schema as $key => $definition) {
            if (($definition['type'] ?? '') === 'link' && is_string($content[$key] ?? null)) {
                $page = $this->pageFor($content[$key], $pages);
                if ($page && $page->public_slug !== null) {
                    $content[$key] = $this->follow($content[$key], $page);
                }
            }
        }

        return $content;
    }

    /** @return array{slug:string,fragment:string}|null Null when the link does not point at a page. */
    public function parse(?string $href): ?array
    {
        $href = trim((string) $href);
        if ($href === '' || $href[0] === '#' || $href[0] === '?') {
            return null;
        }
        if (preg_match('~^[a-z][a-z0-9+.-]*:~i', $href) || str_starts_with($href, '//')) {
            $origin = parse_url((string) config('app.url'));
            $candidate = parse_url($href);
            if (! preg_match('~^https?://~i', $href) || ! $candidate || strtolower($candidate['host'] ?? '') !== strtolower($origin['host'] ?? '') || ($candidate['port'] ?? (($candidate['scheme'] ?? '') === 'https' ? 443 : 80)) !== ($origin['port'] ?? (($origin['scheme'] ?? '') === 'https' ? 443 : 80))) {
                return null;
            }
            $href = preg_replace('~^https?://[^/?#]+~i', '', $href);
        }
        [$location, $fragment] = array_pad(explode('#', $href, 2), 2, '');
        [$location, $query] = array_pad(explode('?', $location, 2), 2, '');
        parse_str($query, $parameters);
        $slug = ltrim($location, '/');
        if ($slug === 'index.html') {
            $slug = '';
        }
        // WebsiteController serves these selectors when the query string is omitted.
        if ($slug === 'service-details.html') {
            $selector = $parameters['service'] ?? 'exam';
            if (! is_string($selector) || ! preg_match('/^[a-zA-Z0-9_-]{1,150}$/D', $selector)) {
                return null;
            }
            $slug = 'service-details.html?service='.$selector;
            unset($parameters['service']);
        } elseif ($slug === 'article-details.html') {
            $selector = $parameters['article'] ?? '1';
            if (! is_string($selector) || ! preg_match('/^[a-zA-Z0-9_-]{1,150}$/D', $selector)) {
                return null;
            }
            $slug = 'article-details.html?article='.$selector;
            unset($parameters['article']);
        }

        return ['slug' => $slug, 'fragment' => $fragment, 'query' => http_build_query($parameters)];
    }

    /** @param Collection<string, CmsPage> $pagesBySlug */
    public function pageFor(?string $href, Collection $pagesBySlug): ?CmsPage
    {
        $parsed = $this->parse($href);

        if (! $parsed) {
            return null;
        }
        $page = $pagesBySlug->get($parsed['slug']);
        if (! $page) {
            $destination = \Illuminate\Support\Facades\DB::table('cms_redirects')->where('from', $parsed['slug'])->where('status', 301)->value('to');
            $page = $destination !== null ? $pagesBySlug->get($destination) : null;
        }

        return $page;
    }

    public function href(CmsPage $page, string $fragment = ''): string
    {
        return '/'.$page->slug.($fragment !== '' ? '#'.$fragment : '');
    }

    /** Points a stored link at the page's current URL, leaving it untouched when already correct. */
    public function follow(?string $href, CmsPage $page): string
    {
        $parsed = $this->parse($href);
        if ($parsed && $parsed['slug'] === $page->slug && $page->public_slug === null) {
            return (string) $href;
        }

        return '/'.$page->slug.(! empty($parsed['query']) ? '?'.$parsed['query'] : '').(! empty($parsed['fragment']) ? '#'.$parsed['fragment'] : '');
    }
}
