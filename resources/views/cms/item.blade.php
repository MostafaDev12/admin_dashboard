@extends('cms.layout')
@section('title','Edit collection item')
@section('content')
<a class="btn btn-soft-secondary mb-3" href="{{ route('cms.sections.edit',$section) }}">Back to collection</a>
@if($record->detached_from)<div class="alert alert-warning" dir="auto">This item is hidden because its page "{{ $record->detached_from }}" was removed. Its text is preserved. Restore the page from the trash, or choose a new destination and show the item again.</div>@endif
<div class="card"><div class="card-body"><form method="POST" action="{{ route('cms.items.update',$record) }}">@csrf @method('PUT')
@if($linkField)
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label class="form-label" for="target-page">Linked page</label>
        <select id="target-page" class="form-select" name="target_page_id">
            <option value="">Use the destination entered below</option>
            @foreach($linkablePages as $linkPage)<option value="{{ $linkPage->id }}" @selected((string) old('target_page_id', $record->target_page_id) === (string) $linkPage->id)>{{ $linkPage->title }}{{ $linkPage->is_visible ? '' : ' (draft)' }}</option>@endforeach
        </select>
        <div class="form-text">A linked item keeps its URL up to date and stays hidden while the page is unpublished.@if($record->overrides) Customised here: {{ collect($record->overrides)->map(fn ($role) => ucfirst(str_replace('_', ' ', $role)))->implode(', ') }}.@endif</div>
    </div>
</div>
@endif
@include('cms.fields')<button class="btn btn-primary mt-3">Save item</button></form>
@if($record->target_page_id && $record->overrides)
<form method="POST" action="{{ route('cms.placements.reset',$record) }}" class="mt-2" data-confirm="Replace the customised text with the page's current values?">@csrf<button class="btn btn-soft-secondary">Use the page's values</button></form>
@endif
</div></div>
@endsection
