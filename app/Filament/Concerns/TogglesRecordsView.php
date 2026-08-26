<?php

namespace App\Filament\Concerns;

use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

trait TogglesRecordsView
{
    public string $recordsView = 'list';

    protected function isCardsView(): bool
    {
        return $this->recordsView === 'cards';
    }

    /**
     * Applies the toggle buttons and, when in card mode, switches the table
     * to a content grid using the given card column layout.
     *
     * @param  array<int, \Filament\Tables\Columns\Column|\Filament\Tables\Columns\Layout\Component>  $cardColumns
     */
    protected function applyRecordsViewToggle(Table $table, array $cardColumns): Table
    {
        if ($this->isCardsView()) {
            $table = $table
                ->contentGrid(['default' => 1, 'md' => 2, 'xl' => 3])
                ->columns($cardColumns);
        }

        return $table->headerActions([
            Action::make('viewAsList')
                ->label(__('backend.list_view'))
                ->tooltip(__('backend.list_view'))
                ->icon('heroicon-o-bars-3')
                ->iconButton()
                ->color(fn () => $this->isCardsView() ? 'gray' : 'primary')
                ->extraAttributes(['class' => 'mq-view-toggle'])
                ->action(function () {
                    $this->recordsView = 'list';
                    $this->resetTable();
                }),
            Action::make('viewAsCards')
                ->label(__('backend.card_view'))
                ->tooltip(__('backend.card_view'))
                ->icon('heroicon-o-squares-2x2')
                ->iconButton()
                ->color(fn () => $this->isCardsView() ? 'primary' : 'gray')
                ->extraAttributes(['class' => 'mq-view-toggle'])
                ->action(function () {
                    $this->recordsView = 'cards';
                    $this->resetTable();
                }),
        ]);
    }
}
