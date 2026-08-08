<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Widgets\StatsWorkerPayments;
use App\Models\Subscription;
use App\Models\WorkerPayment;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class WorkerPaymentsReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?int $navigationSort= 10;

    protected static string $view = 'filament.pages.worker-payments-report';

    protected function getHeaderWidgets(): array
    {
        return [
            StatsWorkerPayments::make(),
        ];
    }

    public function table(Table $table): Table {
        return $table
            ->query(
                WorkerPayment::where('company_id', Auth::user()->company_id)->where('status', 'unpaid')
            )
            /*->defaultPaginationPageOption(5)
            ->paginated(false)*/
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('index')->rowIndex()->label(__('backend.row_number')),
                TextColumn::make('worker.name')->searchable()->sortable()->label(__('backend.worker')),
                TextColumn::make('title')->searchable()->sortable()->label(__('backend.title')),
                TextColumn::make('amount')->searchable()->sortable()->numeric()->label(__('backend.amount')),
                //TextColumn::make('currency')->searchable()->sortable()->label(__('backend.currency')),
                TextColumn::make('payment_date')->searchable()->sortable()->date()->label(__('backend.date')),
                TextColumn::make('payment_method')->searchable()->sortable()->label(__('backend.method')),
                TextColumn::make('status')->formatStateUsing(fn(string $state): string => match($state){
                    'paid' => __('backend.paid'),
                    'unpaid' => __('backend.unpaid'),
                })->badge()->color(fn(string $state): string => match($state){
                    'paid' => 'success',
                    'unpaid' => 'danger',
                })->label(__('backend.status')),
            ])
            ->emptyStateHeading(__('backend.not_found').' '.__('backend.worker_payments'))
            ->actions([
                /*Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                ])*/
            ])
            ->filters([
                SelectFilter::make('title')->options(
                    WorkerPayment::where('title', '<>', null)->groupBy('title')->pluck('title', 'title')
                )->label(__('backend.title')),

                SelectFilter::make('currency')->options(
                    WorkerPayment::where('currency', '<>', null)->groupBy('currency')->pluck('currency', 'currency')
                )->label(__('backend.currency')),

                SelectFilter::make('payment_method')->options(
                    WorkerPayment::where('payment_method', '<>', null)->groupBy('payment_method')->pluck('payment_method', 'payment_method')
                )->label(__('backend.payment_method')),

                /*TernaryFilter::make('is_active')->boolean()->trueLabel(__('backend.active'))->falseLabel(__('backend.inactive'))->native(false)->label(__('backend.active')),

                SelectFilter::make('ethnicity')->options(
                    Worker::where('ethnicity', '<>', null)->groupBy('ethnicity')->pluck('ethnicity', 'ethnicity')
                )->label(__('backend.ethnicity')),

                SelectFilter::make('job_title')->options(
                    Worker::where('job_title', '<>', null)->groupBy('job_title')->pluck('job_title', 'job_title')
                )->label(__('backend.job_title')),*/
            ]);
    }

    public function getBreadcrumbs(): array
    {
        return [__('backend.reports'), __('backend.workers')];
    }

    public function getHeading(): string|Htmlable
    {
        return __('backend.worker_payments_report');
    }

    public static function getNavigationLabel(): string
    {
        return __('backend.worker_payments');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('backend.reports');
    }

    public static function getBreadcrumb(): string
    {
        return __('backend.worker_payments');
    }

    public static function getModelLabel(): string
    {
        return __('backend.worker_payments');
    }

    public static function getPluralLabel(): ?string
    {
        return __('backend.worker_payments');
    }

    public static function canAccess(): bool
    {
        $Subscription= Subscription::where(['company_id' => Auth::user()->company_id, 'is_active' => 1])->orderBy('id', 'desc')->first();

        if(!$Subscription) return false;

        if(!$Subscription->package) return false;

        if(!$Subscription->package->has_worker_expenses_report) return false;

        $Role= Auth::user()->role;
        return $Role->can_read_worker_expenses_report;
    }
}
