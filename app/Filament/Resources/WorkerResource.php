<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkerResource\Pages;
use App\Filament\Resources\WorkerResource\RelationManagers;
use App\Filament\Resources\WorkerResource\RelationManagers\DamagesRelationManager;
use App\Filament\Resources\WorkerResource\RelationManagers\DateOfPauseRelationManager;
use App\Filament\Resources\WorkerResource\RelationManagers\DaysRelationManager;
use App\Filament\Resources\WorkerResource\RelationManagers\FilesRelationManager;
use App\Filament\Resources\WorkerResource\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\WorkerResource\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\WorkerResource\RelationManagers\ProjectsRelationManager;
use App\Models\Subscription;
use App\Models\Worker;
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
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class WorkerResource extends Resource
{
    protected static ?string $model = Worker::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'phone', 'job_title'];
    }

    protected static ?string $navigationGroup= 'Projects';

    protected static ?int $navigationSort= 1;

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
                        FileUpload::make('picture')->directory('form-attachments')->image()->imageEditor()->label(__('backend.picture')),
                    ])->collapsible(),

                    Section::make()->schema([
                        TextInput::make('name')->unique(ignoreRecord:true, modifyRuleUsing: function($rule, $context, $record){
                            return $rule->where('company_id', Auth::user()->company_id);
                        })->validationMessages([
                            'unique' => __('backend.unavailable_to_use'),
                        ])->columnSpanFull()->required()->label(__('backend.name')),

                        TextInput::make('phone')->label(__('backend.phone')),

                        TextInput::make('ethnicity')->label(__('backend.ethnicity')),

                        TextInput::make('id_number')->label(__('backend.id_number')),

                        TextInput::make('living_address')->label(__('backend.living_address')),

                        Select::make('living_type')->options([
                            'temporary' => __('backend.temporary'),
                            'primary' => __('backend.primary'),
                        ])->label(__('backend.living_place')),
                    ])->columns(2),
                ]),

                Group::make()->schema([
                    Section::make()->schema([
                        TextInput::make('job_title')->required()->label(__('backend.job_title')),

                        MarkdownEditor::make('job_description')->columnSpan('full')->label(__('backend.job_description')),
                    ])->columns(1),

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
                TextColumn::make('index')->rowIndex()->label(__('backend.row_number'))->toggleable(),
                ImageColumn::make('picture')->defaultImageUrl(asset('images/no_profile_picture.png'))->circular()->label('')->toggleable(),
                TextColumn::make('name')->searchable()->sortable()->label(__('backend.name'))->toggleable(),
                TextColumn::make('job_title')->searchable()->sortable()->label(__('backend.position'))->toggleable(),
                TextColumn::make('phone')->searchable()->sortable()->label(__('backend.phone'))->toggleable(),
                TextColumn::make('ethnicity')->searchable()->sortable()->label(__('backend.ethnicity'))->toggleable(),
                TextColumn::make('id_number')->searchable()->sortable()->label(__('backend.id_number'))->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('projects.project.name')->listWithLineBreaks()->limitList(2)->label(__('backend.projects'))->toggleable(),
                TextColumn::make('living_address')->searchable()->sortable()->label(__('backend.living_address'))->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('living_type')->formatStateUsing(fn(string $state): string => match($state){
                    'temporary' => __('backend.temporary'),
                    'primary' => __('backend.primary'),
                })->badge()->searchable()->sortable()->label(__('backend.living_place'))->toggleable(),
                ToggleColumn::make('is_active')->onColor('success')->label(__('backend.status'))->toggleable(),
            ])
            ->actionsColumnLabel(__('backend.actions'))
            ->filters([
                //SelectFilter::make('company')->relationship('company', 'name')->label('Company'),
                TernaryFilter::make('is_active')->boolean()->trueLabel(__('backend.active'))->falseLabel(__('backend.inactive'))->native(false)->label(__('backend.active')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton(),
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\Action::make('documents')
                    ->label(__('backend.worker_documents'))
                    ->tooltip(__('backend.worker_documents'))
                    ->icon('heroicon-o-folder-open')
                    ->iconButton()
                    ->color('gray')
                    ->url(fn (Worker $record): string => static::getUrl('edit', [
                        'record' => $record,
                        'activeRelationManager' => array_search(FilesRelationManager::class, static::getRelations()),
                    ])),
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
            ProjectsRelationManager::class,
            FilesRelationManager::class,
            DamagesRelationManager::class,
            PaymentsRelationManager::class,
            DaysRelationManager::class,
            DateOfPauseRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkers::route('/'),
            'edit' => Pages\EditWorker::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('backend.workers');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('backend.company');
    }

    public static function getBreadcrumb(): string
    {
        return __('backend.workers');
    }

    public static function getModelLabel(): string
    {
        return __('backend.workers');
    }

    public static function getPluralLabel(): ?string
    {
        return __('backend.workers');
    }

    public static function canAccess(): bool
    {
        $Subscription= Subscription::where(['company_id' => Auth::user()->company_id, 'is_active' => 1])->orderBy('id', 'desc')->first();

        if(!$Subscription || !$Subscription->package) return false;
        
        //if(!$Subscription->package->has_item_categories) return false;
        
        if(!$Subscription->package->has_workers) return false;

        $Role= Auth::user()->role;
        return $Role->can_read_workers == true || $Role->can_write_workers == true || $Role->can_edit_workers == true;
    }

    public static function canView(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_read_workers;
    }

    public static function canCreate(): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_write_workers;
    }

    public static function canEdit(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_edit_workers;
    }

    public static function canDelete(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_edit_workers;
    }
}
