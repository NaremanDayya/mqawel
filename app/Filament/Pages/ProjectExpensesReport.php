<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Widgets\StatsProjectExpenses;
use App\Models\ProjectExpense;
use App\Models\Subscription;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class ProjectExpensesReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?int $navigationSort= 12;

    protected static string $view = 'filament.pages.project-expenses-report';

    protected function getHeaderWidgets(): array
    {
        return [
            StatsProjectExpenses::make(),
        ];
    }

    public function table(Table $table): Table {
        return $table
            ->query(
                ProjectExpense::where('company_id', Auth::user()->company_id)
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('project.name')->searchable()->sortable()->label(__('backend.project')),

                TextColumn::make('title')->searchable()->sortable()->label(__('backend.title')),

                TextColumn::make('amount')->searchable()->sortable()->numeric()->label(__('backend.amount')),

                //TextColumn::make('currency')->searchable()->sortable()->label(__('backend.currency')),

                TextColumn::make('date')->searchable()->sortable()->date()->label(__('backend.date')),
            ])
            ->emptyStateHeading(__('backend.not_found').' '.__('backend.expenses'))
            ->actions([
                /*Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                ])*/
            ])
            ->filters([
                SelectFilter::make('currency')->options(
                    ProjectExpense::where('currency', '<>', null)->groupBy('currency')->pluck('currency', 'currency')
                )->label(__('backend.currency')),
            ]);
    }

    public function getBreadcrumbs(): array
    {
        return [__('backend.reports'), __('backend.projects')];
    }

    public function getHeading(): string|Htmlable
    {
        return __('backend.project_expenses_report');
    }

    public static function getNavigationLabel(): string
    {
        return __('backend.project_expenses');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('backend.reports');
    }

    public static function getBreadcrumb(): string
    {
        return __('backend.project_expenses');
    }

    public static function getModelLabel(): string
    {
        return __('backend.project_expenses');
    }

    public static function getPluralLabel(): ?string
    {
        return __('backend.project_expenses');
    }

    public static function canAccess(): bool
    {
        $Subscription= Subscription::where(['company_id' => Auth::user()->company_id, 'is_active' => 1])->orderBy('id', 'desc')->first();

        if(!$Subscription) return false;

        if(!$Subscription->package) return false;

        if(!$Subscription->package->has_project_expenses_report) return false;

        $Role= Auth::user()->role;
        return $Role->can_read_project_expenses_report;
    }
}
