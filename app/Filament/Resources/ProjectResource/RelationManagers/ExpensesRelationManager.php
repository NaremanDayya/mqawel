<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
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

class ExpensesRelationManager extends RelationManager
{
    protected static string $relationship = 'expenses';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('company_id')->default(Auth::user()->company_id),

                Hidden::make('created_by')->default(Auth::user()->id),

                TextInput::make('title')->required()->label(__('backend.title')),

                DatePicker::make('date')->label(__('backend.date')),

                TextInput::make('amount')->numeric()->required()->label(__('backend.amount')),

                //TextInput::make('currency')->required()->label(__('backend.currency')),

                MarkdownEditor::make('description')->columnSpanFull()->label(__('backend.description')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('index')->rowIndex()->label(__('backend.row_number')),

                TextColumn::make('title')->label(__('backend.title')),

                TextColumn::make('amount')->numeric()->label(__('backend.amount')),

                //TextColumn::make('currency')->label(__('backend.currency')),

                TextColumn::make('date')->date()->label(__('backend.date')),
            ])
            ->emptyStateHeading(__('backend.not_found').' '.__('backend.expenses'))
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
        return __('backend.expenses');
    }

    public static function getModelLabel(): string
    {
        return __('backend.expenses');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('backend.expenses');
    }
}
