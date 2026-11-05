<section class="section about-form">
      <div class="container appointment-grid">
        <div class="about-contact">
          <span class="eyebrow">{{ $section->content['f1'] ?? '' }}</span><h2 class="section-title">{{ $section->content['f2'] ?? '' }}</h2><p>{{ $section->content['f3'] ?? '' }}</p>
          <div class="contact-cards">
            @foreach($section->items->where('group_key', 'g-1')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.about-s3-g-1')
@endforeach
</div>
        </div>

        
        <form class="card form-card demo-form" method="POST" action="{{ route('website.contact') }}">@csrf
          <h3>{{ $section->content['f4'] ?? '' }}</h3>
          <div class="form-grid">
            <div class="field"><label>{{ $section->content['f5'] ?? '' }}</label><input required="" placeholder="{{ $section->content['f6'] ?? '' }}" name="name"></div>
            <div class="field"><label>{{ $section->content['f7'] ?? '' }}</label><input required="" placeholder="{{ $section->content['f8'] ?? '' }}" name="phone"></div>
            <div class="field"><label>{{ $section->content['f9'] ?? '' }}</label><select name="service">@foreach($section->items->where('group_key', 'g-2')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.about-s3-g-2')
@endforeach
</select></div>
            <div class="field"><label>{{ $section->content['f10'] ?? '' }}</label><select name="preferred_time">@foreach($section->items->where('group_key', 'g-3')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.about-s3-g-3')
@endforeach
</select></div>
            <div class="field field--full"><label>{{ $section->content['f11'] ?? '' }}</label><textarea name="details"></textarea></div>
            <button type="submit" class="btn btn-dark field--full">{{ $section->content['f12'] ?? '' }}<i class="{{ $section->content['f13'] ?? '' }}"></i></button>
          </div>
        </form>
      </div>
    </section>