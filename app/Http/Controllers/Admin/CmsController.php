<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CmsContentRequest;
use App\Models\CmsItem;
use App\Models\CmsMedia;
use App\Models\CmsPage;
use App\Models\CmsSection;
use App\Models\CmsSubmission;
use App\Services\CmsContentHealth;
use App\Services\CmsMenu;
use App\Services\CmsPlacements;
use App\Services\CmsSectionCatalog;
use App\Services\CmsTrash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CmsController extends Controller
{
    public function dashboard()
    {
        $report = app(CmsContentHealth::class)->report();

        return view('cms.dashboard', ['counts' => ['Pages' => CmsPage::count(), 'Sections' => CmsSection::count(), 'Collection items' => CmsItem::count(), 'New requests' => CmsSubmission::where('status', 'new')->count()], 'attention' => count($report['missing_media']) + count($report['broken_links']) + count($report['unpublished_links']) + count($report['placeholder_links']) + count($report['provisional_text'])]);
    }

    public function pages(Request $request)
    {
        $query = CmsPage::query()->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%'.$request->string('q').'%'))->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')));

        return view('cms.pages', ['pages' => $query->orderBy('id')->paginate(20)->withQueryString(), 'templates' => CmsPage::all(), 'listingType' => config('cms.listings.'.$request->string('type')) ? (string) $request->string('type') : null, 'images' => CmsMedia::where('mime', 'like', 'image/%')->orderBy('name')->get(), 'categories' => CmsPage::where('type', 'article')->get()->pluck('listing.category')->filter()->unique()->values()]);
    }

    public function page(CmsPage $page)
    {
        return view('cms.page', [
            'page' => $page->load('sections.items'),
            'placements' => CmsItem::with('section.page')->where('target_page_id', $page->id)->get(),
            'sectionTypes' => app(CmsSectionCatalog::class)->availableFor($page),
            'catalog' => app(CmsSectionCatalog::class),
            'images' => CmsMedia::where('mime', 'like', 'image/%')->orderBy('name')->get(),
            'listingDefinition' => config('cms.listings.'.$page->type),
        ]);
    }

    public function updatePage(Request $request, CmsPage $page)
    {
        $data = $request->validate(['title' => 'required|string|max:190', 'description' => 'nullable|string|max:5000', 'slug' => ['nullable', 'string', 'max:190', Rule::unique('cms_pages', 'slug')->ignore($page->id), new \App\Rules\PublicPagePath], 'is_visible' => 'required|boolean', 'seo' => 'nullable|array', 'seo.title' => 'nullable|string|max:190', 'seo.description' => 'nullable|string|max:5000', 'seo.og_title' => 'nullable|string|max:190', 'seo.og_description' => 'nullable|string|max:5000', 'seo.og_image' => 'nullable|string|max:190', 'seo.canonical' => 'nullable|url:http,https|max:500', 'seo.noindex' => 'nullable|boolean', 'listing' => 'nullable|array', 'listing.title' => 'nullable|string|max:190', 'listing.description' => 'nullable|string|max:2000', 'listing.image' => 'nullable|string|max:190', 'listing.image_alt' => 'nullable|string|max:190', 'listing.category' => 'nullable|string|max:100', 'auto_listing' => 'nullable|boolean']);
        $data['slug'] = $data['slug'] ?? '';
        abort_if(preg_match('~^(admin|api|login|register|sitemap\.xml|robots\.txt)(/|$)~', $data['slug']), 422, 'This URL is reserved.');
        $image = $data['seo']['og_image'] ?? null;
        abort_if($image && ! CmsMedia::where('path', $image)->exists(), 422, 'Select an existing media image.');
        $data['seo'] = array_filter($data['seo'] ?? [], fn ($v) => $v !== null && $v !== '');
        if (array_key_exists('listing', $data)) {
            $listingImage = $data['listing']['image'] ?? null;
            abort_if($listingImage && ! CmsMedia::where('path', $listingImage)->exists(), 422, 'Select an existing media image.');
            $data['listing'] = array_filter($data['listing'] ?? [], fn ($v) => $v !== null && $v !== '');
        }
        $wasAutomatic = (bool) $page->auto_listing;
        DB::transaction(function () use ($page, $data, $wasAutomatic) {
            if ($data['slug'] !== $page->slug) {
                DB::table('cms_redirects')->where('from', $data['slug'])->delete();
                DB::table('cms_redirects')->where('to', $page->slug)->update(['to' => $data['slug'], 'status' => 301]);
                DB::table('cms_redirects')->updateOrInsert(['from' => $page->slug], ['to' => $data['slug'], 'status' => 301]);
            }$page->update($data);
            $placements = app(CmsPlacements::class);
            if (array_key_exists('auto_listing', $data) && (bool) $data['auto_listing'] !== $wasAutomatic) {
                // Hiding keeps the card's copy for when the page returns to the listing.
                CmsItem::where('target_page_id', $page->id)->where('is_managed', true)->update(['is_visible' => (bool) $data['auto_listing']]);
            }
            if ($page->auto_listing) {
                $placements->ensureListing($page);
            }
            $placements->syncPage($page);
        });
        $warning = '';
        if ($page->wasChanged('is_visible') && ! $page->is_visible) {
            $links = count(app(CmsContentHealth::class)->report()['unpublished_links']);
            $warning = $links ? " {$links} fixed link(s) still point to an unpublished page. Review them under Content health." : '';
        }

        return back()->with('success', 'Page and SEO saved.'.$warning);
    }

    public function createPage(Request $request)
    {
        $data = $request->validate(['template_id' => 'required|exists:cms_pages,id', 'title' => 'required|string|max:190', 'slug' => ['required', 'unique:cms_pages,slug', 'regex:/^[a-z0-9-]+$/', 'max:150', new \App\Rules\PublicPagePath], 'summary' => 'nullable|string|max:2000', 'image' => ['nullable', 'string', 'max:190', Rule::exists('cms_media', 'path')], 'category' => 'nullable|string|max:100', 'show_in_listing' => 'nullable|boolean', 'add_to_menu' => 'nullable|boolean']);
        $source = CmsPage::with('sections.items')->findOrFail($data['template_id']);
        $new = DB::transaction(function () use ($source, $data) {
            $new = $source->replicate();
            $new->key = 'custom-'.\Illuminate\Support\Str::uuid();
            $new->slug = $data['slug'];
            $new->title = $data['title'];
            $new->seo = [];
            $new->is_visible = false;
            $new->listing = null;
            $new->auto_listing = false;
            if (config('cms.listings.'.$new->type)) {
                $new->listing = array_filter([
                    'title' => $data['title'],
                    'description' => $data['summary'] ?? null,
                    'image' => $data['image'] ?? ($source->listing['image'] ?? null),
                    'image_alt' => $data['title'],
                    'category' => $data['category'] ?? ($source->listing['category'] ?? null),
                ]);
                $new->auto_listing = $data['show_in_listing'] ?? true;
                $new->description = $data['summary'] ?? $new->description;
            }
            $new->save();
            // A new page must be reachable even if the URL once redirected elsewhere.
            DB::table('cms_redirects')->where('from', $new->slug)->delete();
            foreach ($source->sections as $section) {
                $copy = $section->replicate();
                $copy->key = 'copy-'.\Illuminate\Support\Str::uuid();
                $copy->page_id = $new->id;
                $copy->save();
                app(CmsSectionCatalog::class)->cloneItems($section, $copy);
            }
            $this->fillDetailFields($new);
            if ($new->auto_listing) {
                app(CmsPlacements::class)->ensureListing($new);
            }
            if ($data['add_to_menu'] ?? false) {
                $this->addToMenu($new);
            }

            return $new;
        });

        return redirect()->route('cms.pages.edit', $new)->with('success', 'Page copied as a draft. Edit its sections and publish when ready.');
    }

    /** Seeds the copied page's heading and image; its remaining copy stays a draft to review. */
    private function fillDetailFields(CmsPage $page): void
    {
        foreach ($page->sections()->get() as $section) {
            foreach ((array) config('cms.detail_fields') as $pattern => $roles) {
                if (! \Illuminate\Support\Str::is($pattern, $section->template)) {
                    continue;
                }
                $content = $section->content;
                foreach ($roles as $role => $field) {
                    $value = $page->listing[$role] ?? null;
                    if (is_string($value) && $value !== '') {
                        $content[$field] = $value;
                    }
                }
                $section->update(['content' => $content]);
            }
        }
    }

    /** Adds the page under the menu item for its listing, or at the top level when there is none. */
    private function addToMenu(CmsPage $page): void
    {
        $header = CmsSection::where('key', 'global-header')->first();
        if (! CmsMenu::isConsolidated($header)) {
            return;
        }
        $listing = CmsPage::where('key', config('cms.listings.'.$page->type.'.page_key'))->first();
        $parent = $listing ? $header->items()->where('group_key', CmsMenu::TOP)->where('target_page_id', $listing->id)->first() : null;
        $group = $parent ? CmsMenu::CHILD : CmsMenu::TOP;
        $header->items()->create([
            'group_key' => $group,
            'parent_id' => $parent?->id,
            'label' => $page->listing['title'] ?? $page->title,
            'target_page_id' => $page->id,
            'is_managed' => true,
            'schema' => CmsMenu::schema(),
            'content' => CmsMenu::defaults($page->listing['title'] ?? $page->title, '/'.$page->slug),
            'position' => ((int) $header->items()->reorder()->where('group_key', $group)->where('parent_id', $parent?->id)->max('position')) + 1,
        ]);
    }

    public function deletePage(CmsPage $page)
    {
        abort_if($page->slug === '', 422, 'The home page cannot be deleted.');
        app(CmsTrash::class)->trashPage($page);

        return redirect()->route('cms.pages')->with('success', 'Page moved to trash. Its cards and menu links are hidden, and its old URL redirects to a live page.');
    }

    public function globals()
    {
        return view('cms.sections', ['sections' => CmsSection::whereNull('page_id')->withCount('items')->get()]);
    }

    public function section(CmsSection $section)
    {
        return view('cms.section', ['section' => $section->load('items'), 'record' => $section, 'media' => CmsMedia::orderBy('name')->get(), 'pagesById' => CmsPage::get(['id', 'title', 'is_visible'])->keyBy('id')]);
    }

    public function updateSection(CmsContentRequest $request, CmsSection $section)
    {
        $section->update($request->safe()->only(['label', 'position', 'is_visible']) + ['content' => $request->contentFor($section->schema, $section->content)]);
        app(CmsPlacements::class)->afterSectionEdited($section);

        return back()->with('success', 'Section saved.');
    }

    public function item(CmsItem $item)
    {
        return view('cms.item', ['record' => $item, 'section' => $item->section, 'media' => CmsMedia::orderBy('name')->get(), 'linkField' => app(CmsPlacements::class)->roles($item->section, $item)['link'] ?? null, 'linkablePages' => CmsPage::orderBy('title')->get(['id', 'title', 'slug', 'is_visible'])]);
    }

    public function updateItem(CmsContentRequest $request, CmsItem $item)
    {
        $previous = $item->content;
        $item->update($request->safe()->only(['label', 'position', 'is_visible']) + ['content' => $request->contentFor($item->schema, $item->content)]);
        app(CmsPlacements::class)->afterItemEdited($item, $request->validated('target_page_id') !== null ? (string) $request->validated('target_page_id') : null, $previous);

        return back()->with('success', 'Item saved.');
    }

    public function addItem(Request $request, CmsSection $section)
    {
        $data = $request->validate(['source_id' => ['nullable', Rule::exists('cms_items', 'id')->where('section_id', $section->id)], 'group_key' => 'nullable|string', 'parent_id' => ['nullable', Rule::exists('cms_items', 'id')->where('section_id', $section->id)]]);
        if (empty($data['source_id'])) {
            $group = collect($section->groups)->firstWhere('key', $data['group_key'] ?? '');
            abort_unless($group, 422, 'Select a supported collection.');
            $parent = isset($data['parent_id']) ? $section->items()->findOrFail($data['parent_id']) : null;
            abort_if(($group['parent_group'] ?? null) !== $parent?->group_key, 422, 'Select the matching parent item.');
            $item = $section->items()->create(['group_key' => $group['key'], 'label' => 'New '.$group['label'], 'parent_id' => $parent?->id, 'content' => $group['defaults'] ?? [], 'schema' => $group['schema'], 'position' => $section->items()->reorder()->where('group_key', $group['key'])->max('position') + 1]);
            app(CmsPlacements::class)->afterItemEdited($item);

            return redirect()->route('cms.items.edit', $item)->with('success', 'Item added. Edit its content below.');
        }
        $source = $section->items()->findOrFail($data['source_id']);
        $copy = DB::transaction(function () use ($source, $section) {
            $copy = $source->replicate();
            $copy->import_key = null;
            $copy->is_managed = false;
            $copy->position = $section->items()->reorder()->where('group_key', $source->group_key)->max('position') + 1;
            $copy->label = \Illuminate\Support\Str::limit($source->label, 180, '').' (copy)';
            $copy->save();
            app(CmsSectionCatalog::class)->cloneItems($section->load('items'), $section, $source->id, $copy->id);

            return $copy;
        });

        return redirect()->route('cms.items.edit', $copy)->with('success', 'New item created from the selected design. Edit its content below.');
    }

    public function deleteItem(CmsItem $item)
    {
        $id = $item->section_id;
        app(CmsTrash::class)->trashItem($item);

        return redirect()->route('cms.sections.edit', $id)->with('success', 'Item moved to trash.');
    }

    public function media(Request $request)
    {
        return view('cms.media', ['media' => CmsMedia::when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->string('q').'%'))->latest()->paginate(24)->withQueryString()]);
    }

    public function upload(Request $request)
    {
        $data = $request->validate(['file' => 'required|file|mimes:jpg,jpeg,png,webp,gif,mp4,pdf|max:20480', 'alt' => 'nullable|string|max:190']);
        $file = $request->file('file');
        $path = $file->store('cms', 'public');
        CmsMedia::create(['path' => '/storage/'.$path, 'name' => $file->getClientOriginalName(), 'mime' => $file->getMimeType(), 'size' => $file->getSize(), 'alt' => $data['alt'] ?? null]);

        return back()->with('success', 'Media uploaded.');
    }

    public function updateMedia(Request $request, CmsMedia $media)
    {
        $media->update($request->validate(['alt' => 'nullable|string|max:190']));

        return back()->with('success', 'Media information saved.');
    }

    public function deleteMedia(CmsMedia $media)
    {
        abort_if($media->is_original, 422, 'Original design assets are preserved.');
        $used = app(\App\Services\CmsMediaReferences::class)->isUsed($media->path);
        abort_if($used, 422, 'This file is still used by website content.');
        Storage::disk('public')->delete(substr($media->path, 9));
        $media->delete();

        return back()->with('success', 'Unused media deleted.');
    }

    public function submissions(Request $request)
    {
        return view('cms.submissions', ['submissions' => CmsSubmission::when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))->when($request->filled('q'), fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', '%'.$request->string('q').'%')->orWhere('phone', 'like', '%'.$request->string('q').'%')))->latest()->paginate(20)->withQueryString()]);
    }

    public function submission(Request $request, CmsSubmission $submission)
    {
        $submission->update($request->validate(['status' => 'required|in:new,contacted,closed']));

        return back()->with('success', 'Request status saved.');
    }

    public function deleteSubmission(CmsSubmission $submission)
    {
        $submission->delete();

        return back()->with('success', 'Request deleted.');
    }
}
