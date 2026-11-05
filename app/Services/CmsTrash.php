<?php

namespace App\Services;

use App\Models\CmsItem;
use App\Models\CmsPage;
use App\Models\CmsSection;
use App\Models\CmsTrashEntry;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Removed content is stored as an exact snapshot and its rows are deleted, so public queries stay
 * simple and editors can still recover it. Tombstones record that a removal was intentional, which
 * stops a later import from bringing original records back.
 */
class CmsTrash
{
    public function tombstone(string $kind, ?string $key): void
    {
        if ($key !== null && $key !== '') {
            DB::table('cms_tombstones')->insertOrIgnore(['kind' => $kind, 'key' => $key, 'created_at' => now()]);
        }
    }

    /** @return array<string, array<string, bool>> kind => key => true */
    public function tombstones(): array
    {
        $all = ['page' => [], 'section' => [], 'item' => []];
        foreach (DB::table('cms_tombstones')->get(['kind', 'key']) as $row) {
            $all[$row->kind][$row->key] = true;
        }

        return $all;
    }

    /** Keeps a copy of a section and its items without removing them. Not restorable from the UI. */
    public function archive(string $label, CmsSection $section): CmsTrashEntry
    {
        return $this->store('archive', $label, ['section' => $this->row('cms_sections', $section->id), 'items' => $this->itemRows($section->id)]);
    }

    public function trashItem(CmsItem $item): CmsTrashEntry
    {
        return DB::transaction(function () use ($item) {
            $rows = $this->descendants($item->id);
            $disabledListing = null;
            // Deleting an automatic card is a decision: it must not be recreated.
            if ($item->is_managed && $item->target_page_id && ($page = CmsPage::find($item->target_page_id))?->auto_listing) {
                $page->update(['auto_listing' => false]);
                $disabledListing = $page->id;
            }
            foreach ($rows as $row) {
                $this->tombstone('item', $row['import_key'] ?? null);
            }
            $entry = $this->store('item', $item->label, ['items' => $rows, 'disabled_listing' => $disabledListing]);
            $item->delete();

            return $entry;
        });
    }

    public function trashSection(CmsSection $section): CmsTrashEntry
    {
        return DB::transaction(function () use ($section) {
            $items = $this->itemRows($section->id);
            $this->tombstone('section', $section->key);
            foreach ($items as $row) {
                $this->tombstone('item', $row['import_key'] ?? null);
            }
            $entry = $this->store('section', $section->label, ['section' => $this->row('cms_sections', $section->id), 'items' => $items]);
            $section->delete();

            return $entry;
        });
    }

    /** Hides placements, redirects the old URL to its listing, then removes the page. */
    public function trashPage(CmsPage $page): CmsTrashEntry
    {
        return DB::transaction(function () use ($page) {
            $sections = DB::table('cms_sections')->where('page_id', $page->id)->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
            $items = [];
            foreach ($sections as $section) {
                array_push($items, ...$this->itemRows($section['id']));
            }
            $placements = app(CmsPlacements::class)->detach($page);
            $redirectedFrom = DB::table('cms_redirects')->where('to', $page->slug)->pluck('from')->all();
            $fallback = $this->fallbackUrl($page);
            DB::table('cms_redirects')->where('to', $page->slug)->update(['to' => $fallback, 'status' => 302]);
            DB::table('cms_redirects')->updateOrInsert(['from' => $page->slug], ['to' => $fallback, 'status' => 302]);
            $this->tombstone('page', $page->key);
            foreach ($sections as $section) {
                $this->tombstone('section', $section['key']);
            }
            foreach ($items as $row) {
                $this->tombstone('item', $row['import_key'] ?? null);
            }
            $entry = $this->store('page', $page->title, [
                'page' => $this->row('cms_pages', $page->id),
                'sections' => $sections,
                'items' => $items,
                'placements' => array_values(array_filter($placements, fn ($p) => ! in_array($p['id'], array_column($items, 'id')))),
                'redirected_from' => $redirectedFrom,
            ]);
            $page->delete();

            return $entry;
        });
    }

    /** @return string What was restored, for the editor's confirmation message. */
    public function restore(CmsTrashEntry $entry): string
    {
        return DB::transaction(function () use ($entry) {
            $message = match ($entry->type) {
                'item' => $this->restoreItem($entry->payload),
                'section' => $this->restoreSection($entry->payload),
                'page' => $this->restorePage($entry->payload),
                default => throw new RuntimeException('This archive is kept for reference and cannot be restored.'),
            };
            $entry->delete();

            return $message;
        });
    }

    /** Permanent deletion: the page is not coming back, so its redirect becomes permanent too. */
    public function purge(CmsTrashEntry $entry): void
    {
        DB::transaction(function () use ($entry) {
            if ($entry->type === 'page') {
                $slugs = [$entry->payload['page']['slug'], ...$entry->payload['redirected_from']];
                DB::table('cms_redirects')->whereIn('from', $slugs)->where('status', 302)->update(['status' => 301]);
            }
            $entry->delete();
        });
    }

