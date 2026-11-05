<section class="home-hero">
      <div class="container home-hero__grid">
        <div class="home-hero__content">
          <span class="hero-chip"><i class="{{ $section->content['f1'] ?? '' }}"></i>{{ $section->content['f2'] ?? '' }}</span>
          <h1>{{ $section->content['f3'] ?? '' }}<span>{{ $section->content['f4'] ?? '' }}</span></h1>
          <p>{{ $section->content['f5'] ?? '' }}</p>
          <div class="hero-actions">
            <a class="btn btn-primary" href="{{ $section->content['f6'] ?? '' }}">{{ $section->content['f7'] ?? '' }}<i class="{{ $section->content['f8'] ?? '' }}"></i></a>
            <a class="btn btn-outline hero-outline" href="{{ $section->content['f9'] ?? '' }}">{{ $section->content['f10'] ?? '' }}</a>
          </div>
          <div class="hero-badges">
            @foreach($section->items->where('group_key', 'g-1')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.index-s1-g-1')
@endforeach
</div>
        </div>

        
        <div class="home-hero__visual">
          <div class="doctor-arch"><img src="{{ $section->content['f11'] ?? '' }}" alt="{{ $mediaAlts[$section->content['f11'] ?? ''] ?? $section->content['f12'] ?? '' }}"></div>
        </div>
      </div>
    </section>