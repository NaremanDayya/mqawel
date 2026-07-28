<?php

namespace App\Filament\Master\Resources\SubscriptionPackageResource\Pages;

use App\Filament\Master\Resources\SubscriptionPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptionPackages extends ListRecords
{
    protected static string $resource = SubscriptionPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
