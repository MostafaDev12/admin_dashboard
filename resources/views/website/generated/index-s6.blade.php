<section class="section testimonials">
      <div class="container">
        <div class="center-head"><span class="eyebrow">{{ $section->content['f1'] ?? '' }}</span><h2 class="section-title">{{ $section->content['f2'] ?? '' }}</h2><p>{{ $section->content['f3'] ?? '' }}</p></div>
        <div class="reviews-grid">
          @foreach($section->items->where('group_key', 'g-1')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.index-s6-g-1')
@endforeach
</div>
      </div>
    </section>