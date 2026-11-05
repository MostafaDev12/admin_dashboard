@extends('cms.layout')
@section('title','Edit collection item')
@section('content')
<a class="btn btn-soft-secondary mb-3" href="{{ route('cms.sections.edit',$section) }}">Back to collection</a><div class="card"><div class="card-body"><form method="POST" action="{{ route('cms.items.update',$record) }}">@csrf @method('PUT')@include('cms.fields')<button class="btn btn-primary mt-3">Save item</button></form></div></div>
@endsection
