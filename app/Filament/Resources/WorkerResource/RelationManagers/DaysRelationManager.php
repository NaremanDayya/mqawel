<?php

namespace App\Filament\Resources\WorkerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
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

class DaysRelationManager extends RelationManager
{
    protected static string $relationship = 'days';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('created_by')->default(Auth::user()->id),

                DatePicker::make('date')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, \Filament\Forms\Set $set) {
                        if (! $state) {
                            return;
                        }

                        $set('day', match (\Carbon\Carbon::parse($state)->dayOfWeek) {
                            0 => 'sunday',
                            1 => 'monday',
                            2 => 'tuesday',
                            3 => 'wednesday',
                            4 => 'thursday',
                            5 => 'friday',
                            6 => 'saturday',
                        });
                    })
                    ->label(__('backend.date')),

                Select::make('day')->options([
                    'saturday' => __('backend.saturday'),
                    'sunday' => __('backend.sunday'),
                    'monday' => __('backend.monday'),
                    'tuesday' => __('backend.tuesday'),
                    'wednesday' => __('backend.wednesday'),
                    'thursday' => __('backend.thursday'),
                    'friday' => __('backend.friday'),
                ])->required()->disabled()->dehydrated()->label(__('backend.day')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('day')
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('index')->rowIndex()->label(__('backend.row_number')),
                TextColumn::make('date')->date()->label(__('backend.date')),
                TextColumn::make('day')->formatStateUsing(fn(?string $state): string => match($state){
                    'saturday' => __('backend.saturday'),
                    'sunday' => __('backend.sunday'),
                    'monday' => __('backend.monday'),
                    'tuesday' => __('backend.tuesday'),
                    'wednesday' => __('backend.wednesday'),
                    'thursday' => __('backend.thursday'),
                    'friday' => __('backend.friday'),
                    default => $state ?? '—',
                })->badge()->color('success')->label(__('backend.day')),
            ])
            ->emptyStateHeading(__('backend.not_found').' '.__('backend.days'))
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label(__('backend.register_entry')),
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
        return __('backend.work_days');
    }

    public static function getModelLabel(): string
    {
        return __('backend.work_days');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('backend.work_days');
    }
}