    private function restoreItem(array $payload): string
    {
        $first = $payload['items'][0];
        if (! CmsSection::whereKey($first['section_id'])->exists()) {
            throw new RuntimeException('Restore the section this item belonged to first.');
        }
        if ($first['parent_id'] && ! CmsItem::whereKey($first['parent_id'])->exists()) {
            throw new RuntimeException('Restore the parent item first.');
        }
        $this->insertItems($payload['items'], []);
        if (! empty($payload['disabled_listing'])) {
            CmsPage::whereKey($payload['disabled_listing'])->update(['auto_listing' => true]);
        }

        return 'Item restored.';
    }

    private function restoreSection(array $payload): string
    {
        $section = $payload['section'];
        if ($section['page_id'] && ! CmsPage::whereKey($section['page_id'])->exists()) {
            throw new RuntimeException('Restore the page this section belonged to first.');
        }
        if (CmsSection::where('key', $section['key'])->exists()) {
            throw new RuntimeException('A section with this identity already exists.');
        }
        $id = $this->insert('cms_sections', $section);
        $this->insertItems($payload['items'], [$section['id'] => $id]);
        $this->clearTombstones('section', [$section['key']]);

        return 'Section restored. It is back in its previous position.';
    }

    private function restorePage(array $payload): string
    {
        $page = $payload['page'];
        if (CmsPage::where('slug', $page['slug'])->exists()) {
            throw new RuntimeException('Another page now uses this URL. Change that page\'s URL, then restore this one.');
        }
        if (CmsPage::where('key', $page['key'])->exists()) {
            throw new RuntimeException('A page with this identity already exists.');
        }
        $pageId = $this->insert('cms_pages', $page);
        $sectionIds = [];
        foreach ($payload['sections'] as $section) {
            $sectionIds[$section['id']] = $this->insert('cms_sections', ['page_id' => $pageId] + $section);
        }
        $this->insertItems($payload['items'], $sectionIds);
        $this->clearTombstones('page', [$page['key']]);
        $this->clearTombstones('section', array_column($payload['sections'], 'key'));
        DB::table('cms_redirects')->where('from', $page['slug'])->delete();
        foreach ($payload['redirected_from'] as $from) {
            DB::table('cms_redirects')->where('from', $from)->update(['to' => $page['slug'], 'status' => 301]);
        }
        $restored = CmsPage::findOrFail($pageId);
        app(CmsPlacements::class)->reattach($restored, $payload['placements']);

        return 'Page restored with its sections and placements.';
    }

    /** Inserts parents after their rows exist so self-referencing keys stay valid. */
    private function insertItems(array $rows, array $sectionIds): void
    {
        $itemIds = [];
        $parents = [];
        foreach ($rows as $row) {
            $parents[$row['id']] = $row['parent_id'];
            // A parent outside this snapshot still exists; one inside it is linked below.
            $inSnapshot = in_array($row['parent_id'], array_column($rows, 'id'));
            $values = array_merge($row, [
                'parent_id' => $inSnapshot ? null : $row['parent_id'],
                'section_id' => $sectionIds[$row['section_id']] ?? $row['section_id'],
                'target_page_id' => $row['target_page_id'] && CmsPage::whereKey($row['target_page_id'])->exists() ? $row['target_page_id'] : null,
            ]);
            $itemIds[$row['id']] = $this->insert('cms_items', $values);
        }
        foreach ($parents as $id => $parent) {
            if ($parent && isset($itemIds[$parent])) {
                DB::table('cms_items')->where('id', $itemIds[$id])->update(['parent_id' => $itemIds[$parent]]);
            }
        }
        $this->clearTombstones('item', array_filter(array_column($rows, 'import_key')));
    }

    private function insert(string $table, array $row): int
    {
        if (DB::table($table)->where('id', $row['id'])->exists()) {
            unset($row['id']);

            return (int) DB::table($table)->insertGetId($row);
        }
        DB::table($table)->insert($row);

        return (int) $row['id'];
    }

    private function clearTombstones(string $kind, array $keys): void
    {
        DB::table('cms_tombstones')->where('kind', $kind)->whereIn('key', $keys)->delete();
    }

    private function fallbackUrl(CmsPage $page): string
    {
        $listing = config('cms.listings.'.$page->type.'.page_key');
        $target = $listing ? CmsPage::where('key', $listing)->where('is_visible', true)->first() : null;

        return $target && $target->id !== $page->id ? $target->slug : '';
    }

    private function store(string $type, string $label, array $payload): CmsTrashEntry
    {
        return CmsTrashEntry::create(['type' => $type, 'label' => $label, 'payload' => $payload, 'deleted_by' => auth('admin')->id()]);
    }

    private function row(string $table, int $id): array
    {
        return (array) DB::table($table)->where('id', $id)->first();
    }

    private function itemRows(int $sectionId): array
    {
        return DB::table('cms_items')->where('section_id', $sectionId)->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
    }

    private function descendants(int $itemId): array
    {
        $rows = [$this->row('cms_items', $itemId)];
        $frontier = [$itemId];
        while ($frontier) {
            $children = DB::table('cms_items')->whereIn('parent_id', $frontier)->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
            array_push($rows, ...$children);
            $frontier = array_column($children, 'id');
        }

        return $rows;
    }
}
