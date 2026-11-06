@extends('cms.layout')
@section('title','Media: '.$media->name)
@section('content')
<a href="{{ route('cms.media') }}" class="btn btn-soft-secondary mb-3">Back to library</a>
<div class="card"><div class="card-body">
@if(str_starts_with($media->mime,'image/'))<img src="{{ $media->path }}" alt="{{ $media->alt }}" class="img-fluid mb-3" style="max-height:400px">@else<a href="{{ url($media->path) }}" target="_blank" rel="noopener">Open file</a>@endif
<h5>{{ $media->name }}</h5><p>{{ $media->mime }} · {{ number_format($media->size/1024,1) }} KB @if($media->dimensions) · {{ $media->dimensions['width'] }} × {{ $media->dimensions['height'] }} @endif</p>
<button class="btn btn-soft-secondary" type="button" data-copy="{{ url($media->path) }}">Copy URL</button>
<form method="POST" action="{{ route('cms.media.update',$media) }}" class="mt-3">@csrf @method('PUT')<label class="form-label">Alternative description</label><input class="form-control" name="alt" maxlength="190" value="{{ $media->alt }}"><button class="btn btn-primary mt-2">Save alt text</button></form>
</div></div>
<div class="card" id="usage"><div class="card-header"><h5>Where used ({{ count($locations) }} references)</h5><input class="form-control" data-usage-search placeholder="Search page, section or field"></div><div class="card-body">
@forelse($locations as $location)<div class="border-bottom py-2" data-usage-row><strong>{{ $location['page'] }}</strong> → {{ $location['section'] }} → {{ $location['field'] }} <span class="badge bg-light text-dark">{{ $location['state'] }}</span><div class="mt-1"><a href="{{ $location['edit'] }}">Edit content</a> · <a href="{{ $location['url'] }}" target="_blank" rel="noopener">View page</a></div>@if(!empty($location['pages']))<details class="mt-1"><summary>Shared on {{ count($location['pages']) }} published pages</summary><ul>@foreach($location['pages'] as $sharedPage)<li><a href="{{ $sharedPage['url'] }}" target="_blank" rel="noopener">{{ $sharedPage['title'] }}</a></li>@endforeach</ul></details>@endif</div>@empty<p>No content references.</p>@endforelse
</div></div>
<div class="card" id="replace"><div class="card-header"><h5>Replace image references</h5></div><div class="card-body"><p>Upload or choose another image, then review the affected locations. Files are never overwritten. Shared settings may affect multiple pages; choose Replace everywhere for shared settings.</p>
<form method="POST" action="{{ route('cms.media.preview',$media) }}">@csrf
<label class="form-label">Replacement image</label>@include('cms.media-field',['id'=>'replacement-image','name'=>'target','value'=>''])
<label class="form-label mt-3">Replacement mode</label><select class="form-select" name="mode"><option value="one">Only one selected content field</option><option value="all">Replace everywhere (review required)</option></select>
<label class="form-label mt-3">Content location (for one-field replacement)</label><select name="location" class="form-select"><option value="">Choose a location</option>@foreach($locations as $location)@if($location['editable'])<option value="{{ $location['id'] }}">{{ $location['page'] }} → {{ $location['section'] }} → {{ $location['field'] }}</option>@endif @endforeach</select>
<button class="btn btn-warning mt-3">Review replacement</button></form>
</div></div>
@if(!$media->is_original)<form method="POST" action="{{ route('cms.media.destroy',$media) }}" data-confirm="Permanently delete this unused upload?">@csrf @method('DELETE')<button class="btn btn-danger" @disabled(count($locations)>0)>Delete unused file</button></form>@else<p class="text-muted">Original design assets are protected from deletion.</p>@endif
@endsection
