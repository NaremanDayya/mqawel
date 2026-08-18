<?php

namespace App\Filament\Resources\ContractorResource\Pages;

use App\Filament\Concerns\HasSectionNotificationSettings;
use App\Filament\Resources\ContractorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContractors extends ListRecords
{
    use HasSectionNotificationSettings;

    protected static string $resource = ContractorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->notificationSettingsAction('contractors', __('backend.contractors')),
            Actions\CreateAction::make(),
        ];
    }
}
