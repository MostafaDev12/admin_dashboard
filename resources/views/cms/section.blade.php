@extends('cms.layout')
@section('title',\App\Support\CmsLabels::section($section->label))
@section('content')
@php($editableGroups = collect($section->groups)->reject(fn ($group) => in_array($group['key'], \App\Services\CmsMenu::GROUPS, true))->values())
@php($editableItems = $section->items->reject(fn ($item) => in_array($item->group_key, \App\Services\CmsMenu::GROUPS, true)))
<div class="d-flex gap-2 mb-3 flex-wrap">
    <a class="btn btn-soft-secondary" href="{{ $section->page_id ? route('cms.pages.edit',$section->page_id) : route('cms.globals') }}">Back</a>
    @if(\App\Services\CmsMenu::isConsolidated($section))<a class="btn btn-soft-primary" href="{{ route('cms.menu') }}">Manage navigation links</a>@endif
    @if($section->page_id)
        <form method="POST" action="{{ route('cms.sections.visibility',$section) }}">@csrf @method('PATCH')<input type="hidden" name="is_visible" value="{{ $section->is_visible ? 0 : 1 }}"><button class="btn btn-soft-warning">{{ $section->is_visible ? 'Hide section' : 'Show section' }}</button></form>
    @endif
</div>
<div class="card"><div class="card-header"><h5 class="mb-0">Section content</h5></div><div class="card-body"><form action="{{ route('cms.sections.update',$section) }}" method="POST">@csrf @method('PUT')@include('cms.fields')<button class="btn btn-primary mt-3">Save section</button></form></div></div>
@foreach($editableItems->groupBy('group_key') as $groupKey=>$items)
<div class="card"><div class="card-header"><h5 class="mb-0">{{ \App\Support\CmsLabels::group(collect($section->groups)->firstWhere('key',$groupKey)['label'] ?? 'Collection') }}</h5></div><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Item</th><th>Parent</th><th>Links to</th><th>Visibility</th><th>Actions</th></tr></thead><tbody>
@foreach($items as $item)
<tr>
    <td dir="auto">{{ $item->label }}</td>
    <td>{{ $item->parent_id ? ($section->items->firstWhere('id',$item->parent_id)?->label ?? '') : '—' }}</td>
    <td dir="auto"><small>{{ $pagesById->get($item->target_page_id)?->title ?? ($item->detached_from ? 'Removed: '.$item->detached_from : '—') }}</small></td>
    <td>
        @php($hiddenByPage = $item->target_page_id && ! ($pagesById->get($item->target_page_id)?->is_visible ?? false))
        <span class="badge bg-{{ ! $item->is_visible ? 'secondary' : ($hiddenByPage ? 'warning' : 'success') }}">{{ ! $item->is_visible ? 'Hidden' : ($hiddenByPage ? 'Page unpublished' : 'Visible') }}</span>
    </td>
    <td><div class="d-flex gap-1 flex-wrap">
        <form method="POST" action="{{ route('cms.items.move',$item) }}">@csrf<input type="hidden" name="direction" value="up"><button class="btn btn-sm btn-soft-secondary" aria-label="Move up"><i class="ri-arrow-up-line"></i></button></form>
        <form method="POST" action="{{ route('cms.items.move',$item) }}">@csrf<input type="hidden" name="direction" value="down"><button class="btn btn-sm btn-soft-secondary" aria-label="Move down"><i class="ri-arrow-down-line"></i></button></form>
        <a class="btn btn-sm btn-soft-primary" href="{{ route('cms.items.edit',$item) }}">Edit</a>
        <form method="POST" action="{{ route('cms.items.visibility',$item) }}">@csrf @method('PATCH')<input type="hidden" name="is_visible" value="{{ $item->is_visible ? 0 : 1 }}"><button class="btn btn-sm btn-soft-warning">{{ $item->is_visible ? 'Hide' : 'Show' }}</button></form>
        <form method="POST" action="{{ route('cms.items.store',$section) }}">@csrf<input type="hidden" name="source_id" value="{{ $item->id }}"><button class="btn btn-sm btn-soft-success">Add similar</button></form>
        <form method="POST" action="{{ route('cms.items.destroy',$item) }}" data-confirm="Move this item and any nested items to the trash?">@csrf @method('DELETE')<button class="btn btn-sm btn-soft-danger">Remove</button></form>
    </div></td>
</tr>
@endforeach
</tbody></table></div></div>
@endforeach
@if($editableGroups->isNotEmpty())
<div class="card"><div class="card-header"><h5 class="mb-0">Add an item to a collection</h5></div><div class="card-body"><form method="POST" action="{{ route('cms.items.store',$section) }}" class="row g-3">@csrf<div class="col-md-5"><label>Collection</label><select name="group_key" class="form-select">@foreach($editableGroups as $group)<option value="{{ $group['key'] }}">{{ \App\Support\CmsLabels::group($group['label']) }}{{ $group['parent_group'] ? ' (nested)' : '' }}</option>@endforeach</select></div><div class="col-md-5"><label>Parent (nested collections only)</label><select name="parent_id" class="form-select"><option value="">No parent</option>@foreach($editableItems as $parent)<option value="{{ $parent->id }}">{{ $parent->label }}</option>@endforeach</select></div><div class="col-md-2 align-self-end"><button class="btn btn-primary">Add item</button></div></form></div></div>
@endif
@endsection
