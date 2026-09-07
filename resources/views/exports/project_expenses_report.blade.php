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
    <title>{{ __('backend.project_expenses_report') }}</title>
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
                <th>{{__('backend.project')}}</th>
                <th>{{__('backend.title')}}</th>
                <th>{{__('backend.amount')}}</th>
                <th>{{__('backend.currency')}}</th>
                <th>{{__('backend.date')}}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($Expenses as $Expense)
                <tr>
                    <td>{{$Expense->project->name ?? '—'}}</td>
                    <td>{{$Expense->title}}</td>
                    <td>{{$Expense->amount}}</td>
                    <td>{{$Expense->currency}}</td>
                    <td>{{$Expense->date}}</td>
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
