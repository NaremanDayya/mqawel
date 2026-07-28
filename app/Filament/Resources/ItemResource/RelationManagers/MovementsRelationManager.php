<?php

namespace App\Filament\Resources\ItemResource\RelationManagers;

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

class MovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'Movements';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('company_id')->default(Auth::user()->company_id),

                Hidden::make('created_by')->default(Auth::user()->id),

                Group::make()->schema([
                    Section::make()->schema([
                        Select::make('storage')->relationship('storage', 'name', function($query){
                            $query->where('company_id', Auth::user()->company_id);
                        })->label(__('backend.storage')),
                        Select::make('project')->relationship('project', 'name', function($query){
                            $query->where('company_id', Auth::user()->company_id);
                        })->label(__('backend.project')),
                        Select::make('type')->options([
                            'in' => __('backend.in_to_storage'),
                            'out' => __('backend.out_from_storage'),
                            'adjust' => __('backend.adjust_storage'),
                        ])->label(__('backend.type')),
                        TextInput::make('address')->label(__('backend.from_to_address')),
                    ])->columns(2),

                    Section::make()->schema([
                        TextInput::make('quantity')->numeric()->required()->label(__('backend.quantity')),
                        DatePicker::make('movement_date')->label(__('backend.movement_date')),
                    ])->columns(2),
                ]),

                Group::make()->schema([
                    Section::make()->schema([
                        MarkdownEditor::make('notes')->columnSpanFull()->label(__('backend.notes')),
                    ]),
                ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('address')
            ->columns([
                TextColumn::make('item.name')->label(__('backend.name')),
                TextColumn::make('storage.name')->label(__('backend.storage')),
                TextColumn::make('project.name')->label(__('backend.project')),
                TextColumn::make('type')->formatStateUsing(fn(string $state): string => match($state){
                    'in' => __('backend.in_to_storage'),
                    'out' => __('backend.out_from_storage'),
                    'adjust' => __('backend.adjust_storage'),
                })->badge()->label(__('backend.type')),
                TextColumn::make('quantity')->label(__('backend.quantity')),
                TextColumn::make('movement_date')->label(__('backend.date')),
            ])
            ->emptyStateHeading(__('backend.not_found').' '.__('backend.movements'))
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
        return __('backend.movements');
    }

    public static function getModelLabel(): string
    {
        return __('backend.movements');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('backend.movements');
    }

    protected function canCreate(): bool
    {
        return false;
    }
}
