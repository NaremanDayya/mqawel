<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StorageItemCategoryResource\Pages;
use App\Filament\Resources\StorageItemCategoryResource\RelationManagers;
use App\Filament\Resources\StorageItemCategoryResource\RelationManagers\ItemsRelationManager;
use App\Models\ItemCategory;
use App\Models\StorageItemCategory;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
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

class ItemCategoryResource extends Resource
{
    protected static ?string $model = ItemCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3-bottom-left';

    protected static ?string $navigationGroup= 'Storages';

    protected static ?int $navigationSort= 6;

    protected static ?string $navigationLabel= 'Categories';

    /*public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('company_id', Auth::user()->company_id)->count() > 0 ? static::getModel()::where('company_id', Auth::user()->company_id)->count() : '';
    }*/

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('company_id')->default(Auth::user()->company_id),

                Hidden::make('created_by')->default(Auth::user()->id),

                Group::make()->schema([
                    Section::make()->schema([
                        TextInput::make('name')->columnSpanFull()->required()->label(__('backend.name')),
                    ]),

                    Section::make()->schema([
                        Toggle::make('is_active')->default(true)->label(__('backend.active')),
                    ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function($query){
                $query->where('company_id', Auth::user()->company_id)->where('company_id', '<>', null);
            })
            ->columns([
                TextColumn::make('index')->rowIndex()->label(__('backend.row_number')),
                TextColumn::make('name')->searchable()->sortable()->label(__('backend.name')),
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
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStorageItemCategories::route('/'),
            'create' => Pages\CreateStorageItemCategory::route('/create'),
            'edit' => Pages\EditStorageItemCategory::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('backend.categories');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('backend.storages');
    }

    public static function getBreadcrumb(): string
    {
        return __('backend.categories');
    }

    public static function getModelLabel(): string
    {
        return __('backend.categories');
    }

    public static function getPluralLabel(): ?string
    {
        return __('backend.categories');
    }

    public static function canAccess(): bool
    {
        $Subscription= Subscription::where(['company_id' => Auth::user()->company_id, 'is_active' => 1])->orderBy('id', 'desc')->first();

        if(!$Subscription || !$Subscription->package) return false;
        
        if(!$Subscription->package->has_item_categories) return false;

        $Role= Auth::user()->role;
        return $Role->can_read_item_categories == true || $Role->can_write_item_categories == true || $Role->can_edit_item_categories == true;
    }

    public static function canView(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_read_item_categories;
    }

    public static function canCreate(): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_write_item_categories;
    }

    public static function canEdit(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_edit_item_categories;
    }

    public static function canDelete(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_edit_item_categories;
    }
}
