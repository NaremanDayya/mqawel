<?php

namespace App\Filament\Pages;

use App\Exports\WorkersReportExport;
use App\Filament\Pages\Widgets\StatsWorkers;
use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\WorkerResource;
use App\Models\Subscription;
use App\Models\Worker;
use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ExportAction;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class WorkersReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?int $navigationSort= 9;

    protected static string $view = 'filament.pages.workers-report';

    protected function getHeaderWidgets(): array
    {
        return [
            StatsWorkers::make(),
        ];
    }

    public function table(Table $table): Table {
        return $table
            ->query(
                WorkerResource::getEloquentQuery()->where('company_id', Auth::user()->company_id)
            )
            ->headerActions([
                Tables\Actions\Action::make('exportExcel')
                ->label(__('backend.export_excel'))
                ->color('success')
                ->icon('heroicon-o-document-chart-bar')
                ->action(function(){
                    return Excel::download(new WorkersReportExport, __('backend.workers_report').'-'.date('Y-m-d H:i').'-'.'.xlsx');
                }),
                Tables\Actions\Action::make('exportPDF')
                ->label(__('backend.export_pdf'))
                ->color('danger')
                ->icon('heroicon-o-document-text')
                ->action(function(){
                    $Workers= Worker::where('company_id', Auth::user()->company_id)->where('company_id', '<>', null)->get();

                    $html= Blade::render('exports.workers_report', ['Workers' => $Workers]);

                    $mpdf= new Mpdf([
                        'mode' => 'utf-8',
                        'format' => 'A4',
                        'autoScriptToLang' => true,
                        'autoLangToFont' => true,
                        'tempDir' => storage_path('app/mpdf/tmp'),
                    ]);

                    if(session('current_lang') == 'ar'){
                        $mpdf->SetDirectionality('rtl');
                    }

                    $mpdf->WriteHTML($html);

                    return response()->streamDownload(function() use($mpdf){
                        echo $mpdf->Output('', Destination::STRING_RETURN);
                    }, __('backend.workers_report').'-'.date('Y-m-d H:i').'.pdf');
                })
            ])
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('index')->rowIndex()->label(__('backend.row_number')),
                ImageColumn::make('picture')->defaultImageUrl(asset('images/no_profile_picture.png'))->circular()->label(''),
                TextColumn::make('name')->searchable()->sortable()->label(__('backend.name')),
                TextColumn::make('phone')->searchable()->sortable()->label(__('backend.phone')),
                TextColumn::make('ethnicity')->searchable()->sortable()->label(__('backend.ethnicity'))->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('living_address')->searchable()->sortable()->label(__('backend.living_address'))->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('living_type')->formatStateUsing(fn(string $state): string => match($state){
                    'temporary' => __('backend.temporary'),
                    'primary' => __('backend.primary'),
                })->badge()->searchable()->sortable()->label(__('backend.living_place')),
                IconColumn::make('is_active')->boolean()->label(__('backend.active')),
            ])
            ->emptyStateHeading(__('backend.not_found').' '.__('backend.workers'))
            ->actions([
                /*Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                ])*/
            ])
            ->filters([
                TernaryFilter::make('is_active')->boolean()->trueLabel(__('backend.active'))->falseLabel(__('backend.inactive'))->native(false)->label(__('backend.active')),

                SelectFilter::make('ethnicity')->options(
                    Worker::where('ethnicity', '<>', null)->groupBy('ethnicity')->pluck('ethnicity', 'ethnicity')
                )->label(__('backend.ethnicity')),

                SelectFilter::make('job_title')->options(
                    Worker::where('job_title', '<>', null)->groupBy('job_title')->pluck('job_title', 'job_title')
                )->label(__('backend.job_title')),
            ]);
    }

    public function getBreadcrumbs(): array
    {
        return [__('backend.reports'), __('backend.workers')];
    }

    public function getHeading(): string|Htmlable
    {
        return __('backend.workers_report');
    }

    public static function getNavigationLabel(): string
    {
        return __('backend.workers');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('backend.reports');
    }

    public static function getBreadcrumb(): string
    {
        return __('backend.workers');
    }

    public static function getModelLabel(): string
    {
        return __('backend.workers');
    }

    public static function getPluralLabel(): ?string
    {
        return __('backend.workers');
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('backend.workers');
    }

    public function getTitle(): string|Htmlable
    {
        return __('backend.workers');
    }

    public static function canAccess(): bool
    {
        $Subscription= Subscription::where(['company_id' => Auth::user()->company_id, 'is_active' => 1])->orderBy('id', 'desc')->first();

        if(!$Subscription) return false;

        if(!$Subscription->package) return false;

        if(!$Subscription->package->has_workers_report) return false;

        $Role= Auth::user()->role;
        return $Role->can_read_workers_report;
    }
}
