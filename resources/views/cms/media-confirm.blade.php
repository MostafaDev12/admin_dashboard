@extends('cms.layout')
@section('title','Confirm image replacement')
@section('content')
<div class="card"><div class="card-body"><h5>{{ $media->name }} → {{ $target->name }}</h5><img src="{{ $target->path }}" alt="{{ $target->alt }}" class="img-thumbnail mb-3" style="max-height:180px"><p>Only the following {{ count($locations) }} stored references will change. Original files and other content fields are preserved.</p>
<ul>@foreach($locations as $location)<li>{{ $location['page'] }} → {{ $location['section'] }} → {{ $location['field'] }}</li>@endforeach</ul>
<form method="POST" action="{{ $media->exists ? route('cms.media.replace',$media) : route('cms.media.replace-source') }}">@csrf<input type="hidden" name="source" value="{{ $media->path }}"><input type="hidden" name="target" value="{{ $target->path }}"><input type="hidden" name="token" value="{{ $token }}">@foreach($locations as $location)<input type="hidden" name="locations[]" value="{{ $location['id'] }}">@endforeach<label class="form-check"><input class="form-check-input" type="checkbox" name="confirmed" value="1" required><span class="form-check-label">I confirm these affected content locations.</span></label><button class="btn btn-warning mt-3">Confirm replacement</button><a class="btn btn-light mt-3" href="{{ route('cms.media') }}">Cancel</a></form></div></div>
@endsection
