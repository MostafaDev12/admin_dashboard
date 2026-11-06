<?php

namespace App\Services;

use App\Models\CmsItem;
use App\Models\CmsPage;
use App\Models\CmsSection;
use Illuminate\Support\Str;

/**
 * Keeps page-backed placements (cards, carousel slides, sidebars, menu links) connected to their
 * page. A placement inherits its page's listing values for every role it has not customised, so
 * copy an editor wrote for one placement survives changes to the page.
 */
class CmsPlacements
{
    public function __construct(private CmsPageLinks $links) {}

    /** @return array<string,string> role => content field */
    public function roles(CmsSection $section, CmsItem $item): array
    {
        foreach ((array) config('cms.placements') as $pattern => $roles) {
            if (Str::is($pattern, $section->template.':'.$item->group_key)) {
                return $roles;
            }
        }
        $link = collect($item->schema)->search(fn ($field) => ($field['type'] ?? '') === 'link');

        return $link === false ? [] : ['link' => $link];
    }

    public function inherited(CmsPage $page, string $role): ?string
    {
        $value = $page->listing[$role === 'category_value' ? 'category' : $role] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    /** Establishes relationships on existing data once. Safe to repeat; never edits copy. */
    public function backfill(): array
    {
        $pages = CmsPage::all()->keyBy('slug');
        $linked = 0;
        $items = CmsItem::with('section')->get();
        foreach ($items as $item) {
            if (CmsMenu::isRetiredItem($item)) {
                continue;
            }
            if ($item->target_page_id || $item->detached_from) {
                continue;
            }
            $field = $this->roles($item->section, $item)['link'] ?? null;
            $page = $field ? $this->links->pageFor($item->content[$field] ?? null, $pages) : null;
            if ($page) {
                $item->target_page_id = $page->id;
                $this->quietly($item);
                $linked++;
            }
        }
        $listings = 0;
        foreach ((array) config('cms.listings') as $type => $definition) {
            $section = CmsSection::where('template', $definition['section_template'])->with('items')->first();
            foreach ($section?->items->where('group_key', $definition['group']) ?? [] as $card) {
                $page = $card->target_page_id ? $pages->firstWhere('id', $card->target_page_id) : null;
                if (! $page || $page->type !== $type || $page->listing !== null) {
                    continue;
                }
                $listing = [];
                foreach ($this->roles($section, $card) as $role => $field) {
                    if (! in_array($role, ['link', 'category_value'], true)) {
                        $listing[$role] = trim((string) ($card->content[$field] ?? ''));
                    }
                }
                $page->listing = $listing;
                $page->auto_listing = true;
                $page->timestamps = false;
                $page->saveQuietly();
                $listings++;
            }
        }
        foreach (CmsSection::all() as $section) {
            $map = $this->sectionLinks($section, $pages);
            if ($map != ($section->page_links ?? [])) {
                $section->page_links = $map;
                $section->timestamps = false;
                $section->saveQuietly();
            }
        }
        $byId = CmsPage::all()->keyBy('id');
        foreach ($items as $item) {
            $page = $byId->get($item->target_page_id);
            if ($page) {
                $overrides = $this->overridesFor($item, $page);
                if ($overrides !== ($item->overrides ?? [])) {
                    $item->overrides = $overrides;
                    $this->quietly($item);
                }
            }
        }

        return ['linked_placements' => $linked, 'page_listings' => $listings];
    }

    /** Roles whose current value differs from the page, so they stay as written. */
    public function overridesFor(CmsItem $item, CmsPage $page): array
    {
        $overrides = [];
        foreach ($this->roles($item->section, $item) as $role => $field) {
            if ($role === 'link') {
                continue;
            }
            $value = trim((string) ($item->content[$field] ?? ''));
            $inherited = $this->inherited($page, $role);
            if ($inherited === null ? $value !== '' : $value !== trim($inherited)) {
                $overrides[] = $role;
            }
        }

        return $overrides;
    }

    /** Maps a section's link fields to pages. A link to a removed page keeps its entry for restore. */
    public function sectionLinks(CmsSection $section, $pagesBySlug): array
    {
        $map = [];
        foreach ($section->schema ?? [] as $field => $definition) {
            if (($definition['type'] ?? '') !== 'link') {
                continue;
            }
            $page = $this->links->pageFor($section->content[$field] ?? null, $pagesBySlug);
            if ($page) {
                $map[$field] = $page->id;
            } elseif (isset($section->page_links[$field]) && ! CmsPage::whereKey($section->page_links[$field])->exists() && ($section->content[$field] ?? '') !== '' && $this->links->parse($section->content[$field])) {
                $map[$field] = $section->page_links[$field];
            }
        }

        return $map;
    }

    public function afterSectionEdited(CmsSection $section): void
    {
        $section->page_links = $this->sectionLinks($section, CmsPage::all()->keyBy('slug'));
        $section->saveQuietly();
    }

    /** Applies a page's URL and listing values to its placements. */
    public function syncPage(CmsPage $page): int
    {
        $changed = 0;
        foreach (CmsSection::all() as $section) {
            $fields = array_keys(array_filter($section->page_links ?? [], fn ($id) => (int) $id === $page->id));
            if ($fields) {
                $content = $section->content;
                foreach ($fields as $field) {
                    $content[$field] = $this->links->follow($content[$field] ?? '', $page);
                }
                if ($content !== $section->content) {
                    $section->update(['content' => $content]);
                    $changed++;
                }
            }
        }
        foreach (CmsItem::with('section')->where('target_page_id', $page->id)->get() as $item) {
            $roles = $this->roles($item->section, $item);
            $content = $item->content;
            if (isset($roles['link'])) {
                $content[$roles['link']] = $this->links->follow($content[$roles['link']] ?? '', $page);
            }
            foreach ($roles as $role => $field) {
                $value = $role === 'link' || in_array($role, $item->overrides ?? [], true) ? null : $this->inherited($page, $role);
                // Equal values are left alone: stored whitespace can be significant.
                if ($value !== null && trim((string) ($content[$field] ?? '')) !== trim($value)) {
                    $content[$field] = $value;
                }
            }
            if ($content !== $item->content) {
                $item->update(['content' => $content]);
                $changed++;
            }
        }
        $this->ensureCategoryOption($page);

        return $changed;
    }

    /** Creates the page's listing card if it has none. Repeated calls never add a second card. */
    public function ensureListing(CmsPage $page): ?CmsItem
    {
        $definition = config('cms.listings.'.$page->type);
        $section = $definition ? $this->listingSection($definition) : null;
        if (! $section) {
            return null;
        }
        $existing = $section->items()->where('group_key', $definition['group'])->where('target_page_id', $page->id)->first();
        if ($existing) {
            return $existing;
        }
        $group = collect($section->groups)->firstWhere('key', $definition['group']);
        if (! $group) {
            return null;
        }
        $card = new CmsItem(['group_key' => $group['key'], 'schema' => $group['schema'], 'content' => $group['defaults'] ?? []]);
        $content = $card->content;
        foreach ($this->roles($section, $card) as $role => $field) {
            if ($role === 'link') {
                $content[$field] = $this->links->href($page);
            } elseif (($value = $this->inherited($page, $role)) !== null) {
                $content[$field] = $value;
            } elseif (! in_array($role, ['image', 'image_alt'], true)) {
                // Never carry another page's copy into this page's card.
                $content[$field] = '';
            }
        }
        $item = $section->items()->create([
            'group_key' => $group['key'],
            'label' => $page->listing['title'] ?? $page->title,
            'content' => $content,
            'schema' => $group['schema'],
            'position' => ((int) $section->items()->reorder()->where('group_key', $group['key'])->max('position')) + 1,
            'target_page_id' => $page->id,
            'is_managed' => true,
        ]);
        $this->ensureCategoryOption($page);

        return $item;
    }

    /** Called after an editor saves an item: resolves its page and which roles it customises. */
    public function afterItemEdited(CmsItem $item, ?string $chosenPageId = null, ?array $previous = null): void
    {
        $item->loadMissing('section');
        $this->renameCategory($item, $previous);
        $field = $this->roles($item->section, $item)['link'] ?? null;
        if (! $field) {
            return;
        }
        $content = $item->content;
        $page = $chosenPageId ? CmsPage::find($chosenPageId) : null;
        if ($page) {
            $content[$field] = $this->links->follow($content[$field] ?? '', $page);
        } else {
            $page = $this->links->pageFor($content[$field] ?? null, CmsPage::all()->keyBy('slug'));
            if ($page?->public_slug !== null && $page) {
                $content[$field] = $this->links->follow($content[$field] ?? '', $page);
            }
        }
        $item->fill([
            'content' => $content,
            'target_page_id' => $page?->id,
            'detached_from' => $page ? null : $item->detached_from,
        ]);
        $item->overrides = $page ? $this->overridesFor($item, $page) : null;
        $item->save();
    }

    /** Hides a removed page's placements without touching what editors wrote in them. */
    public function detach(CmsPage $page): array
    {
        $placements = [];
        foreach (CmsItem::where('target_page_id', $page->id)->get() as $item) {
            $placements[] = ['id' => $item->id, 'is_visible' => (bool) $item->is_visible];
            $item->update(['target_page_id' => null, 'is_visible' => false, 'detached_from' => $page->title]);
        }

        return $placements;
    }

    public function reattach(CmsPage $page, array $placements): void
    {
        foreach ($placements as $placement) {
            CmsItem::whereKey($placement['id'])->update(['target_page_id' => $page->id, 'is_visible' => $placement['is_visible'], 'detached_from' => null]);
        }
    }

    private function listingSection(array $definition): ?CmsSection
    {
        $listing = CmsPage::where('key', $definition['page_key'])->first();

        return $listing?->sections()->where('template', $definition['section_template'])->first();
    }

    /** The article filter only works when an option exists for the card's category. */
    private function ensureCategoryOption(CmsPage $page): void
    {
        $definition = config('cms.listings.'.$page->type);
        $category = $this->inherited($page, 'category');
        if ($category === null || empty($definition['filter_group'])) {
            return;
        }
        $section = $this->listingSection($definition);
        $options = $section?->items()->where('group_key', $definition['filter_group'])->get();
        if (! $options || $options->contains(fn ($option) => ($option->content['attributes_1']['value'] ?? null) === $category)) {
            return;
        }
        $section->items()->create([
            'group_key' => $definition['filter_group'],
            'label' => $category,
            'content' => ['attributes_1' => ['value' => $category], 'f1' => $category],
            'schema' => ['attributes_1' => ['type' => 'fixed', 'label' => 'Original element attributes'], 'f1' => ['type' => 'text', 'label' => 'Category']],
            'position' => ((int) $options->max('position')) + 1,
            'is_managed' => true,
        ]);
    }

    /** Keeps a filter option's value and its cards together when the option is renamed. */
    private function renameCategory(CmsItem $item, ?array $previous): void
    {
        $definition = collect((array) config('cms.listings'))->first(fn ($d) => ! empty($d['filter_group']) && $d['section_template'] === $item->section->template && $d['filter_group'] === $item->group_key);
        $old = $previous['attributes_1']['value'] ?? null;
        $new = trim((string) ($item->content['f1'] ?? ''));
        if (! $definition || $old === null || $old === 'all' || $new === '' || $new === $old) {
            return;
        }
        $content = $item->content;
        $content['attributes_1'] = ['value' => $new] + (array) ($content['attributes_1'] ?? []);
        $content['attributes_1']['value'] = $new;
        $item->update(['content' => $content]);
        foreach ($item->section->items()->where('group_key', $definition['group'])->get() as $card) {
            $roles = $this->roles($item->section, $card);
            if (($card->content[$roles['category_value']] ?? null) !== $old) {
                continue;
            }
            $cardContent = $card->content;
            $cardContent[$roles['category_value']] = $new;
            if (trim((string) ($cardContent[$roles['category']] ?? '')) === $old) {
                $cardContent[$roles['category']] = $new;
            }
            $card->update(['content' => $cardContent]);
        }
        CmsPage::where('type', 'article')->get()->filter(fn ($page) => ($page->listing['category'] ?? null) === $old)->each(function ($page) use ($new) {
            $page->update(['listing' => ['category' => $new] + $page->listing]);
        });
    }

    private function quietly(CmsItem $item): void
    {
        $item->timestamps = false;
        $item->saveQuietly();
        $item->timestamps = true;
    }
}
