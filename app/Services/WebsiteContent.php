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
}
