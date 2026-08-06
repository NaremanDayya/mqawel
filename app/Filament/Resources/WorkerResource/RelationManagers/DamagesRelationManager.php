<?php

namespace App\Filament\Resources\WorkerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
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

class DamagesRelationManager extends RelationManager
{
    protected static string $relationship = 'Damages';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('company_id')->default(Auth::user()->company_id),
                Hidden::make('created_by')->default(Auth::user()->id),
                Group::make()->schema([
                    Section::make()->schema([
                        Select::make('storage')->relationship('storage', 'name')->label(__('backend.storage')),
                        Select::make('item')->relationship('item', 'name')->label(__('backend.item')),
                        TextInput::make('quantity')->numeric()->label(__('backend.quantity')),
                        DatePicker::make('damage_date')->label(__('backend.mistake_date')),
                        TextInput::make('reason')->label(__('backend.mistake_reason')),
                        TextInput::make('location')->label(__('backend.location')),
                    ]),
                ]),
                Group::make()->schema([
                    Section::make()->schema([
                        Select::make('responsible')->relationship('responsible', 'name')->label(__('backend.responsible')),
                        MarkdownEditor::make('notes')->label(__('backend.notes')),
                    ])
                ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('notes')
            ->columns([
                TextColumn::make('item.name')->label(__('backend.item')),
                TextColumn::make('storage.name')->label(__('backend.storage')),
                TextColumn::make('responsible.name')->label(__('backend.responsible')),
                TextColumn::make('quantity')->numeric()->label(__('backend.quantity')),
                TextColumn::make('reason')->label(__('backend.mistake_reason')),
                TextColumn::make('location')->label(__('backend.location')),
                TextColumn::make('damage_date')->date()->label(__('backend.mistake_date')),
            ])
            ->emptyStateHeading(__('backend.not_found').' '.__('backend.mistakes'))
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label(__('backend.add_more')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('backend.mistakes');
    }

    public static function getModelLabel(): string
    {
        return __('backend.mistakes');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('backend.mistakes');
    }
}
