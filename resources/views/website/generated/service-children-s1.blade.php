<section class="inner-hero details-hero">
      <div class="container inner-hero__content">
        <span class="eyebrow details-eyebrow">{{ $section->content['f1'] ?? '' }}</span>
        <h1 data-service-title="">{{ $section->content['f2'] ?? '' }}</h1>
        <p data-service-intro="">{{ $section->content['f3'] ?? '' }}</p>
        <div class="breadcrumbs"><a href="{{ $section->content['f4'] ?? '' }}">{{ $section->content['f5'] ?? '' }}</a><span>{{ $section->content['f6'] ?? '' }}</span><a href="{{ $section->content['f7'] ?? '' }}">{{ $section->content['f8'] ?? '' }}</a>@foreach($section->items->where('group_key', 'g-1')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.service-children-s1-g-1')
@endforeach
</div>
      </div>
    </section>