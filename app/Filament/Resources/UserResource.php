<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Filament\Resources\UserResource\RelationManagers\FilesRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\ProjectsRelationManager;
use App\Models\User;
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

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone'];
    }

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
                    Section::make(__('backend.information'))->icon('heroicon-o-identification')->schema([
                        TextInput::make('name')->columnSpanFull()->required()->label(__('backend.name')),

                        Select::make('role')->relationship('role', 'name', function($query){
                            $query->where('company_id', Auth::user()->company_id);
                            $query->orWhere('company_id', null);
                        })->required()/*->preload()*->searchable()*/->label(__('backend.role')),

                        TextInput::make('email')->required()->unique(ignoreRecord:true)->validationMessages([
                            'unique' => __('backend.unavailable_to_use'),
                        ])->label(__('backend.email')),
                        
                        TextInput::make('phone')->label(__('backend.phone')),
                        
                        TextInput::make('password')->password()->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrateStateUsing(fn (?string $state) => filled($state) ? bcrypt($state) : null)
                            ->dehydrated(fn (?string $state): bool => filled($state))->label(__('backend.password')),
                    ])->columns(2),

                    Section::make()->schema([
                        FileUpload::make('picture')->directory('form-attachments')->image()->imageEditor()->label(__('backend.picture'))
                    ])->collapsible(),
                ]),

                Group::make()->schema([
                    Section::make()->schema([
                        TextInput::make('job_title')->required()->label(__('backend.role_name')),

                        MarkdownEditor::make('job_description')->columnSpan('full')->label(__('backend.role_description')),
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
                ImageColumn::make('picture')->defaultImageUrl(asset('images/no_profile_picture.png'))->circular()->label(''),
                TextColumn::make('name')->searchable()->sortable()->label(__('backend.name')),
                TextColumn::make('role.name')->badge()->color('primary')->searchable()->sortable()->label(__('backend.role')),
                TextColumn::make('email')->searchable()->sortable()->label(__('backend.email')),
                TextColumn::make('phone')->searchable()->sortable()->label(__('backend.phone')),
                TextColumn::make('permissions')
                    ->label(__('backend.permissions'))
                    ->state(function (User $record): array {
                        $items = [];

                        if ($record->role?->can_read_users) {
                            $items[] = __('backend.view');
                        }

                        $items[] = $record->role?->can_edit_users ? __('backend.manage') : __('backend.view_only');

                        return $items;
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        __('backend.view') => 'info',
                        __('backend.manage') => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('job_title')->searchable()->sortable()->label(__('backend.role_name'))->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_active')->onColor('success')->label(__('backend.active')),
            ])
            ->filters([
                TernaryFilter::make('is_active')->boolean()->trueLabel(__('backend.active'))->falseLabel(__('backend.inactive'))->native(false)->label(__('backend.active')),
                SelectFilter::make('role')->relationship('role', 'name', function (Builder $query) {
                    $query->where('company_id', Auth::user()->company_id)
                        ->orWhere('company_id', null);
                })->label(__('backend.role')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('backend.users');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('backend.company');
    }

    public static function getBreadcrumb(): string
    {
        return __('backend.users');
    }

    public static function getModelLabel(): string
    {
        return __('backend.users');
    }

    public static function getPluralLabel(): ?string
    {
        return __('backend.users');
    }

    public static function canAccess(): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_read_users == true || $Role->can_write_users == true || $Role->can_edit_users == true;
    }

    public static function canView(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_read_users;
    }

    public static function canCreate(): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_write_users;
    }

    public static function canEdit(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_edit_users;
    }

    public static function canDelete(Model $record): bool
    {
        $Role= Auth::user()->role;
        return $Role->can_edit_users;
    }
}
