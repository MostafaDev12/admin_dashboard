<section class="section details-contact">
      <div class="container details-contact-grid">
        <div><span class="eyebrow">{{ $section->content['f1'] ?? '' }}</span><h2 class="section-title">{{ $section->content['f2'] ?? '' }}</h2><p>{{ $section->content['f3'] ?? '' }}</p></div>
        <form class="card form-card demo-form" method="POST" action="{{ route('website.contact') }}">@csrf
          <div class="form-grid">
            <div class="field"><label>{{ $section->content['f4'] ?? '' }}</label><input required="" placeholder="{{ $section->content['f5'] ?? '' }}" name="name"></div>
            <div class="field"><label>{{ $section->content['f6'] ?? '' }}</label><input required="" placeholder="{{ $section->content['f7'] ?? '' }}" name="phone"></div>
            <div class="field field--full"><label>{{ $section->content['f8'] ?? '' }}</label><textarea placeholder="{{ $section->content['f9'] ?? '' }}" name="service"></textarea></div>
            <button class="btn btn-dark field--full" type="submit">{{ $section->content['f10'] ?? '' }}<i class="{{ $section->content['f11'] ?? '' }}"></i></button>
          </div>
        </form>
      </div>
    </section>