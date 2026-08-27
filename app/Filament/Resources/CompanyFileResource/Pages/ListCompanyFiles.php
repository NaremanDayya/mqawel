<?php

namespace App\Filament\Resources\CompanyFileResource\Pages;

use App\Filament\Concerns\AppliesCompanyLetterhead;
use App\Filament\Concerns\HasMockupPageHeader;
use App\Filament\Concerns\HasSectionNotificationSettings;
use App\Filament\Resources\CompanyFileResource;
use App\Models\File;
use App\Models\Project;
use App\Models\Worker;
use App\Services\Ai\AiRequestException;
use App\Services\Ai\DocumentExtractionService;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ListCompanyFiles extends ListRecords
{
    use AppliesCompanyLetterhead;
    use HasMockupPageHeader;
    use HasSectionNotificationSettings;

    protected static string $resource = CompanyFileResource::class;

    protected function pageSubtitle(): ?string
    {
        return __('backend.documents_page_subtitle');
    }

    public function getTabs(): array
    {
        $baseQuery = fn () => File::query()->where('company_id', Auth::user()->company_id);

        return [
            'companies' => Tab::make(__('backend.company_documents_section'))
                ->icon('heroicon-o-briefcase')
                ->badge(fn () => $baseQuery()->where('parent_table', 'companies')->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('parent_table', 'companies')),
            'projects' => Tab::make(__('backend.project_documents_section'))
                ->icon('heroicon-o-map-pin')
                ->badge(fn () => $baseQuery()->where('parent_table', 'projects')->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('parent_table', 'projects')),
            'workers' => Tab::make(__('backend.worker_documents_section'))
                ->icon('heroicon-o-user-group')
                ->badge(fn () => $baseQuery()->where('parent_table', 'workers')->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('parent_table', 'workers')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->notificationSettingsAction('documents', __('backend.company_documents_section')),

            Actions\Action::make('aiScanDocument')
                ->label(__('backend.ai_scan'))
                ->icon('heroicon-o-viewfinder-circle')
                ->color('primary')
                ->extraAttributes(['class' => 'mq-ai-btn'])
                ->modalHeading(__('backend.ai_scan'))
                ->modalDescription(__('backend.ai_scan_description'))
                ->modalSubmitActionLabel(__('backend.ai_scan_action'))
                ->form([
                    FileUpload::make('scan')
                        ->label(__('backend.ai_scan_upload_label'))
                        ->image()
                        ->directory('documents')
                        ->maxSize(10240)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $path = $data['scan'];
                    $absolutePath = Storage::disk('public')->path($path);
                    $mime = Storage::disk('public')->mimeType($path) ?: 'image/jpeg';

                    try {
                        $extracted = app(DocumentExtractionService::class)->extract([['path' => $absolutePath, 'mime' => $mime]]);
                    } catch (AiRequestException $e) {
                        Notification::make()->title(__('backend.ai_scan_failed'))->danger()->send();
                        $this->replaceMountedAction('create', ['file' => $path]);

                        return;
                    }

                    $typeLabel = match ($extracted['document_type']) {
                        'national_id' => __('backend.document_type_national_id'),
                        'passport' => __('backend.document_type_passport'),
                        default => __('backend.document_type_other'),
                    };

                    $this->replaceMountedAction('create', [
                        'name' => $typeLabel,
                        'expiry_date' => $extracted['expiry_date'],
                        'file' => $path,
                    ]);
                }),

            Actions\CreateAction::make()
                ->label(__('backend.add_document'))
                ->icon('heroicon-o-plus')
                ->modalHeading(__('backend.add_document'))
                ->modalDescription(__('backend.add_document_description'))
                ->modalSubmitActionLabel(__('backend.add_document'))
                ->fillForm(fn (array $arguments): array => $arguments)
                ->form([
                    TextInput::make('name')
                        ->label(__('backend.name'))
                        ->required()
                        ->unique('files', 'name', modifyRuleUsing: fn ($rule) => $rule->where('company_id', Auth::user()->company_id))
                        ->validationMessages([
                            'unique' => __('backend.unavailable_to_use'),
                        ]),

                    Grid::make(2)->schema([
                        Select::make('section')
                            ->label(__('backend.document_section'))
                            ->required()
                            ->live()
                            ->default('companies')
                            ->options([
                                'companies' => __('backend.company_documents_section'),
                                'projects' => __('backend.project_documents_section'),
                                'workers' => __('backend.worker_documents_section'),
                            ])
                            ->afterStateUpdated(fn (Set $set) => $set('parent_id', null)),

                        Select::make('parent_id')
                            ->label(__('backend.related_entity'))
                            ->options(fn (Get $get) => match ($get('section')) {
                                'projects' => Project::query()->where('company_id', Auth::user()->company_id)->pluck('name', 'id'),
                                'workers' => Worker::query()->where('company_id', Auth::user()->company_id)->pluck('name', 'id'),
                                default => [],
                            })
                            ->searchable()
                            ->required(fn (Get $get) => $get('section') !== 'companies')
                            ->hidden(fn (Get $get) => $get('section') === 'companies'),
                    ]),

                    DatePicker::make('issue_date')
                        ->label(__('backend.issue_date')),

                    Select::make('validity_type')
                        ->options([
                            'permanent' => __('backend.validity_type_permanent'),
                            'temporary' => __('backend.validity_type_temporary'),
                        ])
                        ->default('permanent')
                        ->label(__('backend.validity_type')),

                    DatePicker::make('expiry_date')
                        ->label(__('backend.expiry_date'))
                        ->required(),

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
                        ->directory(fn () => 'documents/'.\Illuminate\Support\Str::uuid())
                        ->preserveFilenames()
                        ->maxSize(10240)
                        ->helperText(__('backend.max_file_size_10_mb'))
                        ->openable()
                        ->downloadable()
                        ->required(),

                    static::letterheadFormField()
                        ->visible(fn (Get $get) => $get('section') === 'companies'),
                ])
                ->using(function (array $data): File {
                    $companyId = Auth::user()->company_id;
                    $parentId = $data['section'] === 'companies' ? $companyId : $data['parent_id'];

                    $limits = ['companies' => 30, 'projects' => 20, 'workers' => 10];
                    $limit = $limits[$data['section']] ?? null;

                    if ($limit) {
                        $count = File::where('company_id', $companyId)
                            ->where('parent_table', $data['section'])
                            ->where('parent_id', $parentId)
                            ->count();

                        if ($count >= $limit) {
                            Notification::make()->title(__('backend.file_limit_reached', ['limit' => $limit]))->danger()->send();

                            throw (new \Filament\Support\Exceptions\Halt());
                        }
                    }

                    $file = $data['file'] ?? null;

                    if ($data['section'] === 'companies') {
                        $letterhead = static::persistLetterhead($data);
                        $file = static::stampLetterheadOnUploadedFile($file, $letterhead);
                    }

                    return File::create([
                        'company_id' => $companyId,
                        'created_by' => Auth::user()->id,
                        'parent_table' => $data['section'],
                        'parent_id' => $data['section'] === 'companies' ? $companyId : $data['parent_id'],
                        'name' => $data['name'],
                        'expiry_date' => $data['expiry_date'],
                        'category' => $data['category'] ?? 'general',
                        'file' => $file,
                        'is_active' => true,
                    ]);
                }),
        ];
    }
}
