<section class="about-hero">
      <div class="container about-hero__grid">
        <div class="about-hero__text">
          <span class="eyebrow">{{ $section->content['f1'] ?? '' }}</span>
          <h1>{{ $section->content['f2'] ?? '' }}</h1>
          <h2>{{ $section->content['f3'] ?? '' }}</h2>
          <p>{{ $section->content['f4'] ?? '' }}</p>
          <div class="about-points">
            @foreach($section->items->where('group_key', 'g-1')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.about-s1-g-1')
@endforeach
</div>
          <a class="btn btn-outline" href="{{ $section->content['f5'] ?? '' }}">{{ $section->content['f6'] ?? '' }}<i class="{{ $section->content['f7'] ?? '' }}"></i></a>
        </div>
        <div class="about-hero__photo"><img src="{{ $section->content['f8'] ?? '' }}" alt="{{ $mediaAlts[$section->content['f8'] ?? ''] ?? $section->content['f9'] ?? '' }}"></div>
      </div>
    </section>