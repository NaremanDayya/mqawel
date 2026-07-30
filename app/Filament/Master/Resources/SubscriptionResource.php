<?php

namespace App\Filament\Master\Resources;

use App\Filament\Master\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use App\Models\SubscriptionPackage;
use Filament\Forms\Components\DatePicker;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup= 'subscriptions';

    protected static ?int $navigationSort= 4;

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
                    Section::make(__('backend.information'))->icon('heroicon-o-clipboard-document-list')->schema([
                        Select::make('company')->relationship('company', 'name')->required()->label(__('backend.company')),
                        Select::make('package')->relationship('package', 'title')->required()->label(__('backend.package')),
                    ])->columns(2),
                    Section::make(__('backend.payment'))->icon('heroicon-o-banknotes')->schema([
                        TextInput::make('payment_method')->label(__('backend.payment_method')),
                        TextInput::make('payment_transaction_id')->label(__('backend.transaction_id')),
                        DatePicker::make('payment_date')->label(__('backend.payment_date')),
                        DatePicker::make('starting_date')->label(__('backend.starting_date')),
                        FileUpload::make('receipt')->directory('receipts')->image()->imageEditor()->columnSpanFull()->label(__('backend.receipt')),
                    ])->columns(2),
                ]),
                Group::make()->schema([
                    Section::make()->schema([
                        MarkdownEditor::make('notes')->label(__('backend.notes')),
                    ]),
                    Section::make()->schema([
                        Toggle::make('is_active')->default(true)->label(__('backend.active')),
                    ]),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')->sortable()->searchable()->label(__('backend.company')),
                TextColumn::make('package.title')->sortable()->searchable()->label(__('backend.package')),
                TextColumn::make('period')->sortable()->searchable()->label(__('backend.period'))->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('starting_date')->sortable()->searchable()->label(__('backend.starting'))->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ending_date')->sortable()->searchable()->label(__('backend.ending')),
                TextColumn::make('price')->numeric()->sortable()->searchable()->label(__('backend.price')),
                //TextColumn::make('currency')->sortable()->searchable()->label(__('backend.currency')),
                TextColumn::make('status')->getStateUsing(function($record){
                    if($record->ending_date < date('Y-m-d')) return __('backend.expired');
                    else return __('backend.valid');
                })->badge()->color(function($record){
                    if($record->ending_date < date('Y-m-d')) return 'danger';
                    else return 'primary';
                })->label(__('backend.status')),
                IconColumn::make('is_active')->boolean()->label(__('backend.active')),
            ])
            ->filters([
                TernaryFilter::make('is_active')->boolean()->trueLabel(__('backend.active'))->falseLabel(__('backend.inactive'))->native(false)->label(__('backend.active')),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()->after(function ($record): void {
                        $Package= SubscriptionPackage::find($record->package_id);

                        if($Package){
                            $record->period= $Package->period;
                            $record->ending_date= date('Y-m-d', strtotime('+ '.$Package->period.' month', strtotime($record->starting_date)));
                            $record->price= $Package->price;
                            $record->currency= $Package->currency;
                            $record->save();
                        }
                    }),
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
            'index' => Pages\ListSubscriptions::route('/'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('backend.subscriptions');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('backend.subscriptions');
    }

    public static function getBreadcrumb(): string
    {
        return __('backend.subscriptions');
    }

    public static function getModelLabel(): string
    {
        return __('backend.subscriptions');
    }

    public static function getPluralLabel(): ?string
    {
        return __('backend.subscriptions');
    }
}
