<section class="section vision-section">
      <div class="container vision-grid">
        <div class="vision-copy">
          <span class="eyebrow">{{ $section->content['f1'] ?? '' }}</span>
          <h2 class="section-title">{{ $section->content['f2'] ?? '' }}</h2>
          <p>{{ $section->content['f3'] ?? '' }}</p>
          <a href="{{ $section->content['f4'] ?? '' }}" class="text-link">{{ $section->content['f5'] ?? '' }}<i class="{{ $section->content['f6'] ?? '' }}"></i></a>
        </div>
        <div class="compare" data-compare="">
          <img src="{{ $section->content['f7'] ?? '' }}" alt="{{ $mediaAlts[$section->content['f7'] ?? ''] ?? $section->content['f8'] ?? '' }}">
          <div class="compare__before" data-before=""><img src="{{ $section->content['f9'] ?? '' }}" alt="{{ $mediaAlts[$section->content['f9'] ?? ''] ?? $section->content['f10'] ?? '' }}"></div>
          <input type="range" min="0" max="100" value="50" aria-label="{{ $section->content['f11'] ?? '' }}" data-range="">
          <span class="compare__label before-label">{{ $section->content['f12'] ?? '' }}</span>
          <span class="compare__label after-label">{{ $section->content['f13'] ?? '' }}</span>
          <div class="compare__handle" data-handle=""><i class="{{ $section->content['f14'] ?? '' }}"></i></div>
        </div>
      </div>
    </section>