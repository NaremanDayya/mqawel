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
    <title>{{ __('backend.worker_payments_report') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
        }
        table,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
            padding: 8px;
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
                <th>{{__('backend.worker')}}</th>
                <th>{{__('backend.payment_purpose')}}</th>
                <th>{{__('backend.payment_amount')}}</th>
                <th>{{__('backend.currency')}}</th>
                <th>{{__('backend.payment_method')}}</th>
                <th>{{__('backend.payment_status')}}</th>
                <th>{{__('backend.payment_date')}}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($Payments as $Payment)
                <tr>
                    <td>{{$Payment->worker->name ?? '—'}}</td>
                    <td>{{$Payment->title}}</td>
                    <td>{{$Payment->amount}}</td>
                    <td>{{$Payment->currency}}</td>
                    <td>{{$Payment->payment_method}}</td>
                    <td>{{$Payment->status == 'paid' ? __('backend.paid') : __('backend.unpaid')}}</td>
                    <td>{{$Payment->payment_date}}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="7">{{__('backend.export_date')}}: {{date('Y-m-d H:i')}}</th>
            </tr>
        </tfoot>
    </table>
</body>

</html>
