<?php

namespace App\Services;

use App\Models\CmsItem;
use App\Models\CmsPage;
use App\Models\CmsSection;
use App\Models\CmsTrashEntry;
use Illuminate\Support\Facades\DB;

class CmsMediaUsage
{
    public function normalize(string $value): ?string
    {
        $parts = parse_url(trim($value));
        if (! $parts || isset($parts['scheme']) && ! in_array($parts['scheme'], ['http', 'https'])) {
            return null;
        }
        if (isset($parts['host'])) {
            $origin = parse_url(config('app.url'));
            if (strtolower($parts['host']) !== strtolower($origin['host'] ?? '') || ($parts['port'] ?? (($parts['scheme'] ?? '') === 'https' ? 443 : 80)) !== ($origin['port'] ?? (($origin['scheme'] ?? '') === 'https' ? 443 : 80))) {
                return null;
            }
        }
        $path = rawurldecode($parts['path'] ?? '');

        return str_starts_with($path, '/') && ! preg_match('~(?:^|/)\.\.(?:/|$)|[\x00-\x1f\\\\]~', $path) ? $path : null;
    }

    /** Inventory actual JSON leaves; clients never supply database columns or JSON pointers. */
    public function locations(string $path): array
    {
        $result = [];
        $settings = CmsSection::where('key', 'global-site')->first();
        $defaults = json_decode(file_get_contents(database_path('content/globals.json')), true);
        $shared = [];
        if ($settings?->is_visible) {
            foreach ($defaults as $key => $original) {
                if ($this->normalize((string) $original) && isset($settings->content[$key])) {
                    $shared[$original] = $settings->content[$key];
                }
            }
        }
        foreach ([CmsSection::class => ['content'], CmsItem::class => ['content'], CmsPage::class => ['seo', 'listing'], CmsTrashEntry::class => ['payload']] as $model => $columns) {
            $records = $model === CmsItem::class ? $model::with('section.page')->get() : ($model === CmsSection::class ? $model::with('page')->get() : $model::all());
            foreach ($records as $record) {
                if ($record instanceof CmsTrashEntry && $record->type === 'archive') {
                    continue;
                }
                foreach ($columns as $column) {
                    $this->walk($record->$column, [], function ($value, $pointer) use (&$result, $path, $record, $column, $model, $shared) {
                        $effective = $record instanceof CmsSection && $record->key === 'global-site' ? $value : ($shared[$value] ?? $value);
                        $inherited = $effective !== $value;
                        $matchesEffective = $this->normalize($effective) === $this->normalize($path);
                        if ((! $matchesEffective && $this->normalize($value) !== $this->normalize($path)) || ! $this->normalize($path)) {
                            return;
                        }
                        $section = $record instanceof CmsItem ? $record->section : ($record instanceof CmsSection ? $record : null);
                        $page = $record instanceof CmsPage ? $record : $section?->page;
                        $retired = $record instanceof CmsItem && CmsMenu::isRetiredItem($record);
                        $imageField = ($record->schema[$pointer[0] ?? '']['type'] ?? '') === 'image';
                        if ($record instanceof CmsPage) {
                            $imageField = count($pointer) === 1 && (($column === 'seo' && $pointer[0] === 'og_image') || ($column === 'listing' && $pointer[0] === 'image'));
                        }
                        $editable = ! ($record instanceof CmsTrashEntry) && ! $retired && $matchesEffective && $imageField;
                        $field = implode('.', $pointer);
                        $caption = \App\Support\CmsLabels::field($record->schema[$pointer[0] ?? ''] ?? ['label' => $field]);
                        if ($record instanceof CmsPage && $imageField) {
                            $caption = $column === 'seo' ? 'Open Graph image' : 'Listing image';
                        }
                        $hidden = ($section && ! $section->is_visible) || ($record instanceof CmsItem && ! $record->is_visible);
                        $state = $retired ? 'Preserved original navigation' : (! $matchesEffective ? 'Stored reference overridden by shared setting' : ($page && ! $page->is_visible ? 'Draft' : ($hidden ? 'Hidden content' : ($inherited ? 'Inherits shared image' : 'Content reference'))));
                        $id = hash('sha256', $model.'|'.$record->id.'|'.$column.'|'.json_encode($pointer));
                        $result[$id] = ['id' => $id, 'model' => $model, 'record_id' => $record->id, 'column' => $column, 'pointer' => $pointer, 'value' => $value,
                            'fingerprint' => hash('sha256', json_encode($record->$column)),
                            'page' => $page?->title ?? ($record instanceof CmsTrashEntry ? 'Trash (restore before replacing)' : 'Shared website content'),
                            'url' => $page?->public_url ?? url('/'), 'section' => $section?->label ?? ($record instanceof CmsPage ? 'Page settings' : 'Preserved content'),
                            'field' => ($record instanceof CmsItem ? $record->label.' / ' : '').$caption,
                            'editable' => $editable, 'shared_setting' => $record instanceof CmsSection && $record->key === 'global-site',
                            'state' => $state,
                            'edit' => $record instanceof CmsPage ? route('cms.pages.edit', $record) : ($section ? ($record instanceof CmsItem ? route('cms.items.edit', $record) : route('cms.sections.edit', $record)) : route('cms.trash'))];
                        if ($section && ! $page && ! $hidden) {
                            $sharedPages = CmsPage::where('is_visible', true);
                            if ($section->key === 'global-rotator') {
                                $sharedPages->where('key', 'index');
                            }
                            $result[$id]['pages'] = $sharedPages->get()->map(fn ($page) => ['title' => $page->title, 'url' => $page->public_url])->all();
                        }
                    });
                }
            }
        }

        return $result;
    }

