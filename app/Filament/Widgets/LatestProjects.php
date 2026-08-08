<?php

namespace App\Filament\Widgets;

use App\Filament\Concerns\HasWidgetVisibility;
use App\Filament\Resources\ProjectResource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class LatestProjects extends BaseWidget
{
    use HasWidgetVisibility;

    protected static ?int $sort= 5;

    protected int | string | array $columnSpan= 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProjectResource::getEloquentQuery()->where('company_id', Auth::user()->company_id)->limit(5)
            )
            ->defaultPaginationPageOption(5)
            ->paginated(false)
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->label(__('backend.name')),
                TextColumn::make('address')->label(__('backend.address')),
                TextColumn::make('budget')->numeric()->label(__('backend.budget')),
                TextColumn::make('currency')->label(__('backend.currency')),
                TextColumn::make('status')->formatStateUsing(fn(string $state): string => match($state){
                    'pending' => __('backend.pending'),
                    'processing' => __('backend.processing'),
                    'completed' => __('backend.completed'),
                    'cancelled' => __('backend.cancelled'),
                })->badge(true)->color(fn(string $state): string => match($state){
                    'pending' => 'warning',
                    'processing' => 'primary',
                    'completed' => 'success',
                    'cancelled' => 'danger',
                })->label(__('backend.status')),
            ])
            ->emptyStateHeading(__('backend.not_found').' '.__('backend.projects'))
            ->actions([
                /*Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])*/
            ])
            /*->filters([
                SelectFilter::make('company')->relationship('company', 'name')->label('Company'),
                SelectFilter::make('storage')->relationship('storage', 'name')->label('Storage'),
                SelectFilter::make('status')->options([
                    'pending' => 'pending',
                    'processing' => 'processing',
                    'completed' => 'completed',
                    'cancelled' => 'cancelled',
                ]),
            ])*/;
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('backend.latest_projects');
    }
}
