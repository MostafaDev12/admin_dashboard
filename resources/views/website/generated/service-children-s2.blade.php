<section class="section details-main">
      <div class="container details-grid">

        
        <div class="details-media">
          <img data-service-image="" src="{{ $section->content['f1'] ?? '' }}" alt="{{ $mediaAlts[$section->content['f1'] ?? ''] ?? $section->content['f2'] ?? '' }}">
        </div>

        
        <article class="details-copy">
          <span class="eyebrow">{{ $section->content['f3'] ?? '' }}</span>
          <h2 class="section-title" data-service-heading="">{{ $section->content['f4'] ?? '' }}</h2>
          <p class="details-lead" data-service-description="">{{ $section->content['f5'] ?? '' }}</p>
          <div class="details-points" data-service-points="">@foreach($section->items->where('group_key', 'g-1')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.service-children-s2-g-1')
@endforeach
</div>
          <div class="details-actions">
            <a class="btn btn-primary" href="{{ $section->content['f6'] ?? '' }}">{{ $section->content['f7'] ?? '' }}<i class="{{ $section->content['f8'] ?? '' }}"></i></a>
            <a class="btn btn-outline" href="{{ $section->content['f9'] ?? '' }}" target="_blank" rel="noopener"><i class="{{ $section->content['f10'] ?? '' }}"></i>{{ $section->content['f11'] ?? '' }}</a>
          </div>
        </article>
      </div>
    </section>