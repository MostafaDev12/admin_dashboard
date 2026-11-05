<section class="section section--white clinic-section">
      <div class="container clinic-grid">
        <div class="clinic-photo">
          
          <img src="{{ $section->content['f1'] ?? '' }}" alt="{{ $mediaAlts[$section->content['f1'] ?? ''] ?? $section->content['f2'] ?? '' }}" onerror="this.closest('.clinic-photo').classList.add('is-empty')">
          <a class="clinic-video" href="{{ $section->content['f3'] ?? '' }}"><i class="{{ $section->content['f4'] ?? '' }}"></i>{{ $section->content['f5'] ?? '' }}</a>
        </div>
        <div>
          <span class="eyebrow">{{ $section->content['f6'] ?? '' }}</span>
          <h2 class="section-title">{{ $section->content['f7'] ?? '' }}</h2>
          <p class="section-copy">{{ $section->content['f8'] ?? '' }}</p>
          <div class="clinic-features">
            @foreach($section->items->where('group_key', 'g-1')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.about-s2-g-1')
@endforeach
</div>
          <a class="btn btn-outline" href="{{ $section->content['f9'] ?? '' }}">{{ $section->content['f10'] ?? '' }}<i class="{{ $section->content['f11'] ?? '' }}"></i></a>
        </div>
      </div>
    </section>