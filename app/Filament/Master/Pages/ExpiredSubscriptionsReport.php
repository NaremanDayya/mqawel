<?php

namespace App\Filament\Master\Pages;

use App\Filament\Master\Pages\Widgets\StatsExpiredSubscriptions;
use App\Models\Contractor;
use App\Models\File;
use App\Models\Item;
use App\Models\Project;
use App\Models\Storage as ModelsStorage;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Worker;
use Filament\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpiredSubscriptionsReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?int $navigationSort= 11;

    protected static string $view = 'filament.pages.expired-subscriptions-report';

    protected function getHeaderWidgets(): array
    {
        return [
            StatsExpiredSubscriptions::make(),
        ];
    }

    public function table(Table $table): Table {
        return $table
            ->query(
                Subscription::where('is_active', 1)->where('ending_date', '<', date('Y-m-d'))
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('index')->rowIndex()->label(__('backend.row_number')),
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
            ->emptyStateHeading(__('backend.not_found').' '.__('backend.subscriptions'))
            ->actions([
                
            ])
            ->filters([
                SelectFilter::make('year')->options(function(){
                    $years= [];

                    for($i= (intval(date('Y')) - 25); $i <= date('Y'); $i++){
                        $years[$i]= $i;
                    }

                    return $years;
                })->query(function(Builder $query, array $data): Builder {
                    if(isset($data['value']) && $data['value'] !== null){
                        $selected_year= (int) $data['value'];

                        return $query->whereYear('ending_date', $selected_year);
                    }

                    return $query;
                })->label(__('backend.year')),

                SelectFilter::make('month')->options(function(){
                    $months= [];

                    $months[1]= __('backend.jan');
                    $months[2]= __('backend.feb');
                    $months[3]= __('backend.mar');
                    $months[4]= __('backend.apr');
                    $months[5]= __('backend.may');
                    $months[6]= __('backend.jun');
                    $months[7]= __('backend.jul');
                    $months[8]= __('backend.aug');
                    $months[9]= __('backend.sep');
                    $months[10]= __('backend.oct');
                    $months[11]= __('backend.nov');
                    $months[12]= __('backend.dec');

                    return $months;
                })->query(function(Builder $query, array $data): Builder {
                    if(isset($data['value']) && $data['value'] !== null){
                        $selected_month= (int) $data['value'];
                        
                        return $query->whereMonth('ending_date', $selected_month);
                    }

                    return $query;
                })->label(__('backend.month')),
            ]);
    }

    public function getBreadcrumbs(): array
    {
        return [__('backend.reports'), __('backend.subscriptions')];
    }

    public function getHeading(): string|Htmlable
    {
        return __('backend.expired_subscriptions_report');
    }

    public static function getNavigationLabel(): string
    {
        return __('backend.expired_subscriptions');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('backend.reports');
    }

    public static function getBreadcrumb(): string
    {
        return __('backend.expired_subscriptions');
    }

    public static function getModelLabel(): string
    {
        return __('backend.expired_subscriptions');
    }

    public static function getPluralLabel(): ?string
    {
        return __('backend.expired_subscriptions');
    }
}
