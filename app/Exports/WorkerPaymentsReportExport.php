<?php

namespace App\Exports;

use App\Models\WorkerPayment;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WorkerPaymentsReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $rows;

    public function __construct($rows = null)
    {
        $this->rows = $rows ?? WorkerPayment::where('company_id', Auth::user()->company_id)
            ->where('status', 'unpaid')
            ->with('worker')
            ->get();
    }

    public function collection()
    {
        return $this->rows;
    }

    public function map($row): array
    {
        return [
            $row->worker->name ?? '—',
            $row->title,
            $row->amount,
            $row->currency,
            $row->payment_method,
            $row->status === 'paid' ? __('backend.paid') : __('backend.unpaid'),
            $row->payment_date,
        ];
    }

    public function headings(): array
    {
        return [
            __('backend.worker'),
            __('backend.payment_purpose'),
            __('backend.payment_amount'),
            __('backend.currency'),
            __('backend.payment_method'),
            __('backend.payment_status'),
            __('backend.payment_date'),
        ];
    }
}
