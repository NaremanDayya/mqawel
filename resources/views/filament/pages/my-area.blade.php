<x-filament-panels::page>
    @php
        $banner = $this->getBannerStats();
        $alerts = $this->getAlerts();
        $suggestions = $this->getSuggestedActions();
        $kpis = $this->getKpis();
        $activity = $this->getRecentActivity();
        $distribution = $this->getInventoryDistribution();
        $totalUnits = collect($distribution)->sum('value');
        $progress = $this->getProjectProgress();
        $prefs = $this->getPreferences();
    @endphp

    <p class="my-area-greeting">
        {{ $this->getGreeting() }}
        @if ($lastSeen = $this->getLastSeen())
            <span class="my-area-last-seen">
                <x-filament::icon icon="heroicon-o-clock" class="my-area-last-seen-icon" />
                {{ __('backend.last_seen') }} {{ $lastSeen }}
            </span>
        @endif
    </p>

    @if ($prefs['attention'])
        @if ($banner['pending_actions'] > 0)
            <div class="my-area-banner">
                <x-filament::icon icon="heroicon-o-exclamation-triangle" class="my-area-banner-icon" />
                <div class="my-area-banner-text">
                    <b>{{ __('backend.attention_needed_title') }}</b>
                </div>
                <div class="my-area-banner-stats">
                    <div class="my-area-banner-stat">
                        <b style="color: var(--mq-warn)">{{ $banner['pending_actions'] }}</b>
                        <span>{{ __('backend.pending_actions') }}</span>
                    </div>
                    <div class="my-area-banner-stat">
                        <b style="color: var(--mq-clay)">{{ $banner['workers_under_management'] }}</b>
                        <span>{{ __('backend.workers_under_management') }}</span>
                    </div>
                    <div class="my-area-banner-stat">
                        <b style="color: var(--mq-info)">{{ $banner['active_projects'] }}</b>
                        <span>{{ __('backend.active_projects') }}</span>
                    </div>
                </div>
            </div>
        @else
            <div class="my-area-banner my-area-banner-ok">
                <x-filament::icon icon="heroicon-o-check-circle" class="my-area-banner-icon" />
                <div class="my-area-banner-text"><b>{{ __('backend.all_clear_message') }}</b></div>
            </div>
        @endif
    @endif

    @if ($prefs['alerts'] || $prefs['actions'])
    <div class="my-area-grid-2">
        @if ($prefs['alerts'])
        <section class="fi-section my-area-card" id="alerts-panel">
            <div class="my-area-card-head">
                <div>
                    <h2 class="my-area-card-title">{{ __('backend.alerts') }}</h2>
                    <p class="my-area-card-sub">{{ __('backend.alerts_message') }}</p>
                </div>
                @if (count($alerts) > 0)
                    <span class="fi-badge my-area-count-badge" style="--c-50:var(--mq-danger-bg);--c-400:var(--mq-danger);--c-600:var(--mq-danger);" data-color="danger">{{ count($alerts) }}</span>
                @endif
            </div>

            @forelse ($alerts as $alert)
                <a href="{{ $alert['url'] }}" class="my-area-alert-row">
                    <span class="my-area-alert-icon my-area-tone-{{ $alert['tone'] }}">
                        <x-filament::icon :icon="$alert['icon']" class="h-5 w-5" />
                    </span>
                    <span class="my-area-alert-body">
                        <b>{{ $alert['title'] }}</b>
                        <span>{{ $alert['desc'] }}</span>
                    </span>
                    <span class="my-area-alert-count">{{ $alert['count'] }}</span>
                </a>
            @empty
                <p class="my-area-empty">{{ __('backend.all_clear_message') }}</p>
            @endforelse

            @if (count($alerts) > 0)
                <div class="my-area-card-foot">
                    <a href="{{ url('company/expired-files-report') }}" class="my-area-link">{{ __('backend.view_all_reports') }}</a>
                </div>
            @endif
        </section>
        @endif

        @if ($prefs['actions'])
        <section class="fi-section my-area-card">
            <div class="my-area-card-head">
                <div>
                    <h2 class="my-area-card-title">{{ __('backend.suggested_actions') }}</h2>
                    <p class="my-area-card-sub">{{ __('backend.suggested_actions_desc') }}</p>
                </div>
            </div>

            @forelse ($suggestions as $suggestion)
                <div class="my-area-suggestion-row">
                    <span class="my-area-suggestion-dot my-area-tone-{{ $suggestion['tone'] }}"></span>
                    <span class="my-area-suggestion-msg">{!! $suggestion['message'] !!}</span>
                    <a href="{{ $suggestion['url'] }}" class="fi-btn fi-btn-size-sm fi-btn-color-gray my-area-suggestion-btn">
                        {{ $suggestion['label'] }}
                    </a>
                </div>
            @empty
                <p class="my-area-empty">{{ __('backend.no_suggested_actions') }}</p>
            @endforelse
        </section>
        @endif
    </div>
    @endif

    @if ($prefs['kpis'])
    <div class="my-area-kpi-grid">
        <div class="fi-wi-stats-overview-stat my-area-kpi-hero">
            <div class="my-area-kpi-top">
                <span class="my-area-kpi-lbl">{{ __('backend.total_projects') }}</span>
                <span class="my-area-kpi-ico my-area-kpi-ico-dark"><x-filament::icon icon="heroicon-o-map-pin" class="h-5 w-5" /></span>
            </div>
            <div class="my-area-kpi-val">{{ $kpis['total_projects'] }}</div>
            <div class="my-area-kpi-foot">
                @if ($kpis['total_projects_trend'] != 0)
                    <span class="my-area-kpi-trend {{ $kpis['total_projects_trend'] > 0 ? 'my-area-trend-up' : 'my-area-trend-down' }}">
                        <x-filament::icon :icon="$kpis['total_projects_trend'] > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down'" class="h-3 w-3" />
                        {{ $kpis['total_projects_trend'] > 0 ? '+' : '' }}{{ $kpis['total_projects_trend'] }}
                    </span>
                    <span>{{ __('backend.vs_last_month') }}</span>
                @endif
            </div>
        </div>

        <div class="fi-wi-stats-overview-stat">
            <div class="my-area-kpi-top">
                <span class="my-area-kpi-lbl">{{ __('backend.processing_projects') }}</span>
                <span class="my-area-kpi-ico"><x-filament::icon icon="heroicon-o-clock" class="h-5 w-5" /></span>
            </div>
            <div class="my-area-kpi-val">{{ $kpis['processing_projects'] }}</div>
            <div class="my-area-kpi-foot">
                @if ($kpis['processing_project_name'])
                    <span class="my-area-kpi-foot-text">{{ $kpis['processing_project_name'] }}</span>
                @endif
            </div>
        </div>

        <div class="fi-wi-stats-overview-stat">
            <div class="my-area-kpi-top">
                <span class="my-area-kpi-lbl">{{ __('backend.total_workers') }}</span>
                <span class="my-area-kpi-ico"><x-filament::icon icon="heroicon-o-user-group" class="h-5 w-5" /></span>
            </div>
            <div class="my-area-kpi-val">{{ $kpis['total_workers'] }}</div>
            <div class="my-area-kpi-foot">
                @if ($kpis['incomplete_workers'] > 0)
                    <span class="my-area-kpi-pill my-area-tone-clay">{{ __('backend.workers_incomplete_count', ['count' => $kpis['incomplete_workers']]) }}</span>
                @endif
            </div>
        </div>

        <a href="#alerts-panel" class="fi-wi-stats-overview-stat my-area-kpi-alert">
            <div class="my-area-kpi-top">
                <span class="my-area-kpi-lbl">{{ __('backend.active_warnings') }}</span>
                <span class="my-area-kpi-ico my-area-tone-danger"><x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-5 w-5" /></span>
            </div>
            <div class="my-area-kpi-val">{{ $kpis['pending_actions'] }}</div>
            <div class="my-area-kpi-foot">
                @if ($kpis['pending_actions'] > 0)
                    <span class="my-area-kpi-pill my-area-tone-danger">{{ __('backend.needs_attention') }}</span>
                @endif
            </div>
        </a>
    </div>
    @endif

    @if ($prefs['progress'] && count($progress['rows']) > 0)
        <section class="fi-section my-area-card" style="margin-bottom: 16px">
            <div class="my-area-card-head">
                <div>
                    <h2 class="my-area-card-title">{{ __('backend.project_completion') }}</h2>
                    <p class="my-area-card-sub">{{ __('backend.project_completion_desc') }}</p>
                </div>
                <span class="fi-badge my-area-count-badge" data-color="info">{{ __('backend.average') }} {{ $progress['average'] }}%</span>
            </div>

            @foreach ($progress['rows'] as $row)
                <div class="my-area-goal-row">
                    <div class="my-area-goal-top">
                        <b>{{ $row['name'] }}</b>
                        <span>{{ $row['meta'] }}</span>
                    </div>
                    <div class="my-area-goal-bar">
                        <div class="my-area-goal-fill" style="width: {{ $row['pct'] }}%; background: {{ $row['color'] }}"></div>
                    </div>
                    <span class="my-area-goal-pct">{{ $row['pct'] }}%</span>
                </div>
            @endforeach

            <div class="my-area-goal-row">
                <div class="my-area-goal-top">
                    <b>{{ __('backend.overall_average_completion') }}</b>
                </div>
                <div class="my-area-goal-bar">
                    <div class="my-area-goal-fill" style="width: {{ $progress['average'] }}%; background: #22C1D6"></div>
                </div>
                <span class="my-area-goal-pct">{{ $progress['average'] }}%</span>
            </div>
        </section>
    @endif

    @if ($prefs['activity'] || $prefs['distribution'])
    <div class="my-area-grid-2">
        @if ($prefs['activity'])
        <section class="fi-section my-area-card">
            <div class="my-area-card-head">
                <div>
                    <h2 class="my-area-card-title">{{ __('backend.recent_activity') }}</h2>
                    <p class="my-area-card-sub">{{ __('backend.recent_activity_desc') }}</p>
                </div>
                <a href="{{ url('company/movements') }}" class="my-area-link">{{ __('backend.view_all_reports') }}</a>
            </div>

            @forelse ($activity as $row)
                <div class="my-area-feed-row">
                    <span class="my-area-feed-dot my-area-tone-{{ $row['type'] === 'in' ? 'ok' : ($row['type'] === 'out' ? 'warning' : 'info') }}"></span>
                    <span class="my-area-feed-body">
                        <b>{{ $row['item'] }}</b> — {{ $row['typeLabel'] }} — {{ $row['storage'] }}
                        @if ($row['performer'])
                            — {{ __('backend.executed_by') }}: {{ $row['performer'] }}
                        @endif
                    </span>
                    <span class="my-area-feed-time">{{ $row['time'] }}</span>
                </div>
            @empty
                <p class="my-area-empty">{{ __('backend.no_recent_activity') }}</p>
            @endforelse
        </section>
        @endif

        @if ($prefs['distribution'])
        <section class="fi-section my-area-card">
            <div class="my-area-card-head">
                <div>
                    <h2 class="my-area-card-title">{{ __('backend.inventory_distribution') }}</h2>
                    <p class="my-area-card-sub">{{ __('backend.inventory_distribution_desc') }}</p>
                </div>
            </div>

            @if (count($distribution) > 0)
                @php
                    $circumference = 2 * M_PI * 52;
                    $offsetAcc = 0;
                @endphp
                <div class="my-area-donut-wrap">
                    <div class="my-area-donut">
                        <svg viewBox="0 0 150 150" width="150" height="150">
                            <circle cx="75" cy="75" r="52" fill="none" stroke="var(--mq-line)" stroke-width="20" />
                            @foreach ($distribution as $slice)
                                @php
                                    $frac = $totalUnits > 0 ? $slice['value'] / $totalUnits : 0;
                                    $dash = round($frac * $circumference, 2).' '.round($circumference - $frac * $circumference, 2);
                                    $offset = round(-$offsetAcc * $circumference, 2);
                                    $offsetAcc += $frac;
                                @endphp
                                <circle cx="75" cy="75" r="52" fill="none" stroke="{{ $slice['color'] }}" stroke-width="20"
                                    stroke-dasharray="{{ $dash }}" stroke-dashoffset="{{ $offset }}" transform="rotate(-90 75 75)" />
                            @endforeach
                        </svg>
                        <div class="my-area-donut-center">
                            <small>{{ __('backend.total_units') }}</small>
                            <b>{{ rtrim(rtrim(number_format($totalUnits, 2), '0'), '.') }}</b>
                        </div>
                    </div>
                    <div class="my-area-donut-legend">
                        @foreach ($distribution as $slice)
                            <div class="my-area-donut-legend-row">
                                <i style="background:{{ $slice['color'] }}"></i>
                                <span class="my-area-donut-legend-name">{{ $slice['name'] }}</span>
                                <span class="my-area-donut-legend-val">{{ rtrim(rtrim(number_format($slice['value'], 2), '0'), '.') }}</span>
                                <span class="my-area-donut-legend-pct">{{ $totalUnits > 0 ? round($slice['value'] / $totalUnits * 100) : 0 }}%</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <p class="my-area-empty">{{ __('backend.no_inventory_recorded') }}</p>
            @endif
        </section>
        @endif
    </div>
    @endif

    <style>
        .my-area-greeting { font-size: 14px; color: var(--mq-txt-2); margin: -8px 0 20px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .my-area-last-seen { display: inline-flex; align-items: center; gap: 4px; font-size: 12px; color: var(--mq-txt-3, #9a9ab0); background: var(--mq-surface-2, rgba(110,86,207,0.08)); padding: 3px 10px; border-radius: 999px; }
        .my-area-last-seen-icon { width: 13px; height: 13px; }

        .my-area-banner {
            display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
            background: var(--mq-warn-bg); border: 1px solid rgba(245,165,36,.25);
            border-radius: var(--mq-r-lg); padding: 16px 20px; margin-bottom: 20px;
        }
        .my-area-banner-ok { background: var(--mq-ok-bg); border-color: rgba(18,165,148,.25); }
        .my-area-banner-icon { width: 24px; height: 24px; color: var(--mq-warn); flex-shrink: 0; }
        .my-area-banner-ok .my-area-banner-icon { color: var(--mq-ok); }
        .my-area-banner-text { flex: 1; min-width: 200px; color: var(--mq-ink); font-size: 14px; }
        .my-area-banner-stats { display: flex; gap: 24px; }
        .my-area-banner-stat { display: flex; flex-direction: column; align-items: center; gap: 2px; }
        .my-area-banner-stat b { font-size: 20px; font-weight: 800; }
        .my-area-banner-stat span { font-size: 11px; color: var(--mq-txt-2); }

        .my-area-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; align-items: start; }
        .my-area-grid-2 > *:only-child { grid-column: 1 / -1; }
        @media (max-width: 1024px) { .my-area-grid-2 { grid-template-columns: 1fr; } }

        .my-area-card { padding: 0; }
        .my-area-card-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 18px 20px 12px; }
        .my-area-card-title { font-size: 15px; font-weight: 700; color: var(--mq-ink); }
        .my-area-card-sub { font-size: 12px; color: var(--mq-txt-3); margin-top: 2px; }
        .my-area-card-foot { padding: 12px 20px; border-top: 1px solid var(--mq-line-2); }
        .my-area-link { font-size: 12.5px; font-weight: 600; color: var(--mq-clay); text-decoration: none; }
        .my-area-link:hover { text-decoration: underline; }
        .my-area-empty { padding: 24px 20px; text-align: center; font-size: 13px; color: var(--mq-txt-3); }
        .my-area-count-badge { flex-shrink: 0; }

        .my-area-alert-row {
            display: flex; align-items: center; gap: 14px; padding: 12px 20px;
            border-top: 1px solid var(--mq-line-2); text-decoration: none; transition: background .15s;
        }
        .my-area-alert-row:hover { background: var(--mq-bg); }
        .my-area-alert-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .my-area-alert-body { flex: 1; display: flex; flex-direction: column; }
        .my-area-alert-body b { font-size: 13px; font-weight: 600; color: var(--mq-ink); }
        .my-area-alert-body span { font-size: 11.5px; color: var(--mq-txt-3); }
        .my-area-alert-count { font-size: 18px; font-weight: 700; color: var(--mq-ink); }

        .my-area-tone-danger { background: var(--mq-danger-bg); color: var(--mq-danger); }
        .my-area-tone-warning { background: var(--mq-warn-bg); color: var(--mq-warn); }
        .my-area-tone-info { background: var(--mq-info-bg); color: var(--mq-info); }
        .my-area-tone-ok { background: var(--mq-ok-bg); color: var(--mq-ok); }
        .my-area-tone-clay { background: var(--mq-clay-bg); color: var(--mq-clay); }
        span.my-area-suggestion-dot, span.my-area-feed-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; background: currentColor; }

        .my-area-suggestion-row { display: flex; align-items: center; gap: 12px; padding: 12px 20px; border-top: 1px solid var(--mq-line-2); }
        .my-area-suggestion-msg { flex: 1; font-size: 13px; color: var(--mq-txt-2); line-height: 1.6; }
        .my-area-suggestion-msg b { color: var(--mq-ink); }
        .my-area-suggestion-btn { flex-shrink: 0; text-decoration: none; }

        .my-area-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px; }
        .my-area-kpi-grid .fi-wi-stats-overview-stat { padding: 18px 20px !important; }
        a.my-area-kpi-alert { text-decoration: none; display: block; transition: transform .15s; }
        a.my-area-kpi-alert:hover { transform: translateY(-2px); }

        .my-area-kpi-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; gap: 10px; }
        .my-area-kpi-lbl { font-size: 13px; font-weight: 500; color: var(--mq-txt-2); }
        .my-area-kpi-ico {
            width: 46px; height: 46px; border-radius: 13px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            background: var(--mq-bg); color: var(--mq-steel);
        }
        .my-area-kpi-ico svg { width: 22px; height: 22px; }
        .my-area-kpi-ico-dark { background: rgba(255,255,255,.14); color: #fff; }
        .my-area-kpi-val { font-size: 34px; font-weight: 800; line-height: 1.1; letter-spacing: -.01em; color: var(--mq-ink); }
        .my-area-kpi-foot { display: flex; align-items: center; gap: 6px; margin-top: 8px; font-size: 12px; color: var(--mq-txt-3); min-height: 22px; flex-wrap: wrap; }
        .my-area-kpi-foot-text { color: var(--mq-txt-3); }
        .my-area-kpi-pill {
            display: inline-flex; align-items: center; font-weight: 600; font-size: 11.5px;
            padding: 2px 10px; border-radius: 20px;
        }
        .my-area-kpi-trend {
            display: inline-flex; align-items: center; gap: 3px; font-weight: 600; font-size: 11.5px;
            padding: 2px 8px; border-radius: 20px;
        }
        .my-area-trend-up { color: #CFE3D8; background: rgba(255,255,255,.14); }
        .my-area-trend-down { color: #FBD1D3; background: rgba(255,255,255,.14); }

        .my-area-kpi-hero {
            background: linear-gradient(150deg, #5B4BD1 0%, #4A3DB8 55%, #2E2A73 100%) !important;
            border-color: transparent !important;
        }
        .my-area-kpi-hero .my-area-kpi-lbl { color: rgba(255,255,255,.85); }
        .my-area-kpi-hero .my-area-kpi-val { color: #fff; }
        .my-area-kpi-hero .my-area-kpi-foot { color: rgba(255,255,255,.7); }

        .my-area-goal-row { padding: 14px 20px; border-top: 1px solid var(--mq-line-2); }
        .my-area-goal-top { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; margin-bottom: 8px; }
        .my-area-goal-top b { font-size: 13.5px; font-weight: 600; color: var(--mq-ink); }
        .my-area-goal-top span { font-size: 12px; color: var(--mq-txt-3); }
        .my-area-goal-bar { height: 8px; border-radius: 6px; background: var(--mq-line-2); overflow: hidden; }
        .my-area-goal-fill { height: 100%; border-radius: 6px; }
        .my-area-goal-pct { font-size: 11px; font-weight: 600; color: var(--mq-txt-2); margin-top: 6px; display: inline-block; }

        .my-area-feed-row { display: flex; align-items: center; gap: 12px; padding: 11px 20px; border-top: 1px solid var(--mq-line-2); }
        .my-area-feed-body { flex: 1; font-size: 13px; color: var(--mq-txt-2); }
        .my-area-feed-body b { color: var(--mq-ink); }
        .my-area-feed-time { font-size: 11.5px; color: var(--mq-txt-3); white-space: nowrap; }

        .my-area-donut-wrap { display: flex; align-items: center; gap: 22px; padding: 8px 20px 22px; flex-wrap: wrap; }
        .my-area-donut { position: relative; width: 150px; height: 150px; flex-shrink: 0; }
        .my-area-donut-center { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; }
        .my-area-donut-center small { font-size: 11px; color: var(--mq-txt-3); }
        .my-area-donut-center b { font-size: 20px; font-weight: 700; color: var(--mq-ink); }
        .my-area-donut-legend { flex: 1; min-width: 160px; display: flex; flex-direction: column; gap: 10px; }
        .my-area-donut-legend-row { display: flex; align-items: center; gap: 10px; font-size: 13px; }
        .my-area-donut-legend-row i { width: 11px; height: 11px; border-radius: 4px; flex-shrink: 0; }
        .my-area-donut-legend-name { flex: 1; color: var(--mq-txt-2); }
        .my-area-donut-legend-val { font-weight: 600; color: var(--mq-ink); }
        .my-area-donut-legend-pct { font-size: 11.5px; color: var(--mq-txt-3); min-width: 34px; text-align: left; }
    </style>
</x-filament-panels::page>
