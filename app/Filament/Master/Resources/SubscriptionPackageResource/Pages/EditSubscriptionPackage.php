<?php

namespace App\Filament\Master\Resources\SubscriptionPackageResource\Pages;

use App\Filament\Master\Resources\SubscriptionPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubscriptionPackage extends EditRecord
{
    protected static string $resource = SubscriptionPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
