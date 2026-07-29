@php
    $direction = 'ltr';
    $text_align = 'left';

    if (session()->has('current_lang') && session('current_lang') == 'ar') {
        $direction = 'rtl';
        $text_align = 'right';
    }
@endphp

<!DOCTYPE html>
<html dir="{{ $direction }}">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('backend.company_profile') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 20px; margin-bottom: 2px; }
        h2 { font-size: 14px; margin: 18px 0 6px; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        td, th { border: 1px solid #ccc; padding: 6px; text-align: {{ $text_align }}; }
        th { background: #333; color: #fff; }
    </style>
</head>

<body>
    <h1>{{ $company->name }}</h1>
    <p class="muted">{{ $company->activity }} @if($company->address) — {{ $company->address }} @endif @if($company->founded_year) — {{ __('backend.founded_year') }}: {{ $company->founded_year }} @endif</p>

    <h2>{{ __('backend.completion_title') }} — {{ $overall }}%</h2>
    <table>
        <thead><tr><th>{{ __('backend.section') }}</th><th>%</th></tr></thead>
        <tbody>
            @foreach ($completion as $item)
                <tr><td>{{ $item['label'] }}</td><td>{{ $item['percentage'] }}%</td></tr>
            @endforeach
        </tbody>
    </table>

    <h2>{{ __('backend.main_company_info') }}</h2>
    <table>
        <tbody>
            <tr><td>{{ __('backend.business_number') }}</td><td>{{ $company->business_number ?: '—' }}</td></tr>
            <tr><td>{{ __('backend.phone') }}</td><td>{{ $company->phone ?: '—' }}</td></tr>
            <tr><td>{{ __('backend.email') }}</td><td>{{ $company->email ?: '—' }}</td></tr>
            <tr><td>{{ __('backend.website') }}</td><td>{{ $company->website ?: '—' }}</td></tr>
        </tbody>
    </table>

    @if ($company->services)
        <h2>{{ __('backend.services_offered') }}</h2>
        <p>{{ implode(' · ', $company->services) }}</p>
    @endif

    @if ($featuredProjects->count())
        <h2>{{ __('backend.featured_projects') }}</h2>
        <table>
            <thead><tr><th>{{ __('backend.project_name') }}</th><th>{{ __('backend.budget') }}</th></tr></thead>
            <tbody>
                @foreach ($featuredProjects as $project)
                    <tr><td>{{ $project->name }}</td><td>{{ number_format($project->budget) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>{{ __('backend.staff_count') }}</h2>
    <p>{{ __('backend.workers') }}: {{ $staffCounts['workers'] }} · {{ __('backend.users') }}: {{ $staffCounts['users'] }}</p>

    @if ($company->description)
        <h2>{{ __('backend.about_company') }}</h2>
        <p>{{ $company->description }}</p>
    @endif

    <p class="muted">{{ __('backend.export_date') }}: {{ date('Y-m-d H:i') }}</p>
</body>

</html>
