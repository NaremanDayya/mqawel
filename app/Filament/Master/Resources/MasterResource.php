<?php

namespace App\Filament\Master\Resources;

use App\Filament\Master\Resources\MasterResource\Pages;
use App\Filament\Master\Resources\MasterResource\RelationManagers;
use App\Models\Master;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
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
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class MasterResource extends Resource
{
    protected static ?string $model = Master::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone'];
    }

    protected static ?string $navigationGroup= 'masters';

    protected static ?int $navigationSort= 7;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() > 0 ? static::getModel()::count() : '';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('master_id')->default(Auth::user()->id),

                Group::make()->schema([
                    Section::make(__('backend.information'))->icon('heroicon-o-identification')->schema([
                        TextInput::make('name')->columnSpanFull()->required()->label(__('backend.name')),

                        Select::make('role')->options([
                            'admin' => __('backend.admin'),
                            'staff' => __('backend.staff'),
                        ])->required()->label(__('backend.role')),

                        TextInput::make('email')->required()->unique(ignoreRecord:true)->label(__('backend.email')),
                        
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
                        Toggle::make('is_active')->default(true)->label(__('backend.active')),
                    ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('picture')->defaultImageUrl(asset('images/no_profile_picture.png'))->circular()->label(''),
                TextColumn::make('name')->searchable()->sortable()->label(__('backend.name')),
                TextColumn::make('role')->formatStateUsing(fn(string $state): string => match($state){
                    'admin' => __('backend.admin'),
                    'staff' => __('backend.staff'),
                })->badge()->searchable()->sortable()->label(__('backend.role')),
                TextColumn::make('email')->searchable()->sortable()->label(__('backend.email')),
                TextColumn::make('phone')->searchable()->sortable()->label(__('backend.phone')),
                IconColumn::make('is_active')->boolean()->label(__('backend.active')),
            ])
            ->filters([
                TernaryFilter::make('is_active')->boolean()->trueLabel(__('backend.active'))->falseLabel(__('backend.inactive'))->native(false)->label(__('backend.active')),
                SelectFilter::make('role')->options([
                    'admin' => 'admin',
                    'staff' => 'staff',
                ]),
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
            'index' => Pages\ListMasters::route('/'),
            'create' => Pages\CreateMaster::route('/create'),
            'edit' => Pages\EditMaster::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('backend.system_users');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('backend.system_admin');
    }

    public static function getBreadcrumb(): string
    {
        return __('backend.system_users');
    }

    public static function getModelLabel(): string
    {
        return __('backend.system_users');
    }

    public static function getPluralLabel(): ?string
    {
        return __('backend.system_users');
    }
}
