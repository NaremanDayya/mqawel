<?php

namespace App\Filament\Master\Resources;

use App\Filament\Master\Resources\SettingResource\Pages;
use App\Filament\Master\Resources\SettingResource\RelationManagers;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\RichEditor;
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

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static ?string $navigationGroup= 'masters';

    protected static ?int $navigationSort= 8;

    protected static bool $shouldRegisterNavigation= false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make(__('backend.contact_info'))->icon('heroicon-o-phone')->schema([
                        TextInput::make('address_1')->label(__('backend.address_1'))->columnSpanFull(),
                        TextInput::make('address_2')->label(__('backend.address_2')),
                        TextInput::make('address_3')->label(__('backend.address_3')),
                        TextInput::make('email_1')->label(__('backend.email_1'))->columnSpanFull(),
                        TextInput::make('email_2')->label(__('backend.email_2')),
                        TextInput::make('email_3')->label(__('backend.email_3')),
                        TextInput::make('phone_1')->label(__('backend.phone_1'))->columnSpanFull(),
                        TextInput::make('phone_2')->label(__('backend.phone_2')),
                        TextInput::make('phone_3')->label(__('backend.phone_3')),
                    ])->columns(2),
                    Section::make(__('backend.social_media_info'))->icon('heroicon-o-share')->schema([
                        TextInput::make('whatsapp')->label(__('backend.whatsapp')),
                        TextInput::make('facebook')->label(__('backend.facebook')),
                        TextInput::make('x')->label(__('backend.x')),
                        TextInput::make('linkedin')->label(__('backend.linkedin')),
                        TextInput::make('snapchat')->label(__('backend.snapchat')),
                        TextInput::make('instagram')->label(__('backend.instagram')),
                        TextInput::make('telegram')->label(__('backend.telegram')),
                    ])->columns(2),
                ]),
                Group::make()->schema([
                    Section::make(__('backend.policies'))->icon('heroicon-o-document-text')->schema([
                        RichEditor::make('privacy_policy')->label(__('backend.privacy_policy'))->columnSpanFull(),
                        RichEditor::make('terms_and_conditions')->label(__('backend.terms_and_conditions'))->columnSpanFull(),
                    ])->collapsible(true)
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                /*TextColumn::make('address_1')->label(__('backend.address_1')),
                TextColumn::make('address_2')->label(__('backend.address_2')),
                TextColumn::make('address_3')->label(__('backend.address_3')),
                TextColumn::make('email_1')->label(__('backend.email_1')),
                TextColumn::make('email_2')->label(__('backend.email_2')),
                TextColumn::make('email_3')->label(__('backend.email_3')),
                TextColumn::make('phone_1')->label(__('backend.phone_1')),
                TextColumn::make('phone_2')->label(__('backend.phone_2')),
                TextColumn::make('phone_3')->label(__('backend.phone_3')),
                TextColumn::make('whatsapp')->label(__('backend.whatsapp')),
                TextColumn::make('facebook')->label(__('backend.facebook')),
                TextColumn::make('x')->label(__('backend.x')),
                TextColumn::make('linkedin')->label(__('backend.linkedin')),
                TextColumn::make('snapchat')->label(__('backend.snapchat')),
                TextColumn::make('instagram')->label(__('backend.instagram')),
                TextColumn::make('telegram')->label(__('backend.telegram')),
                TextColumn::make('privacy_policy')->label(__('backend.privacy_policy'))->formatStateUsing(function($state){
                    return '--';
                }),
                TextColumn::make('terms_and_conditions')->label(__('backend.terms_and_conditions'))->formatStateUsing(function($state){
                    return '--';
                }),*/
            ])
            ->paginated(false)
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
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
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('backend.settings');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('backend.system_admin');
    }

    public static function getBreadcrumb(): string
    {
        return __('backend.settings');
    }

    public static function getModelLabel(): string
    {
        return __('backend.settings');
    }

    public static function getPluralLabel(): ?string
    {
        return __('backend.settings');
    }

    public static function canView(Model $record): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}
