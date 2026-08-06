<?php

namespace App\Filament\Resources\WorkerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'Payments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('company_id')->default(Auth::user()->company_id),
                Hidden::make('worker_id')->default($this->ownerRecord->id),
                Hidden::make('created_by')->default(Auth::user()->id),
                Group::make()->schema([
                    Section::make()->schema([
                        TextInput::make('title')->required()->label(__('backend.payment_purpose')),
                        TextInput::make('amount')->numeric()->required()->label(__('backend.payment_amount')),
                    ])->columns(2),
                    Section::make()->schema([
                        //TextInput::make('currency')->required()->label(__('backend.currency')),
                        DatePicker::make('payment_date')->label(__('backend.payment_date')),
                        Select::make('payment_method')->options([
                            'cash' => __('backend.payment_method_cash'),
                            'bank_transfer' => __('backend.payment_method_bank_transfer'),
                            'cheque' => __('backend.payment_method_cheque'),
                        ])->label(__('backend.payment_method')),
                        Select::make('status')->options([
                            'paid' => __('backend.paid'),
                            'unpaid' => __('backend.unpaid'),
                        ])->label(__('backend.payment_status')),
                    ])->columns(2),
                ]),
                Group::make()->schema([
                    Section::make()->schema([
                        MarkdownEditor::make('notes')->label(__('backend.notes')),
                    ])
                ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('worker.name')->label(__('backend.worker')),
                TextColumn::make('title')->label(__('backend.payment_purpose')),
                TextColumn::make('amount')->numeric()->label(__('backend.payment_amount')),
                //TextColumn::make('currency')->label(__('backend.currency')),
                TextColumn::make('payment_date')->date()->label(__('backend.date')),
                TextColumn::make('payment_method')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'cash' => __('backend.payment_method_cash'),
                    'bank_transfer' => __('backend.payment_method_bank_transfer'),
                    'cheque' => __('backend.payment_method_cheque'),
                    default => $state ?? '—',
                })->label(__('backend.method')),
                TextColumn::make('status')->formatStateUsing(fn(string $state): string => match($state){
                    'paid' => __('backend.paid'),
                    'unpaid' => __('backend.unpaid'),
                })->badge()->color(fn(string $state): string => match($state){
                    'paid' => 'success',
                    'unpaid' => 'danger',
                })->label(__('backend.status')),
            ])
            ->emptyStateHeading(__('backend.not_found').' '.__('backend.dues'))
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label(__('backend.add_more')),
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

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('backend.dues');
    }

    public static function getModelLabel(): string
    {
        return __('backend.dues');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('backend.dues');
    }
}
