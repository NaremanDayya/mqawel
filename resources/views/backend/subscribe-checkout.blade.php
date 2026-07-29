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
                                    <a href="{{ route('subscribe') }}">{{ __('frontend.packages_plans') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('logout') }}">{{ __('frontend.signout') }}</a>
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
                        <h1>{{ $Package->title }}</h1>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ====== Banner End ====== -->

    <section class="ud-pricing bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">
                    <div class="card p-4 p-md-5" style="border: 1px solid #eee;">
                        <form action="{{ route('payment') }}" method="GET">
                            <input type="hidden" name="package_id" value="{{ $Package->id }}">

                            <div class="mb-4">
                                <span class="d-block text-muted mb-1">{{ __('frontend.monthly_price') }}</span>
                                <span class="price-display">
                                    {{ number_format($Package->price / $Package->period, 3) }}
                                    <small style="font-size: 18px;">{{ __('backend.omani_riyal_code') }}</small>
                                    / {{ __('frontend.months_singular') }}
                                </span>
                            </div>

                            <div class="mb-4">
                                <label for="months" class="form-label fw-bold">{{ __('frontend.select_months_count') }}</label>
                                <select name="months" id="months" class="form-select" data-monthly-price="{{ $Package->price / $Package->period }}">
                                    @foreach ([1, 3, 6, 12] as $option)
                                        <option value="{{ $option }}">{{ $option }} {{ __('frontend.months') }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="d-flex align-items-center justify-content-between p-3 mb-4"
                                style="background: #f6f7fb; border-radius: 10px;">
                                <span class="fw-bold">{{ __('frontend.total_amount') }}</span>
                                <span id="total-amount" class="fw-bold" style="font-size: 22px; color: #6E56CF;">
                                    {{ number_format($Package->price / $Package->period, 3) }} {{ __('backend.omani_riyal_code') }}
                                </span>
                            </div>

                            <button type="submit" class="ud-main-btn ud-login-btn w-100 text-center justify-content-center">
                                {{ __('frontend.proceed_to_payment') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        (function () {
            var monthsSelect = document.getElementById('months');
            var totalEl = document.getElementById('total-amount');
            var currency = @json(__('backend.omani_riyal_code'));

            function updateTotal() {
                var monthlyPrice = parseFloat(monthsSelect.dataset.monthlyPrice);
                var months = parseInt(monthsSelect.value, 10);
                var total = monthlyPrice * months;

                totalEl.textContent = total.toLocaleString(undefined, { minimumFractionDigits: 3, maximumFractionDigits: 3 }) + ' ' + currency;
            }

            monthsSelect.addEventListener('change', updateTotal);
            updateTotal();
        })();
    </script>
@endsection
