
  <header class="site-header" data-site-header="">

    
    <div class="top-strip">
      <div class="container top-strip__inner">
        <div class="top-social" aria-label="{{ $section->content['f1'] ?? '' }}">
          @foreach($section->items->where('group_key', 'g-1')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.global-header-g-1')
@endforeach
</div>
        <div class="top-contact">
          @foreach($section->items->where('group_key', 'g-2')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.global-header-g-2')
@endforeach
</div>
      </div>
    </div>

    
    <div class="header-main">
      <div class="container header-main__inner">
        <a class="brand" href="{{ $section->content['f2'] ?? '' }}" aria-label="{{ $section->content['f3'] ?? '' }}">
          
          <img src="{{ $section->content['f4'] ?? '' }}" alt="{{ $mediaAlts[$section->content['f4'] ?? ''] ?? $section->content['f5'] ?? '' }}" onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
          <span class="brand-fallback">{{ $section->content['f6'] ?? '' }}</span>
        </a>

        
        <nav class="main-nav" aria-label="{{ $section->content['f7'] ?? '' }}">
          @foreach($section->items->where('group_key', 'g-3')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.global-header-g-3')
@endforeach
<div class="nav-dropdown" data-dropdown="">
           <a class="nav-link nav-dropdown__trigger" data-nav="services" href="{{ $section->content['f8'] ?? '' }}" aria-expanded="false">{{ $section->content['f9'] ?? '' }}<i class="{{ $section->content['f10'] ?? '' }}"></i>

</a>
            <div class="nav-dropdown__menu">
              @foreach($section->items->where('group_key', 'g-4')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.global-header-g-4')
@endforeach
</div>
          </div>

          @foreach($section->items->where('group_key', 'g-5')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.global-header-g-5')
@endforeach
</nav>

        
        <a class="header-cta" href="{{ $section->content['f11'] ?? '' }}"><span>{{ $section->content['f12'] ?? '' }}</span><i class="{{ $section->content['f13'] ?? '' }}"></i></a>

        
        <button class="menu-toggle" type="button" aria-label="{{ $section->content['f14'] ?? '' }}" data-menu-toggle="">
          @foreach($section->items->where('group_key', 'g-6')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.global-header-g-6')
@endforeach
</button>
      </div>

      
      <div class="mobile-menu" data-mobile-menu="">
        <div class="container mobile-menu__inner">
          @foreach($section->items->where('group_key', 'g-7')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.global-header-g-7')
@endforeach
<div class="mobile-services" data-mobile-services="">
            <button class="mobile-services__trigger" type="button" aria-expanded="false" data-mobile-services-trigger="">
              <span>{{ $section->content['f15'] ?? '' }}</span><i class="{{ $section->content['f16'] ?? '' }}"></i>
            </button>
            <div class="mobile-services__menu" data-mobile-services-menu="">
              @foreach($section->items->where('group_key', 'g-8')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.global-header-g-8')
@endforeach
</div>
          </div>

          @foreach($section->items->where('group_key', 'g-9')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.global-header-g-9')
@endforeach
</div>
      </div>
    </div>
  </header>
