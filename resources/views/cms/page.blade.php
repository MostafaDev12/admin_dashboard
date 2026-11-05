@extends('cms.layout')
@section('title',$page->title)
@section('content')
<a href="{{ route('cms.pages') }}" class="btn btn-soft-secondary mb-3">Back to pages</a>
<div class="card"><div class="card-body"><form action="{{ route('cms.pages.update',$page) }}" method="POST">@csrf @method('PUT')<div class="row g-3">
@foreach(['title'=>'Page title','slug'=>'Public URL path','description'=>'Meta description'] as $key=>$label)<div class="col-md-{{ $key==='description'?'12':'6' }}"><label class="form-label">{{ $label }}</label><input class="form-control" name="{{ $key }}" value="{{ old($key,$page->$key) }}"></div>@endforeach
<div class="col-md-6"><label class="form-label">Publication</label><select class="form-select" name="is_visible"><option value="1" @selected(old('is_visible',$page->is_visible)==1)>Published</option><option value="0" @selected(old('is_visible',$page->is_visible)==0)>Draft / hidden</option></select></div>
@if($listingDefinition)
<div class="col-12"><h5>Listing card</h5><p class="text-muted mb-0">Shown wherever this {{ strtolower($listingDefinition['label']) }} is listed. Cards and links that have not been customised follow these values.</p></div>
<div class="col-md-6"><label class="form-label" for="listing-title">Listing title</label><input dir="auto" id="listing-title" class="form-control" name="listing[title]" value="{{ old('listing.title',$page->listing['title']??'') }}"></div>
<div class="col-md-6"><label class="form-label" for="listing-auto">Show in the listing automatically</label><select id="listing-auto" class="form-select" name="auto_listing"><option value="1" @selected(old('auto_listing',$page->auto_listing)==1)>Yes</option><option value="0" @selected(old('auto_listing',$page->auto_listing)==0)>No</option></select></div>
<div class="col-12"><label class="form-label" for="listing-description">Listing summary</label><textarea dir="auto" id="listing-description" rows="2" class="form-control" name="listing[description]">{{ old('listing.description',$page->listing['description']??'') }}</textarea></div>
<div class="col-md-6"><label class="form-label" for="listing-image">Listing image</label><select id="listing-image" class="form-select" name="listing[image]"><option value="">No image selected</option>@foreach($images as $file)<option value="{{ $file->path }}" @selected(old('listing.image',$page->listing['image']??'')===$file->path)>{{ $file->name }}</option>@endforeach</select></div>
<div class="col-md-6"><label class="form-label" for="listing-alt">Image description</label><input dir="auto" id="listing-alt" class="form-control" name="listing[image_alt]" value="{{ old('listing.image_alt',$page->listing['image_alt']??'') }}"></div>
@if(!empty($listingDefinition['filter_group']))<div class="col-md-6"><label class="form-label" for="listing-category">Category</label><input dir="auto" id="listing-category" class="form-control" name="listing[category]" value="{{ old('listing.category',$page->listing['category']??'') }}"></div>@endif
@endif
<div class="col-12"><h5>SEO & social sharing</h5></div>
@foreach(['title'=>'SEO title','description'=>'SEO description','canonical'=>'Canonical URL','og_title'=>'Open Graph title','og_description'=>'Open Graph description','og_image'=>'Open Graph image'] as $key=>$label)<div class="col-md-6"><label class="form-label">{{ $label }}</label><input class="form-control" name="seo[{{ $key }}]" value="{{ old('seo.'.$key,$page->seo[$key]??'') }}" @if($key==='og_image') list="seo-media" @endif></div>@endforeach
<datalist id="seo-media">@foreach(\App\Models\CmsMedia::all() as $file)<option value="{{ $file->path }}">{{ $file->name }}</option>@endforeach</datalist>
<div class="col-md-6"><label class="form-label">Search engine visibility</label><select class="form-select" name="seo[noindex]"><option value="0">Allow indexing</option><option value="1" @selected($page->seo['noindex']??false)>Noindex</option></select></div><div class="col-12"><button class="btn btn-primary">Save page & SEO</button></div></div></form></div></div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center"><h5 class="mb-0">Page sections</h5></div>
    <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Section</th><th>Status</th><th class="text-end">Actions</th></tr></thead><tbody>
    @foreach($page->sections as $section)
        <tr>
            <td dir="auto">{{ \App\Support\CmsLabels::section($section->label) }}</td>
            <td><span class="badge bg-{{ $section->is_visible ? 'success' : 'secondary' }}">{{ $section->is_visible ? 'Visible' : 'Hidden' }}</span></td>
            <td><div class="d-flex gap-1 justify-content-end flex-wrap">
                <form method="POST" action="{{ route('cms.sections.move',$section) }}">@csrf<input type="hidden" name="direction" value="up"><button class="btn btn-sm btn-soft-secondary" aria-label="Move up"><i class="ri-arrow-up-line"></i></button></form>
                <form method="POST" action="{{ route('cms.sections.move',$section) }}">@csrf<input type="hidden" name="direction" value="down"><button class="btn btn-sm btn-soft-secondary" aria-label="Move down"><i class="ri-arrow-down-line"></i></button></form>
                <a class="btn btn-sm btn-soft-primary" href="{{ route('cms.sections.edit',$section) }}">Edit content & items</a>
                <form method="POST" action="{{ route('cms.sections.visibility',$section) }}">@csrf @method('PATCH')<input type="hidden" name="is_visible" value="{{ $section->is_visible ? 0 : 1 }}"><button class="btn btn-sm btn-soft-warning">{{ $section->is_visible ? 'Hide' : 'Show' }}</button></form>
                @if($catalog->canDuplicate($section))<form method="POST" action="{{ route('cms.sections.duplicate',$section) }}">@csrf<button class="btn btn-sm btn-soft-success">Duplicate</button></form>@endif
                <form method="POST" action="{{ route('cms.sections.destroy',$section) }}" data-confirm="Move this section and its items to the trash?">@csrf @method('DELETE')<button class="btn btn-sm btn-soft-danger">Remove</button></form>
            </div></td>
        </tr>
    @endforeach
    </tbody></table></div>
    <div class="card-body border-top">
        @if($sectionTypes->isNotEmpty())
        <form method="POST" action="{{ route('cms.sections.store',$page) }}" class="row g-2 align-items-end">@csrf
            <div class="col-md-9"><label class="form-label" for="section-type">Add a section</label><select id="section-type" class="form-select" name="type" required><option value="">Select a section design</option>@foreach($sectionTypes as $type)<option value="{{ $type['key'] }}">{{ $type['label'] }}</option>@endforeach</select><div class="form-text">Only designs that work with this page's styles are offered. New sections start hidden so you can replace their starter content first.</div></div>
            <div class="col-md-3 align-self-start" style="margin-top:1.9rem"><button class="btn btn-primary w-100">Add section</button></div>
        </form>
        @else<p class="text-muted mb-0">Every section design that works on this page is already in use.</p>@endif
    </div>
