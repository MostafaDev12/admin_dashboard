<?php

namespace App\Services;

use App\Models\CmsItem;
use App\Models\CmsPage;
use App\Models\CmsSection;

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
            // Match local paths, equivalent absolute URLs, and cache-busting queries.
            $candidate = parse_url($value, PHP_URL_PATH);

            return is_string($candidate) && rawurldecode($candidate) === rawurldecode($path);
        };
        foreach ([CmsSection::class, CmsItem::class, CmsPage::class] as $model) {
            foreach ($model::cursor() as $record) {
                if ($matches($record instanceof CmsPage ? $record->seo : $record->content)) {
                    return true;
                }
            }
        }

        return false;
    }
}
