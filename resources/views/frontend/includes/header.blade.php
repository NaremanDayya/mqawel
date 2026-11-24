<!-- ─────────────────── NAVBAR ─────────────────── -->
<nav class="nav" role="navigation" aria-label="Main navigation">
  <div class="nav-inner">
    <a href="#home" class="nav-logo" aria-label="{{__('frontend.app_name')}}">
      <span class="nav-logo-text">{{ __('landing.brand') }}<span>+</span></span>
    </a>

    <ul class="nav-links" role="list">
      <li><a href="#home" class="active">{{__('frontend.homepage')}}</a></li>
      <li><a href="#about">{{__('frontend.about_us')}}</a></li>
      <li><a href="#pricing">{{__('frontend.pricing')}}</a></li>
      <li><a href="#features">{{__('frontend.features')}}</a></li>
      <li><a href="#faq">{{__('frontend.contact')}}</a></li>
    </ul>

    <div class="nav-actions">
      <a href="{{ url('lang/'.(app()->getLocale() === 'ar' ? 'en' : 'ar')) }}" class="btn btn-ghost lang-switch" lang="{{ app()->getLocale() === 'ar' ? 'en' : 'ar' }}" hreflang="{{ app()->getLocale() === 'ar' ? 'en' : 'ar' }}" aria-label="{{ __('landing.switch_aria') }}" style="padding-inline:14px;font-weight:700;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true" style="vertical-align:-3px;margin-inline-end:6px;"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.7 3.8 5.7 3.8 9s-1.3 6.3-3.8 9c-2.5-2.7-3.8-5.7-3.8-9S9.5 5.7 12 3z"/></svg>{{ __('landing.switch_label') }}
      </a>
      <a href="{{url('company')}}" class="btn btn-ghost">{{__('frontend.sign_in')}}</a>
      <a href="{{url('company/register')}}" class="btn btn-primary">
        {{__('frontend.subscribe')}}
      </a>
    </div>

    <button class="hamburger" aria-label="Open menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>
