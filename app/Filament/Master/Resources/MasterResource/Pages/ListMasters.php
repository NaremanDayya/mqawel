<?php

namespace App\Filament\Master\Resources\MasterResource\Pages;

use App\Filament\Master\Resources\MasterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasters extends ListRecords
{
    protected static string $resource = MasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
