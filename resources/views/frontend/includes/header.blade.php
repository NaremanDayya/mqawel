<!-- ─────────────────── NAVBAR ─────────────────── -->
<nav class="nav" role="navigation" aria-label="Main navigation">
  <div class="nav-inner">
    <a href="#home" class="nav-logo" aria-label="{{__('frontend.app_name')}}">
      <span class="nav-logo-text">مقاول<span>+</span></span>
    </a>

    <ul class="nav-links" role="list">
      <li><a href="#home" class="active">{{__('frontend.homepage')}}</a></li>
      <li><a href="#about">{{__('frontend.about_us')}}</a></li>
      <li><a href="#pricing">{{__('frontend.pricing')}}</a></li>
      <li><a href="#features">{{__('frontend.features')}}</a></li>
      <li><a href="#faq">{{__('frontend.contact')}}</a></li>
    </ul>

    <div class="nav-actions">
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
