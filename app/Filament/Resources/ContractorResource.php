<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractorResource\Pages;
use App\Filament\Resources\ContractorResource\RelationManagers;
use App\Filament\Resources\ContractorResource\RelationManagers\FilesRelationManager;
use App\Models\Contractor;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ContractorResource extends Resource
{
    protected static ?string $model = Contractor::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'phone'];
    }

    protected static ?string $navigationGroup= 'Entities';

    protected static ?int $navigationSort= 5;

    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('company_id', Auth::user()->company_id)->count() > 0 ? static::getModel()::where('company_id', Auth::user()->company_id)->count() : '';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('company_id')->default(Auth::user()->company_id),

                Hidden::make('created_by')->default(Auth::user()->id),

                Group::make()->schema([
                    Section::make(__('backend.information'))->icon('heroicon-o-identification')->schema([
                        TextInput::make('name')->required()->label(__('backend.name')),

                        TextInput::make('type')->required()->label(__('backend.type')),

                        TextInput::make('phone')->label(__('backend.phone')),

                        TextInput::make('email')->label(__('backend.email')),

                        TextInput::make('fax')->label(__('backend.fax')),

                        TextInput::make('website')->label(__('backend.website')),

                        TextInput::make('address')->label(__('backend.address')),

                        TextInput::make('registry_number')->required()->label(__('backend.registry_number')),

                        TextInput::make('tax_number')->required()->label(__('backend.tax_number')),

                        TextInput::make('category')->required()->label(__('backend.category')),
                    ])->columns(2),

                    Section::make()->schema([
                        MarkdownEditor::make('fields')->columnSpanFull()->label(__('backend.fields')),
                    ])->columns(2),
                ]),

                Group::make()->schema([
                    Section::make()->schema([
                        TextInput::make('years_of_experience')->numeric()->label(__('backend.years_of_experience')),

                        TextInput::make('number_of_projects')->numeric()->label(__('backend.number_of_projects')),

                        MarkdownEditor::make('areas')->columnSpanFull()->label(__('backend.areas')),
                    ])->columns(2),

                    Section::make()->schema([
                        Toggle::make('is_active')->default(true)->label(__('backend.active')),
                    ])
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function($query){
                $query->where('company_id', Auth::user()->company_id)->where('company_id', '<>', null);
            })
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->label(__('backend.name')),

                TextColumn::make('type')->searchable()->sortable()->label(__('backend.type')),

                TextColumn::make('category')->searchable()->sortable()->label(__('backend.category')),

                TextColumn::make('years_of_experience')->numeric()->searchable()->sortable()->label(__('backend.experience')),

                TextColumn::make('number_of_projects')->numeric()->searchable()->sortable()->label(__('backend.projects')),

                IconColumn::make('is_active')->boolean()->label(__('backend.active')),
            ])
            ->filters([
                TernaryFilter::make('is_active')->boolean()->trueLabel(__('backend.active'))->falseLabel(__('backend.inactive'))->native(false)->label(__('backend.active')),
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

    public static function getRelations(): array
    {
        return [
            FilesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContractors::route('/'),
            'create' => Pages\CreateContractor::route('/create'),
            'edit' => Pages\EditContractor::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('backend.contractors');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('backend.entities');
    }

    public static function getBreadcrumb(): string
    {
        return __('backend.contractors');
    }

    public static function getModelLabel(): string
    {
        return __('backend.contractors');
    }

    public static function getPluralLabel(): ?string
    {
        return __('backend.contractors');
    }

    public static function canAccess(): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_read_contractors == true || $Role->can_write_contractors == true || $Role->can_edit_contractors == true;
    }

    public static function canView(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_read_contractors;
    }

    public static function canCreate(): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_write_contractors;
    }

    public static function canEdit(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_edit_contractors;
    }

    public static function canDelete(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_edit_contractors;
    }
}
