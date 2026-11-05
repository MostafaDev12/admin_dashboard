<?php

namespace App\Services;

use App\Models\CmsItem;
use App\Models\CmsPage;
use App\Models\CmsSection;
use App\Models\CmsTrashEntry;

class CmsMediaReferences
{
    public function isUsed(string $path): bool
    {
        $matches = function ($value) use (&$matches, $path): bool {
            if (is_array($value)) {
                foreach ($value as $nested) {
                    if ($matches($nested)) {
                        return true;
                    }
                }

                return false;
            }
            if (! is_string($value)) {
                return false;
            }
            // Trash snapshots hold content as stored JSON text.
            if (str_starts_with($value, '{') || str_starts_with($value, '[')) {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    return $matches($decoded);
                }
            }
            // Match local paths, equivalent absolute URLs, and cache-busting queries.
            $candidate = parse_url($value, PHP_URL_PATH);

            return is_string($candidate) && rawurldecode($candidate) === rawurldecode($path);
        };
        foreach ([CmsSection::class, CmsItem::class] as $model) {
            foreach ($model::cursor() as $record) {
                if ($matches($record->content)) {
                    return true;
                }
            }
        }
        foreach (CmsPage::cursor() as $page) {
            if ($matches($page->seo) || $matches($page->listing)) {
                return true;
            }
        }
        // Removing a file a restorable record needs would make its restore incomplete.
        foreach (CmsTrashEntry::where('type', '!=', 'archive')->cursor() as $entry) {
            if ($matches($entry->payload)) {
                return true;
            }
        }

        return false;
    }
}
