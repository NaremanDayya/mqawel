<?php

namespace App\Filament\Master\Resources\CompanyResource\RelationManagers;

use App\Models\Master;
use App\Models\SubscriptionPackage;
use Filament\Forms;
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
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class SubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'Subscriptions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('master_id')->default(Auth::user()->id),
                Group::make()->schema([
                    Section::make(__('backend.information'))->schema([
                        Select::make('package')->relationship('package', 'title')->required()->label(__('backend.package')),
                    ])->columns(1),
                    Section::make(__('backend.payment'))->schema([
                        TextInput::make('payment_method')->extraInputAttributes(['autocomplete' => 'off'])->label(__('backend.payment_method')),
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('period')
            ->columns([
                TextColumn::make('package.title')->sortable()->searchable()->label(__('backend.package')),
                TextColumn::make('period')->sortable()->searchable()->label(__('backend.period')),
                TextColumn::make('starting_date')->sortable()->searchable()->label(__('backend.starting')),
                TextColumn::make('ending_date')->sortable()->searchable()->label(__('backend.ending')),
                TextColumn::make('price')->sortable()->searchable()->numeric()->label(__('backend.price')),
                //TextColumn::make('currency')->sortable()->searchable()->label(__('backend.currency')),
                IconColumn::make('is_active')->boolean()->label(__('backend.active')),
            ])
            ->filters([
                TernaryFilter::make('is_active')->boolean()->trueLabel(__('backend.active'))->falseLabel(__('backend.inactive'))->native(false)->label(__('backend.active')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label(__('backend.add_more'))->after(function($record){
                    $Package= SubscriptionPackage::find($record->package_id);

                    if($Package){
                        $package_period= $Package->period;
                        $package_price= $Package->price;
                        $package_currency= $Package->currency;

                        $record->period= $package_period;
                        $record->ending_date= date('Y-m-d', strtotime('+ '.$package_period.' month', strtotime($record->starting_date)));
                        $record->price= $package_price;
                        $record->currency= $package_currency;
                        $record->save();
                    }

                    $Masters= Master::where('is_active', 1)->get();

                    if($Masters){
                        foreach($Masters as $Master){
                            $message= '';

                            if($Master->locale == 'en'){
                                $message= 'New subscription added to the company "'.$record->company->name.'"';
                            }
                            else if($Master->locale == 'ar'){
                                $message= 'تمت اضافة اشتراك جديد الى شركة '.'"'.$record->company->name.'"';
                            }

                            Notification::make()->title($message)->sendToDatabase($Master);
                        }
                    }
                }),
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

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('backend.subscriptions');
    }

    public static function getModelLabel(): string
    {
        return __('backend.subscriptions');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('backend.subscriptions');
    }
}
