<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Concerns\TogglesRecordsView;
use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ListProjects extends ListRecords
{
    use TogglesRecordsView;

    protected static string $resource = ProjectResource::class;

    public function table(Table $table): Table
    {
        return $this->applyRecordsViewToggle(parent::table($table), $this->projectCardColumns());
    }

    protected function projectCardColumns(): array
    {
        return [
            Stack::make([
                TextColumn::make('name')->weight('bold')->label(__('backend.project_name')),
                TextColumn::make('address')
                    ->formatStateUsing(fn (?string $state) => $state ?: '—')
                    ->color('gray')
                    ->size('sm')
                    ->label(__('backend.project_location')),
                TextColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => __('backend.pending'),
                        'processing' => __('backend.processing'),
                        'completed' => __('backend.completed'),
                        'cancelled' => __('backend.cancelled'),
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->label(__('backend.project_level')),
            ])->space(2),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
