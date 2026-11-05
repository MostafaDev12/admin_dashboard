@extends('cms.layout')
@section('title','Dashboard')
@section('content')
<div class="row">@foreach($counts as $label=>$count)<div class="col-md-3"><div class="card card-animate"><div class="card-body"><p class="text-uppercase fw-medium text-muted mb-0">{{ $label }}</p><h2 class="mt-3">{{ $count }}</h2></div></div></div>@endforeach</div>
<div class="card"><div class="card-body"><h5>Manage your website</h5><p>Edit a page, then open one of its sections. Collections support adding, editing, ordering, visibility, and deletion. Shared navigation, footer and carousel content are under Menus, footer & settings.</p><a class="btn btn-primary" href="{{ route('cms.pages') }}">Manage pages</a></div></div>
@endsection
