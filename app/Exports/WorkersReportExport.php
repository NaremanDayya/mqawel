<?php

namespace App\Exports;

use App\Filament\Resources\WorkerResource;
use App\Models\Worker;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WorkersReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Worker::where('company_id', Auth::user()->company_id)->where('company_id', '<>', null)->get();
    }

    public function map($row): array
    {
        $name= $row->name;
        $phone= $row->phone;
        $ethnicity= $row->ethnicity;
        $living_address= $row->living_address;
        $is_active= $row->is_active == 1 ? 'active' : 'inactive';

        return [
            $name,
            $phone,
            $ethnicity,
            $living_address,
            $is_active
        ];
    }

    public function headings(): array {
        return [
            __('backend.name'),
            __('backend.phone'),
            __('backend.ethnicity'),
            __('backend.living_address'),
            __('backend.active'),
        ];
    }
}
