@php($content = $item?->content ?? [])
<div class="row g-3">
    <div class="col-md-6"><label class="form-label" for="menu-label">Label</label><input dir="auto" id="menu-label" class="form-control" name="label" value="{{ old('label', $content['label'] ?? '') }}" required maxlength="190"></div>
    <div class="col-md-6"><label class="form-label" for="menu-mobile-label">Mobile label <span class="text-muted">(optional)</span></label><input dir="auto" id="menu-mobile-label" class="form-control" name="mobile_label" value="{{ old('mobile_label', $content['mobile_label'] ?? '') }}" maxlength="190"></div>
    <div class="col-md-6"><label class="form-label" for="menu-page">Website page</label>
        <select id="menu-page" class="form-select" name="target_page_id">
            <option value="">Not linked to a page</option>
            @foreach($pages as $page)<option value="{{ $page->id }}" @selected((string) old('target_page_id', $item?->target_page_id) === (string) $page->id)>{{ $page->title }}{{ $page->is_visible ? '' : ' (draft)' }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-6"><label class="form-label" for="menu-url">Or custom link</label><input dir="ltr" id="menu-url" class="form-control" name="url" value="{{ old('url', $item && ! $item->target_page_id ? ($content['link'] ?? '') : '') }}" placeholder="https://example.com or #section"><div class="form-text">Used only when no page is selected.</div></div>
    <div class="col-md-6"><label class="form-label" for="menu-parent">Position</label>
        <select id="menu-parent" class="form-select" name="parent_id">
            <option value="">Top level</option>
            @foreach($parents as $parent)<option value="{{ $parent->id }}" @selected((string) old('parent_id', $item?->parent_id) === (string) $parent->id)>Inside: {{ $parent->content['label'] ?? $parent->label }}</option>@endforeach
        </select>
        @if($item && $parents->isEmpty())<div class="form-text">This item has its own dropdown, so it stays at the top level.</div>@endif
    </div>
    <div class="col-md-2"><label class="form-label" for="menu-desktop">Desktop</label><select id="menu-desktop" class="form-select" name="show_desktop"><option value="1" @selected(old('show_desktop', $content['show_desktop'] ?? '1') !== '0')>Show</option><option value="0" @selected(old('show_desktop', $content['show_desktop'] ?? '1') === '0')>Hide</option></select></div>
    <div class="col-md-2"><label class="form-label" for="menu-mobile">Mobile</label><select id="menu-mobile" class="form-select" name="show_mobile"><option value="1" @selected(old('show_mobile', $content['show_mobile'] ?? '1') !== '0')>Show</option><option value="0" @selected(old('show_mobile', $content['show_mobile'] ?? '1') === '0')>Hide</option></select></div>
    <div class="col-md-2"><label class="form-label" for="menu-visible">Status</label><select id="menu-visible" class="form-select" name="is_visible"><option value="1" @selected((string) old('is_visible', $item ? (int) $item->is_visible : 1) === '1')>Visible</option><option value="0" @selected((string) old('is_visible', $item ? (int) $item->is_visible : 1) === '0')>Hidden</option></select></div>
</div>
