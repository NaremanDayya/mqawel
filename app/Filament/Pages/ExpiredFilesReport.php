<?php

namespace App\Filament\Pages;

use App\Exports\ExpiredFilesReportExport;
use App\Filament\Concerns\HasSectionNotificationSettings;
use App\Filament\Pages\Widgets\StatsExpiredFiles;
use App\Models\Company;
use App\Models\Contractor;
use App\Models\File;
use App\Models\Item;
use App\Models\Project;
use App\Models\Storage as ModelsStorage;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Worker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
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
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpiredFilesReport extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;
    use HasSectionNotificationSettings;

    public function getSubheading(): string|Htmlable|null
    {
        return __('backend.expired_files_report_subtitle');
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('exportExcel')
                ->label(__('backend.export_excel'))
                ->color('gray')
                ->icon('heroicon-o-table-cells')
                ->action(fn () => Excel::download(new ExpiredFilesReportExport, __('backend.expired_files_report').'-'.date('Y-m-d H:i').'.xlsx')),

            \Filament\Actions\Action::make('exportPdf')
                ->label(__('backend.export_pdf'))
                ->color('gray')
                ->icon('heroicon-o-document-text')
                ->action(function () {
                    $files = File::where('company_id', Auth::user()->company_id)->where('expiry_date', '<', date('Y-m-d'))->get();
                    $html = Blade::render('exports.expired_files_report', ['files' => $files]);

                    $mpdf = new Mpdf([
                        'mode' => 'utf-8',
                        'format' => 'A4',
                        'autoScriptToLang' => true,
                        'autoLangToFont' => true,
                        'tempDir' => storage_path('app/mpdf/tmp'),
                    ]);

                    if (session('current_lang') == 'ar') {
                        $mpdf->SetDirectionality('rtl');
                    }

                    $mpdf->WriteHTML($html);

                    return response()->streamDownload(function () use ($mpdf) {
                        echo $mpdf->Output('', Destination::STRING_RETURN);
                    }, __('backend.expired_files_report').'-'.date('Y-m-d H:i').'.pdf');
                }),

            $this->notificationSettingsAction('expired_files', __('backend.files')),
        ];
    }

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?int $navigationSort= 11;

    protected static string $view = 'filament.pages.expired-files-report';

    protected function getHeaderWidgets(): array
    {
        return [
            StatsExpiredFiles::make(),
        ];
    }

    public function table(Table $table): Table {
        return $table
            ->query(
                File::where('company_id', Auth::user()->company_id)->where('expiry_date', '<', date('Y-m-d'))
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('parent_table')->formatStateUsing(fn(string $state): string => match($state){
                    'users' => __('backend.users'),
                    'workers' => __('backend.workers'),
                    'storages' => __('backend.storages'),
                    'projects' => __('backend.projects'),
                    'storage_items' => __('backend.storage_items'),
                    'contractors' => __('backend.contractors'),
                    'items' => __('backend.items'),
                    'companies' => __('backend.companies'),
                })->badge()->searchable()->sortable()->label(__('backend.section')),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (File $record): string => match ($record->category ?? 'general') {
                        'certificate' => __('backend.file_category_certificate'),
                        'work_photo' => __('backend.file_category_work_photo'),
                        default => __('backend.file_category_general'),
                    })
                    ->label(__('backend.name')),
                TextColumn::make('parent_id')->getStateUsing(function(Model $record): ?string {
                    $value= '';

                    switch($record->parent_table){
                        case 'users':
                            $Entity= User::find($record->parent_id);
                            $value= $Entity?->name;
                            break;
                        case 'workers':
                            $Entity= Worker::find($record->parent_id);
                            $value= $Entity?->name;
                            break;
                        case 'storages':
                            $Entity= ModelsStorage::find($record->parent_id);
                            $value= $Entity?->name;
                            break;
                        case 'projects':
                            $Entity= Project::find($record->parent_id);
                            $value= $Entity?->name;
                            break;
                        case 'storage_items':
                            $Entity= Item::find($record->parent_id);
                            $value= $Entity?->name;
                            break;
                        case 'contractors':
                            $Entity= Contractor::find($record->parent_id);
                            $value= $Entity?->name;
                            break;
                        case 'items':
                            $Entity= Item::find($record->parent_id);
                            $value= $Entity?->name;
                            break;
                    }

                    return $value;
                })->searchable()->sortable()->label(__('backend.party')),
                TextColumn::make('expiry_date')
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(function (File $record): string {
                        if (! $record->expiry_date) {
                            return '';
                        }

                        $daysUntilExpiry = now()->diffInDays($record->expiry_date, false);

                        return $daysUntilExpiry < 0
                            ? __('backend.expired_since_days', ['days' => abs((int) $daysUntilExpiry)])
                            : __('backend.valid');
                    })
                    ->color(fn (File $record): string => ($record->expiry_date && now()->greaterThan($record->expiry_date)) ? 'danger' : 'success')
                    ->description(fn (File $record): ?string => optional($record->expiry_date)->format('d-m-Y'))
                    ->label(__('backend.expiry_date')),
            ])
            ->actionsColumnLabel(__('backend.actions'))
            ->emptyStateHeading(__('backend.not_found').' '.__('backend.files'))
            ->actions([
                Tables\Actions\ViewAction::make()->form([
                    Group::make()->schema([
                        Section::make()->schema([
                            TextInput::make('name')->required()->label(__('backend.name')),
                            DatePicker::make('expiry_date')->label(__('backend.expiry_date')),
                            MarkdownEditor::make('description')->columnSpanFull()->label(__('backend.description')),
                        ])->columns(2),
                    ]),

                    Group::make()->schema([
                        Section::make()->schema([
                            FileUpload::make('file')->directory('documents')->required()->label(__('backend.document')),
                        ]),

                        Section::make()->schema([
                            Toggle::make('is_active')->default(true)->label(__('backend.active')),
                        ]),
                    ]),
                ])->icon('heroicon-o-eye')->iconButton()->recordTitle(__('backend.file')),

                Tables\Actions\EditAction::make()->form([
                    Group::make()->schema([
                        Section::make()->schema([
                            TextInput::make('name')->required()->label(__('backend.name')),
                            DatePicker::make('expiry_date')->label(__('backend.expiry_date')),
                            MarkdownEditor::make('description')->columnSpanFull()->label(__('backend.description')),
                        ])->columns(2),
                    ]),

                    Group::make()->schema([
                        Section::make()->schema([
                            FileUpload::make('file')->directory('documents')->required()->label(__('backend.document')),
                        ]),

                        Section::make()->schema([
                            Toggle::make('is_active')->default(true)->label(__('backend.active')),
                        ]),
                    ]),
                ])->iconButton()->recordTitle(__('backend.file')),

                Tables\Actions\Action::make('download_file')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->tooltip(__('backend.download'))
                    ->iconButton()
                    ->color('success')
                    ->action(function(array $data, $record) : StreamedResponse {
                        $filePath= 'public/'.$record->file;

                        if(!Storage::exists($filePath)){
                            abort(404, __('backend.file_not_found'));
                        }

                        return Storage::download($filePath);
                    })->label(__('backend.download')),
            ])
            ->filters([
                SelectFilter::make('parent_table')->options([
                    'users' => __('backend.users'),
                    'workers' => __('backend.workers'),
                    'storages' => __('backend.storages'),
                    'projects' => __('backend.projects'),
                    'storage_items' => __('backend.storage_items'),
                    'contractors' => __('backend.contractors'),
                    'items' => __('backend.items'),
                    'companies' => __('backend.companies'),
                ])->query(function(Builder $query, array $data): Builder {
                    if(isset($data['value']) && $data['value'] !== null){
                        $parent_table= $data['value'];

                        return $query->where('parent_table', $parent_table);
                    }

                    return $query;
                })->label(__('backend.section')),

                SelectFilter::make('year')->options(function(){
                    $years= [];

                    for($i= (intval(date('Y')) - 25); $i <= date('Y'); $i++){
                        $years[$i]= $i;
                    }

                    return $years;
                })->query(function(Builder $query, array $data): Builder {
                    if(isset($data['value']) && $data['value'] !== null){
                        $selected_year= (int) $data['value'];

                        return $query->whereYear('expiry_date', $selected_year);
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

                        return $query->whereMonth('expiry_date', $selected_month);
                    }

                    return $query;
                })->label(__('backend.month')),
            ]);
    }

    public function getBreadcrumbs(): array
    {
        return [__('backend.reports'), __('backend.files')];
    }

    public function getHeading(): string|Htmlable
    {
        return __('backend.expired_files_report');
    }

    public static function getNavigationLabel(): string
    {
        return __('backend.expired_files');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('backend.reports');
    }

    public static function getBreadcrumb(): string
    {
        return __('backend.expired_files');
    }

    public static function getModelLabel(): string
    {
        return __('backend.expired_files');
    }

    public static function getPluralLabel(): ?string
    {
        return __('backend.expired_files');
    }

    public static function canAccess(): bool
    {
        $Subscription= Subscription::where(['company_id' => Auth::user()->company_id, 'is_active' => 1])->orderBy('id', 'desc')->first();

        if(!$Subscription) return false;

        if(!$Subscription->package) return false;

        if(!$Subscription->package->has_expired_files_report) return false;

        $Role= Auth::user()->role;
        return $Role->can_read_expired_files_report;
    }
}
