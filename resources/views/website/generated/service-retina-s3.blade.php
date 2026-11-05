<section class="section details-info-section">
      <div class="container details-info-grid">
        @foreach($section->items->where('group_key', 'g-1')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.service-retina-s3-g-1')
@endforeach
</div>
    </section>