@php($menu = app(\App\Services\CmsMenu::class)->tree($section, $page))

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
@foreach($menu['desktop'] as $node)
@if($node->children->isNotEmpty())
<div class="nav-dropdown" data-dropdown="">
           <a class="nav-link nav-dropdown__trigger{{ $node->active ? ' active' : '' }}" href="{{ $node->href }}" aria-expanded="false" @if($node->current) aria-current="page" @endif {{ new \Illuminate\View\ComponentAttributeBag($node->attributes) }}>{{ $node->label }} <i class="{{ $node->icon }}"></i>

</a>
            <div class="nav-dropdown__menu">
              @foreach($node->children as $child)
<a href="{{ $child->href }}" @if($child->current) aria-current="page" @endif {{ new \Illuminate\View\ComponentAttributeBag($child->attributes) }}>{{ $child->label }}</a>
@endforeach
</div>
          </div>
@else
<a class="nav-link{{ $node->active ? ' active' : '' }}" href="{{ $node->href }}" @if($node->current) aria-current="page" @endif {{ new \Illuminate\View\ComponentAttributeBag($node->attributes) }}>{{ $node->label }}</a>
@endif
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
@foreach($menu['mobile'] as $node)
@if($node->children->isNotEmpty())
<div class="mobile-services" data-mobile-services="">
            <button class="mobile-services__trigger" type="button" aria-expanded="false" data-mobile-services-trigger="">
              <span>{{ $node->mobile_label }}</span><i class="{{ $node->mobile_icon }}"></i>
            </button>
            <div class="mobile-services__menu" data-mobile-services-menu="">
              @foreach($node->children as $child)
<a href="{{ $child->href }}" @if($child->current) aria-current="page" @endif {{ new \Illuminate\View\ComponentAttributeBag($child->attributes) }}>{{ $child->mobile_label }}</a>
@endforeach
</div>
          </div>
@else
<a href="{{ $node->href }}" @if($node->current) aria-current="page" @endif {{ new \Illuminate\View\ComponentAttributeBag($node->attributes) }}>{{ $node->mobile_label }}</a>
@endif
@endforeach
</div>
      </div>
    </div>
  </header>
