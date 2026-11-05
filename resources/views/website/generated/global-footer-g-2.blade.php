<div class="{{ $item1->content['f1'] ?? '' }}" {{ new \Illuminate\View\ComponentAttributeBag($item1->content['attributes_1'] ?? []) }}>
        <h3 {{ new \Illuminate\View\ComponentAttributeBag($item1->content['attributes_2'] ?? []) }}>{{ $item1->content['f2'] ?? '' }}</h3>
        @foreach($section->items->where('group_key', 'g-2-1')->where('parent_id', $item1->id)->where('is_visible', true)->sortBy('position') as $item2)
@include('website.generated.global-footer-g-2-1')
@endforeach
</div>