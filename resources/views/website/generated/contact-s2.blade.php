<section class="section section--white" id="appointment">
      <div class="container contact-grid">
  

        <div class="contact-side">
          <span class="eyebrow">{{ $section->content['f1'] ?? '' }}</span><h2 class="section-title">{{ $section->content['f2'] ?? '' }}</h2><p class="section-copy">{{ $section->content['f3'] ?? '' }}</p>
          <div class="contact-list">
            @foreach($section->items->where('group_key', 'g-1')->where('parent_id', null)->where('is_visible', true)->sortBy('position') as $item1)
@include('website.generated.contact-s2-g-1')
@endforeach
</div>
        </div>
        <div data-appointment-form="">
        <form class="booking-form" method="POST" action="{{ route('website.contact') }}">@csrf

            <h3 class="booking-form__title">{{ $section->content['f4'] ?? '' }}</h3>

            <div class="booking-form__grid">

                <div class="booking-field">
                    <label>{{ $section->content['f5'] ?? '' }}<span>{{ $section->content['f6'] ?? '' }}</span></label>
                    <input type="text" name="name" placeholder="{{ $section->content['f7'] ?? '' }}" required="">
                </div>

                <div class="booking-field">
                    <label>{{ $section->content['f8'] ?? '' }}</label>
                    <input type="number" name="age" placeholder="{{ $section->content['f9'] ?? '' }}" min="1" max="120">
                </div>

                <div class="booking-field">
                    <label>{{ $section->content['f10'] ?? '' }}<span>{{ $section->content['f11'] ?? '' }}</span></label>
                    <input type="tel" name="phone" placeholder="{{ $section->content['f12'] ?? '' }}" required="">
                </div>

                <div class="booking-field">
                    <label>{{ $section->content['f13'] ?? '' }}</label>
                    <input type="email" name="email" placeholder="{{ $section->content['f14'] ?? '' }}">
                </div>

       <div class="booking-field booking-field--full">
    <label>{{ $section->content['f15'] ?? '' }}</label>

    <input type="text" name="complaint" placeholder="{{ $section->content['f16'] ?? '' }}">
</div>

                <div class="booking-field booking-field--full">
                    <label>{{ $section->content['f17'] ?? '' }}</label>

                    <input type="date" name="date">
                </div>

                <div class="booking-field booking-field--full">
                    <label>{{ $section->content['f18'] ?? '' }}</label>

                    <textarea name="details" placeholder="{{ $section->content['f19'] ?? '' }}" rows="4"></textarea>
                </div>

                <button class="booking-form__submit booking-field--full" type="submit">
                    <i class="{{ $section->content['f20'] ?? '' }}"></i>{{ $section->content['f21'] ?? '' }}</button>

            </div>

        </form>
    </div>
      </div>
    </section>