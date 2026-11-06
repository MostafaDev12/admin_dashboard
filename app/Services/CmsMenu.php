<?php

namespace App\Services;

use App\Models\CmsItem;
use App\Models\CmsPage;
use App\Models\CmsSection;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * One menu for desktop and mobile. Records are ordinary collection items in the header section, so
 * they share editing, visibility, recovery and page relationships with the rest of the CMS.
 */
class CmsMenu
{
    public const TOP = 'menu';

    public const CHILD = 'menu-child';

    public const GROUPS = [self::TOP, self::CHILD];

    public const TEMPLATE = 'website.header';

    private const DESKTOP_ICON = 'fa-solid fa-chevron-down nav-chevron';

    private const MOBILE_ICON = 'fa-solid fa-chevron-down';

    private const RETIRED_GROUPS = ['g-3', 'g-4', 'g-5', 'g-7', 'g-8', 'g-9'];

    private const RETIRED_FIELDS = ['f8', 'f9', 'f10', 'f15', 'f16'];

    private const TRIGGER_IMPORT_KEY = 'global-header:menu-trigger';

    public function __construct(private CmsPageLinks $links, private CmsTrash $trash) {}

    public static function isConsolidated(?CmsSection $header): bool
    {
        return $header?->template === self::TEMPLATE;
    }

    public static function isRetiredItem(CmsItem $item): bool
    {
        return in_array($item->group_key, self::RETIRED_GROUPS, true) && self::isConsolidated($item->section);
    }

    public function header(): CmsSection
    {
        return CmsSection::where('key', 'global-header')->firstOrFail();
    }

    public static function schema(): array
    {
        return [
            'label' => ['type' => 'text', 'label' => 'Label'],
            'mobile_label' => ['type' => 'text', 'label' => 'Mobile label (optional, when different)'],
            'link' => ['type' => 'link', 'label' => 'Destination'],
            'show_desktop' => ['type' => 'boolean', 'label' => 'Show on desktop'],
            'show_mobile' => ['type' => 'boolean', 'label' => 'Show on mobile'],
            'icon' => ['type' => 'icon', 'label' => 'Dropdown icon (desktop)'],
            'mobile_icon' => ['type' => 'icon', 'label' => 'Dropdown icon (mobile)'],
            'attributes_1' => ['type' => 'fixed', 'label' => 'Original element attributes'],
        ];
    }

    public static function defaults(string $label = 'New menu item', string $link = '#'): array
    {
        return ['label' => $label, 'mobile_label' => '', 'link' => $link, 'show_desktop' => '1', 'show_mobile' => '1', 'icon' => self::DESKTOP_ICON, 'mobile_icon' => self::MOBILE_ICON, 'attributes_1' => []];
    }

