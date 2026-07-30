<?php

namespace App\Filament\Resources\StorageItemResource\Pages;

use App\Filament\Concerns\HasMockupPageHeader;
use App\Filament\Resources\ItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListStorageItems extends ListRecords
{
    use HasMockupPageHeader;

    protected static string $resource = ItemResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('backend.main_storage');
    }

    protected function pageSubtitle(): ?string
    {
        return __('backend.main_storage_subtitle');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('backend.add_item'))
                ->icon('heroicon-o-plus'),
        ];
    }
}
