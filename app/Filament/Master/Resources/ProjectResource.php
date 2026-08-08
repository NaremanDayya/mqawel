<?php

namespace App\Filament\Master\Resources;

use App\Filament\Master\Resources\ProjectResource\Pages;
use App\Filament\Master\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'address'];
    }

    protected static ?string $navigationGroup= 'companies';

    protected static ?int $navigationSort= 2;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() > 0 ? static::getModel()::count() : '';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('master_id')->default(Auth::user()->id),

                Group::make()->schema([
                    Section::make()->schema([
                        TextInput::make('name')->required()->label(__('backend.name')),

                        Select::make('company')->relationship('company', 'name')->label(__('backend.company')),

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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')->rowIndex()->label(__('backend.row_number')),
                TextColumn::make('name')->searchable()->sortable()->label(__('backend.name')),
                TextColumn::make('company.name')->searchable()->sortable()->label(__('backend.company')),
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
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getNavigationLabel(): string
    {
        return __('backend.projects');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('backend.companies');
    }

    public static function getBreadcrumb(): string
    {
        return __('backend.projects');
    }

    public static function getModelLabel(): string
    {
        return __('backend.projects');
    }

    public static function getPluralLabel(): ?string
    {
        return __('backend.projects');
    }
}
