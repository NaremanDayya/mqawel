<div class="mq-brand">
    <span class="mq-brand-mark">
        <img src="{{ asset('images/logo-mark.png') }}" alt="{{ __('backend.dashboard_brand_name') }}" />
    </span>
    <span class="mq-brand-txt" x-show="$store.sidebar.isOpen">
        <b>{{ __('backend.dashboard_brand_name') }}</b>
        <i class="mq-brand-rule"></i>
        <span>{{ __('backend.brand_tagline') }}</span>
    </span>
</div>
