<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\RelationManagers\DamagesRelationManager;
use App\Filament\Resources\ItemResource\RelationManagers\FilesRelationManager;
use App\Filament\Resources\ItemResource\RelationManagers\MovementsRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\StorageItemResource\Pages;
use App\Filament\Resources\StorageItemResource\RelationManagers;
use App\Models\Item;
use App\Models\StorageItem;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup= 'Storages';

    protected static ?int $navigationSort= 5;

    protected static ?string $navigationLabel= 'Items';

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
                        TextInput::make('name')/*->unique(ignoreRecord:true, modifyRuleUsing: function($rule, $context, $record){
                            return $rule->where('company_id', Auth::user()->company_id);
                        })->validationMessages([
                            'unique' => __('backend.unavailable_to_use'),
                        ])*/->required()->label(__('backend.name')),
                        
                        TextInput::make('quantity')->required()->label(__('backend.quantity')),

                        MarkdownEditor::make('description')->columnSpan('full')->label(__('backend.description')),
                    ])->columns(2),

                    Section::make(__('backend.status'))->icon('heroicon-o-flag')->schema([
                        Select::make('status')->options([
                            'new' => __('backend.new'),
                            'used' => __('backend.used'),
                            'damaged' => __('backend.damaged'),
                        ])->required()->label(__('backend.status')),

                        Toggle::make('is_active')->default(true)->label(__('backend.active')),
                    ]),
                ]),

                Group::make()->schema([
                    Section::make()->schema([
                        /*Select::make('storage')->relationship('storage', 'name', function($query){
                            $query->where('company_id', Auth::user()->company_id);
                        })->label(__('backend.storage')),*/

                        Select::make('category')->relationship('category', 'name', function($query){
                            $query->where('company_id', Auth::user()->company_id);
                        })->label(__('backend.category')),

                        Select::make('project')->relationship('project', 'name', function($query){
                            $query->where('company_id', Auth::user()->company_id);
                        })->label(__('backend.project')),
                    ])->columns(2)->hiddenOn(ItemsRelationManager::class),

                    Section::make()->schema([
                        FileUpload::make('picture')->directory('form-attachments')->image()->imageEditor()->label(__('backend.picture'))
                    ])->collapsible(),
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
                ImageColumn::make('picture')->defaultImageUrl(asset('images/no_profile_picture.png'))->circular()->label(''),
                TextColumn::make('name')->searchable()->sortable()->label(__('backend.name')),
                TextColumn::make('project.name')->searchable()->sortable()->default('----')->label(__('backend.project')),
                //TextColumn::make('storage.name')->searchable()->sortable()->default('----')->label(__('backend.storage')),
                //TextColumn::make('category.name')->searchable()->sortable()->default('----')->label(__('backend.category')),
                TextColumn::make('quantity')->searchable()->sortable()->label(__('backend.quantity')),
                TextColumn::make('status')->formatStateUsing(fn(string $state): string => match($state){
                    'new' => __('backend.new'),
                    'used' => __('backend.used'),
                    'damaged' => __('backend.damaged'),
                })->badge()->sortable()->searchable()->label(__('backend.status')),
                IconColumn::make('is_active')->boolean()->label(__('backend.active')),
            ])
            ->emptyStateHeading(__('backend.not_found').' '.__('backend.items'))
            ->filters([
                SelectFilter::make('category')->relationship('category', 'name')->label(__('backend.category')),
                SelectFilter::make('storage')->relationship('storage', 'name')->label(__('backend.storage')),
                SelectFilter::make('status')->options([
                    'new' => __('backend.new'),
                    'used' => __('backend.used'),
                    'damaged' => __('backend.damaged'),
                ])->label(__('backend.status')),
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
            //MovementsRelationManager::class,
            DamagesRelationManager::class,
            FilesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStorageItems::route('/'),
            'create' => Pages\CreateStorageItem::route('/create'),
            'edit' => Pages\EditStorageItem::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('backend.main_storage');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('backend.storages');
    }

    public static function getBreadcrumb(): string
    {
        return __('backend.main_storage');
    }

    public static function getModelLabel(): string
    {
        return __('backend.items');
    }

    public static function getPluralLabel(): ?string
    {
        return __('backend.items');
    }

    public static function canAccess(): bool
    {
        $Subscription= Subscription::where(['company_id' => Auth::user()->company_id, 'is_active' => 1])->orderBy('id', 'desc')->first();

        if(!$Subscription || !$Subscription->package) return false;
        
        //if(!$Subscription->package->has_item_categories) return false;
        
        if(!$Subscription->package->has_items) return false;

        $Role= Auth::user()->role;
        return $Role->can_read_items == true || $Role->can_write_items == true || $Role->can_edit_items == true;
    }

    public static function canView(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_read_items;
    }

    public static function canCreate(): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_write_items;
    }

    public static function canEdit(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_edit_items;
    }

    public static function canDelete(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_edit_items;
    }
}
