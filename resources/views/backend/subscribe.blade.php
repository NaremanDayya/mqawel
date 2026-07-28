@extends('frontend.global')

@section('content')
    <header class="ud-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <nav class="navbar navbar-expand-lg">
                        <a class="navbar-brand" href="{{ route('index') }}">
                            <img src="{{ asset('images/logo_white.png') }}" alt="Logo" style="height: 70px;" />
                        </a>
                        <button class="navbar-toggler">
                            <span class="toggler-icon"> </span>
                            <span class="toggler-icon"> </span>
                            <span class="toggler-icon"> </span>
                        </button>

                        <div class="navbar-collapse">
                            <ul id="nav" class="navbar-nav mx-auto">
                                <li class="nav-item">
                                    <a href="{{ route('logout') }}">{{ __('frontend.signout') }}</a>
                                </li>
                                <li class="nav-item nav-item-has-children">
                                    <a href="javascript:void(0)"> {{ __('frontend.language') }} </a>
                                    <ul class="ud-submenu">
                                        <li class="ud-submenu-item">
                                            <a href="{{ url('lang/ar') }}" class="ud-submenu-link">
                                                {{ 'العربية' }}
                                            </a>
                                        </li>
                                        <li class="ud-submenu-item">
                                            <a href="{{ url('lang/en') }}" class="ud-submenu-link">
                                                {{ 'English' }}
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>

                        <div class="navbar-btn d-none d-sm-inline-block">
                            <a class="ud-main-btn ud-login-btn">
                                <b>{{ Auth::user()->name }}</b> <small>({{ Auth::user()->email }})</small>
                                <br />
                                <small>{{ Auth::user()->company->name }}</small>
                            </a>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <!-- ====== Banner Start ====== -->
    <section class="ud-page-banner">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ud-banner-content">
                        <h1>{{ __('frontend.select_package_that_suites_you') }}</h1>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ====== Banner End ====== -->

    <!-- ====== Pricing Start ====== -->
    <section id="pricing" class="ud-pricing bg-light">
        <div class="container">
            <div class="row">
                @if (isset($failed_payment) && $failed_payment)
                    <div class="col-lg-12 mb-5">
                        <div class="ud-404-wrapper p-5"
                            style="background: #ffeeff; box-shadow: none; border: 1px solid #eeeeee;">
                            <div class="ud-404-content">
                                <h2 class="ud-404-title mb-0"
                                    style="font-weight: semibold; font-size: 20px; color: #ff1234">
                                    {{ __('frontend.subscription_process_unsuccessful') }}!</h2>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="col-lg-12">
                    <div class="ud-section-title mx-auto text-center">
                        <span>{{ __('frontend.packages') }}</span>
                        <h2>{{ __('frontend.packages_plans') }}</h2>
                    </div>
                </div>
            </div>

            @php
                $Packages = App\Models\SubscriptionPackage::where(['is_active' => true])
                    ->orderBy('price', 'asc')
                    ->limit(10)
                    ->get();
                $count = 0;
            @endphp

            <div class="row g-4 align-items-center justify-content-center">
                @foreach ($Packages as $Package)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card pricing-card h-100" style="border: 1px solid #eee;">
                            <a href="{{ route('payment', ['package_id' => $Package->id, 'payment_name' => $Package->title, 'payment_quantity' => 1, 'payment_amount' => $Package->price]) }}"
                                class="card-body d-flex flex-column">
                                <h3 class="plan-title" style="font-size: 17px;">{{ $Package->title }}</h3>
                                <div class="d-flex align-items-baseline mt-2 mb-0">
                                    <span class="price-display">{{ number_format($Package->price) }} <small style="font-size: 25px;">{{__('backend.omani_riyal_code')}}</small></span>
                                </div>
                                <div class="billed-monthly mt-1 mb-3">
                                    {{ $Package->period }} {{ __('frontend.months') }}
                                </div>

                                <ul class="list-group list-group-flush feature-list" style="text-align: {{ session('current_lang') == 'ar' ? 'right' : 'left' }};">
                                    <li class="list-group-item">
                                        <i class="bi bi-{{ $Package->has_workers ? 'check' : 'x' }}-circle-fill text-{{ $Package->has_workers ? 'success' : 'danger' }} ms-2"></i>
                                        <span class="feature-text">{{ __('frontend.workers_management') }}</span>
                                    </li>
                                    <li class="list-group-item">
                                        <i class="bi bi-{{ $Package->has_projects ? 'check' : 'x' }}-circle-fill text-{{ $Package->has_projects ? 'success' : 'danger' }} ms-2"></i>
                                        <span class="feature-text">{{ __('frontend.projects_management') }}</span>
                                    </li>
                                    <li class="list-group-item">
                                        <i class="bi bi-{{ $Package->has_storages ? 'check' : 'x' }}-circle-fill text-{{ $Package->has_storages ? 'success' : 'danger' }} ms-2"></i>
                                        <span class="feature-text">{{ __('frontend.storages_management') }}</span>
                                    </li>
                                    <li class="list-group-item">
                                        <i class="bi bi-{{ $Package->has_items ? 'check' : 'x' }}-circle-fill text-{{ $Package->has_items ? 'success' : 'danger' }} ms-2"></i>
                                        <span class="feature-text">{{ __('frontend.items_management') }}</span>
                                    </li>
                                    <li class="list-group-item">
                                        <i class="bi bi-{{ $Package->has_item_categories ? 'check' : 'x' }}-circle-fill text-{{ $Package->has_item_categories ? 'success' : 'danger' }} ms-2"></i>
                                        <span class="feature-text">{{ __('frontend.item_categories_management') }}</span>
                                    </li>
                                    <li class="list-group-item">
                                        <i class="bi bi-{{ $Package->has_item_movements ? 'check' : 'x' }}-circle-fill text-{{ $Package->has_item_movements ? 'success' : 'danger' }} ms-2"></i>
                                        <span class="feature-text">{{ __('frontend.item_movements_management') }}</span>
                                    </li>
                                    <li class="list-group-item">
                                        <i class="bi bi-{{ $Package->has_workers_report ? 'check' : 'x' }}-circle-fill text-{{ $Package->has_workers_report ? 'success' : 'danger' }} ms-2"></i>
                                        <span class="feature-text">{{ __('frontend.workers_report') }}</span>
                                    </li>
                                    <li class="list-group-item">
                                        <i class="bi bi-{{ $Package->has_worker_expenses_report ? 'check' : 'x' }}-circle-fill text-{{ $Package->has_worker_expenses_report ? 'success' : 'danger' }} ms-2"></i>
                                        <span class="feature-text">{{ __('frontend.worker_expenses_report') }}</span>
                                    </li>
                                    <li class="list-group-item">
                                        <i class="bi bi-{{ $Package->has_expired_files_report ? 'check' : 'x' }}-circle-fill text-{{ $Package->has_expired_files_report ? 'success' : 'danger' }} ms-2"></i>
                                        <span class="feature-text">{{ __('frontend.expired_documents_report') }}</span>
                                    </li>
                                    <li class="list-group-item">
                                        <i class="bi bi-{{ $Package->has_project_expenses_report ? 'check' : 'x' }}-circle-fill text-{{ $Package->has_project_expenses_report ? 'success' : 'danger' }} ms-2"></i>
                                        <span class="feature-text">{{ __('frontend.project_expenses_report') }}</span>
                                    </li>
                                </ul>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    <!-- ====== Pricing End ====== -->
@endsection
