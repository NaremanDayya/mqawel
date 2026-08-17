<?php

namespace App\Filament\Resources\WorkerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectsRelationManager extends RelationManager
{
    protected static string $relationship = 'projects';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('index')->rowIndex()->label(__('backend.row_number')),
                TextColumn::make('project.name')->label(__('backend.project')),
                TextColumn::make('role')->label(__('backend.role')),
                TextColumn::make('project.address')->label(__('backend.address')),
                TextColumn::make('project.budget')->label(__('backend.budget')),
                //TextColumn::make('project.currency')->label(__('backend.currency')),
                TextColumn::make('project.status')->formatStateUsing(fn(string $state): string => match($state){
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
            ->filters([
                //
            ])
            ->headerActions([
                //Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                /*Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])*/
            ])
            ->bulkActions([
                /*Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),*/
            ]);
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('backend.projects');
    }

    public static function getModelLabel(): string
    {
        return __('backend.projects');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('backend.projects');
    }
}
