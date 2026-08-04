<?php

namespace App\Exports;

use App\Models\File;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExpiredFilesReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return File::where('company_id', Auth::user()->company_id)
            ->where('expiry_date', '<', date('Y-m-d'))
            ->get();
    }

    public function map($row): array
    {
        return [
            match ($row->parent_table) {
                'users' => __('backend.users'),
                'workers' => __('backend.workers'),
                'storages' => __('backend.storages'),
                'projects' => __('backend.projects'),
                'storage_items' => __('backend.storage_items'),
                'contractors' => __('backend.contractors'),
                'items' => __('backend.items'),
                'companies' => __('backend.companies'),
                default => $row->parent_table,
            },
            $row->name,
            $row->expiry_date,
        ];
    }

    public function headings(): array
    {
        return [
            __('backend.section'),
            __('backend.name'),
            __('backend.expiry_date'),
        ];
    }
}
