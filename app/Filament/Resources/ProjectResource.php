<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Filament\Resources\ProjectResource\RelationManagers\ExpensesRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\FilesRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\StoragesRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\UsersRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\WorkersRelationManager;
use App\Models\Project;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
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

    protected static ?string $navigationGroup= 'Projects';

    protected static ?int $navigationSort= 3;

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
                        TextInput::make('name')->required()->columnSpanFull()->label(__('backend.name')),

                        //Select::make('storage')->relationship('storage', 'name')->label(__('backend.storage')),

                        MarkdownEditor::make('description')->columnSpan('full')->label(__('backend.description')),
                    ])->columns(2),
                ]),

                Group::make()->schema([
                    Section::make()->schema([
                        TextInput::make('address')->label(__('backend.location')),
                        
                        TextInput::make('budget')->label(__('backend.budget')),

                        //TextInput::make('currency')->label(__('backend.currency')),
                    ])->columns(2),

                    Section::make()->schema([
                        Select::make('status')->options([
                            'pending' => __('backend.pending'),
                            'processing' => __('backend.processing'),
                            'completed' => __('backend.completed'),
                            'cancelled' => __('backend.cancelled'),
                        ])->required()->label(__('backend.level')),

                        TextInput::make('completion_percentage')->numeric()->minValue(0)->maxValue(100)->suffix('%')->default(0)->label(__('backend.completion_percentage')),
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
                TextColumn::make('name')->searchable()->sortable()->label(__('backend.project_name')),
                TextColumn::make('address')->searchable()->sortable()->label(__('backend.project_location')),
                /*TextColumn::make('budget')->searchable()->sortable()->numeric()->label(__('backend.budget')),
                TextColumn::make('currency')->searchable()->sortable()->label(__('backend.currency')),*/
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
                })->searchable()->sortable()->label(__('backend.project_level')),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => __('backend.pending'),
                    'processing' => __('backend.processing'),
                    'completed' => __('backend.completed'),
                    'cancelled' => __('backend.cancelled'),
                ])->label(__('backend.project_level')),
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
            ExpensesRelationManager::class,
            //StoragesRelationManager::class,
            ItemsRelationManager::class,
            //UsersRelationManager::class,
            WorkersRelationManager::class,
            FilesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('backend.projects');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('backend.projects');
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

    public static function canAccess(): bool
    {
        $Subscription= Subscription::where(['company_id' => Auth::user()->company_id, 'is_active' => 1])->orderBy('id', 'desc')->first();

        if(!$Subscription || !$Subscription->package) return false;
        
        //if(!$Subscription->package->has_item_categories) return false;
        
        if(!$Subscription->package->has_projects) return false;

        $Role= Auth::user()->role;
        return $Role->can_read_projects == true || $Role->can_write_projects == true || $Role->can_edit_projects == true;
    }

    public static function canView(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_read_projects;
    }

    public static function canCreate(): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_write_projects;
    }

    public static function canEdit(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_edit_projects;
    }

    public static function canDelete(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_edit_projects;
    }
}
