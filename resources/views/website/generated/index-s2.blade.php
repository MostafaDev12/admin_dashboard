<section class="trust-strip">
      <div class="container trust-grid">
        @foreach($section->items->where('group_key', 'g-1')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.index-s2-g-1')
@endforeach
</div>
    </section>