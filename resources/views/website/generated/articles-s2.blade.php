<section class="section section--white">
      <div class="container">
        <div class="article-tools">
          <input type="search" placeholder="{{ $section->content['f1'] ?? '' }}" data-article-search="">
          <select data-article-filter="">
            @foreach($section->items->where('group_key', 'g-1')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.articles-s2-g-1')
@endforeach
</select>
        </div>

        <div class="articles-grid" data-articles-grid="">
          @foreach($section->items->where('group_key', 'g-2')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.articles-s2-g-2')
@endforeach
</div>
      </div>
    </section>