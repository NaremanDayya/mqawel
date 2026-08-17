<?php

namespace App\Filament\Resources\StorageItemCategoryResource\RelationManagers;

use App\Models\ItemMovement;
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
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
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
        return $form
            ->schema([
                Hidden::make('company_id')->default(Auth::user()->company_od),

                Hidden::make('created_by')->default(Auth::user()->id),

                Group::make()->schema([
                    Section::make()->schema([
                        TextInput::make('name')->unique(ignoreRecord:true, modifyRuleUsing: function($rule, $context, $record){
                            return $rule->where('company_id', Auth::user()->company_id);
                        })->validationMessages([
                            'unique' => __('backend.unavailable_to_use'),
                        ])->required()->label(__('backend.name')),
                        
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
                        Select::make('storage')->relationship('storage', 'name', function($query){
                            $query->where('company_id', Auth::user()->company_id);
                        })->label(__('backend.storage')),
                    ])->columns(1),

                    Section::make()->schema([
                        FileUpload::make('picture')->directory('form-attachments')->image()->imageEditor()->label(__('backend.picture'))
                    ])->collapsible(),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('index')->rowIndex()->label(__('backend.row_number')),
                ImageColumn::make('picture')->defaultImageUrl(asset('images/no_profile_picture.png'))->circular()->label(''),
                TextColumn::make('name')->searchable()->sortable()->label(__('backend.name')),
                TextColumn::make('storage.name')->searchable()->sortable()->label(__('backend.storage')),
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
                    $Movement->storage_id= $record->storage_id;
                    $Movement->project_id= null;
                    $Movement->item_id= $record->id;
                    $Movement->type= 'in';
                    $Movement->quantity= $record->quantity;
                    $Movement->previous_storage_quantity= $previous_storage_quantity;
                    $Movement->new_storage_quantity= $new_storage_quantity;
                    $Movement->movement_date= date('Y-m-d', strtotime($record->created_at));
                    $Movement->notes= null;
                    $Movement->address= $record->storage ? $record->storage->name : null;
                    $Movement->created_by= Auth::user()->id;
                    $Movement->save();

                    Notification::make()->title(__('backend.item_movement_success_message'))->success()->send();
                }),
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
