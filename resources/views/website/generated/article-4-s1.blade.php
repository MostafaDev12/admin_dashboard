<section class="section section--white">
      <div class="container article-details-wrap">

        
        <article class="article-main">
          <div class="article-main__meta">@foreach($section->items->where('group_key', 'g-1')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.article-4-s1-g-1')
@endforeach
</div>
          <h1 data-article-title="">{{ $section->content['f1'] ?? '' }}</h1>
          <img class="article-main__image" data-article-image="" src="{{ $section->content['f2'] ?? '' }}" alt="{{ $mediaAlts[$section->content['f2'] ?? ''] ?? $section->content['f3'] ?? '' }}">
          <div class="article-main__body" data-article-body="">
    <p>{{ $section->content['f4'] ?? '' }}</p>
    <h2>{{ $section->content['f5'] ?? '' }}</h2>
    <p>{{ $section->content['f6'] ?? '' }}</p>
    <h2>{{ $section->content['f7'] ?? '' }}</h2>
    <p>{{ $section->content['f8'] ?? '' }}</p>
    <h2>{{ $section->content['f9'] ?? '' }}</h2>
    <p>{{ $section->content['f10'] ?? '' }}</p></div>
        </article>

        
        <aside class="article-sidebar">
          <h3>{{ $section->content['f11'] ?? '' }}</h3>
          @foreach($section->items->where('group_key', 'g-2')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.article-4-s1-g-2')
@endforeach
</aside>
      </div>
    </section>