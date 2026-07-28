@extends('frontend.global')

@section('content')
    <header class="ud-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <nav class="navbar navbar-expand-lg">
                        <a class="navbar-brand" href="{{ route('index') }}">
                            <img src="{{ asset('images/logo_white.png') }}" alt="Logo" style="height: 30px;" />
                        </a>
                        <button class="navbar-toggler">
                            <span class="toggler-icon"> </span>
                            <span class="toggler-icon"> </span>
                            <span class="toggler-icon"> </span>
                        </button>

                        <div class="navbar-collapse">
                            <ul id="nav" class="navbar-nav mx-auto">
                                <li class="nav-item">
                                    <a class="ud-menu-scroll" href="{{ route('index') }}">{{ __('frontend.homepage') }}</a>
                                </li>
                                {{-- <li class="nav-item">
                                    <a class="ud-menu-scroll" href="#features">{{__('frontend.features')}}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="ud-menu-scroll" href="#about">{{__('frontend.about_us')}}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="ud-menu-scroll" href="#contact">{{__('frontend.contact')}}</a>
                                </li> --}}
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
                            <a href="{{ url('company') }}" class="ud-main-btn ud-login-btn">
                                {{ __('frontend.sign_in') }}
                            </a>
                            {{-- <a class="ud-main-btn ud-white-btn" href="#contact">
                                {{__('frontend.send_request')}}
                            </a> --}}
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
                        <h1>{{ __('frontend.create_new_account') }}</h1>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ====== Banner End ====== -->

    <section class="ud-login">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 mb-5">
                    <div class="ud-login-wrapper">
                        <form class="ud-login-form">
                            <div class="ud-form-group">
                                <label class="mb-3">{{__('frontend.email')}}</label>
                                <input type="email" name="email" placeholder="" />
                            </div>
                            <div class="ud-form-group">
                                <label class="mb-3">{{__('frontend.password')}}</label>
                                <input type="password" name="password" placeholder="" />
                            </div>
                            <div class="ud-form-group">
                                <label class="mb-3">{{__('frontend.reenter_password')}}</label>
                                <input type="password" name="password_confirmation" placeholder="" />
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="col-lg-12">
                    <div class="ud-login-wrapper">
                        <form class="ud-login-form">
                            <div class="ud-form-group">
                                <label class="mb-3">{{__('frontend.email')}}</label>
                                <input type="email" name="email" placeholder="" />
                            </div>
                            <div class="ud-form-group">
                                <label class="mb-3">{{__('frontend.password')}}</label>
                                <input type="password" name="password" placeholder="" />
                            </div>
                            <div class="ud-form-group">
                                <label class="mb-3">{{__('frontend.reenter_password')}}</label>
                                <input type="password" name="password_confirmation" placeholder="" />
                            </div>
                            <div class="ud-form-group">
                                <button type="submit" class="ud-main-btn w-100">Login</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
