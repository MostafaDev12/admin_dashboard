<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsItem;
use App\Models\CmsPage;
use App\Models\CmsSection;
use App\Services\CmsPlacements;
use App\Services\CmsSectionCatalog;
use App\Services\CmsTrash;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use RuntimeException;

class CmsStructureController extends Controller
{
    public function addSection(Request $request, CmsPage $page, CmsSectionCatalog $catalog)
    {
        $data = $request->validate(['type' => 'required|string|max:64']);
        try {
            $section = $catalog->add($page, $data['type']);
        } catch (RuntimeException $e) {
            return back()->withErrors(['type' => $e->getMessage()]);
        }

        return redirect()->route('cms.sections.edit', $section)->with('success', 'Section added and hidden. Replace its starter content, then show it when ready.');
    }

    public function duplicateSection(CmsSection $section, CmsSectionCatalog $catalog)
    {
        try {
            $copy = $catalog->duplicate($section);
        } catch (RuntimeException $e) {
            return back()->withErrors(['section' => $e->getMessage()]);
        }

        return redirect()->route('cms.sections.edit', $copy)->with('success', 'Section duplicated and hidden. Show it when ready.');
    }

    public function moveSection(Request $request, CmsSection $section)
    {
        abort_if($section->page_id === null, 422, 'Shared sections have a fixed position.');
        $data = $request->validate(['direction' => 'required|in:up,down']);
        $this->move(CmsSection::where('page_id', $section->page_id)->orderBy('position')->orderBy('id')->get(), $section, $data['direction']);

        return back()->with('success', 'Section order saved.');
    }

    public function sectionVisibility(Request $request, CmsSection $section)
    {
        $data = $request->validate(['is_visible' => 'required|boolean']);
        $section->update($data);

        return back()->with('success', $data['is_visible'] ? 'Section is now visible.' : 'Section is now hidden.');
    }

    public function destroySection(CmsSection $section, CmsTrash $trash)
    {
        abort_if($section->page_id === null, 422, 'Shared sections cannot be removed. Hide them instead.');
        $page = $section->page_id;
        $trash->trashSection($section);

        return redirect()->route('cms.pages.edit', $page)->with('success', 'Section moved to trash. You can restore it from there.');
    }

    public function moveItem(Request $request, CmsItem $item)
    {
        abort_if(\App\Services\CmsMenu::isRetiredItem($item), 409, 'Original navigation records are retained read-only.');
        $data = $request->validate(['direction' => 'required|in:up,down']);
        $siblings = CmsItem::where('section_id', $item->section_id)->where('group_key', $item->group_key)->where('parent_id', $item->parent_id)->orderBy('position')->orderBy('id')->get();
        $this->move($siblings, $item, $data['direction']);

        return back()->with('success', 'Order saved.');
    }

    public function itemVisibility(Request $request, CmsItem $item)
    {
        abort_if(\App\Services\CmsMenu::isRetiredItem($item), 409, 'Original navigation records are retained read-only.');
        $data = $request->validate(['is_visible' => 'required|boolean']);
        $item->update($data);

        return back()->with('success', $data['is_visible'] ? 'Item is now visible.' : 'Item is now hidden.');
    }

    /** Lets an editor bring a page's automatic card back after turning it off or deleting it. */
    public function addToListing(CmsPage $page, CmsPlacements $placements)
    {
        abort_unless(config('cms.listings.'.$page->type), 422, 'This page type has no listing.');
        $page->update(['auto_listing' => true]);
        $card = $placements->ensureListing($page);
        $card?->update(['is_visible' => true]);
        $placements->syncPage($page);

        return back()->with('success', 'Page added to its listing.');
    }

    /** Drops a placement's customisations so it follows its page again. */
    public function resetPlacement(CmsItem $item, CmsPlacements $placements)
    {
        abort_if(\App\Services\CmsMenu::isRetiredItem($item), 409, 'Original navigation records are retained read-only.');
        $page = CmsPage::findOrFail($item->target_page_id);
        $item->update(['overrides' => []]);
        $placements->syncPage($page);

        return back()->with('success', 'This item now follows its page.');
    }

    private function move(Collection $siblings, $record, string $direction): void
    {
        $ids = $siblings->pluck('id')->values()->all();
        $from = array_search($record->id, $ids, true);
        $to = $direction === 'up' ? $from - 1 : $from + 1;
        if ($from === false || ! isset($ids[$to])) {
            return;
        }
        [$ids[$from], $ids[$to]] = [$ids[$to], $ids[$from]];
        foreach ($ids as $position => $id) {
            $record::whereKey($id)->update(['position' => $position]);
        }
    }
}