</div>

@if($placements->isNotEmpty() || $listingDefinition)
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center"><h5 class="mb-0">Where this page appears</h5>
        @if($listingDefinition && ! $placements->contains(fn ($p) => $p->section?->template === $listingDefinition['section_template']))<form method="POST" action="{{ route('cms.placements.listing',$page) }}">@csrf<button class="btn btn-sm btn-soft-success">Add to listing</button></form>@endif
    </div>
    @if($placements->isEmpty())<div class="card-body"><p class="text-muted mb-0">No cards or menu links point to this page yet.</p></div>
    @else<div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Placement</th><th>Location</th><th>Customised</th><th>Status</th><th></th></tr></thead><tbody>
    @foreach($placements as $placement)
        <tr>
            <td dir="auto">{{ $placement->label }}</td>
            <td dir="auto"><small>{{ $placement->section?->page?->title ?? 'Shared content' }}</small></td>
            <td>@if($placement->overrides)<small>{{ collect($placement->overrides)->map(fn ($role) => ucfirst(str_replace('_', ' ', $role)))->implode(', ') }}</small>@else<span class="text-muted"><small>Follows this page</small></span>@endif</td>
            <td><span class="badge bg-{{ ! $placement->is_visible ? 'secondary' : ($page->is_visible ? 'success' : 'warning') }}">{{ ! $placement->is_visible ? 'Hidden' : ($page->is_visible ? 'Visible' : 'Hidden until published') }}</span></td>
            <td class="text-end"><a class="btn btn-sm btn-soft-primary" href="{{ route('cms.items.edit',$placement) }}">Edit</a></td>
        </tr>
    @endforeach
    </tbody></table></div>@endif
</div>
@endif
@if($page->slug!=='')<form action="{{ route('cms.pages.destroy',$page) }}" method="POST" data-confirm="Move this page to the trash? Its cards and menu links will be hidden, and you can restore it later.">@csrf @method('DELETE')<button class="btn btn-danger">Move page to trash</button></form>@endif
@endsection
