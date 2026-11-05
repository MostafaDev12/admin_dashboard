<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CmsContentRequest;
use App\Models\CmsItem;
use App\Models\CmsPage;
use App\Services\CmsMenu;
use App\Services\CmsPageLinks;
use App\Services\CmsPlacements;
use App\Services\CmsTrash;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CmsMenuController extends Controller
{
    public function __construct(private CmsMenu $menu, private CmsPageLinks $links, private CmsPlacements $placements) {}

    public function index()
    {
        $header = $this->menu->header();
        abort_unless(CmsMenu::isConsolidated($header), 409, 'Run website:upgrade to enable the consolidated menu.');
        $items = $header->items()->whereIn('group_key', CmsMenu::GROUPS)->get();

        return view('cms.menu', [
            'topLevel' => $items->whereNull('parent_id')->values(),
            'childrenOf' => $items->whereNotNull('parent_id')->groupBy('parent_id'),
            'pages' => $this->pageOptions(),
            'pagesById' => CmsPage::get(['id', 'title', 'is_visible'])->keyBy('id'),
        ]);
    }

    public function store(Request $request)
    {
        $header = $this->menu->header();
        $data = $this->validated($request);
        $parent = $this->parent($data);
        $group = $parent ? CmsMenu::CHILD : CmsMenu::TOP;
        $link = $this->destination($data, '#');
        $item = $header->items()->create([
            'group_key' => $group,
            'parent_id' => $parent?->id,
            'label' => $data['label'],
            'position' => $this->nextPosition($parent?->id),
            'is_visible' => $data['is_visible'] ?? true,
            'schema' => CmsMenu::schema(),
            'content' => array_merge(CmsMenu::defaults($data['label'], $link), $this->deviceContent($data)),
        ]);
        $this->placements->afterItemEdited($item, $data['target_page_id'] ?? null);

        return redirect()->route('cms.menu')->with('success', 'Menu item added.');
    }

    public function edit(CmsItem $item)
    {
        $this->assertMenuItem($item);

        return view('cms.menu-edit', [
            'item' => $item,
            'pages' => $this->pageOptions(),
            'parents' => $item->children()->exists() ? collect() : $this->menu->header()->items()->where('group_key', CmsMenu::TOP)->whereNull('parent_id')->where('id', '!=', $item->id)->get(),
        ]);
    }

    public function update(Request $request, CmsItem $item)
    {
        $this->assertMenuItem($item);
        $data = $this->validated($request);
        $parent = $this->parent($data, $item);
        $moved = $item->parent_id !== $parent?->id;
        $content = array_merge($item->content, $this->deviceContent($data), ['label' => $data['label'], 'link' => $this->destination($data, $item->content['link'] ?? '#')]);
        $item->update([
            'label' => $data['label'],
            'group_key' => $parent ? CmsMenu::CHILD : CmsMenu::TOP,
            'parent_id' => $parent?->id,
            'position' => $moved ? $this->nextPosition($parent?->id) : $item->position,
            'is_visible' => $data['is_visible'] ?? $item->is_visible,
            'content' => $content,
        ]);
        $this->placements->afterItemEdited($item, $data['target_page_id'] ?? null);

        return redirect()->route('cms.menu')->with('success', 'Menu item saved.');
    }

    public function move(Request $request, CmsItem $item)
    {
        $this->assertMenuItem($item);
        $data = $request->validate(['direction' => 'required|in:up,down']);
        $siblings = $this->menu->header()->items()->whereIn('group_key', CmsMenu::GROUPS)->where('parent_id', $item->parent_id)->pluck('id')->values()->all();
        $from = array_search($item->id, $siblings, true);
        $to = $data['direction'] === 'up' ? $from - 1 : $from + 1;
        if ($from !== false && isset($siblings[$to])) {
            [$siblings[$from], $siblings[$to]] = [$siblings[$to], $siblings[$from]];
            foreach ($siblings as $position => $id) {
                CmsItem::whereKey($id)->update(['position' => $position]);
            }
        }

        return redirect()->route('cms.menu')->with('success', 'Menu order saved.');
    }

    public function destroy(CmsItem $item, CmsTrash $trash)
    {
        $this->assertMenuItem($item);
        $trash->trashItem($item);

        return redirect()->route('cms.menu')->with('success', 'Menu item moved to trash.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'label' => 'required|string|max:190',
            'mobile_label' => 'nullable|string|max:190',
            'target_page_id' => 'nullable|integer|exists:cms_pages,id',
            'url' => 'nullable|string|max:500',
            'parent_id' => 'nullable|integer',
            'show_desktop' => 'nullable|boolean',
            'show_mobile' => 'nullable|boolean',
            'is_visible' => 'nullable|boolean',
        ]);
    }

    private function destination(array $data, string $current): string
    {
        if (! empty($data['target_page_id'])) {
            return $this->links->follow($current, CmsPage::findOrFail($data['target_page_id']));
        }
        $url = trim((string) ($data['url'] ?? ''));
        if ($url === '') {
            return $current;
        }
        if (! CmsContentRequest::isSafeLink($url)) {
            throw ValidationException::withMessages(['url' => 'Choose a website page, an anchor, or a valid http(s), mailto, or telephone link.']);
        }

        return $url;
    }

    private function deviceContent(array $data): array
    {
        return [
            'mobile_label' => trim((string) ($data['mobile_label'] ?? '')),
            'show_desktop' => ($data['show_desktop'] ?? true) ? '1' : '0',
            'show_mobile' => ($data['show_mobile'] ?? true) ? '1' : '0',
        ];
    }

    /** The design supports one dropdown level, so parents must be top-level and childless moves only. */
    private function parent(array $data, ?CmsItem $item = null): ?CmsItem
    {
        if (empty($data['parent_id'])) {
            return null;
        }
        $parent = $this->menu->header()->items()->where('group_key', CmsMenu::TOP)->whereNull('parent_id')->find($data['parent_id']);
        if (! $parent || $parent->is($item)) {
            throw ValidationException::withMessages(['parent_id' => 'Choose a top-level menu item as the parent.']);
        }
        if ($item?->children()->exists()) {
            throw ValidationException::withMessages(['parent_id' => 'An item with its own dropdown cannot be nested. Move its items first.']);
        }

        return $parent;
    }

    private function nextPosition(?int $parentId): int
    {
        return ((int) $this->menu->header()->items()->reorder()->whereIn('group_key', CmsMenu::GROUPS)->where('parent_id', $parentId)->max('position')) + 1;
    }

    private function assertMenuItem(CmsItem $item): void
    {
        abort_unless(in_array($item->group_key, CmsMenu::GROUPS, true), 404);
    }

    private function pageOptions()
    {
        return CmsPage::orderBy('title')->get(['id', 'title', 'slug', 'is_visible']);
    }
}
