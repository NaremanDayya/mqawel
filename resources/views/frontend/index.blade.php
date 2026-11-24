@extends('frontend.global')

@section('content')
    @php
        $Setting = App\Models\Setting::find(1);

        $Packages = App\Models\SubscriptionPackage::where(['is_active' => true])->orderBy('price', 'asc')->limit(10)->get();
        $packagesCount = $Packages->count();
        $popularIndex = $packagesCount >= 3 ? intdiv($packagesCount - 1, 2) : -1;
        $featureFlags = [
            'has_workers' => 'frontend.workers_management',
            'has_projects' => 'frontend.projects_management',
            'has_storages' => 'frontend.storages_management',
            'has_items' => 'frontend.items_management',
            'has_item_categories' => 'frontend.item_categories_management',
            'has_item_movements' => 'frontend.item_movements_management',
            'has_workers_report' => 'frontend.workers_report',
            'has_worker_expenses_report' => 'frontend.worker_expenses_report',
            'has_expired_files_report' => 'frontend.expired_documents_report',
            'has_project_expenses_report' => 'frontend.project_expenses_report',
        ];
    @endphp

    <!-- ====== Header Start ====== -->
    @include('frontend.includes.header')
    <!-- ====== Header End ====== -->

    <!-- ─────────────────── HERO ─────────────────── -->
    <section class="hero" id="home" aria-label="Hero section">
      <canvas id="hero-canvas" style="position:absolute;inset:0;width:100%;height:100%;"></canvas>
      <div class="hero-glow hero-glow-1"></div>
      <div class="hero-glow hero-glow-2"></div>

      <div class="hero-inner-wrap">

        <!-- Text Side -->
        <div class="hero-text-side" id="hero-text-side">

          <div class="hero-badge-new">
            <img src="{{asset('images/landing/whatsapp.png')}}" alt="{{ __('landing.alt_whatsapp') }}" class="hero-badge-icon">
            {{ __('landing.hero_badge') }}
          </div>

          <h1 class="hero-h1">
            {{ __('landing.hero_h1') }}
            <span class="hero-h1-grad">{{ __('landing.hero_h1_grad') }}</span>
          </h1>

          <p class="hero-para" id="hero-para">
            {{ __('landing.hero_sub') }}
          </p>

          <!-- Mobile mockup (after paragraph, mobile only) -->
          <div class="hero-mockup-mobile">
            <img src="{{asset('images/landing/mockup.png')}}" alt="{{ __('landing.alt_mockup') }}">
          </div>

          <div class="hero-btns-new">
            <a href="{{url('company/register')}}" class="hero-btn-primary">
              {{ __('landing.hero_cta') }}
            </a>
            <a href="#features" class="hero-btn-outline">{{ __('landing.hero_features_btn') }}</a>
          </div>

          <div class="hero-stats-new">
            <div class="hero-stat-item">
              <span class="hero-stat-val">{{ __('landing.stat1_val') }}</span>
              <span class="hero-stat-lbl">{{ __('landing.stat1_lbl') }}</span>
            </div>
            <div class="hero-stat-div"></div>
            <div class="hero-stat-item">
              <span class="hero-stat-val">{{ __('landing.stat2_val') }}</span>
              <span class="hero-stat-lbl">{{ __('landing.stat2_lbl') }}</span>
            </div>
            <div class="hero-stat-div"></div>
            <div class="hero-stat-item">
              <span class="hero-stat-val">50+</span>
              <span class="hero-stat-lbl">{{ __('landing.stat3_lbl') }}</span>
            </div>
          </div>
        </div>

        <!-- Image Side (desktop only) -->
        <div class="hero-img-side">
          <img src="{{asset('images/landing/mockup.png')}}" alt="{{ __('landing.alt_mockup') }}">
        </div>

      </div>
    </section>

    <!-- ─────────────────── SHOWCASE ─────────────────── -->
    <section class="showcase" id="about" aria-labelledby="showcase-heading">
      <div class="showcase-inner">

        <!-- Content -->
        <div class="showcase-content">
          <p class="showcase-eyebrow">{{ __('landing.showcase_eyebrow') }}</p>
          <h2 class="showcase-title" id="showcase-heading">
            {{ __('landing.showcase_title_a') }}<br><span>{{ __('landing.showcase_title_b') }}</span>
          </h2>
          <p class="showcase-desc">
            {{ __('landing.showcase_desc') }}
          </p>

          <ul class="showcase-list">
            <li>
              <div class="showcase-list-icon">
                <svg viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
              </div>
              <div class="showcase-list-text">
                <strong>{{ __('landing.feat1_t') }}</strong>
                <span>{{ __('landing.feat1_d') }}</span>
              </div>
            </li>
            <li>
              <div class="showcase-list-icon">
                <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
              </div>
              <div class="showcase-list-text">
                <strong>{{ __('landing.feat2_t') }}</strong>
                <span>{{ __('landing.feat2_d') }}</span>
              </div>
            </li>
            <li>
              <div class="showcase-list-icon">
                <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 001 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
              </div>
              <div class="showcase-list-text">
                <strong>{{ __('landing.feat3_t') }}</strong>
                <span>{{ __('landing.feat3_d') }}</span>
              </div>
            </li>
            <li>
              <div class="showcase-list-icon">
                <svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
              </div>
              <div class="showcase-list-text">
                <strong>{{ __('landing.feat4_t') }}</strong>
                <span>{{ __('landing.feat4_d') }}</span>
              </div>
            </li>
          </ul>

          <a href="#pricing" class="btn-hero" style="display:inline-flex;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            {{ __('landing.demo_title') }}
          </a>
        </div>

        <!-- Phone mockup -->
        <div class="showcase-phone-wrap">

          <!-- Floating badge left -->
          <div class="showcase-badge showcase-badge-left">
            <div class="sbadge-icon" style="background:#ECFDF5;">
              <svg viewBox="0 0 24 24" style="stroke:#059669"><path d="M20 6L9 17l-5-5"/></svg>
            </div>
            <div class="sbadge-text">
              <strong>{{ __('landing.demo_tasks') }}</strong>
              <span>{{ __('landing.demo_today') }}</span>
            </div>
          </div>

          <!-- Floating badge right -->
          <div class="showcase-badge showcase-badge-right">
            <div class="sbadge-icon" style="background:var(--purple-pale);">
              <svg viewBox="0 0 24 24" style="stroke:var(--purple)"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <div class="sbadge-text">
              <strong>{{ __('landing.demo_clients') }}</strong>
              <span>{{ __('landing.demo_week') }}</span>
            </div>
          </div>

          <!-- Phone -->
          <img src="{{asset('images/landing/phoneMo.png')}}" alt="{{ __('landing.alt_app') }}" class="phone-image">

        </div>

      </div>
    </section>

    <!-- ─────────────────── PRICING ─────────────────── -->
    <section class="pricing" id="pricing" aria-labelledby="pricing-heading">
      <div class="pricing-inner">

        <div class="pricing-header">
          <p class="pricing-eyebrow">{{__('frontend.packages')}}</p>
          <h2 class="pricing-title" id="pricing-heading">{{__('frontend.packages_plans')}}</h2>
          <p class="pricing-sub">{{__('frontend.pricing_headline')}}</p>

          <div class="pricing-toggle" role="tablist" aria-label="{{__('frontend.billing_cycle')}}">
            <button type="button" class="toggle-btn active" onclick="setMonthly(this)">{{__('frontend.billed_monthly')}}</button>
            <button type="button" class="toggle-btn" onclick="setYearly(this)">
              {{__('frontend.billed_yearly')}}
              <span class="toggle-save">{{__('frontend.two_months_free')}}</span>
            </button>
          </div>

          <p class="pricing-note">{{__('frontend.pricing_note')}}</p>
        </div>

        <div class="pricing-grid">
          @foreach($Packages as $index => $Package)
            <div class="pricing-card @if($index === $popularIndex) pricing-card--popular @endif">
              @if($index === $popularIndex)
                <div class="popular-badge">⭐ {{__('frontend.most_popular')}}</div>
              @endif

              <p class="pricing-plan">{{ $Package->title }}</p>
              @php
                $monthlyRate = $Package->period > 0 ? $Package->price / $Package->period : $Package->price;
                $monthlyDisplay = $monthlyRate == floor($monthlyRate) ? number_format($monthlyRate) : number_format($monthlyRate, 2);
                $yearlyPrice = $Package->yearlyPrice();
                $yearlyDisplay = $yearlyPrice == floor($yearlyPrice) ? number_format($yearlyPrice) : number_format($yearlyPrice, 2);
              @endphp
              @if ($Package->is_trial)
                <p class="pricing-trial" style="margin:0 0 6px;font-weight:700;color:#2f7a57;">🎁 {{ __('trial.free_for_days', ['days' => $Package->trial_days]) }}</p>
              @endif
              <div class="pricing-price">
                <span class="price-amount" data-monthly="{{ $monthlyDisplay }}" data-yearly="{{ $yearlyDisplay }}">{{ $monthlyDisplay }}</span>
                <span class="price-currency">{{ __('backend.omani_riyal_code') }}</span>
                <span class="price-period" data-monthly="/ {{ __('frontend.months_singular') }}" data-yearly="/ 12 {{ __('frontend.months') }}">/ {{ __('frontend.months_singular') }}</span>
              </div>
              <p class="price-term" data-monthly="{{ __('frontend.renews_every_30_days') }}" data-yearly="12 {{ __('frontend.months') }} — {{ __('frontend.paid_upfront') }}">{{ __('frontend.renews_every_30_days') }}</p>
              @if ($Package->is_trial)
                <p class="price-term trial-then" style="color:#2f7a57;font-weight:600;" data-monthly="{{ __('trial.then') }} {{ $monthlyDisplay }} {{ __('backend.omani_riyal_code') }} / {{ __('frontend.months_singular') }}" data-yearly="{{ __('trial.then') }} {{ $yearlyDisplay }} {{ __('backend.omani_riyal_code') }} / 12 {{ __('frontend.months') }}">{{ __('trial.then') }} {{ $monthlyDisplay }} {{ __('backend.omani_riyal_code') }} / {{ __('frontend.months_singular') }}</p>
              @endif

              @if(!empty($Package->description))
                <p class="pricing-desc">{{ $Package->description }}</p>
              @endif

              @if(!empty($Package->traditional_cost_items))
                @php
                  $costTotal = collect($Package->traditional_cost_items)->sum(fn($item) => (float) ($item['value'] ?? 0));
                  $savings = max(0, $costTotal - $monthlyRate);
                @endphp
                <div class="plan-value">
                  <p class="plan-value-title">{{ __('frontend.instead_of_traditional_management') }}</p>
                  @foreach($Package->traditional_cost_items as $costItem)
                    <div class="plan-value-row"><span>{{ $costItem['label'] }}</span><span>{{ number_format($costItem['value']) }} {{ __('backend.omani_riyal_code') }}</span></div>
                  @endforeach
                  <div class="plan-value-total"><span>{{ __('frontend.traditional_cost_total') }}</span><span>{{ number_format($costTotal) }} {{ __('backend.omani_riyal_code') }} / {{ __('frontend.months_singular') }}</span></div>
                  <p class="plan-value-save">
                    {{ __('frontend.you_save', ['amount' => number_format($savings), 'currency' => __('backend.omani_riyal_code')]) }}
                    @if(!empty($Package->savings_note))
                      — {{ $Package->savings_note }}
                    @endif
                  </p>
                </div>
              @endif

              <div class="pricing-divider"></div>
              <ul class="pricing-features">
                @php
                  $projectsLabel = empty($Package->max_projects)
                    ? __('frontend.unlimited_projects')
                    : ($Package->max_projects == 1 ? __('frontend.one_project') : __('frontend.up_to_projects', ['count' => $Package->max_projects]));
                  $workersLabel = empty($Package->max_workers)
                    ? __('frontend.unlimited_workers')
                    : ($Package->max_workers == 1 ? __('frontend.one_worker') : __('frontend.up_to_workers', ['count' => $Package->max_workers]));
                @endphp
                <li>
                  <div class="feat-check"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></div>
                  {{ $projectsLabel }}
                </li>
                <li>
                  <div class="feat-check"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></div>
                  {{ $workersLabel }}
                </li>
                @if(!empty($Package->feature_bullets))
                  @foreach($Package->feature_bullets as $bullet)
                    <li>
                      <div class="feat-check"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></div>
                      {{ $bullet['text'] }}
                    </li>
                  @endforeach
                @else
                  @foreach($featureFlags as $flag => $labelKey)
                    <li @if(!$Package->{$flag}) class="feat-disabled" @endif>
                      <div class="feat-check @if(!$Package->{$flag}) feat-x @endif">
                        @if($Package->{$flag})
                          <svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                        @else
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                        @endif
                      </div>
                      {{ __($labelKey) }}
                    </li>
                  @endforeach
                @endif
              </ul>

              <a href="{{url('company/register')}}" class="btn-pricing @if($index === $popularIndex) btn-pricing--white @else btn-pricing--outline @endif">
                {{ !empty($Package->cta_label) ? $Package->cta_label : __('frontend.purchase_now') }}
              </a>
            </div>
          @endforeach
        </div>
      </div>
    </section>

    <!-- ─────────────────── HOW IT WORKS ─────────────────── -->
    <section class="hiw" id="how" aria-labelledby="hiw-heading">
      <div class="hiw-inner">

        <div class="hiw-header">
          <p class="hiw-eyebrow">{{ __('landing.hiw_eyebrow') }}</p>
          <h2 class="hiw-title" id="hiw-heading">{{ __('landing.hiw_title') }}</h2>
          <p class="hiw-desc">{{ __('landing.hiw_desc') }}</p>
        </div>

        <div class="hiw-steps">

          <!-- connector line -->
          <div class="hiw-line" aria-hidden="true"></div>

          <div class="hiw-step">
            <div class="hiw-circle">1</div>
            <h3 class="hiw-step-title">{{ __('landing.hiw1_t') }}</h3>
            <p class="hiw-step-desc">{{ __('landing.hiw1_d') }}</p>
          </div>

          <div class="hiw-step">
            <div class="hiw-circle">2</div>
            <h3 class="hiw-step-title">{{ __('landing.hiw2_t') }}</h3>
            <p class="hiw-step-desc">{{ __('landing.hiw2_d') }}</p>
          </div>

          <div class="hiw-step">
            <div class="hiw-circle">3</div>
            <h3 class="hiw-step-title">{{ __('landing.hiw3_t') }}</h3>
            <p class="hiw-step-desc">{{ __('landing.hiw3_d') }}</p>
          </div>

        </div>

      </div>
    </section>

    <!-- ─────────────────── WHY US ─────────────────── -->
    <section class="why" id="features" aria-labelledby="why-heading">
      <div class="why-inner">

        <div class="why-header">
          <div class="why-eyebrow">
            <div class="why-eyebrow-line"></div>
            {{ __('landing.why_eyebrow') }}
            <div class="why-eyebrow-line"></div>
          </div>
          <h2 class="why-title" id="why-heading">{{ __('landing.why_title_a') }} <span>{{ __('landing.why_title_b') }}</span></h2>
          <p class="why-desc">{{ __('landing.why_desc') }}</p>
        </div>

        <div class="why-grid">

          <div class="why-item">
            <div class="why-item-top">
              <div class="why-icon">
                <img src="{{asset('images/landing/shuttle.png')}}" alt="">
              </div>
            </div>
            <h3 class="why-item-title">{{ __('landing.why1_t') }}</h3>
            <p class="why-item-desc">{{ __('landing.why1_d') }}</p>
          </div>

          <div class="why-item">
            <div class="why-item-top">
              <div class="why-icon">
                <img src="{{asset('images/landing/customer-service-headset.png')}}" alt="">
              </div>
            </div>
            <h3 class="why-item-title">{{ __('landing.why2_t') }}</h3>
            <p class="why-item-desc">{{ __('landing.why2_d') }}</p>
          </div>

          <div class="why-item">
            <div class="why-item-top">
              <div class="why-icon">
                <img src="{{asset('images/landing/insurance.png')}}" alt="">
              </div>
            </div>
            <h3 class="why-item-title">{{ __('landing.why3_t') }}</h3>
            <p class="why-item-desc">{{ __('landing.why3_d') }}</p>
          </div>

          <div class="why-item">
            <div class="why-item-top">
              <div class="why-icon">
                <img src="{{asset('images/landing/lightning.png')}}" alt="">
              </div>
            </div>
            <h3 class="why-item-title">{{ __('landing.why4_t') }}</h3>
            <p class="why-item-desc">{{ __('landing.why4_d') }}</p>
          </div>

          <div class="why-item">
            <div class="why-item-top">
              <div class="why-icon">
                <img src="{{asset('images/landing/0-percent.png')}}" alt="">
              </div>
            </div>
            <h3 class="why-item-title">{{ __('landing.why5_t') }}</h3>
            <p class="why-item-desc">{{ __('landing.why5_d') }}</p>
          </div>

          <div class="why-item">
            <div class="why-item-top">
              <div class="why-icon">
                <img src="{{asset('images/landing/pointer.png')}}" alt="">
              </div>
            </div>
            <h3 class="why-item-title">{{ __('landing.why6_t') }}</h3>
            <p class="why-item-desc">{{ __('landing.why6_d') }}</p>
          </div>

        </div>

      </div>
    </section>

    <!-- ─────────────────── PAIN POINTS ─────────────────── -->
    <section class="pain" id="solutions" aria-labelledby="pain-heading">
      <div class="section-header">
        <p class="section-eyebrow">
          <span class="section-eyebrow-dot" aria-hidden="true"></span>
          {{ __('landing.pain_eyebrow') }}
        </p>
        <h2 class="section-title" id="pain-heading">
          {{ __('landing.pain_title_a') }} <span class="highlight">{{ __('landing.pain_title_b') }}</span>
        </h2>
        <p class="section-desc">{{ __('landing.pain_desc') }}</p>
      </div>

      <div class="pain-grid">

        <div class="pain-card">
          <div class="pain-icon-wrap" aria-hidden="true">
            <img src="{{asset('images/landing/bill.png')}}" alt="">
          </div>
          <div class="pain-card-body">
            <h3 class="pain-title">{{ __('landing.pain1_t') }}</h3>
            <p class="pain-desc">{{ __('landing.pain1_d') }}</p>
          </div>
        </div>

        <div class="pain-card">
          <div class="pain-icon-wrap" aria-hidden="true">
            <img src="{{asset('images/landing/chat.png')}}" alt="">
          </div>
          <div class="pain-card-body">
            <h3 class="pain-title">{{ __('landing.pain2_t') }}</h3>
            <p class="pain-desc">{{ __('landing.pain2_d') }}</p>
          </div>
        </div>

        <div class="pain-card">
          <div class="pain-icon-wrap" aria-hidden="true">
            <img src="{{asset('images/landing/warning.png')}}" alt="">
          </div>
          <div class="pain-card-body">
            <h3 class="pain-title">{{ __('landing.pain3_t') }}</h3>
            <p class="pain-desc">{{ __('landing.pain3_d') }}</p>
          </div>
        </div>

        <div class="pain-card">
          <div class="pain-icon-wrap" aria-hidden="true">
            <img src="{{asset('images/landing/multiple.png')}}" alt="">
          </div>
          <div class="pain-card-body">
            <h3 class="pain-title">{{ __('landing.pain4_t') }}</h3>
            <p class="pain-desc">{{ __('landing.pain4_d') }}</p>
          </div>
        </div>

      </div>

    </section>

    <!-- ─────────────────── CTA ─────────────────── -->
    <section class="cta-section" aria-label="Call to action">
      <div class="cta-inner">

        <!-- Decorative blobs -->
        <div class="cta-blob cta-blob-1" aria-hidden="true"></div>
        <div class="cta-blob cta-blob-2" aria-hidden="true"></div>

        <div class="cta-content">
          <div class="cta-badge">
            <span class="cta-badge-dot"></span>
            {{ __('landing.cta_eyebrow') }}
          </div>

          <h2 class="cta-title">
            {{ __('landing.cta_title_a') }}<br>
            <span>{{ __('landing.cta_title_b') }}</span>
          </h2>

          <p class="cta-desc">
            {{ __('landing.cta_desc') }}
          </p>

          <div class="cta-notice">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="var(--purple-light)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <strong>{{ __('landing.cta_note_a') }}</strong>{{ __('landing.cta_note_b') }}
          </div>

          <div class="cta-btns">
            <a href="{{url('company/register')}}" class="btn-cta-primary">
              {{ __('landing.cta_register') }}
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
            <a href="{{ !empty($Setting->whatsapp) ? $Setting->whatsapp : (!empty($Setting->email_1) ? 'mailto:'.$Setting->email_1 : '#faq') }}" class="btn-cta-ghost" @if(!empty($Setting->whatsapp)) target="_blank" rel="nofollow noopener" @endif>
              <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.8a19.79 19.79 0 01-3.07-8.67A2 2 0 012 1h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.09 8.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
              {{ __('landing.cta_contact') }}
            </a>
          </div>
        </div>

      </div>
    </section>

    <!-- ─────────────────── REVIEWS ─────────────────── -->
    <section class="reviews" aria-labelledby="reviews-heading">
      <div class="reviews-header">
        <p class="reviews-eyebrow">{{ __('landing.reviews_eyebrow') }}</p>
        <h2 class="reviews-title" id="reviews-heading">{{ __('landing.reviews_title') }}</h2>
      </div>
      <div class="reviews-track-wrap">
        <div class="reviews-track" id="reviewsTrack">
          <div class="review-card">
            <div class="review-stars">★★★★★</div>
            <p class="review-text">"{{ __('landing.rev1_text') }}"</p>
            <div class="review-author">
              <div class="review-av" style="background:#6C5CE7;">{{ __('landing.rev1_av') }}</div>
              <div><strong>{{ __('landing.rev1_name') }}</strong><span>{{ __('landing.rev1_role') }}</span></div>
            </div>
          </div>
          <div class="review-card">
            <div class="review-stars">★★★★★</div>
            <p class="review-text">"{{ __('landing.rev2_text') }}"</p>
            <div class="review-author">
              <div class="review-av" style="background:#00B894;">{{ __('landing.rev2_av') }}</div>
              <div><strong>{{ __('landing.rev2_name') }}</strong><span>{{ __('landing.rev2_role') }}</span></div>
            </div>
          </div>
          <div class="review-card">
            <div class="review-stars">★★★★★</div>
            <p class="review-text">"{{ __('landing.rev3_text') }}"</p>
            <div class="review-author">
              <div class="review-av" style="background:#A29BFE;">{{ __('landing.rev3_av') }}</div>
              <div><strong>{{ __('landing.rev3_name') }}</strong><span>{{ __('landing.rev3_role') }}</span></div>
            </div>
          </div>
          <div class="review-card">
            <div class="review-stars">★★★★★</div>
            <p class="review-text">"{{ __('landing.rev4_text') }}"</p>
            <div class="review-author">
              <div class="review-av" style="background:#FDCB6E;color:#333;">{{ __('landing.rev4_av') }}</div>
              <div><strong>{{ __('landing.rev4_name') }}</strong><span>{{ __('landing.rev4_role') }}</span></div>
            </div>
          </div>
          <div class="review-card">
            <div class="review-stars">★★★★★</div>
            <p class="review-text">"{{ __('landing.rev5_text') }}"</p>
            <div class="review-author">
              <div class="review-av" style="background:#E17055;">{{ __('landing.rev5_av') }}</div>
              <div><strong>{{ __('landing.rev5_name') }}</strong><span>{{ __('landing.rev5_role') }}</span></div>
            </div>
          </div>
        </div>
        <button class="reviews-arrow reviews-arrow--prev" onclick="scrollReviews(-1)" aria-label="{{ __('landing.arrow_prev') }}">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
        </button>
        <button class="reviews-arrow reviews-arrow--next" onclick="scrollReviews(1)" aria-label="{{ __('landing.arrow_next') }}">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
        </button>
      </div>
    </section>

    <!-- ─────────────────── FAQ ─────────────────── -->
    <section class="faq" id="faq" aria-labelledby="faq-heading">
      <div class="faq-inner">
        <div class="faq-header">
          <p class="faq-eyebrow">{{ __('landing.faq_eyebrow') }}</p>
          <h2 class="faq-title" id="faq-heading">{{ __('landing.faq_title_a') }}<br>{{ __('landing.faq_title_b') }}</h2>
        </div>

        <div class="faq-list">

          <div class="faq-item active" onclick="toggleFaq(this)">
            <div class="faq-item-head">
              <span class="faq-item-q">{{ __('landing.faq1_q') }}</span>
              <button class="faq-toggle" aria-label="{{ __('landing.faq_close') }}">
                <svg class="icon-close" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                <svg class="icon-open" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
              </button>
            </div>
            <div class="faq-item-body">
              <p>{{ __('landing.faq1_a') }}</p>
            </div>
          </div>

          <div class="faq-item" onclick="toggleFaq(this)">
            <div class="faq-item-head">
              <span class="faq-item-q">{{ __('landing.faq2_q') }}</span>
              <button class="faq-toggle" aria-label="{{ __('landing.faq_open') }}">
                <svg class="icon-close" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                <svg class="icon-open" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
              </button>
            </div>
            <div class="faq-item-body">
              <p>{{ __('landing.faq2_a') }}</p>
            </div>
          </div>

          <div class="faq-item" onclick="toggleFaq(this)">
            <div class="faq-item-head">
              <span class="faq-item-q">{{ __('landing.faq3_q') }}</span>
              <button class="faq-toggle" aria-label="{{ __('landing.faq_open') }}">
                <svg class="icon-close" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                <svg class="icon-open" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
              </button>
            </div>
            <div class="faq-item-body">
              <p>{{ __('landing.faq3_a') }}</p>
            </div>
          </div>

          <div class="faq-item" onclick="toggleFaq(this)">
            <div class="faq-item-head">
              <span class="faq-item-q">{{ __('landing.faq4_q') }}</span>
              <button class="faq-toggle" aria-label="{{ __('landing.faq_open') }}">
                <svg class="icon-close" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                <svg class="icon-open" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
              </button>
            </div>
            <div class="faq-item-body">
              <p>{{ __('landing.faq4_a') }}</p>
            </div>
          </div>

          <div class="faq-item" onclick="toggleFaq(this)">
            <div class="faq-item-head">
              <span class="faq-item-q">{{ __('landing.faq5_q') }}</span>
              <button class="faq-toggle" aria-label="{{ __('landing.faq_open') }}">
                <svg class="icon-close" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                <svg class="icon-open" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
              </button>
            </div>
            <div class="faq-item-body">
              <p>{{ __('landing.faq5_a') }}</p>
            </div>
          </div>

          <div class="faq-item" onclick="toggleFaq(this)">
            <div class="faq-item-head">
              <span class="faq-item-q">{{ __('landing.faq6_q') }}</span>
              <button class="faq-toggle" aria-label="{{ __('landing.faq_open') }}">
                <svg class="icon-close" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                <svg class="icon-open" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
              </button>
            </div>
            <div class="faq-item-body">
              <p>{{ __('landing.faq6_a') }}</p>
            </div>
          </div>

          <div class="faq-item" onclick="toggleFaq(this)">
            <div class="faq-item-head">
              <span class="faq-item-q">{{ __('landing.faq7_q') }}</span>
              <button class="faq-toggle" aria-label="{{ __('landing.faq_open') }}">
                <svg class="icon-close" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                <svg class="icon-open" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
              </button>
            </div>
            <div class="faq-item-body">
              <p>{{ __('landing.faq7_a') }}</p>
            </div>
          </div>

        </div>
      </div>
    </section>
@endsection
