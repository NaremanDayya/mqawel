<?php

namespace App\Filament\Resources\ItemMovementResource\Pages;

use App\Filament\Concerns\HasMockupPageHeader;
use App\Filament\Resources\ItemMovementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListItemMovements extends ListRecords
{
    use HasMockupPageHeader;

    protected static string $resource = ItemMovementResource::class;

    protected function pageSubtitle(): ?string
    {
        return __('backend.updates_subtitle');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('backend.register_update'))
                ->icon('heroicon-o-plus')
                ->modalHeading(__('backend.register_update'))
                ->modalDescription(__('backend.register_update_description')),
        ];
    }
}
