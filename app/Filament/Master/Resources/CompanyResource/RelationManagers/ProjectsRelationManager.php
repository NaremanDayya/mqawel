<?php

namespace App\Filament\Master\Resources\CompanyResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectsRelationManager extends RelationManager
{
    protected static string $relationship = 'Projects';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make()->schema([
                        TextInput::make('name')->required()->columnSpanFull()->label(__('backend.name')),

                        MarkdownEditor::make('description')->columnSpan('full')->label(__('backend.description')),
                    ])->columns(2),
                ]),

                Group::make()->schema([
                    Section::make()->schema([
                        TextInput::make('address')->label(__('backend.address')),
                        
                        TextInput::make('budget')->label(__('backend.budget')),

                        //TextInput::make('currency')->label(__('backend.currency')),
                    ])->columns(2),

                    Section::make()->schema([
                        Select::make('status')->options([
                            'pending' => __('backend.pending'),
                            'processing' => __('backend.processing'),
                            'completed' => __('backend.completed'),
                            'cancelled' => __('backend.cancelled'),
                        ])->required()->label(__('backend.status')),
                    ]),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('index')->rowIndex()->label(__('backend.row_number')),
                TextColumn::make('name')->searchable()->sortable()->label(__('backend.name')),
                TextColumn::make('address')->searchable()->sortable()->label(__('backend.address')),
                TextColumn::make('budget')->searchable()->sortable()->numeric()->label(__('backend.budget')),
                //TextColumn::make('currency')->searchable()->sortable()->label(__('backend.currency')),
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
                })->searchable()->sortable()->label(__('backend.status')),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => __('backend.pending'),
                    'processing' => __('backend.processing'),
                    'completed' => __('backend.completed'),
                    'cancelled' => __('backend.cancelled'),
                ])->label(__('backend.status')),
            ])
            ->headerActions([
                //Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
