<?php

namespace App\Filament\Resources\CompanyFileResource\Pages;

use App\Filament\Resources\CompanyFileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompanyFile extends EditRecord
{
    protected static string $resource = CompanyFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
