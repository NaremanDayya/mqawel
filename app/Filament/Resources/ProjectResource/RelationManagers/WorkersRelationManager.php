<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class WorkersRelationManager extends RelationManager
{
    protected static string $relationship = 'workers';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('company_id')->default(Auth::user()->company_id),

                Hidden::make('created_by')->default(Auth::user()->id),
                
                Select::make('worker')->relationship('worker', 'name', function(Builder $query, RelationManager $livewire){
                    $project_id = $livewire->getOwnerRecord()->id;
                    $company_id = Auth::user()->company_id;
                    return $query->where('company_id', $company_id)
                    ->whereNotIn('id', function ($subQuery) use ($project_id) {
                        $subQuery->select('worker_id')
                            ->from('project_workers')
                            ->where('project_id', $project_id);
                    });
                })->required()->label(__('backend.worker')),

                DatePicker::make('date')
                    ->displayFormat('d/m/Y')
                    ->format('Y-m-d')
                    ->native(false)
                    //->locale('ar')
                    ->label(__('backend.date')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('worker.name')->label('Worker')->label(__('backend.worker')),
                TextColumn::make('date')->date()->label(__('backend.date')),
            ])->emptyStateHeading(__('backend.not_found').' '.__('backend.workers'))
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('backend.workers');
    }

    public static function getModelLabel(): string
    {
        return __('backend.workers');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('backend.workers');
    }
}
