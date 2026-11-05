
  <footer class="site-footer">
    <div class="container footer-grid">

      
      <div class="footer-brand">
        <img src="{{ $section->content['f1'] ?? '' }}" alt="{{ $mediaAlts[$section->content['f1'] ?? ''] ?? $section->content['f2'] ?? '' }}" class="footer-logo" onerror="this.style.display='none'">
        <p>{{ $section->content['f3'] ?? '' }}</p>
        <div class="footer-social">
          @foreach($section->items->where('group_key', 'g-1')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.global-footer-g-1')
@endforeach
</div>
      </div>

      
      @foreach($section->items->where('group_key', 'g-2')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.global-footer-g-2')
@endforeach
<div class="footer-col footer-contact">
        <h3>{{ $section->content['f4'] ?? '' }}</h3>
        @foreach($section->items->where('group_key', 'g-3')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.global-footer-g-3')
@endforeach
</div>
    </div>

    
   
<div class="footer-bottom">
    <div class="container footer-bottom__inner">

        <p class="footer-copyright">{{ $section->content['f5'] ?? '' }}<a href="{{ $section->content['f6'] ?? '' }}" target="_blank" rel="noopener noreferrer">{{ $section->content['f7'] ?? '' }}</a>
        </p>

    </div>
</div>
  </footer>
