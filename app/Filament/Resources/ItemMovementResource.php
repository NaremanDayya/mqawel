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
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
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

    public static function typeOptions(): array
    {
        return [
            'in' => __('backend.in_to_storage'),
            'out' => __('backend.out_from_storage'),
            'transfer' => __('backend.transfer_between_projects'),
            'adjust' => __('backend.adjust_storage'),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('company_id')->default(Auth::user()->company_id),

                Hidden::make('created_by')->default(Auth::user()->id),

                Group::make()->schema([
                    Section::make()->schema([
                        Select::make('item')->relationship('item', 'name', function($query){
                            $query->where('company_id', Auth::user()->company_id);
                        })->required()->searchable()->label(__('backend.item')),

                        Select::make('type')
                            ->options(self::typeOptions())
                            ->required()
                            ->live()
                            ->label(__('backend.type')),

                        Select::make('project')->relationship('project', 'name', function($query){
                            $query->where('company_id', Auth::user()->company_id);
                        })->searchable()->label(__('backend.project')),

                        Select::make('to_project')->relationship('toProject', 'name', function($query){
                            $query->where('company_id', Auth::user()->company_id);
                        })
                            ->searchable()
                            ->label(__('backend.to_project'))
                            ->visible(fn (Get $get) => $get('type') === 'transfer')
                            ->required(fn (Get $get) => $get('type') === 'transfer'),
                    ])->columns(2),

                    Section::make()->schema([
                        TextInput::make('quantity')->numeric()->required()->label(__('backend.quantity')),
                        TextInput::make('previous_storage_quantity')->numeric()->label(__('backend.previous_storage_quantity')),
                        TextInput::make('new_storage_quantity')->numeric()->label(__('backend.new_storage_quantity')),
                        DatePicker::make('movement_date')->default(now())->label(__('backend.movement_date')),
                    ])->columns(2),
                ])->columnSpanFull(),

                Group::make()->schema([
                    Section::make()->schema([
                        MarkdownEditor::make('notes')->columnSpanFull()->label(__('backend.notes')),
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
            ->defaultSort('movement_date', 'desc')
            ->columns([
                TextColumn::make('index')->rowIndex()->label(__('backend.row_number')),
                TextColumn::make('item.name')
                    ->description(fn (ItemMovement $record): ?string => $record->item?->category?->name)
                    ->searchable()
                    ->label(__('backend.item')),
                TextColumn::make('type')
                    ->formatStateUsing(fn (?string $state): string => self::typeOptions()[$state] ?? '—')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'in' => 'info',
                        'out' => 'warning',
                        'transfer' => 'primary',
                        default => 'gray',
                    })
                    ->sortable()
                    ->label(__('backend.type')),
                TextColumn::make('from_to')
                    ->label(__('backend.from_to'))
                    ->state(function (ItemMovement $record): string {
                        $from = $record->project?->name ?? '—';
                        $to = $record->type === 'transfer' ? ($record->toProject?->name ?? '—') : ($record->project?->name ?? '—');

                        return $record->type === 'out'
                            ? "{$from} ← —"
                            : ($record->type === 'in' ? "— ← {$to}" : "{$from} ← {$to}");
                    }),
                TextColumn::make('quantity')
                    ->formatStateUsing(fn (?string $state, ItemMovement $record): string => match ($record->type) {
                        'in' => '+'.number_format((float) $state, 2),
                        'out' => '-'.number_format((float) $state, 2),
                        default => number_format((float) $state, 2),
                    })
                    ->color(fn (ItemMovement $record): string => match ($record->type) {
                        'in' => 'success',
                        'out' => 'danger',
                        default => 'gray',
                    })
                    ->weight('bold')
                    ->sortable()
                    ->label(__('backend.quantity')),
                TextColumn::make('item.status')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'new' => __('backend.new'),
                        'used' => __('backend.used'),
                        'damaged' => __('backend.damaged'),
                        default => '—',
                    })
                    ->badge()
                    ->label(__('backend.item_status')),
                TextColumn::make('createdBy.name')
                    ->default('—')
                    ->label(__('backend.executed_by')),
                TextColumn::make('movement_date')
                    ->date()
                    ->sortable()
                    ->label(__('backend.date')),
            ])
            ->actionsColumnLabel(__('backend.actions'))
            ->filters([
                Filter::make('movement_date')
                    ->form([
                        DatePicker::make('from')->label(__('backend.from_date')),
                        DatePicker::make('until')->label(__('backend.to_date')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('movement_date', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('movement_date', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = __('backend.from_date').': '.$data['from'];
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = __('backend.to_date').': '.$data['until'];
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton(),
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
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
        return __('backend.updates');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('backend.storages');
    }

    public static function getBreadcrumb(): string
    {
        return __('backend.updates');
    }

    public static function getModelLabel(): string
    {
        return __('backend.updates');
    }

    public static function getPluralLabel(): ?string
    {
        return __('backend.updates');
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
        $Role= Auth::user()->role;
        return $Role->can_write_item_movements;
    }

    public static function canEdit(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_edit_item_movements;
    }

    public static function canDelete(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_edit_item_movements;
    }
}
