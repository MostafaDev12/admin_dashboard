@extends('cms.layout')
@section('title',$page->title)
@section('content')
<a href="{{ route('cms.pages') }}" class="btn btn-soft-secondary mb-3">Back to pages</a>
<div class="card"><div class="card-body"><form action="{{ route('cms.pages.update',$page) }}" method="POST">@csrf @method('PUT')<div class="row g-3">
@foreach(['title'=>'Page title','slug'=>'Public URL path','description'=>'Meta description'] as $key=>$label)<div class="col-md-{{ $key==='description'?'12':'6' }}"><label class="form-label">{{ $label }}</label><input class="form-control" name="{{ $key }}" value="{{ old($key,$page->$key) }}"></div>@endforeach
<div class="col-md-6"><label class="form-label">Publication</label><select class="form-select" name="is_visible"><option value="1" @selected(old('is_visible',$page->is_visible)==1)>Published</option><option value="0" @selected(old('is_visible',$page->is_visible)==0)>Draft / hidden</option></select></div>
<div class="col-12"><h5>SEO & social sharing</h5></div>
@foreach(['title'=>'SEO title','description'=>'SEO description','canonical'=>'Canonical URL','og_title'=>'Open Graph title','og_description'=>'Open Graph description','og_image'=>'Open Graph image'] as $key=>$label)<div class="col-md-6"><label class="form-label">{{ $label }}</label><input class="form-control" name="seo[{{ $key }}]" value="{{ old('seo.'.$key,$page->seo[$key]??'') }}" @if($key==='og_image') list="seo-media" @endif></div>@endforeach
<datalist id="seo-media">@foreach(\App\Models\CmsMedia::all() as $file)<option value="{{ $file->path }}">{{ $file->name }}</option>@endforeach</datalist>
<div class="col-md-6"><label class="form-label">Search engine visibility</label><select class="form-select" name="seo[noindex]"><option value="0">Allow indexing</option><option value="1" @selected($page->seo['noindex']??false)>Noindex</option></select></div><div class="col-12"><button class="btn btn-primary">Save page & SEO</button></div></div></form></div></div>
<div class="card"><div class="card-header"><h5 class="mb-0">Page sections</h5></div><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Section</th><th>Order</th><th>Visibility</th><th></th></tr></thead><tbody>@foreach($page->sections as $section)<tr><td>{{ $section->label }}</td><td>{{ $section->position }}</td><td>{{ $section->is_visible?'Visible':'Hidden' }}</td><td><a class="btn btn-sm btn-soft-primary" href="{{ route('cms.sections.edit',$section) }}">Edit content & items</a></td></tr>@endforeach</tbody></table></div></div>
@if($page->slug!=='')<form action="{{ route('cms.pages.destroy',$page) }}" method="POST" data-confirm="Delete this page and all its content?">@csrf @method('DELETE')<button class="btn btn-danger">Delete page</button></form>@endif
@endsection
