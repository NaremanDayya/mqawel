<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\HasMockupPageHeader;
use App\Models\Company;
use App\Models\CompanyActivityLog;
use App\Models\File;
use App\Services\Ai\AiRequestException;
use App\Services\Ai\CompanyProfileAiService;
use App\Services\CompanyProfileInsights;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class CompanyProfile extends Page
{
    use HasMockupPageHeader;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.company-profile';

    public string $activeTab = 'main';

    public static function getNavigationLabel(): string
    {
        return __('backend.company_profile');
    }

    public function getTitle(): string|Htmlable
    {
        return __('backend.company_profile');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('backend.company');
    }

    protected function pageSubtitle(): ?string
    {
        return __('backend.company_profile_subtitle');
    }

    protected function company(): Company
    {
        return Auth::user()->company;
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    /**
     * @return array<int, array{key: string, label: string, percentage: int, color: string}>
     */
    public function getCompletionBreakdown(): array
    {
        return app(CompanyProfileInsights::class)->completionBreakdown($this->company());
    }

    public function getOverallCompletion(): int
    {
        return app(CompanyProfileInsights::class)->overallCompletion($this->company());
    }

    public function getWeakestCategory(): ?array
    {
        return app(CompanyProfileInsights::class)->weakestCategory($this->company());
    }

    public function getFeaturedProjects()
    {
        return app(CompanyProfileInsights::class)->featuredProjects($this->company());
    }

    /**
     * @return array{workers: int, users: int}
     */
    public function getStaffCounts(): array
    {
        return app(CompanyProfileInsights::class)->staffCounts($this->company());
    }

    public function getCompanyFiles()
    {
        return File::where('company_id', $this->company()->id)
            ->where('parent_table', 'companies')
            ->latest()
            ->limit(8)
            ->get();
    }

    public function getActivityLogs()
    {
        return CompanyActivityLog::where('company_id', $this->company()->id)
            ->latest('created_at')
            ->limit(15)
            ->get();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportPdf')
                ->label(__('backend.export'))
                ->color('gray')
                ->icon('heroicon-o-document-text')
                ->action(fn () => $this->exportPdf()),

            Action::make('improveWithAi')
                ->label(__('backend.improve_with_ai'))
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->action(function () {
                    $company = $this->company();

                    try {
                        $draft = app(CompanyProfileAiService::class)->draftAbout(
                            $company,
                            $this->getFeaturedProjects()->pluck('name')->all(),
                        );
                    } catch (AiRequestException $e) {
                        Notification::make()->title(__('backend.ai_scan_failed'))->danger()->send();

                        return;
                    }

                    $this->mountAction('editAbout', ['description' => $draft]);
                }),

            Action::make('createCompanyFile')
                ->label(__('backend.create_file'))
                ->icon('heroicon-o-document-plus')
                ->form([
                    TextInput::make('name')->label(__('backend.name'))->required(),
                    Select::make('category')
                        ->label(__('backend.file_category'))
                        ->options([
                            'certificate' => __('backend.file_category_certificate'),
                            'work_photo' => __('backend.file_category_work_photo'),
                            'general' => __('backend.file_category_general'),
                        ])
                        ->default('general')
                        ->required(),
                    FileUpload::make('file')
                        ->label(__('backend.document'))
                        ->directory('documents')
                        ->maxSize(10240)
                        ->helperText(__('backend.max_file_size_10_mb'))
                        ->required(),
                ])
                ->action(function (array $data) {
                    $company = $this->company();

                    File::create([
                        'company_id' => $company->id,
                        'created_by' => Auth::user()->id,
                        'parent_table' => 'companies',
                        'parent_id' => $company->id,
                        'name' => $data['name'],
                        'category' => $data['category'],
                        'file' => $data['file'] ?? null,
                        'is_active' => true,
                    ]);

                    CompanyActivityLog::log(
                        $company->id,
                        __('backend.activity_file_created', ['name' => $data['name']]),
                        'heroicon-o-document-plus',
                        'clay',
                    );

                    Notification::make()->title(__('backend.settings_saved'))->success()->send();
                }),
        ];
    }

    public function editMainInfoAction(): Action
    {
        return Action::make('editMainInfo')
            ->label(__('backend.edit'))
            ->icon('heroicon-o-pencil-square')
            ->color('gray')
            ->modalHeading(__('backend.main_company_info'))
            ->fillForm(fn () => $this->company()->only([
                'name', 'business_number', 'founded_year', 'address', 'phone', 'email', 'website',
            ]))
            ->form([
                TextInput::make('name')->label(__('backend.name'))->required(),
                TextInput::make('business_number')->label(__('backend.business_number')),
                TextInput::make('founded_year')->label(__('backend.founded_year'))->numeric()->minValue(1900)->maxValue((int) date('Y')),
                TextInput::make('address')->label(__('backend.address')),
                TextInput::make('phone')->label(__('backend.phone'))->tel(),
                TextInput::make('email')->label(__('backend.email'))->email(),
                TextInput::make('website')->label(__('backend.website'))->url(),
            ])
            ->action(function (array $data) {
                $company = $this->company();
                $company->update($data);

                CompanyActivityLog::log($company->id, __('backend.activity_info_updated'));

                Notification::make()->title(__('backend.settings_saved'))->success()->send();
            });
    }

    public function editServicesAction(): Action
    {
        return Action::make('editServices')
            ->label(__('backend.edit'))
            ->icon('heroicon-o-pencil-square')
            ->color('gray')
            ->modalHeading(__('backend.services_offered'))
            ->fillForm(fn () => ['services' => $this->company()->services ?? []])
            ->form([
                TagsInput::make('services')->label(__('backend.services_offered'))->placeholder(__('backend.add_service_placeholder')),
            ])
            ->action(function (array $data) {
                $company = $this->company();
                $company->update(['services' => $data['services']]);

                CompanyActivityLog::log(
                    $company->id,
                    __('backend.activity_services_updated'),
                    'heroicon-o-plus',
                );

                Notification::make()->title(__('backend.settings_saved'))->success()->send();
            });
    }

    public function editAboutAction(): Action
    {
        return Action::make('editAbout')
            ->label(__('backend.edit'))
            ->icon('heroicon-o-pencil-square')
            ->color('gray')
            ->modalHeading(__('backend.about_company'))
            ->fillForm(fn (array $arguments) => ['description' => $arguments['description'] ?? $this->company()->description])
            ->form([
                Textarea::make('description')->label(__('backend.about_company'))->rows(5)->required(),
            ])
            ->action(function (array $data) {
                $company = $this->company();
                $company->update(['description' => $data['description']]);

                CompanyActivityLog::log($company->id, __('backend.activity_about_updated'), 'heroicon-o-sparkles', 'warn');

                Notification::make()->title(__('backend.settings_saved'))->success()->send();
            });
    }

    protected function exportPdf()
    {
        $company = $this->company();

        $html = \Illuminate\Support\Facades\Blade::render('exports.company_profile', [
            'company' => $company,
            'completion' => $this->getCompletionBreakdown(),
            'overall' => $this->getOverallCompletion(),
            'featuredProjects' => $this->getFeaturedProjects(),
            'staffCounts' => $this->getStaffCounts(),
        ]);

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
        }, __('backend.company_profile').'-'.date('Y-m-d H:i').'.pdf');
    }
}
