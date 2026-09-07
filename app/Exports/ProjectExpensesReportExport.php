<?php

namespace App\Exports;

use App\Models\ProjectExpense;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProjectExpensesReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $rows;

    public function __construct($rows = null)
    {
        $this->rows = $rows ?? ProjectExpense::where('company_id', Auth::user()->company_id)
            ->with('project')
            ->get();
    }

    public function collection()
    {
        return $this->rows;
    }

    public function map($row): array
    {
        return [
            $row->project->name ?? '—',
            $row->title,
            $row->amount,
            $row->currency,
            $row->date,
        ];
    }

    public function headings(): array
    {
        return [
            __('backend.project'),
            __('backend.title'),
            __('backend.amount'),
            __('backend.currency'),
            __('backend.date'),
        ];
    }
}
