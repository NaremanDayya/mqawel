@php
    $direction = 'ltr';
    $text_align = 'left';

    if (session()->has('current_lang') && session('current_lang') == 'ar') {
        $direction = 'rtl';
        $text_align = 'right';
    }

    $sectionLabel = fn (string $table) => match ($table) {
        'users' => __('backend.users'),
        'workers' => __('backend.workers'),
        'storages' => __('backend.storages'),
        'projects' => __('backend.projects'),
        'storage_items' => __('backend.storage_items'),
        'contractors' => __('backend.contractors'),
        'items' => __('backend.items'),
        'companies' => __('backend.companies'),
        default => $table,
    };
@endphp

<!DOCTYPE html>
<html dir="{{ $direction }}">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('backend.expired_files_report') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table, th, td { border: 1px solid black; border-collapse: collapse; padding: 8px; text-align: {{ $text_align }}; }
        table { width: 100%; }
        th { background-color: #333333; color: white; }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th>{{ __('backend.section') }}</th>
                <th>{{ __('backend.name') }}</th>
                <th>{{ __('backend.expiry_date') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($files as $file)
                <tr>
                    <td>{{ $sectionLabel($file->parent_table) }}</td>
                    <td>{{ $file->name }}</td>
                    <td>{{ optional($file->expiry_date)->format('Y-m-d') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3">{{ __('backend.export_date') }}: {{ date('Y-m-d H:i') }}</th>
            </tr>
        </tfoot>
    </table>
</body>

</html>
