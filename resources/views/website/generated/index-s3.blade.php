<section class="section section--white services-preview">

    <div class="container">

        
        <div class="section-head">
            <div>
<a href="{{ $section->content['f1'] ?? '' }}" class="eyebrow">{{ $section->content['f2'] ?? '' }}</a>                <h4 class="section-title">{{ $section->content['f3'] ?? '' }}<br>{{ $section->content['f4'] ?? '' }}</h4>
            </div>

            <p class="section-copy">{{ $section->content['f5'] ?? '' }}</p>
        </div>


        
        <div class="expand-services">

            
            @foreach($section->items->where('group_key', 'g-1')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.index-s3-g-1')
@endforeach
</div>

    </div>

</section>