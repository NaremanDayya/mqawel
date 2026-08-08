<?php

namespace App\Filament\Master\Resources;

use App\Filament\Master\Resources\SubscriptionPackageResource\Pages;
use App\Models\SubscriptionPackage;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
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
use Illuminate\Support\Facades\Auth;

class SubscriptionPackageResource extends Resource
{
    protected static ?string $model = SubscriptionPackage::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationGroup= 'subscriptions';

    protected static ?int $navigationSort= 5;

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
                        FileUpload::make('picture')->directory('subscription_packages')->image()->imageEditor()->label(__('backend.picture')),
                    ]),
                    Section::make()->schema([
                        TextInput::make('title')->required()->label(__('backend.title')),
                        MarkdownEditor::make('description')->label(__('backend.description')),
                    ]),
                    Section::make()->schema([
                        TextInput::make('period')->numeric()->label(__('backend.period_in_months')),
                        TextInput::make('price')->numeric()->label(__('backend.price')),
                        //TextInput::make('currency')->label(__('backend.currency')),
                    ])->columns(2),
                    Section::make()->schema([
                        TextInput::make('max_projects')->numeric()->minValue(0)->label(__('backend.max_projects')),
                        TextInput::make('max_workers')->numeric()->minValue(0)->label(__('backend.max_workers')),
                    ])->columns(2),
                    Section::make(__('backend.traditional_cost_comparison'))->icon('heroicon-o-scale')->schema([
                        Repeater::make('traditional_cost_items')
                            ->schema([
                                TextInput::make('label')->required()->label(__('backend.title')),
                                TextInput::make('value')->numeric()->required()->label(__('backend.amount')),
                            ])
                            ->columns(2)
                            ->addActionLabel(__('backend.add_more'))
                            ->label(__('backend.cost_comparison_items')),
                        TextInput::make('savings_note')->label(__('backend.savings_note')),
                    ]),
                    Section::make(__('backend.pricing_card_display'))->icon('heroicon-o-list-bullet')->schema([
                        Repeater::make('feature_bullets')
                            ->schema([
                                TextInput::make('text')->required()->label(__('backend.title')),
                            ])
                            ->addActionLabel(__('backend.add_more'))
                            ->label(__('backend.feature_bullets')),
                        TextInput::make('cta_label')->label(__('backend.cta_label'))->helperText(__('backend.cta_label_helper')),
                    ]),
                ]),
                Group::make()->schema([
                    Section::make(__('backend.services'))->icon('heroicon-o-squares-2x2')->schema([
                        Checkbox::make('has_workers')->label(__('backend.workers')),
                        Checkbox::make('has_projects')->label(__('backend.projects')),
                        Checkbox::make('has_storages')->label(__('backend.storages')),
                        Checkbox::make('has_items')->label(__('backend.items')),
                        Checkbox::make('has_item_categories')->label(__('backend.item_categories')),
                        Checkbox::make('has_item_movements')->label(__('backend.item_movements')),
                        Checkbox::make('has_workers_report')->label(__('backend.workers_report')),
                        Checkbox::make('has_worker_expenses_report')->label(__('backend.worker_expenses_report')),
                        Checkbox::make('has_expired_files_report')->label(__('backend.expired_files_report')),
                        Checkbox::make('has_project_expenses_report')->label(__('backend.project_expenses_report')),
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
                TextColumn::make('index')->rowIndex()->label(__('backend.row_number')),
                ImageColumn::make('picture')->defaultImageUrl(asset('images/no_profile_picture.png'))->circular()->label(''),
                TextColumn::make('title')->label(__('backend.title')),
                TextColumn::make('period')->label(__('backend.period_in_months')),
                TextColumn::make('price')->numeric()->label(__('backend.price')),
                //TextColumn::make('currency')->label(__('backend.currency')),
                TextColumn::make('max_projects')->label(__('backend.max_projects'))->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('max_workers')->label(__('backend.max_workers'))->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')->boolean()->label(__('backend.active')),
            ])
            ->filters([
                TernaryFilter::make('is_active')->boolean()->trueLabel(__('backend.active'))->falseLabel(__('backend.inactive'))->native(false)->label(__('backend.active')),
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
            'index' => Pages\ListSubscriptionPackages::route('/'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('backend.packages');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('backend.subscriptions');
    }

    public static function getBreadcrumb(): string
    {
        return __('backend.packages');
    }

    public static function getModelLabel(): string
    {
        return __('backend.packages');
    }

    public static function getPluralLabel(): ?string
    {
        return __('backend.packages');
    }
}