    /**
     * Converts the fixed desktop/mobile groups into one menu. Desktop and mobile records for the
     * same destination become one record; anything that only exists on one device stays there.
     * A snapshot of the previous structure is kept in the trash as an archive.
     */
    public function migrate(): array
    {
        $header = CmsSection::with('items')->where('key', 'global-header')->first();
        if (! $header || self::isConsolidated($header)) {
            return ['menu_migrated' => false];
        }
        $this->trash->archive('Header navigation before menu consolidation', $header);
        $content = $header->content;
        $trigger = ['trigger' => true, 'link' => (string) ($content['f8'] ?? ''), 'label' => trim((string) ($content['f9'] ?? '')), 'visible' => true, 'item' => null];
        $mobileTrigger = ['trigger' => true, 'link' => (string) ($content['f8'] ?? ''), 'label' => trim((string) ($content['f15'] ?? '')), 'visible' => true, 'item' => null];
        $top = $this->merge(
            [...$this->entries($header, 'g-3', 'f3', 'f4'), $trigger, ...$this->entries($header, 'g-5', 'f3', 'f4')],
            [...$this->entries($header, 'g-7', 'f1', 'f2'), $mobileTrigger, ...$this->entries($header, 'g-9', 'f1', 'f2')],
        );
        $children = $this->merge($this->entries($header, 'g-4', 'f1', 'f2'), $this->entries($header, 'g-8', 'f1', 'f2'));
        $parentId = null;
        foreach ($top as $position => $pair) {
            $isTrigger = ! empty(($pair['desktop'] ?? $pair['mobile'])['trigger']);
            $record = $this->convert($header, $pair, self::TOP, $position, null, $isTrigger ? ['icon' => $content['f10'] ?? self::DESKTOP_ICON, 'mobile_icon' => $content['f16'] ?? self::MOBILE_ICON] : []);
            if ($isTrigger) {
                $parentId = $record->id;
            }
        }
        foreach ($children as $position => $pair) {
            $this->convert($header, $pair, self::CHILD, $position, $parentId);
        }
        $groups = collect($header->groups)->reject(fn ($group) => in_array($group['key'], self::RETIRED_GROUPS, true))->values()->all();
        $groups[] = ['key' => self::TOP, 'label' => 'Main navigation', 'parent_group' => null, 'template' => self::TEMPLATE, 'schema' => self::schema(), 'defaults' => self::defaults()];
        $groups[] = ['key' => self::CHILD, 'label' => 'Navigation dropdown items', 'parent_group' => self::TOP, 'template' => self::TEMPLATE, 'schema' => self::schema(), 'defaults' => self::defaults()];
        $header->update([
            'template' => self::TEMPLATE,
            'groups' => $groups,
            'schema' => array_diff_key($header->schema, array_flip(self::RETIRED_FIELDS)),
            'content' => $content,
        ]);

        return ['menu_migrated' => true, 'menu_items' => count($top) + count($children)];
    }

    private function entries(CmsSection $header, string $group, string $link, string $label): array
    {
        return $header->items->where('group_key', $group)->sortBy('position')->map(fn (CmsItem $item) => [
            'trigger' => false,
            'link' => (string) ($item->content[$link] ?? ''),
            'label' => trim((string) ($item->content[$label] ?? '')),
            'visible' => (bool) $item->is_visible,
            'item' => $item,
        ])->values()->all();
    }

    /** Order-preserving merge, so neither device's existing order changes. */
    private function merge(array $desktop, array $mobile): array
    {
        $key = fn (array $entry) => ! empty($entry['trigger']) ? '__trigger' : ($this->links->parse($entry['link'])['slug'] ?? $entry['link'].'|'.$entry['label']);
        $mobileKeys = array_map($key, $mobile);
        $merged = [];
        $d = 0;
        $m = 0;
        while ($d < count($desktop) || $m < count($mobile)) {
            if ($d >= count($desktop)) {
                $merged[] = ['mobile' => $mobile[$m++]];
            } elseif ($m >= count($mobile)) {
                $merged[] = ['desktop' => $desktop[$d++]];
            } elseif ($key($desktop[$d]) === $mobileKeys[$m]) {
                $merged[] = ['desktop' => $desktop[$d++], 'mobile' => $mobile[$m++]];
            } elseif (in_array($key($desktop[$d]), array_slice($mobileKeys, $m), true)) {
                $merged[] = ['mobile' => $mobile[$m++]];
            } else {
                $merged[] = ['desktop' => $desktop[$d++]];
            }
        }

        return $merged;
    }

