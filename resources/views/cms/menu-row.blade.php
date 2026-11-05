<tr>
    <td class="{{ $nested ? 'ps-4' : 'fw-semibold' }}"><span dir="auto">{{ $item->content['label'] ?? $item->label }}</span>@if($nested)<div class="text-muted"><small><i class="ri-corner-down-right-line me-1"></i>Inside <span dir="auto">{{ $parentLabel }}</span></small></div>@endif</td>
    <td dir="auto"><small>{{ $pagesById->get($item->target_page_id)?->title ?? ($item->content['link'] ?? '') }}</small></td>
    <td>
        @if(($item->content['show_desktop'] ?? '1') !== '0')<span class="badge bg-info-subtle text-info">Desktop</span>@endif
        @if(($item->content['show_mobile'] ?? '1') !== '0')<span class="badge bg-info-subtle text-info">Mobile</span>@endif
    </td>
    <td>
        @if(! $item->is_visible)<span class="badge bg-secondary">Hidden</span>
        @elseif($status($item))<span class="badge bg-warning">Page unpublished</span>
        @else<span class="badge bg-success">Visible</span>@endif
    </td>
    <td>
        <div class="d-flex gap-1 justify-content-end flex-wrap">
            <form method="POST" action="{{ route('cms.menu.move', $item) }}">@csrf<input type="hidden" name="direction" value="up"><button class="btn btn-sm btn-soft-secondary" aria-label="Move up"><i class="ri-arrow-up-line"></i></button></form>
            <form method="POST" action="{{ route('cms.menu.move', $item) }}">@csrf<input type="hidden" name="direction" value="down"><button class="btn btn-sm btn-soft-secondary" aria-label="Move down"><i class="ri-arrow-down-line"></i></button></form>
            <form method="POST" action="{{ route('cms.items.visibility', $item) }}">@csrf @method('PATCH')<input type="hidden" name="is_visible" value="{{ $item->is_visible ? 0 : 1 }}"><button class="btn btn-sm btn-soft-warning">{{ $item->is_visible ? 'Hide' : 'Show' }}</button></form>
            <a class="btn btn-sm btn-soft-primary" href="{{ route('cms.menu.edit', $item) }}">Edit</a>
            <form method="POST" action="{{ route('cms.menu.destroy', $item) }}" data-confirm="Move this menu item and anything nested under it to the trash?">@csrf @method('DELETE')<button class="btn btn-sm btn-soft-danger">Remove</button></form>
        </div>
    </td>
</tr>
