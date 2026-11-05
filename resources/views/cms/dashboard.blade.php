@extends('cms.layout')
@section('title','Dashboard')
@section('content')
<div class="row">@foreach($counts as $label=>$count)<div class="col-md-3"><div class="card card-animate"><div class="card-body"><p class="text-uppercase fw-medium text-muted mb-0">{{ $label }}</p><h2 class="mt-3">{{ $count }}</h2></div></div></div>@endforeach</div>
<div class="card"><div class="card-body"><h5>Manage your website</h5><p>Edit a page, then open one of its sections. Collections support adding, editing, ordering, visibility, and deletion. Navigation is under Navigation menu; footer and carousel content are under Footer & shared settings.</p><a class="btn btn-primary" href="{{ route('cms.pages') }}">Manage pages</a></div></div>
@if($attention)<div class="card border-warning"><div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2"><div><h5 class="mb-1">{{ $attention }} content item(s) need attention</h5><p class="mb-0 text-muted">Missing images, placeholder links, and provisional text waiting for owner-approved content.</p></div><a class="btn btn-soft-warning" href="{{ route('cms.health') }}">Review content health</a></div></div>@endif
@endsection
