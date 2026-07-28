<?php

namespace App\Filament\Resources\ItemMovementResource\Pages;

use App\Filament\Resources\ItemMovementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditItemMovement extends EditRecord
{
    protected static string $resource = ItemMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
