<?php

namespace App\Services;

use App\Models\CmsItem;
use App\Models\CmsMedia;
use App\Models\CmsPage;
use App\Models\CmsSection;
use App\Support\CmsLabels;
use Illuminate\Support\Str;

/**
 * Finds content an editor or owner still has to act on, and exactly where it lives. Read-only:
 * nothing here changes content, and it does not guess replacements.
 */
class CmsContentHealth
{
    public function __construct(private CmsPageLinks $links) {}

    /** @return array<string, array<int, array>> issue type => rows */
    public function report(): array
    {
        $report = ['missing_media' => [], 'broken_links' => [], 'unpublished_links' => [], 'placeholder_links' => [], 'provisional_text' => [], 'detached' => [], 'hidden_by_page' => []];
        $pages = CmsPage::all();
        $bySlug = $pages->keyBy('slug');
        $byId = $pages->keyBy('id');
        $sections = CmsSection::with('page', 'items')->get();
        foreach ($sections as $section) {
            if ($section->is_visible) {
                $this->scan($section, $section->content ?? [], $section->schema ?? [], $this->location($section), route('cms.sections.edit', $section), $bySlug, $report);
            }
            foreach ($section->items as $item) {
                if (CmsMenu::isRetiredItem($item)) {
                    continue;
                }
                $location = $this->location($section).' → '.$item->label;
                $url = route('cms.items.edit', $item);
                $parentVisible = ! $item->parent_id || ($section->items->firstWhere('id', $item->parent_id)?->is_visible ?? false);
                if ($section->is_visible && $item->is_visible && $parentVisible) {
                    $this->scan($item, $item->content ?? [], $item->schema ?? [], $location, $url, $bySlug, $report);
                }
                if ($item->detached_from) {
                    $report['detached'][] = ['location' => $location, 'field' => 'Removed page', 'value' => $item->detached_from, 'url' => $url];
                }
                $target = $byId->get($item->target_page_id);
                if ($target && ! $target->is_visible && $item->is_visible) {
                    $report['hidden_by_page'][] = ['location' => $location, 'field' => 'Hidden while this page is unpublished', 'value' => $target->title, 'url' => $url];
                }
            }
        }

        return $report;
    }

    /** Sections the owner is expected to review, resolved to their editors. */
    public function destinations(): array
    {
        $keys = collect((array) config('cms.destinations'))->pluck('section')->unique();
        $sections = CmsSection::whereIn('key', $keys)->get()->keyBy('key');

        return collect((array) config('cms.destinations'))->map(fn ($row) => $row + ['url' => isset($sections[$row['section']]) ? route('cms.sections.edit', $sections[$row['section']]) : null])->all();
    }

    /** Local images referenced by content but absent from disk, with how often each is used. */
    public function missingImages(): array
    {
        return collect($this->report()['missing_media'])->groupBy('value')->map->count()->all();
    }

    /** Replaces one image path in every image field that uses it. */
    public function replaceImage(string $from, string $to): int
    {
        $count = 0;
        foreach ([CmsSection::class, CmsItem::class] as $model) {
            foreach ($model::all() as $record) {
                $content = $record->content;
                $changed = false;
                foreach ($record->schema ?? [] as $key => $field) {
                    if (($field['type'] ?? '') === 'image' && ($content[$key] ?? null) === $from) {
                        $content[$key] = $to;
                        $changed = true;
                        $count++;
                    }
                }
                if ($changed) {
                    $record->update(['content' => $content]);
                }
            }
        }

        return $count;
    }

    public function replaceableImages()
    {
        return CmsMedia::where('mime', 'like', 'image/%')->orderBy('name')->get();
    }

    private function scan($record, array $content, array $schema, string $location, string $url, $bySlug, array &$report): void
    {
        foreach ($schema as $key => $field) {
            $value = $content[$key] ?? null;
            if (! is_string($value)) {
                continue;
            }
            $row = ['location' => $location, 'field' => CmsLabels::purpose($field), 'value' => $value, 'url' => $url, 'optional' => ! empty($field['optional'])];
            match ($field['type'] ?? '') {
                'image' => $this->scanImage($value, $row, $report),
                'link' => $this->scanLink($value, $record, $bySlug, $row, $report),
                'text' => $this->scanText($value, $row, $report),
                default => null,
            };
        }
    }

    private function scanImage(string $value, array $row, array &$report): void
    {
        if ($value === '' || preg_match('~^(https?:)?//~i', $value)) {
            return;
        }
        $path = public_path(rawurldecode((string) parse_url($value, PHP_URL_PATH)));
        if (! is_file($path)) {
            $report['missing_media'][] = $row;
        }
    }

    private function scanLink(string $value, $record, $bySlug, array $row, array &$report): void
    {
        if ($value === '#' || ($value === '' && empty($row['optional']))) {
            $report['placeholder_links'][] = $row;

            return;
        }
        $parsed = $this->links->parse($value);
        if (! $parsed || str_starts_with($parsed['slug'], 'website/') || str_starts_with($parsed['slug'], 'storage/')) {
            return;
        }
        $page = $bySlug->get($parsed['slug']);
        if (! $page) {
            $report['broken_links'][] = $row;
        } elseif (! $page->is_visible && ! ($record instanceof CmsItem && $record->target_page_id)) {
            // Items with a relationship hide themselves; section-level links cannot.
            $report['unpublished_links'][] = $row;
        }
    }

    private function scanText(string $value, array $row, array &$report): void
    {
        if (Str::contains($value, (array) config('cms.provisional_phrases'))) {
            $report['provisional_text'][] = $row;
        }
    }

    private function location(CmsSection $section): string
    {
        return ($section->page?->title ?? 'Shared content').' → '.Str::limit(CmsLabels::section($section->label), 60);
    }
}