    private function walk($value, array $pointer, callable $visit): void
    {
        if (is_array($value)) {
            foreach ($value as $key => $child) {
                $this->walk($child, [...$pointer, $key], $visit);
            }
        } elseif (is_string($value)) {
            // Stored trash snapshots contain JSON text: report protection without permitting edits.
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $this->walk($decoded, $pointer, $visit);
            } else {
                $visit($value, $pointer);
            }
        }
    }

    public function token(string $source, string $target, array $locations): string
    {
        $inventory = array_map(fn ($row) => [$row['id'], $row['fingerprint'], $row['editable']], $this->locations($source));

        return hash_hmac('sha256', json_encode([$source, $target, $locations, $inventory]), (string) config('app.key'));
    }

    public function replace(string $source, string $target, array $ids, string $token): int
    {
        return DB::transaction(function () use ($source, $target, $ids, $token) {
            $locations = array_intersect_key($this->locations($source), array_flip($ids));
            abort_unless(count($locations) === count(array_unique($ids)) && count($locations) && collect($locations)->every(fn ($v) => $v['editable']), 422, 'Selected references changed or cannot be edited.');
            abort_unless(hash_equals($this->token($source, $target, $locations), $token), 409, 'Content changed. Review the affected locations again.');
            $records = [];
            foreach ($locations as $location) {
                $key = $location['model'].'|'.$location['record_id'].'|'.$location['column'];
                if (! isset($records[$key])) {
                    $record = $location['model']::whereKey($location['record_id'])->lockForUpdate()->firstOrFail();
                    abort_unless(hash_equals($location['fingerprint'], hash('sha256', json_encode($record->{$location['column']}))), 409, 'Content changed. Review the affected locations again.');
                    $records[$key] = [$record, $location['column'], $record->{$location['column']}];
                }
                $value = &$records[$key][2];
                foreach ($location['pointer'] as $part) {
                    $value = &$value[$part];
                }
                $value = $target;
                unset($value);
            }
            foreach ($records as [$record,$column,$value]) {
                $record->update([$column => $value]);
            }
            // Compare with the final listing value, after all selected leaves have been saved.
            // A global replacement preserves inheritance; a local override survives later saves.
            foreach ($records as [$record,$column,$value]) {
                if ($record instanceof CmsItem && $record->target_page_id) {
                    $roles = app(CmsPlacements::class)->roles($record->section, $record);
                    $page = CmsPage::find($record->target_page_id);
                    if (isset($roles['image']) && ($value[$roles['image']] ?? null) === $target && ($page?->listing['image'] ?? null) !== $target) {
                        $record->update(['overrides' => array_values(array_unique([...($record->overrides ?? []), 'image']))]);
                    }
                }
                if ($record instanceof CmsPage && $column === 'listing') {
                    foreach (CmsItem::with('section')->where('target_page_id', $record->id)->get() as $item) {
                        $field = app(CmsPlacements::class)->roles($item->section, $item)['image'] ?? null;
                        if ($field && $this->normalize($item->content[$field] ?? '') === $this->normalize($source)) {
                            $item->update(['overrides' => array_values(array_unique([...($item->overrides ?? []), 'image']))]);
                        }
                    }
                }
            }

            return count($locations);
        });
    }
}
