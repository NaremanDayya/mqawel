<?php

namespace App\Filament\Master\Resources;

use App\Filament\Master\Resources\CompanyResource\Pages;
use App\Filament\Master\Resources\CompanyResource\RelationManagers;
use App\Filament\Master\Resources\CompanyResource\RelationManagers\ProjectsRelationManager;
use App\Filament\Master\Resources\CompanyResource\RelationManagers\SubscriptionsRelationManager;
use App\Filament\Master\Resources\CompanyResource\RelationManagers\UsersRelationManager;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone'];
    }

    protected static ?string $navigationGroup= 'companies';

    protected static ?int $navigationSort= 1;

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
                    Section::make()->schema([
                        FileUpload::make('picture')->directory('companies')->image()->imageEditor()->label(__('backend.picture')),
                    ]),
                    Section::make()->schema([
                        TextInput::make('name')->required()->columnSpanFull()->label(__('backend.name')),
                        TextInput::make('email')->label(__('backend.email')),
                        TextInput::make('phone')->label(__('backend.phone')),
                        TextInput::make('website')->columnSpanFull()->label(__('backend.website')),
                    ])->columns(2),
                ]),
                Group::make()->schema([
                    Section::make()->schema([
                        TextInput::make('business_number')->label(__('backend.business_number')),
                        TextInput::make('tax_number')->label(__('backend.tax_number')),
                        TextInput::make('activity')->label(__('backend.activity')),
                    ])->columns(2),
                    Section::make()->schema([
                        Toggle::make('is_active')->default(true)->label(__('backend.active')),
                    ])
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('picture')->defaultImageUrl(asset('images/no_profile_picture.png'))->circular()->label(''),
                TextColumn::make('name')->label(__('backend.name'))->searchable()->sortable(),
                TextColumn::make('email')->label(__('backend.email'))->searchable()->sortable(),
                TextColumn::make('phone')->label(__('backend.phone'))->searchable()->sortable(),
                TextColumn::make('business_number')->label(__('backend.business_number'))->searchable()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tax_number')->label(__('backend.tax_number'))->searchable()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('activity')->label(__('backend.activity'))->searchable()->sortable()->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')->boolean()->label(__('backend.active')),
            ])
            ->filters([
                TernaryFilter::make('is_active')->trueLabel(__('backend.active'))->falseLabel(__('backend.inactive'))->label(__('backend.active')),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
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
            UsersRelationManager::class,
            SubscriptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('backend.companies');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('backend.companies');
    }

    public static function getBreadcrumb(): string
    {
        return __('backend.companies');
    }

    public static function getModelLabel(): string
    {
        return __('backend.companies');
    }

    public static function getPluralLabel(): ?string
    {
        return __('backend.companies');
    }
}
