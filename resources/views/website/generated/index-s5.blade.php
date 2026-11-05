<section class="section section--white">
      <div class="container content-duo">
        <a href="{{ $section->content['f1'] ?? '' }}" class="content-panel content-panel--article" data-home-article="">
          <div class="home-article__media"><img src="{{ $section->content['f2'] ?? '' }}" alt="{{ $mediaAlts[$section->content['f2'] ?? ''] ?? $section->content['f3'] ?? '' }}" data-home-article-image=""></div>
          <div class="home-article__copy"><span><i class="{{ $section->content['f4'] ?? '' }}"></i>{{ $section->content['f5'] ?? '' }}</span><h3 data-home-article-title="">{{ $section->content['f6'] ?? '' }}</h3><p data-home-article-copy="">{{ $section->content['f7'] ?? '' }}</p><b>{{ $section->content['f8'] ?? '' }}<i class="{{ $section->content['f9'] ?? '' }}"></i></b></div>
        </a>
        <a href="{{ $section->content['f10'] ?? '' }}" class="content-panel content-panel--dark" id="media">
          <span><i class="{{ $section->content['f11'] ?? '' }}"></i>{{ $section->content['f12'] ?? '' }}</span>
          <h3>{{ $section->content['f13'] ?? '' }}</h3>
          <p>{{ $section->content['f14'] ?? '' }}</p>
          <b>{{ $section->content['f15'] ?? '' }}<i class="{{ $section->content['f16'] ?? '' }}"></i></b>
          <div class="mini-video"><i class="{{ $section->content['f17'] ?? '' }}"></i></div>
        </a>
      </div>
    </section>