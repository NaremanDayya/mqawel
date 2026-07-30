<?php

namespace App\Filament\Master\Resources;

use App\Filament\Master\Resources\ContactResource\Pages;
use App\Filament\Master\Resources\ContactResource\RelationManagers;
use App\Models\Contact;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope-open';

    protected static ?string $navigationGroup= 'contacts';

    protected static ?int $navigationSort= 6;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() > 0 ? static::getModel()::count() : '';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make()->schema([
                        TextInput::make('name')->disabled()->required()->label(__('backend.name')),

                        TextInput::make('phone')->disabled()->label(__('backend.phone')),

                        TextInput::make('email')->disabled()->label(__('backend.email')),

                        TextInput::make('company_name')->disabled()->label(__('backend.company')),
                    ])->columns(2),

                    Section::make()->schema([
                        TextInput::make('title')->disabled()->label(__('backend.title')),

                        MarkdownEditor::make('description')->disabled()->columnSpanFull()->label(__('backend.description')),
                    ]),
                ]),

                Group::make()->schema([
                    Section::make()->schema([
                        Select::make('purpose')->disabled()->options([
                            'registration' => __('backend.registration'),
                            'consultation' => __('backend.consultation'),
                            'training' => __('backend.training'),
                            'other' => __('backend.other'),
                        ])->label(__('backend.purpose')),
                    ]),

                    Section::make()->schema([
                        Toggle::make('is_open')->default(false)->label(__('backend.open')),
                    ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->label(__('backend.name')),
                TextColumn::make('phone')->searchable()->sortable()->label(__('backend.phone')),
                TextColumn::make('email')->searchable()->sortable()->label(__('backend.email')),
                TextColumn::make('company_name')->searchable()->sortable()->label(__('backend.company'))->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('purpose')->searchable()->sortable()->label(__('backend.purpose'))->badge()->formatStateUsing(fn(string $state): string => match($state){
                    'registration' => __('backend.registration'),
                    'consultation' => __('backend.consultation'),
                    'training' => __('backend.training'),
                    'other' => __('backend.other'),
                }),
                TextColumn::make('title')->searchable()->sortable()->label(__('backend.title'))->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_open')->boolean()->label(__('backend.open')),
            ])
            ->filters([
                TernaryFilter::make('is_open')->boolean()->trueLabel(__('backend.open'))->falseLabel(__('backend.pending'))->label(__('backend.open')),
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
            'index' => Pages\ListContacts::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationLabel(): string
    {
        return __('backend.webmail');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('backend.contacts');
    }

    public static function getBreadcrumb(): string
    {
        return __('backend.webmail');
    }

    public static function getModelLabel(): string
    {
        return __('backend.webmail');
    }

    public static function getPluralLabel(): ?string
    {
        return __('backend.webmail');
    }
}
