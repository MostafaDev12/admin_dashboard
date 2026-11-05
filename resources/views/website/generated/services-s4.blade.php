<section class="section why-strip">
      <div class="container">
        <h2 class="why-title">{{ $section->content['f1'] ?? '' }}</h2>
        <div class="why-grid">
          @foreach($section->items->where('group_key', 'g-1')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.services-s4-g-1')
@endforeach
</div>
      </div>
    </section>