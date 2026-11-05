<section class="inner-hero">
      <div class="container inner-hero__content">
        <h1>{{ $section->content['f1'] ?? '' }}</h1>
        <p>{{ $section->content['f2'] ?? '' }}</p>
        <div class="breadcrumbs"><a href="{{ $section->content['f3'] ?? '' }}">{{ $section->content['f4'] ?? '' }}</a>@foreach($section->items->where('group_key', 'g-1')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.articles-s1-g-1')
@endforeach
</div>
      </div>
    </section>