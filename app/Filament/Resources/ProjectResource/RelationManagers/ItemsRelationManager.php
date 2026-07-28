<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Filament\Resources\ItemResource;
use App\Models\ItemMovement;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return ItemResource::form($form);
        /*return $form
            ->schema([
                Hidden::make('company_id')->default(Auth::user()->company_id),

                Hidden::make('created_by')->default(Auth::user()->id),

                Select::make('item')->relationship('item', 'name', function($query){
                    $query->where('company_id', Auth::user()->company_id);
                })->required()->label(__('backend.item')),

                TextInput::make('quantity')->required()->label(__('backend.quantity')),

                DatePicker::make('date')
                    ->displayFormat('d/m/Y') // Defines how the date appears in the box
                    ->format('Y-m-d')        // Defines how it's stored in the database
                    ->native(false)          // Disables browser native picker to fix reversal
                    ->locale('ar')
                    ->label(__('backend.date')),
            ]);*/
    }

    public function table(Table $table): Table
    {
        return ItemResource::table($table)->headerActions([
            Tables\Actions\CreateAction::make()->after(function($record){
                $previous_storage_quantity= 0;
                $new_storage_quantity= 0;

                if($record->storage){
                    //must calculate previous and new storage quantities.
                }

                $Movement= new ItemMovement();
                $Movement->company_id= Auth::user()->company_id;
                //$Movement->storage_id= $record->storage_id;
                $Movement->project_id= $record->project_id;
                $Movement->item_id= $record->id;
                $Movement->type= 'in';
                $Movement->quantity= $record->quantity;
                $Movement->previous_storage_quantity= $previous_storage_quantity;
                $Movement->new_storage_quantity= $new_storage_quantity;
                $Movement->movement_date= date('Y-m-d', strtotime($record->created_at));
                $Movement->notes= null;
                //$Movement->address= $record->storage ? $record->storage->name : null;
                $Movement->created_by= Auth::user()->id;
                $Movement->save();

                Notification::make()->title(__('backend.item_movement_success_message'))->success()->send();
            }),
        ]);
        /*return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('item.name')->label(__('backend.item')),

                TextColumn::make('quantity')->label(__('backend.quantity')),

                TextColumn::make('date')->date()->label(__('backend.date')),
            ])
            ->emptyStateHeading(__('backend.not_found').' '.__('backend.items'))
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->after(function($record){
                    $previous_storage_quantity= 0;
                    $new_storage_quantity= 0;

                    if($record->storage){
                        //must calculate previous and new storage quantities.
                    }

                    $Movement= new ItemMovement();
                    $Movement->company_id= Auth::user()->company_id;
                    $Movement->storage_id= $record->item && $record->item->storage ? $record->item->storage->id : null;
                    $Movement->project_id= $record->project_id;
                    $Movement->item_id= $record->item_id;
                    $Movement->type= 'out';
                    $Movement->quantity= $record->quantity;
                    $Movement->previous_storage_quantity= $previous_storage_quantity;
                    $Movement->new_storage_quantity= $new_storage_quantity;
                    $Movement->movement_date= $record->date;
                    $Movement->notes= null;
                    $Movement->address= $record->project ? $record->project->address : null;
                    $Movement->created_by= Auth::user()->id;
                    $Movement->save();

                    Notification::make()->title(__('backend.item_movement_success_message'))->success()->send();
                })->createAnother(false),
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
            ]);*/
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('backend.items');
    }

    public static function getModelLabel(): string
    {
        return __('backend.items');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('backend.items');
    }
}
