@extends('cms.layout')
@section('title','Edit menu item')
@section('content')
<a class="btn btn-soft-secondary mb-3" href="{{ route('cms.menu') }}">Back to menu</a>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('cms.menu.update', $item) }}">
        @csrf @method('PUT')
        @include('cms.menu-fields', ['item' => $item, 'parents' => $parents])
        <button class="btn btn-primary mt-3">Save menu item</button>
    </form>
</div></div>
@endsection
