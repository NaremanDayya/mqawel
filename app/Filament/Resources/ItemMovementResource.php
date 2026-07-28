<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemMovementResource\Pages;
use App\Filament\Resources\ItemMovementResource\RelationManagers;
use App\Models\ItemMovement;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ItemMovementResource extends Resource
{
    protected static ?string $model = ItemMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup= 'Storages';

    protected static ?int $navigationSort= 8;

    protected static ?string $navigationLabel= 'Item movements';

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
                        /*Select::make('storage')->relationship('storage', 'name', function($query){
                            $query->where('company_id', Auth::user()->company_id);
                        })->label(__('backend.storage')),*/
                        Select::make('project')->relationship('project', 'name', function($query){
                            $query->where('company_id', Auth::user()->company_id);
                        })->label(__('backend.project')),
                        Select::make('item')->relationship('item', 'name', function($query){
                            $query->where('company_id', Auth::user()->company_id);
                        })->label(__('backend.item')),
                        /*Select::make('type')->options([
                            'in' => __('backend.in_to_storage'),
                            'out' => __('backend.out_from_storage'),
                            'adjust' => __('backend.adjust_storage'),
                        ])->label(__('backend.type')),*/
                        //TextInput::make('address')->columnSpanFull()->label(__('backend.from_to_address')),
                    ])->columns(2),

                    Section::make()->schema([
                        TextInput::make('quantity')->numeric()->required()->label(__('backend.quantity')),
                        TextInput::make('previous_storage_quantity')->numeric()->label(__('backend.previous_storage_quantity')),
                        TextInput::make('new_storage_quantity')->numeric()->label(__('backend.new_storage_quantity')),
                        DatePicker::make('movement_date')->label(__('backend.movement_date')),
                    ])->columns(2),
                ])->columnSpanFull(),

                /*Group::make()->schema([
                    Section::make()->schema([
                        MarkdownEditor::make('notes')->columnSpanFull()->label(__('backend.notes')),
                    ]),
                ])*/
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function($query){
                $query->where('company_id', Auth::user()->company_id)->where('company_id', '<>', null);
            })
            ->columns([
                TextColumn::make('item.name')->label(__('backend.item')),
                TextColumn::make('type')->formatStateUsing(fn(string $state): string => match($state){
                    'in' => __('backend.in_to_storage'),
                    'out' => __('backend.out_from_storage'),
                    'adjust' => __('backend.adjust_storage'),
                })->badge()->label(__('backend.type')),
                //TextColumn::make('storage.name')->label(__('backend.storage'))->default('-- --'),
                TextColumn::make('project.name')->label(__('backend.project'))->default('-- --'),
                TextColumn::make('quantity')->label(__('backend.quantity')),
                TextColumn::make('movement_date')->label(__('backend.date')),
            ])
            ->filters([
                //
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItemMovements::route('/'),
            'create' => Pages\CreateItemMovement::route('/create'),
            'edit' => Pages\EditItemMovement::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationLabel(): string
    {
        return __('backend.movements');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('backend.storages');
    }

    public static function getBreadcrumb(): string
    {
        return __('backend.movements');
    }

    public static function getModelLabel(): string
    {
        return __('backend.movements');
    }

    public static function getPluralLabel(): ?string
    {
        return __('backend.movements');
    }

    public static function canAccess(): bool
    {
        $Subscription= Subscription::where(['company_id' => Auth::user()->company_id, 'is_active' => 1])->orderBy('id', 'desc')->first();

        if(!$Subscription || !$Subscription->package) return false;
        
        //if(!$Subscription->package->has_item_categories) return false;
        
        if(!$Subscription->package->has_item_movements) return false;

        $Role= Auth::user()->role;
        return $Role->can_read_item_movements == true || $Role->can_write_item_movements == true || $Role->can_edit_item_movements == true;
    }

    public static function canView(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_read_item_movements;
    }

    public static function canCreate(): bool
    {
        /*$Role= Auth::user()->role;
        return $Role->can_write_item_movements;*/
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        /*$Role= Auth::user()->role;
        return $Role->can_edit_item_movements;*/
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_edit_item_movements;
    }
}
