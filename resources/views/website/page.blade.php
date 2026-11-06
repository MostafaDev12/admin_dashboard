<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
@if(isset($globals['global-site']) && $globals['global-site']->is_visible && !empty($globals['global-site']->content['favicon']))<link rel="icon" href="{{ $globals['global-site']->content['favicon'] }}">@endif
<title>{{ $page->seo['title'] ?? $page->title }}</title>
<meta name="description" content="{{ $page->seo['description'] ?? $page->description }}">
<meta name="robots" content="{{ ($page->seo['noindex'] ?? false) ? 'noindex,nofollow' : 'index,follow' }}">
<link rel="canonical" href="{{ $page->public_slug !== null ? $page->public_url : ($page->seo['canonical'] ?? $page->public_url) }}">
<meta property="og:url" content="{{ $page->public_url }}">
<meta property="og:title" content="{{ $page->seo['og_title'] ?? $page->title }}">
<meta property="og:description" content="{{ $page->seo['og_description'] ?? $page->description }}">
@if(!empty($page->seo['og_image']))<meta property="og:image" content="{{ url($page->seo['og_image']) }}">@endif
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
@foreach($page->assets['styles'] as $style)<link rel="stylesheet" href="{{ str_starts_with($style, '/') ? url($style) : $style }}">@endforeach
</head>
<body data-page="{{ $page->body_page }}">
@if(isset($globals['global-header']) && $globals['global-header']->is_visible)@include($globals['global-header']->template,['section'=>$globals['global-header']])@endif
<main>
@if(session('website_success'))<div class="container" role="status"><p>{{ session('website_success') }}</p></div>@endif
@if($errors->any())<div class="container" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@foreach($page->sections->where('is_visible',true) as $section)
@include($section->template,['section'=>$section])
@endforeach
</main>
@if(isset($globals['global-footer']) && $globals['global-footer']->is_visible)@include($globals['global-footer']->template,['section'=>$globals['global-footer']])@endif
@if(isset($globals['global-floating']) && $globals['global-floating']->is_visible)@include($globals['global-floating']->template,['section'=>$globals['global-floating']])@endif
<script>window.websiteMessages=@json(app(\App\Services\CmsMessages::class)->forBrowser());</script>
<script>window.websiteRotator=@json(isset($globals['global-rotator']) && $globals['global-rotator']->is_visible ? $globals['global-rotator']->items->where('is_visible',true)->sortBy('position')->pluck('content')->values() : []);</script>
@foreach($page->assets['scripts'] as $script)<script src="{{ url($script) }}" defer></script>@endforeach
</body>
</html>
