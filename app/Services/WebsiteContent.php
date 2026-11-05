<?php

namespace App\Services;

use App\Models\CmsPage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class WebsiteContent
{
    /** Resolve shared identity and contact references in memory, preserving per-page overrides. */
    public function apply(CmsPage $page, Collection $globals): void
    {
        $this->hideUnavailablePlacements($page, $globals);
        $settings = $globals->get('global-site');
        if (! $settings || ! $settings->is_visible) {
            return;
        }
        $defaults = json_decode(File::get(database_path('content/globals.json')), true, 512, JSON_THROW_ON_ERROR);
        $map = [];
        foreach ($defaults as $key => $original) {
            if ($original !== '' && isset($settings->content[$key])) {
                $map[$original] = $settings->content[$key];
            }
        }
        $replace = function ($value) use (&$replace, $map) {
            if (is_string($value)) {
                return strtr($value, $map);
            }if (is_array($value)) {
                return array_map($replace, $value);
            }

            return $value;
        };
        $page->title = $replace($page->title);
        $page->description = $replace($page->description);
        $page->seo = $replace($page->seo);
        foreach ($page->sections->concat($globals->values())->unique('id') as $section) {
            if ($section->key === 'global-site') {
                continue;
            }$section->content = $replace($section->content);
            foreach ($section->items as $item) {
                $item->content = $replace($item->content);
            }
        }
    }

    /**
     * A public link must never lead to a page visitors cannot open. Items disappear (the menu
     * handles its own). Fixed buttons stay in place and lead to the nearest live page instead.
     */
    private function hideUnavailablePlacements(CmsPage $page, Collection $globals): void
    {
        $pages = CmsPage::get(['id', 'key', 'type', 'slug', 'is_visible'])->keyBy('id');
        $available = $pages->where('is_visible', true)->keys()->flip();
        foreach ($page->sections->concat($globals->values())->unique('id') as $section) {
            $content = $section->content;
            foreach ($section->page_links ?? [] as $field => $id) {
                // A removed page has a redirect at its old URL, so its link needs no change.
                if ($pages->has($id) && ! isset($available[$id]) && isset($content[$field])) {
                    $content[$field] = $this->nearestLivePage($pages->get($id), $pages);
                }
            }
            if ($content !== $section->content) {
                $section->content = $content;
            }
            $section->setRelation('items', $section->items->reject(
                fn ($item) => $item->target_page_id && ! isset($available[$item->target_page_id]) && ! in_array($item->group_key, CmsMenu::GROUPS, true)
            ));
        }
    }

    private function nearestLivePage(CmsPage $hidden, Collection $pages): string
    {
        $listing = config('cms.listings.'.$hidden->type.'.page_key');
        foreach ([$listing, 'index'] as $key) {
            $target = $key ? $pages->first(fn ($page) => $page->key === $key && $page->is_visible && $page->id !== $hidden->id) : null;
            if ($target) {
                return '/'.$target->slug;
            }
        }

        return '#';
    }
}
