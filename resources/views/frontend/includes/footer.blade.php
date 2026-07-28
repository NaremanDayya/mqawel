@php
    $Setting= App\Models\Setting::find(1);
@endphp

<!-- ─────────────────── FOOTER ─────────────────── -->
<footer class="footer">
  <div class="footer-top">
    <div class="footer-inner">

      <!-- Brand col -->
      <div class="footer-brand">
        <a href="{{route('index')}}" class="footer-logo">
          <img src="{{asset('images/landing/LOGO_PNG-23.png')}}" alt="{{__('frontend.app_name')}}" class="footer-logo-image">
        </a>
        <p class="footer-brand-desc">{{__('frontend.footer_description')}}</p>
        <div class="footer-social">
          @if(!empty($Setting->telegram))
            <a href="{{$Setting->telegram}}" class="footer-social-btn" aria-label="تيليغرام" target="_blank" rel="nofollow noopener">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0a12 12 0 00-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
            </a>
          @endif
          @if(!empty($Setting->snapchat))
            <a href="{{$Setting->snapchat}}" class="footer-social-btn" aria-label="سناب شات" target="_blank" rel="nofollow noopener">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12.065 1.645c3.374 0 6.315 2.486 6.315 6.527v.948c0 .236.048.35.271.413.564.158 1.17.29 1.732.43.36.088.578.285.578.606 0 .442-.322.71-.895.71-.13 0-.26-.014-.391-.028-.274-.028-.543-.073-.816-.073-.261 0-.408.043-.408.332 0 .13.014.264.028.397.043.438.1.877.1 1.32 0 1.974-1.27 3.594-3.05 4.36-.19.083-.254.185-.23.389.13 1.093.844 1.754 1.987 1.754.49 0 .976-.13 1.419-.32.145-.059.274-.101.4-.101.346 0 .633.268.633.62 0 .636-.847 1.113-1.854 1.113-.49 0-.985-.113-1.463-.296-.49-.185-.959-.426-1.463-.426-.534 0-1.052.287-1.532.635-1.013.726-2.13 1.084-3.274 1.084-1.168 0-2.284-.365-3.281-1.084-.48-.348-.998-.635-1.532-.635-.504 0-.972.241-1.463.426-.478.183-.972.296-1.463.296-1.007 0-1.854-.477-1.854-1.113 0-.352.287-.62.634-.62.126 0 .254.042.4.1.443.19.929.32 1.418.32 1.143 0 1.857-.66 1.988-1.754.024-.204-.04-.306-.23-.388-1.78-.766-3.05-2.387-3.05-4.36 0-.444.057-.883.1-1.32.014-.134.028-.268.028-.398 0-.289-.147-.332-.408-.332-.273 0-.542.045-.816.073-.13.014-.261.028-.391.028-.573 0-.895-.268-.895-.71 0-.321.218-.518.578-.607.562-.138 1.168-.27 1.732-.429.223-.062.271-.177.271-.413v-.948c0-4.041 2.94-6.527 6.315-6.527z"/></svg>
            </a>
          @endif
          @if(!empty($Setting->linkedin))
            <a href="{{$Setting->linkedin}}" class="footer-social-btn" aria-label="لينكد إن" target="_blank" rel="nofollow noopener">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
            </a>
          @endif
          @if(!empty($Setting->instagram))
            <a href="{{$Setting->instagram}}" class="footer-social-btn" aria-label="إنستغرام" target="_blank" rel="nofollow noopener">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
            </a>
          @endif
          @if(!empty($Setting->x))
            <a href="{{$Setting->x}}" class="footer-social-btn" aria-label="تويتر" target="_blank" rel="nofollow noopener">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.737-8.835L1.254 2.25H8.08l4.253 5.622L18.244 2.25zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77z"/></svg>
            </a>
          @endif
          @if(!empty($Setting->facebook))
            <a href="{{$Setting->facebook}}" class="footer-social-btn" aria-label="فيسبوك" target="_blank" rel="nofollow noopener">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </a>
          @endif
          @if(!empty($Setting->whatsapp))
            <a href="{{$Setting->whatsapp}}" class="footer-social-btn" aria-label="واتساب" target="_blank" rel="nofollow noopener">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            </a>
          @endif
        </div>
      </div>

      <!-- Links cols -->
      <div class="footer-col">
        <h4 class="footer-col-title">{{__('frontend.about_us')}}</h4>
        <ul class="footer-col-links">
          <li><a href="{{route('index')}}#home">{{__('frontend.homepage')}}</a></li>
          <li><a href="{{route('index')}}#features">{{__('frontend.features')}}</a></li>
          <li><a href="{{route('index')}}#about">{{__('frontend.about_us')}}</a></li>
          <li><a href="{{route('index')}}#pricing">{{__('frontend.pricing')}}</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4 class="footer-col-title">{{__('frontend.features')}}</h4>
        <ul class="footer-col-links">
          <li><a href="{{route('index')}}#features">{{__('frontend.feature_1')}}</a></li>
          <li><a href="{{route('index')}}#features">{{__('frontend.feature_2')}}</a></li>
          <li><a href="{{route('index')}}#features">{{__('frontend.feature_3')}}</a></li>
          <li><a href="{{route('index')}}#features">{{__('frontend.feature_4')}}</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4 class="footer-col-title">{{__('frontend.contact')}}</h4>
        <ul class="footer-col-links">
          @if(!empty($Setting->address_1))
            <li><span>{{__('frontend.location')}}: {{$Setting->address_1}}</span></li>
          @endif
          @if(!empty($Setting->email_1))
            <li><a href="mailto:{{$Setting->email_1}}">{{__('frontend.emails')}}: {{$Setting->email_1}}</a></li>
          @endif
          @if(!empty($Setting->phone_1))
            <li><a href="tel:{{$Setting->phone_1}}">{{__('frontend.phones')}}: {{$Setting->phone_1}}</a></li>
          @endif
        </ul>
      </div>

    </div>
  </div>

  <!-- Bottom bar -->
  <div class="footer-bottom">
    <div class="footer-bottom-inner">
      <p>{{__('frontend.all_rights_reserved')}} &copy; {{ now()->year }} <a href="{{route('index')}}" style="color:inherit;">{{__('frontend.app_name')}}</a></p>
      <div class="footer-bottom-links">
        <a href="{{route('policy', ['key' => 'privacy_policy'])}}">{{__('frontend.privacy_policy')}}</a>
        <a href="{{route('policy', ['key' => 'terms_and_conditions'])}}">{{__('frontend.terms_and_conditions')}}</a>
      </div>
    </div>
  </div>
</footer>
