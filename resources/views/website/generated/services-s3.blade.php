<section class="section feature-service">
      <div class="container feature-grid">
        <div><img src="{{ $section->content['f1'] ?? '' }}" alt="{{ $mediaAlts[$section->content['f1'] ?? ''] ?? $section->content['f2'] ?? '' }}"></div>
        <div><span class="eyebrow">{{ $section->content['f3'] ?? '' }}</span><h2 class="section-title">{{ $section->content['f4'] ?? '' }}</h2><p class="section-copy">{{ $section->content['f5'] ?? '' }}</p><a class="btn btn-outline" href="{{ $section->content['f6'] ?? '' }}">{{ $section->content['f7'] ?? '' }}<i class="{{ $section->content['f8'] ?? '' }}"></i></a></div>
      </div>
    </section>