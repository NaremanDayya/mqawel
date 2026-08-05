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
            <img src="{{asset('images/landing/whatsapp.png')}}" alt="واتساب" class="hero-badge-icon">
            ابدأ أعمالك برسالة واتساب، ودع الذكاء الاصطناعي يتولى التنفيذ
          </div>

          <h1 class="hero-h1">
            خّل الفورمن يشتغل
            <span class="hero-h1-grad">وأنت تشوف كل شيء</span>
          </h1>

          <p class="hero-para" id="hero-para">
            تكاليف مشاريعك، حضور عمالك، ومخزونك كلها في هاتفك لحظياً — بدون واتساب وبدون فوضى.
          </p>

          <!-- Mobile mockup (after paragraph, mobile only) -->
          <div class="hero-mockup-mobile">
            <img src="{{asset('images/landing/mockup.png')}}" alt="مقاول بلس">
          </div>

          <div class="hero-btns-new">
            <a href="{{url('company/register')}}" class="hero-btn-primary">
              جرّب مجاناً 14 يوم — بدون بطاقة بنكية
            </a>
            <a href="#features" class="hero-btn-outline">اكتشف المميزات</a>
          </div>

          <div class="hero-stats-new">
            <div class="hero-stat-item">
              <span class="hero-stat-val">0 ر.ع</span>
              <span class="hero-stat-lbl">تبدأ بدون بطاقة بنكية</span>
            </div>
            <div class="hero-stat-div"></div>
            <div class="hero-stat-item">
              <span class="hero-stat-val">14 يوماً</span>
              <span class="hero-stat-lbl">تجربة مجانية بكل المميزات</span>
            </div>
            <div class="hero-stat-div"></div>
            <div class="hero-stat-item">
              <span class="hero-stat-val">50+</span>
              <span class="hero-stat-lbl">مقاول يستخدم النظام اليوم</span>
            </div>
          </div>
        </div>

        <!-- Image Side (desktop only) -->
        <div class="hero-img-side">
          <img src="{{asset('images/landing/mockup.png')}}" alt="مقاول بلس">
        </div>

      </div>
    </section>

    <!-- ─────────────────── SHOWCASE ─────────────────── -->
    <section class="showcase" id="about" aria-labelledby="showcase-heading">
      <div class="showcase-inner">

        <!-- Content -->
        <div class="showcase-content">
          <p class="showcase-eyebrow">نبذة عن النظام</p>
          <h2 class="showcase-title" id="showcase-heading">
            نظام واحد يجمع<br><span>مشاريعك وعمالك ومخزونك</span>
          </h2>
          <p class="showcase-desc">
            مقاول+ منصة عربية لإدارة شركات المقاولات: تفتحها من هاتفك وترى وضع كل مشروع خلال ثوانٍ — بدون تعقيد وبدون تدريب.
          </p>

          <ul class="showcase-list">
            <li>
              <div class="showcase-list-icon">
                <svg viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
              </div>
              <div class="showcase-list-text">
                <strong>إدارة المشاريع</strong>
                <span>تتبع كل مشروع من البداية للتسليم مع تقارير يومية واضحة</span>
              </div>
            </li>
            <li>
              <div class="showcase-list-icon">
                <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
              </div>
              <div class="showcase-list-text">
                <strong>إدارة العمالة</strong>
                <span>حضور وغياب وإنتاجية كل عامل في الموقع — كل شيء موثق</span>
              </div>
            </li>
            <li>
              <div class="showcase-list-icon">
                <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 001 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
              </div>
              <div class="showcase-list-text">
                <strong>مراقبة المخزون</strong>
                <span>تنبيهات تلقائية عند انخفاض المواد قبل ما تتوقف الشغل</span>
              </div>
            </li>
            <li>
              <div class="showcase-list-icon">
                <svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
              </div>
              <div class="showcase-list-text">
                <strong>التقارير المالية</strong>
                <span>اعرف أرباح كل مشروع بدقة وفي الوقت الفعلي — مش بعد ما يخلص</span>
              </div>
            </li>
          </ul>

          <a href="#pricing" class="btn-hero" style="display:inline-flex;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            شاهد كيف يعمل
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
              <strong>6 مهام مكتملة</strong>
              <span>اليوم</span>
            </div>
          </div>

          <!-- Floating badge right -->
          <div class="showcase-badge showcase-badge-right">
            <div class="sbadge-icon" style="background:var(--purple-pale);">
              <svg viewBox="0 0 24 24" style="stroke:var(--purple)"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <div class="sbadge-text">
              <strong>+12 عميل جديد</strong>
              <span>هذا الأسبوع</span>
            </div>
          </div>

          <!-- Phone -->
          <img src="{{asset('images/landing/phoneMo.png')}}" alt="تطبيق مقاول بلس" class="phone-image">

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
              @endphp
              <div class="pricing-price">
                <span class="price-amount" data-monthly="{{ $monthlyDisplay }}" data-yearly="{{ number_format($Package->price) }}">{{ $monthlyDisplay }}</span>
                <span class="price-currency">{{ __('backend.omani_riyal_code') }}</span>
                <span class="price-period" data-monthly="/ {{ __('frontend.months_singular') }}" data-yearly="/ {{ $Package->period }} {{ __('frontend.months') }}">/ {{ __('frontend.months_singular') }}</span>
              </div>
              <p class="price-term" data-monthly="{{ __('frontend.renews_every_30_days') }}" data-yearly="{{ $Package->period }} {{ __('frontend.months') }} — {{ __('frontend.paid_upfront') }}">{{ __('frontend.renews_every_30_days') }}</p>

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
          <p class="hiw-eyebrow">كيف يعمل</p>
          <h2 class="hiw-title" id="hiw-heading">ابدأ في 3 خطوات بسيطة</h2>
          <p class="hiw-desc">بدون تدريب معقّد وبدون إعداد طويل — تبدأ الاستفادة من اليوم الأول.</p>
        </div>

        <div class="hiw-steps">

          <!-- connector line -->
          <div class="hiw-line" aria-hidden="true"></div>

          <div class="hiw-step">
            <div class="hiw-circle">1</div>
            <h3 class="hiw-step-title">سجّل شركتك</h3>
            <p class="hiw-step-desc">أنشئ حسابك في أقل من دقيقتين — بدون بطاقة بنكية، وأدخل بيانات شركتك الأساسية.</p>
          </div>

          <div class="hiw-step">
            <div class="hiw-circle">2</div>
            <h3 class="hiw-step-title">أضف مشاريعك وفريقك</h3>
            <p class="hiw-step-desc">سجّل مشاريعك الحالية وأضف العمال والمخزون — النظام يرشدك خطوة بخطوة.</p>
          </div>

          <div class="hiw-step">
            <div class="hiw-circle">3</div>
            <h3 class="hiw-step-title">تابع وتحكم من أي مكان</h3>
            <p class="hiw-step-desc">ابدأ المتابعة الفورية، واحصل على تقارير التشغيلية والمالية في أي وقت تريد.</p>
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
            بعض الأسباب
            <div class="why-eyebrow-line"></div>
          </div>
          <h2 class="why-title" id="why-heading">لماذا <span>نظامنا؟</span></h2>
          <p class="why-desc">صمّمنا النظام بناءً على مشاكل حقيقية سمعناها من مقاولين على أرض الواقع — كل ميزة هنا تحلّ مشكلة.</p>
        </div>

        <div class="why-grid">

          <div class="why-item">
            <div class="why-item-top">
              <div class="why-icon">
                <img src="{{asset('images/landing/shuttle.png')}}" alt="">
              </div>
            </div>
            <h3 class="why-item-title">سرعة استجابة فورية</h3>
            <p class="why-item-desc">النظام يستجيب خلال أقل من 0.3 ثانية. لا انتظار، لا تحميل، لا إضاعة وقت الفريق.</p>
          </div>

          <div class="why-item">
            <div class="why-item-top">
              <div class="why-icon">
                <img src="{{asset('images/landing/customer-service-headset.png')}}" alt="">
              </div>
            </div>
            <h3 class="why-item-title">دعم بشري 24/7</h3>
            <p class="why-item-desc">ليس بوتاً — إنسان حقيقي يرد خلال دقيقتين في أي وقت من الليل أو النهار.</p>
          </div>

          <div class="why-item">
            <div class="why-item-top">
              <div class="why-icon">
                <img src="{{asset('images/landing/insurance.png')}}" alt="">
              </div>
            </div>
            <h3 class="why-item-title">أمان بمعيار بنكي</h3>
            <p class="why-item-desc">تشفير AES-256 لكل بياناتك. ضمان استرداد البيانات خلال 30 يوماً بدون قيود.</p>
          </div>

          <div class="why-item">
            <div class="why-item-top">
              <div class="why-icon">
                <img src="{{asset('images/landing/lightning.png')}}" alt="">
              </div>
            </div>
            <h3 class="why-item-title">تطوير مستمر وأجايل</h3>
            <p class="why-item-desc">نُصدر تحديثات أسبوعية بناءً على طلبات المستخدمين. اقتراحك اليوم يصبح ميزة الأسبوع القادم.</p>
          </div>

          <div class="why-item">
            <div class="why-item-top">
              <div class="why-icon">
                <img src="{{asset('images/landing/0-percent.png')}}" alt="">
              </div>
            </div>
            <h3 class="why-item-title">تجربة مجانية كاملة</h3>
            <p class="why-item-desc">14 يوماً مجاناً بكامل الميزات، بدون بطاقة ائتمان. جرّب وقرّر بعدين.</p>
          </div>

          <div class="why-item">
            <div class="why-item-top">
              <div class="why-icon">
                <img src="{{asset('images/landing/pointer.png')}}" alt="">
              </div>
            </div>
            <h3 class="why-item-title">سهولة استخدام عالية</h3>
            <p class="why-item-desc">واجهة بسيطة يتعلمها أي موظف خلال دقائق وبدون تدريب — مصمّمة للإنسان أولاً.</p>
          </div>

        </div>

      </div>
    </section>

    <!-- ─────────────────── PAIN POINTS ─────────────────── -->
    <section class="pain" id="solutions" aria-labelledby="pain-heading">
      <div class="section-header">
        <p class="section-eyebrow">
          <span class="section-eyebrow-dot" aria-hidden="true"></span>
          الحلول
        </p>
        <h2 class="section-title" id="pain-heading">
          مشاكل يومية <span class="highlight">يحلّها مقاول+</span>
        </h2>
        <p class="section-desc">هذه أكثر المشاكل تكراراً في شركات المقاولات — وهذه هي النقاط التي بُني النظام لمعالجتها.</p>
      </div>

      <div class="pain-grid">

        <div class="pain-card">
          <div class="pain-icon-wrap" aria-hidden="true">
            <img src="{{asset('images/landing/bill.png')}}" alt="">
          </div>
          <div class="pain-card-body">
            <h3 class="pain-title">ما تعرف وين راحت الفلوس</h3>
            <p class="pain-desc">مصاريف تخرج يومياً بدون تفاصيل واضحة، وما تعرف وين الخلل إلا بعد فوات الأوان.</p>
          </div>
        </div>

        <div class="pain-card">
          <div class="pain-icon-wrap" aria-hidden="true">
            <img src="{{asset('images/landing/chat.png')}}" alt="">
          </div>
          <div class="pain-card-body">
            <h3 class="pain-title">بياناتك موزّعة بين واتساب وإكسل</h3>
            <p class="pain-desc">أرقامك مبعثرة بين المجموعات والملفات — ما تقدر تعتمد على أي رقم منها.</p>
          </div>
        </div>

        <div class="pain-card">
          <div class="pain-icon-wrap" aria-hidden="true">
            <img src="{{asset('images/landing/warning.png')}}" alt="">
          </div>
          <div class="pain-card-body">
            <h3 class="pain-title">مشاريع تتوقف وما تعرف ليش</h3>
            <p class="pain-desc">تكتشف خسارة المشروع بعد تسليمه — وما بقي وقت للتصحيح.</p>
          </div>
        </div>

        <div class="pain-card">
          <div class="pain-icon-wrap" aria-hidden="true">
            <img src="{{asset('images/landing/multiple.png')}}" alt="">
          </div>
          <div class="pain-card-body">
            <h3 class="pain-title">ما تعرف شو يسوي الفريق</h3>
            <p class="pain-desc">الفورمن يقول إن كل شيء تمام، وما عندك طريقة تتأكد بنفسك.</p>
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
            ابدأ اليوم
          </div>

          <h2 class="cta-title">
            شركتك تستحق نظاماً<br>
            <span>يواكب طموحاتك</span>
          </h2>

          <p class="cta-desc">
            ابدأ تجربتك المجانية الآن — فريقنا يساعدك في الإعداد خلال 24 ساعة. بدون عقود. بدون التزامات.
          </p>

          <div class="cta-notice">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="var(--purple-light)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <strong>14 يوماً مجاناً</strong> بكل المميزات — بدون بطاقة بنكية، وتلغي متى ما أردت.
          </div>

          <div class="cta-btns">
            <a href="{{url('company/register')}}" class="btn-cta-primary">
              سجّل مجاناً الآن
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
            <a href="{{ !empty($Setting->whatsapp) ? $Setting->whatsapp : (!empty($Setting->email_1) ? 'mailto:'.$Setting->email_1 : '#faq') }}" class="btn-cta-ghost" @if(!empty($Setting->whatsapp)) target="_blank" rel="nofollow noopener" @endif>
              <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.8a19.79 19.79 0 01-3.07-8.67A2 2 0 012 1h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.09 8.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
              تواصل مع الفريق
            </a>
          </div>
        </div>

      </div>
    </section>

    <!-- ─────────────────── REVIEWS ─────────────────── -->
    <section class="reviews" aria-labelledby="reviews-heading">
      <div class="reviews-header">
        <p class="reviews-eyebrow">آراء المقاولين</p>
        <h2 class="reviews-title" id="reviews-heading">ماذا يقول المقاولون؟</h2>
      </div>
      <div class="reviews-track-wrap">
        <div class="reviews-track" id="reviewsTrack">
          <div class="review-card">
            <div class="review-stars">★★★★★</div>
            <p class="review-text">"قبل مقاول+ كنت أعرف وضع مشاريعي بعد فوات الأوان. الآن أفتح هاتفي وأشوف كل شيء في ثوانٍ."</p>
            <div class="review-author">
              <div class="review-av" style="background:#6C5CE7;">أ</div>
              <div><strong>أبو خالد</strong><span>مقاول في مسقط — يدير 4 مشاريع</span></div>
            </div>
          </div>
          <div class="review-card">
            <div class="review-stars">★★★★★</div>
            <p class="review-text">"وفّر علّي ساعتين كل يوم كنت أضيعهم في واتساب والإكسل. الفورمن الآن يدخل البيانات مباشرة وأنا أشوف كل شيء."</p>
            <div class="review-author">
              <div class="review-av" style="background:#00B894;">أ</div>
              <div><strong>أبو سالم</strong><span>شركة مقاولات بمسقط — 30 عامل</span></div>
            </div>
          </div>
          <div class="review-card">
            <div class="review-stars">★★★★★</div>
            <p class="review-text">"المخزون كان دايم مشكلة — المواد تنتهي وما أعرف إلا لما يتوقف الشغل. الآن آخذ تنبيه قبل يومين."</p>
            <div class="review-author">
              <div class="review-av" style="background:#A29BFE;">م</div>
              <div><strong>محمد الشحي</strong><span>مقاول مباني — مسقط</span></div>
            </div>
          </div>
          <div class="review-card">
            <div class="review-stars">★★★★★</div>
            <p class="review-text">"أول ما شغّلت النظام اكتشفت إن في مشروع كان خاسر من الأول وما أعرف. الآن ما أبدأ مشروع بدون مقاول+."</p>
            <div class="review-author">
              <div class="review-av" style="background:#FDCB6E;color:#333;">ع</div>
              <div><strong>عبدالله الرواحي</strong><span>شركة مقاولات صغيرة — صحار</span></div>
            </div>
          </div>
          <div class="review-card">
            <div class="review-stars">★★★★★</div>
            <p class="review-text">"الفورمن معاه هاتف وبيدخل الحضور كل يوم. أنا بالمكتب وأشوف كل شيء. هذا اللي كنت أبغاه من زمان."</p>
            <div class="review-author">
              <div class="review-av" style="background:#E17055;">خ</div>
              <div><strong>خالد البلوشي</strong><span>مقاول عام — مسقط الكبرى</span></div>
            </div>
          </div>
        </div>
        <button class="reviews-arrow reviews-arrow--prev" onclick="scrollReviews(-1)" aria-label="السابق">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
        </button>
        <button class="reviews-arrow reviews-arrow--next" onclick="scrollReviews(1)" aria-label="التالي">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
        </button>
      </div>
    </section>

    <!-- ─────────────────── FAQ ─────────────────── -->
    <section class="faq" id="faq" aria-labelledby="faq-heading">
      <div class="faq-inner">
        <div class="faq-header">
          <p class="faq-eyebrow">أسئلة شائعة</p>
          <h2 class="faq-title" id="faq-heading">نجيب على<br>أسئلتك</h2>
        </div>

        <div class="faq-list">

          <div class="faq-item active" onclick="toggleFaq(this)">
            <div class="faq-item-head">
              <span class="faq-item-q">هل بياناتي آمنة في السحابة؟</span>
              <button class="faq-toggle" aria-label="إغلاق">
                <svg class="icon-close" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                <svg class="icon-open" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
              </button>
            </div>
            <div class="faq-item-body">
              <p>نعم. نستخدم تشفيراً كاملاً AES-256 وخوادم موثوقة. بياناتك لك فقط ولن نشاركها مع أي جهة مهما كانت الظروف.</p>
            </div>
          </div>

          <div class="faq-item" onclick="toggleFaq(this)">
            <div class="faq-item-head">
              <span class="faq-item-q">هل يحتاج فريقي تدريباً طويلاً؟</span>
              <button class="faq-toggle" aria-label="فتح">
                <svg class="icon-close" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                <svg class="icon-open" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
              </button>
            </div>
            <div class="faq-item-body">
              <p>لا. الواجهة بسيطة ويفهمها أي موظف خلال أقل من ساعة، ونوفّر جلسة تعريفية مجانية عند الحاجة — والتدريب الكامل للفريق مشمول في الباقة الاحترافية.</p>
            </div>
          </div>

          <div class="faq-item" onclick="toggleFaq(this)">
            <div class="faq-item-head">
              <span class="faq-item-q">ماذا لو أردت إلغاء الاشتراك؟</span>
              <button class="faq-toggle" aria-label="فتح">
                <svg class="icon-close" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                <svg class="icon-open" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
              </button>
            </div>
            <div class="faq-item-body">
              <p>لا عقود طويلة، لا غرامات. يمكنك الإلغاء في أي وقت وتحميل كل بياناتك قبل المغادرة بضغطة واحدة.</p>
            </div>
          </div>

          <div class="faq-item" onclick="toggleFaq(this)">
            <div class="faq-item-head">
              <span class="faq-item-q">كم مدة كل اشتراك؟</span>
              <button class="faq-toggle" aria-label="فتح">
                <svg class="icon-close" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                <svg class="icon-open" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
              </button>
            </div>
            <div class="faq-item-body">
              <p>مدة كل اشتراك موضّحة في بطاقة الباقة نفسها ضمن قسم الأسعار أعلاه، وتُجدَّد تلقائياً في نهاية المدة.</p>
            </div>
          </div>

          <div class="faq-item" onclick="toggleFaq(this)">
            <div class="faq-item-head">
              <span class="faq-item-q">هل يناسب شركتي الصغيرة؟</span>
              <button class="faq-toggle" aria-label="فتح">
                <svg class="icon-close" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                <svg class="icon-open" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
              </button>
            </div>
            <div class="faq-item-body">
              <p>بالتأكيد. مصمم للشركات من كل الأحجام — من المقاول الفردي إلى الشركة التي تدير عشرات المشاريع.</p>
            </div>
          </div>

          <div class="faq-item" onclick="toggleFaq(this)">
            <div class="faq-item-head">
              <span class="faq-item-q">هل يعمل على الجوال؟</span>
              <button class="faq-toggle" aria-label="فتح">
                <svg class="icon-close" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                <svg class="icon-open" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
              </button>
            </div>
            <div class="faq-item-body">
              <p>نعم، تطبيق iOS وAndroid متاح بكامل الميزات. أدِر شركتك من أي مكان في العالم.</p>
            </div>
          </div>

          <div class="faq-item" onclick="toggleFaq(this)">
            <div class="faq-item-head">
              <span class="faq-item-q">كيف أبدأ التجربة المجانية؟</span>
              <button class="faq-toggle" aria-label="فتح">
                <svg class="icon-close" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                <svg class="icon-open" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
              </button>
            </div>
            <div class="faq-item-body">
              <p>سجّل ببريدك الإلكتروني فقط وستكون جاهزاً خلال دقيقة: بدون بطاقة بنكية، وبدون التزام، و14 يوماً مجاناً بكل المميزات.</p>
            </div>
          </div>

        </div>
      </div>
    </section>
@endsection
