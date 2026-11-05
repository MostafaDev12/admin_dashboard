@extends('cms.layout')
@section('title', $listingType ? config('cms.listings.'.$listingType.'.label').'s' : 'Pages & SEO')
@section('content')
<div class="card"><div class="card-body"><form class="row g-2"><div class="col-md-6"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Search pages"></div><div class="col-md-3"><select name="type" class="form-select"><option value="">All types</option>@foreach(['page','service','article'] as $type)<option @selected(request('type')===$type)>{{ $type }}</option>@endforeach</select></div><div class="col-md-3"><button class="btn btn-primary">Search</button></div></form></div>
<div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Page</th><th>URL</th><th>Status</th><th></th></tr></thead><tbody>@foreach($pages as $page)<tr><td>{{ $page->title }}</td><td><a href="{{ $page->public_url }}" target="_blank">/{{ $page->slug }}</a></td><td><span class="badge bg-{{ $page->is_visible?'success':'secondary' }}">{{ $page->is_visible?'Published':'Draft' }}</span></td><td><a class="btn btn-sm btn-soft-primary" href="{{ route('cms.pages.edit',$page) }}">Edit</a></td></tr>@endforeach</tbody></table></div><div class="card-body">{{ $pages->links('pagination::bootstrap-5') }}</div></div>
@if($listingType)
<div class="card">
    <div class="card-header"><h5 class="mb-0">Add {{ $listingType === 'article' ? 'an article' : 'a service' }}</h5></div>
    <div class="card-body">
        <p class="text-muted">This creates a draft detail page and its listing card together. Nothing appears on the website until you publish the page. Review the copied body text in the page's sections first.</p>
        <form action="{{ route('cms.pages.store') }}" method="POST" class="row g-3">@csrf
            <div class="col-md-4"><label class="form-label" for="new-template">Page design to copy</label><select id="new-template" name="template_id" class="form-select" required>@foreach($templates->where('type',$listingType) as $template)<option value="{{ $template->id }}">{{ $template->title }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label" for="new-title">Title</label><input dir="auto" id="new-title" name="title" class="form-control" value="{{ old('title') }}" required maxlength="190"></div>
            <div class="col-md-4"><label class="form-label" for="new-slug">URL slug</label><input dir="ltr" id="new-slug" name="slug" class="form-control" value="{{ old('slug') }}" pattern="[a-z0-9-]+" required placeholder="{{ $listingType === 'article' ? 'new-article' : 'new-service' }}"></div>
            <div class="col-12"><label class="form-label" for="new-summary">Summary shown on the card</label><textarea dir="auto" id="new-summary" name="summary" rows="2" class="form-control" required maxlength="2000">{{ old('summary') }}</textarea></div>
            <div class="col-md-4"><label class="form-label" for="new-image">Image</label><select id="new-image" name="image" class="form-select"><option value="">Same as the copied design</option>@foreach($images as $image)<option value="{{ $image->path }}" @selected(old('image')===$image->path)>{{ $image->name }}</option>@endforeach</select></div>
            @if($listingType === 'article')
            <div class="col-md-4"><label class="form-label" for="new-category">Category</label><input dir="auto" id="new-category" name="category" class="form-control" list="article-categories" value="{{ old('category') }}" required maxlength="100"><datalist id="article-categories">@foreach($categories as $category)<option value="{{ $category }}"></option>@endforeach</datalist></div>
            @else
            <div class="col-md-4"><label class="form-label" for="new-menu">Add to the services menu</label><select id="new-menu" name="add_to_menu" class="form-select"><option value="1">Yes</option><option value="0">No</option></select></div>
            @endif
            <div class="col-md-4"><label class="form-label" for="new-listing">Show in the listing</label><select id="new-listing" name="show_in_listing" class="form-select"><option value="1">Yes, when published</option><option value="0">No</option></select></div>
            <div class="col-12"><button class="btn btn-primary">Create {{ $listingType === 'article' ? 'article' : 'service' }} draft</button></div>
        </form>
    </div>
</div>
@else
<div class="card"><div class="card-header"><h5 class="mb-0">Create a page from an existing design</h5></div><div class="card-body"><form action="{{ route('cms.pages.store') }}" method="POST" class="row g-3">@csrf<div class="col-md-4"><label>Design template</label><select name="template_id" class="form-select">@foreach($templates as $template)<option value="{{ $template->id }}">{{ $template->title }}</option>@endforeach</select></div><div class="col-md-3"><label>Page title</label><input name="title" class="form-control" required></div><div class="col-md-3"><label>URL slug</label><input name="slug" class="form-control" pattern="[a-z0-9-]+" required placeholder="new-page"></div><div class="col-md-2 align-self-end"><button class="btn btn-primary">Create draft</button></div></form></div></div>
@endif
@endsection
