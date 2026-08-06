<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\RelationManagers;
use App\Models\CompanyRole;
use App\Models\Role;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
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

class RoleResource extends Resource
{
    protected static ?string $model = CompanyRole::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-vertical';

    protected static ?int $navigationSort= 3;

    /*public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('company_id', Auth::user()->company_id)->count() > 0 ? static::getModel()::where('company_id', Auth::user()->company_id)->count() : '';
    }*/

    public static function form(Form $form): Form
    {
        $Subscription= Subscription::where(['company_id' => Auth::user()->company_id, 'is_active' => 1])->orderBy('id', 'desc')->first();

        return $form
            ->schema([
                Hidden::make('company_id')->default(Auth::user()->company_id),
                Hidden::make('created_by')->default(Auth::user()->id),

                Group::make()->schema([
                    Section::make()->schema([
                        TextInput::make('name')->required()->label(__('backend.name')),
                    ]),

                    Section::make(__('backend.users'))->icon('heroicon-o-users')->schema([
                        Checkbox::make('can_read_users')->label(__('backend.read')),
                        Checkbox::make('can_write_users')->label(__('backend.write')),
                        Checkbox::make('can_edit_users')->label(__('backend.edit')),
                    ])->columns(3)->collapsible(true),

                    Section::make(__('backend.workers'))->icon('heroicon-o-user-group')->schema([
                        Checkbox::make('can_read_workers')->label(__('backend.read')),
                        Checkbox::make('can_write_workers')->label(__('backend.write')),
                        Checkbox::make('can_edit_workers')->label(__('backend.edit')),
                    ])->columns(3)->collapsible(true)->hidden(!$Subscription->package->has_workers),

                    Section::make(__('backend.projects'))->icon('heroicon-o-map-pin')->schema([
                        Checkbox::make('can_read_projects')->label(__('backend.read')),
                        Checkbox::make('can_write_projects')->label(__('backend.write')),
                        Checkbox::make('can_edit_projects')->label(__('backend.edit')),
                    ])->columns(3)->collapsible(true)->hidden(!$Subscription->package->has_projects),

                    Section::make(__('backend.storages'))->icon('heroicon-o-archive-box')->schema([
                        Checkbox::make('can_read_storages')->label(__('backend.read')),
                        Checkbox::make('can_write_storages')->label(__('backend.write')),
                        Checkbox::make('can_edit_storages')->label(__('backend.edit')),
                    ])->columns(3)->collapsible(true)->hidden(!$Subscription->package->has_storages),

                    Section::make(__('backend.items'))->icon('heroicon-o-wrench-screwdriver')->schema([
                        Checkbox::make('can_read_items')->label(__('backend.read')),
                        Checkbox::make('can_write_items')->label(__('backend.write')),
                        Checkbox::make('can_edit_items')->label(__('backend.edit')),
                    ])->columns(3)->collapsible(true)->hidden(!$Subscription->package->has_items),

                    Section::make(__('backend.item_categories'))->icon('heroicon-o-bars-3-bottom-left')->schema([
                        Checkbox::make('can_read_item_categories')->label(__('backend.read')),
                        Checkbox::make('can_write_item_categories')->label(__('backend.write')),
                        Checkbox::make('can_edit_item_categories')->label(__('backend.edit')),
                    ])->columns(3)->collapsible(true)->hidden(!$Subscription->package->has_item_categories),

                    Section::make(__('backend.item_movements'))->icon('heroicon-o-arrows-right-left')->schema([
                        Checkbox::make('can_read_item_movements')->label(__('backend.read')),
                        Checkbox::make('can_write_item_movements')->label(__('backend.write')),
                        Checkbox::make('can_edit_item_movements')->label(__('backend.edit')),
                    ])->columns(3)->collapsible(true)->hidden(!$Subscription->package->has_item_movements),
                ]),

                Group::make()->schema([
                    /*Section::make(__('backend.contractors'))->schema([
                        Checkbox::make('can_read_contractors')->label(__('backend.read')),
                        Checkbox::make('can_write_contractors')->label(__('backend.write')),
                        Checkbox::make('can_edit_contractors')->label(__('backend.edit')),
                    ])->columns(3)->collapsible(true),*/

                    Section::make(__('backend.workers_report'))->icon('heroicon-o-chart-bar')->schema([
                        Checkbox::make('can_read_workers_report')->label(__('backend.read')),
                        Checkbox::make('can_write_workers_report')->label(__('backend.write')),
                        Checkbox::make('can_edit_workers_report')->label(__('backend.edit')),
                    ])->columns(3)->collapsible(true)->hidden(!$Subscription->package->has_workers_report),

                    Section::make(__('backend.worker_expenses_report'))->icon('heroicon-o-banknotes')->schema([
                        Checkbox::make('can_read_worker_expenses_report')->label(__('backend.read')),
                        Checkbox::make('can_write_worker_expenses_report')->label(__('backend.write')),
                        Checkbox::make('can_edit_worker_expenses_report')->label(__('backend.edit')),
                    ])->columns(3)->collapsible(true)->hidden(!$Subscription->package->has_worker_expenses_report),

                    Section::make(__('backend.expired_files_report'))->icon('heroicon-o-clock')->schema([
                        Checkbox::make('can_read_expired_files_report')->label(__('backend.read')),
                        Checkbox::make('can_write_expired_files_report')->label(__('backend.write')),
                        Checkbox::make('can_edit_expired_files_report')->label(__('backend.edit')),
                    ])->columns(3)->collapsible(true)->hidden(!$Subscription->package->has_expired_files_report),

                    Section::make(__('backend.project_expenses_report'))->icon('heroicon-o-currency-dollar')->schema([
                        Checkbox::make('can_read_project_expenses_report')->label(__('backend.read')),
                        Checkbox::make('can_write_project_expenses_report')->label(__('backend.write')),
                        Checkbox::make('can_edit_project_expenses_report')->label(__('backend.edit')),
                    ])->columns(3)->collapsible(true)->hidden(!$Subscription->package->has_project_expenses_report),

                    Section::make(__('backend.roles'))->icon('heroicon-o-shield-check')->schema([
                        Checkbox::make('can_read_roles')->label(__('backend.read')),
                        Checkbox::make('can_write_roles')->label(__('backend.write')),
                        Checkbox::make('can_edit_roles')->label(__('backend.edit')),
                    ])->columns(3)->collapsible(true),

                    Section::make(__('backend.document_creator'))->icon('heroicon-o-sparkles')->schema([
                        Checkbox::make('can_read_document_creator')->label(__('backend.read')),
                        Checkbox::make('can_write_document_creator')->label(__('backend.write')),
                        Checkbox::make('can_edit_document_creator')->label(__('backend.edit')),
                    ])->columns(3)->collapsible(true),
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
                TextColumn::make('name')->label(__('backend.name')),
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getNavigationLabel(): string
    {
        return __('backend.roles');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('backend.company');
    }

    public static function getBreadcrumb(): string
    {
        return __('backend.roles');
    }

    public static function getModelLabel(): string
    {
        return __('backend.roles');
    }

    public static function getPluralLabel(): ?string
    {
        return __('backend.roles');
    }

    public static function canAccess(): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_read_roles == true || $Role->can_write_roles == true || $Role->can_edit_roles == true;
    }

    public static function canView(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_read_roles;
    }

    public static function canCreate(): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_write_roles;
    }

    public static function canEdit(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_edit_roles;
    }

    public static function canDelete(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_edit_roles;
    }
}
