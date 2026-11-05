<section class="section location-section" id="location">
      <div class="container location-grid">
        <div class="location-media">
          
          <img src="{{ $section->content['f1'] ?? '' }}" alt="{{ $mediaAlts[$section->content['f1'] ?? ''] ?? $section->content['f2'] ?? '' }}">
          <div class="fake-map"><span><i class="{{ $section->content['f3'] ?? '' }}"></i></span><b>{{ $section->content['f4'] ?? '' }}</b><small>{{ $section->content['f5'] ?? '' }}</small>@if(!empty($section->content['map_url']))<a href="{{ $section->content['map_url'] }}" target="_blank" rel="noopener" aria-label="{{ $section->content['f4'] ?? '' }}" style="position:absolute;inset:0"></a>@endif</div>
        </div>
        <div><span class="eyebrow">{{ $section->content['f6'] ?? '' }}</span><h2 class="section-title">{{ $section->content['f7'] ?? '' }}</h2><p class="section-copy">{{ $section->content['f8'] ?? '' }}</p><div class="location-features">@foreach($section->items->where('group_key', 'g-1')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.contact-s3-g-1')
@endforeach
</div><a class="btn btn-outline" href="{{ $section->content['f9'] ?? '' }}">{{ $section->content['f10'] ?? '' }}</a></div>
      </div>
    </section>