    private function convert(CmsSection $header, array $pair, string $group, int $position, ?int $parentId, array $extra = []): CmsItem
    {
        $desktop = $pair['desktop'] ?? null;
        $mobile = $pair['mobile'] ?? null;
        $primary = $desktop ?? $mobile;
        $anyVisible = ($desktop['visible'] ?? false) || ($mobile['visible'] ?? false);
        $mobileLabel = $desktop && $mobile && $mobile['label'] !== $desktop['label'] ? $mobile['label'] : '';
        $original = $primary['item'];
        $values = [
            'section_id' => $header->id,
            'parent_id' => $parentId,
            'group_key' => $group,
            'label' => Str::limit($primary['label'], 180, '') ?: 'Menu item',
            'position' => $position,
            'is_visible' => $anyVisible,
            'schema' => self::schema(),
            'content' => array_merge(self::defaults($primary['label'], $primary['link']), [
                'mobile_label' => $mobileLabel,
                'show_desktop' => $desktop && ($desktop['visible'] || ! $anyVisible) ? '1' : '0',
                'show_mobile' => $mobile && ($mobile['visible'] || ! $anyVisible) ? '1' : '0',
                'attributes_1' => $original?->content['attributes_1'] ?? [],
            ], $extra),
        ];
        // New rows carry the consolidated menu. Keep every original row and its content;
        // retired groups are excluded by tree() and by the header's editable group list.
        $record = CmsItem::firstOrNew(['import_key' => $original
            ? 'global-header:consolidated-menu:'.$original->id
            : self::TRIGGER_IMPORT_KEY]);
        $record->forceFill($values)->saveQuietly();

        return $record;
    }

    /** @return array{desktop:array,mobile:array} Trees of visible, reachable nodes per device. */
    public function tree(CmsSection $header, CmsPage $current): array
    {
        $pages = CmsPage::get(['id', 'key', 'is_visible'])->keyBy('id');
        $available = fn (CmsItem $item) => ! $item->target_page_id || ($pages->get($item->target_page_id)?->is_visible ?? false);
        $all = $header->items->whereIn('group_key', self::GROUPS);
        $items = $all->where('is_visible', true);
        $active = $this->activeIds($all, $current, $pages);
        $node = fn (CmsItem $item, Collection $children) => (object) [
            'id' => $item->id,
            'label' => $item->content['label'] ?? '',
            'mobile_label' => ($item->content['mobile_label'] ?? '') !== '' ? $item->content['mobile_label'] : ($item->content['label'] ?? ''),
            'href' => $available($item) ? ($item->content['link'] ?? '') : '#',
            'icon' => $item->content['icon'] ?? self::DESKTOP_ICON,
            'mobile_icon' => $item->content['mobile_icon'] ?? self::MOBILE_ICON,
            'attributes' => $item->content['attributes_1'] ?? [],
            'desktop' => ($item->content['show_desktop'] ?? '1') !== '0',
            'mobile' => ($item->content['show_mobile'] ?? '1') !== '0',
            'active' => isset($active[$item->id]),
            'current' => $item->target_page_id === $current->id,
            'children' => $children,
        ];
        $nodes = collect();
        foreach ($items->whereNull('parent_id')->sortBy('position') as $item) {
            $children = $items->where('parent_id', $item->id)->filter($available)->sortBy('position')->map(fn ($child) => $node($child, collect()))->values();
            $link = trim((string) ($item->content['link'] ?? ''));
            $hasDescendants = $all->where('parent_id', $item->id)->isNotEmpty();
            if ($children->isEmpty() && (! $available($item) || ($hasDescendants && in_array($link, ['', '#'], true)))) {
                continue;
            }
            $nodes->push($node($item, $children));
        }
        $device = function (string $name) use ($nodes) {
            return $nodes->filter(fn ($n) => $n->$name)->map(function ($n) use ($name) {
                $copy = clone $n;
                $copy->children = $n->children->filter(fn ($child) => $child->$name)->values();

                return $copy;
            })->values()->all();
        };

        return ['desktop' => $device('desktop'), 'mobile' => $device('mobile')];
    }

    /** Highlights by destination: the page's own item and its parent, else its type's listing item. */
    private function activeIds(Collection $items, CmsPage $current, Collection $pages): array
    {
        $byTarget = fn ($pageId) => $items->where('target_page_id', $pageId);
        $matches = $byTarget($current->id);
        if ($matches->isEmpty() && ($listing = config('cms.listings.'.$current->type.'.page_key'))) {
            $listingPage = $pages->firstWhere('key', $listing);
            $matches = $listingPage ? $byTarget($listingPage->id) : collect();
        }
        $active = [];
        foreach ($matches as $item) {
            $active[$item->id] = true;
            if ($item->parent_id) {
                $active[$item->parent_id] = true;
            }
        }

        return $active;
    }
}
