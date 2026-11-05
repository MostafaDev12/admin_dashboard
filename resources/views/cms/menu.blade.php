@extends('cms.layout')
@section('title','Navigation menu')
@section('content')
@php($status = fn ($item) => $item->target_page_id && ! ($pagesById->get($item->target_page_id)?->is_visible ?? false))
<div class="card">
    <div class="card-header"><h5 class="mb-0">Menu structure</h5></div>
    <div class="card-body pb-0"><p class="text-muted mb-3">One menu drives both desktop and mobile. Items linked to a page disappear automatically while that page is unpublished, and the current page is highlighted from its destination.</p></div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Label</th><th>Destination</th><th>Devices</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @foreach($topLevel as $item)
                @include('cms.menu-row', ['item' => $item, 'nested' => false])
                @foreach($childrenOf->get($item->id, collect()) as $child)
                    @include('cms.menu-row', ['item' => $child, 'nested' => true, 'parentLabel' => $item->content['label'] ?? $item->label])
                @endforeach
            @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="card">
    <div class="card-header"><h5 class="mb-0">Add a menu item</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('cms.menu.store') }}">
            @csrf
            @include('cms.menu-fields', ['item' => null, 'parents' => $topLevel])
            <button class="btn btn-primary mt-3">Add menu item</button>
        </form>
    </div>
</div>
@endsection
