<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CmsContentRequest;
use App\Models\CmsItem;
use App\Models\CmsMedia;
use App\Models\CmsPage;
use App\Models\CmsSection;
use App\Models\CmsSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CmsController extends Controller
{
    public function dashboard()
    {
        return view('cms.dashboard', ['counts' => ['Pages' => CmsPage::count(), 'Sections' => CmsSection::count(), 'Collection items' => CmsItem::count(), 'New requests' => CmsSubmission::where('status', 'new')->count()]]);
    }

    public function pages(Request $request)
    {
        $query = CmsPage::query()->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%'.$request->string('q').'%'))->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')));

        return view('cms.pages', ['pages' => $query->orderBy('id')->paginate(20)->withQueryString(), 'templates' => CmsPage::all()]);
    }

    public function page(CmsPage $page)
    {
        return view('cms.page', ['page' => $page->load('sections')]);
    }

    public function updatePage(Request $request, CmsPage $page)
    {
        $data = $request->validate(['title' => 'required|string|max:190', 'description' => 'nullable|string|max:5000', 'slug' => ['nullable', 'string', 'max:190', Rule::unique('cms_pages', 'slug')->ignore($page->id), new \App\Rules\PublicPagePath], 'is_visible' => 'required|boolean', 'seo' => 'nullable|array', 'seo.title' => 'nullable|string|max:190', 'seo.description' => 'nullable|string|max:5000', 'seo.og_title' => 'nullable|string|max:190', 'seo.og_description' => 'nullable|string|max:5000', 'seo.og_image' => 'nullable|string|max:190', 'seo.canonical' => 'nullable|url:http,https|max:500', 'seo.noindex' => 'nullable|boolean']);
        $data['slug'] = $data['slug'] ?? '';
        abort_if(preg_match('~^(admin|api|login|register|sitemap\.xml|robots\.txt)(/|$)~', $data['slug']), 422, 'This URL is reserved.');
        $image = $data['seo']['og_image'] ?? null;
        abort_if($image && ! CmsMedia::where('path', $image)->exists(), 422, 'Select an existing media image.');
        $data['seo'] = array_filter($data['seo'] ?? [], fn ($v) => $v !== null && $v !== '');
        DB::transaction(function () use ($page, $data) {
            if ($data['slug'] !== $page->slug) {
                DB::table('cms_redirects')->where('from', $data['slug'])->delete();
                DB::table('cms_redirects')->where('to', $page->slug)->update(['to' => $data['slug']]);
                DB::table('cms_redirects')->updateOrInsert(['from' => $page->slug], ['to' => $data['slug']]);
            }$page->update($data);
        });

        return back()->with('success', 'Page and SEO saved.');
    }

    public function createPage(Request $request)
    {
        $data = $request->validate(['template_id' => 'required|exists:cms_pages,id', 'title' => 'required|string|max:190', 'slug' => ['required', 'unique:cms_pages,slug', 'regex:/^[a-z0-9-]+$/', 'max:150', new \App\Rules\PublicPagePath]]);
        $source = CmsPage::with('sections.items')->findOrFail($data['template_id']);
        $new = DB::transaction(function () use ($source, $data) {
            $new = $source->replicate();
            $new->key = 'custom-'.\Illuminate\Support\Str::uuid();
            $new->slug = $data['slug'];
            $new->title = $data['title'];
            $new->seo = [];
            $new->is_visible = false;
            $new->save();
            foreach ($source->sections as $section) {
                $copy = $section->replicate();
                $copy->key = 'copy-'.\Illuminate\Support\Str::uuid();
                $copy->page_id = $new->id;
                $copy->save();
                $this->cloneItems($section, $copy);
            }

            return $new;
        });

        return redirect()->route('cms.pages.edit', $new)->with('success', 'Page copied as a draft. Edit its sections and publish when ready.');
    }

    private function cloneItems(CmsSection $source, CmsSection $target, ?int $parent = null, ?int $newParent = null): void
    {
        foreach ($source->items->where('parent_id', $parent) as $item) {
            $copy = $item->replicate();
            $copy->import_key = null;
            $copy->section_id = $target->id;
            $copy->parent_id = $newParent;
            $copy->save();
            $this->cloneItems($source, $target, $item->id, $copy->id);
        }
    }

    public function deletePage(CmsPage $page)
    {
        abort_if($page->slug === '', 422, 'The home page cannot be deleted.');
        $page->delete();

        return redirect()->route('cms.pages')->with('success', 'Page deleted. Update menu links if needed.');
    }

    public function globals()
    {
        return view('cms.sections', ['sections' => CmsSection::whereNull('page_id')->withCount('items')->get()]);
    }

    public function section(CmsSection $section)
    {
        return view('cms.section', ['section' => $section->load('items'), 'record' => $section, 'media' => CmsMedia::orderBy('name')->get()]);
    }

    public function updateSection(CmsContentRequest $request, CmsSection $section)
    {
        $section->update($request->safe()->only(['label', 'position', 'is_visible']) + ['content' => $request->contentFor($section->schema, $section->content)]);

        return back()->with('success', 'Section saved.');
    }

    public function item(CmsItem $item)
    {
        return view('cms.item', ['record' => $item, 'section' => $item->section, 'media' => CmsMedia::orderBy('name')->get()]);
    }

    public function updateItem(CmsContentRequest $request, CmsItem $item)
    {
        $item->update($request->safe()->only(['label', 'position', 'is_visible']) + ['content' => $request->contentFor($item->schema, $item->content)]);

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
            $item = $section->items()->create(['group_key' => $group['key'], 'label' => 'New '.$group['label'], 'parent_id' => $parent?->id, 'content' => $group['defaults'] ?? [], 'schema' => $group['schema'], 'position' => $section->items()->where('group_key', $group['key'])->max('position') + 1]);

            return redirect()->route('cms.items.edit', $item)->with('success', 'Item added. Edit its content below.');
        }
        $source = $section->items()->findOrFail($data['source_id']);
        $copy = DB::transaction(function () use ($source, $section) {
            $copy = $source->replicate();
            $copy->import_key = null;
            $copy->position = $section->items()->where('group_key', $source->group_key)->max('position') + 1;
            $copy->label = $source->label.' (copy)';
            $copy->save();
            $this->cloneItems($section->load('items'), $section, $source->id, $copy->id);

            return $copy;
        });

        return redirect()->route('cms.items.edit', $copy)->with('success', 'New item created from the selected design. Edit its content below.');
    }

    public function deleteItem(CmsItem $item)
    {
        $id = $item->section_id;
        $item->delete();

        return redirect()->route('cms.sections.edit', $id)->with('success', 'Item deleted.');
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
