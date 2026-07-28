@php
    $direction= 'ltr';
    $text_align= 'left';

    if(session()->has('current_lang') && session('current_lang') == 'ar'){
        $direction= 'rtl';
        $text_align= 'right';
    }
@endphp

<!DOCTYPE html>

<html dir="{{$direction}}">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('backend.workers_report') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
        }
        /* Basic CSS for table borders and styling */
        table,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
            /* Merges borders into a single line */
            padding: 8px;
            /* Space between cell content and border */
            text-align: {{ $text_align}};
        }

        table {
            width: 100%;
        }

        th {
            background-color: #333333;
            color: white;
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th>{{__('backend.name')}}</th>
                <th>{{__('backend.phone')}}</th>
                <th>{{__('backend.ethnicity')}}</th>
                <th>{{__('backend.living_address')}}</th>
                <th>{{__('backend.status')}}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($Workers as $Worker)
                <tr>
                    <td>{{$Worker->name}}</td>
                    <td>{{$Worker->phone}}</td>
                    <td>{{$Worker->ethnicity}}</td>
                    <td>{{$Worker->living_address}}</td>
                    <td>{{$Worker->is_active == 1 ? __('backend.active') : __('backend.inactive')}}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5">{{__('backend.export_date')}}: {{date('Y-m-d H:i')}}</th>
            </tr>
        </tfoot>
    </table>
</body>

</html>
