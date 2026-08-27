<?php

namespace App\Filament\Resources\WorkerResource\Pages;

use App\Filament\Concerns\HasMockupPageHeader;
use App\Filament\Concerns\HasSectionNotificationSettings;
use App\Filament\Concerns\TogglesRecordsView;
use App\Filament\Pages\WorkersReport;
use App\Filament\Resources\WorkerResource;
use App\Models\Project;
use App\Models\ProjectWorker;
use App\Models\Worker;
use App\Services\Ai\AiRequestException;
use App\Services\Ai\DocumentExtractionService;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ListWorkers extends ListRecords
{
    use HasMockupPageHeader;
    use HasSectionNotificationSettings;
    use TogglesRecordsView;

    protected static string $resource = WorkerResource::class;

    protected function pageSubtitle(): ?string
    {
        return __('backend.workers_page_subtitle');
    }

    public function table(Table $table): Table
    {
        return $this->applyRecordsViewToggle(parent::table($table), $this->workerCardColumns());
    }

    protected function workerCardColumns(): array
    {
        return [
            Split::make([
                ImageColumn::make('picture')
                    ->defaultImageUrl(asset('images/no_profile_picture.png'))
                    ->circular()
                    ->width(48)
                    ->height(48)
                    ->grow(false),
                Stack::make([
                    TextColumn::make('name')->weight('bold')->label(__('backend.name')),
                    TextColumn::make('job_title')->color('gray')->size('sm')->label(__('backend.job_title')),
                ]),
            ]),
            Stack::make([
                TextColumn::make('ethnicity')
                    ->formatStateUsing(fn (?string $state) => __('backend.ethnicity').': '.($state ?? '—'))
                    ->size('sm'),
                TextColumn::make('projects.project.name')
                    ->listWithLineBreaks()
                    ->limitList(1)
                    ->size('sm')
                    ->color('gray'),
                TextColumn::make('living_type')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'temporary' => __('backend.temporary'),
                        'primary' => __('backend.primary'),
                        default => '—',
                    })
                    ->badge(),
            ])->space(2),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->notificationSettingsAction('workers', __('backend.workers')),

            Actions\Action::make('workersReport')
                ->label(__('backend.workers_report'))
                ->icon('heroicon-o-chart-bar')
                ->color('gray')
                ->url(WorkersReport::getUrl()),

            Actions\Action::make('aiScanWorker')
                ->label(__('backend.ai_scan'))
                ->icon('heroicon-o-viewfinder-circle')
                ->color('primary')
                ->extraAttributes(['class' => 'mq-ai-btn'])
                ->modalHeading(__('backend.ai_scan'))
                ->modalDescription(__('backend.ai_scan_new_worker_description'))
                ->modalSubmitActionLabel(__('backend.ai_scan_action'))
                ->form([
                    Select::make('document_type')
                        ->label(__('backend.document_type'))
                        ->options([
                            'passport' => __('backend.document_type_passport'),
                            'national_id' => __('backend.document_type_national_id'),
                        ])
                        ->default('passport')
                        ->live()
                        ->required(),

                    FileUpload::make('scan')
                        ->label(__('backend.ai_scan_upload_label'))
                        ->image()
                        ->directory('form-attachments')
                        ->maxSize(10240)
                        ->visible(fn (Get $get) => $get('document_type') !== 'national_id')
                        ->required(fn (Get $get) => $get('document_type') !== 'national_id'),

                    Grid::make(2)
                        ->schema([
                            FileUpload::make('scan_front')
                                ->label(__('backend.id_card_front'))
                                ->image()
                                ->directory('form-attachments')
                                ->maxSize(10240)
                                ->required(fn (Get $get) => $get('document_type') === 'national_id'),

                            FileUpload::make('scan_back')
                                ->label(__('backend.id_card_back'))
                                ->image()
                                ->directory('form-attachments')
                                ->maxSize(10240),
                        ])
                        ->visible(fn (Get $get) => $get('document_type') === 'national_id'),
                ])
                ->action(function (array $data) {
                    $isNationalId = $data['document_type'] === 'national_id';

                    $paths = $isNationalId
                        ? array_filter([$data['scan_front'] ?? null, $data['scan_back'] ?? null])
                        : array_filter([$data['scan'] ?? null]);

                    $images = array_map(fn (string $path) => [
                        'path' => Storage::disk('public')->path($path),
                        'mime' => Storage::disk('public')->mimeType($path) ?: 'image/jpeg',
                    ], $paths);

                    $picture = array_values($paths)[0] ?? null;

                    try {
                        $extracted = app(DocumentExtractionService::class)->extract($images);
                    } catch (AiRequestException $e) {
                        Notification::make()->title(__('backend.ai_scan_failed'))->danger()->send();
                        $this->replaceMountedAction('create', ['picture' => $picture]);

                        return;
                    }

                    $this->replaceMountedAction('create', [
                        'picture' => $picture,
                        'name' => $extracted['full_name'],
                        'ethnicity' => $this->matchNationality($extracted['nationality']),
                        'id_number' => $extracted['id_number'],
                        'job_title' => $extracted['job_title'],
                    ]);
                }),

            Actions\CreateAction::make()
                ->label(__('backend.add_worker'))
                ->icon('heroicon-o-plus')
                ->modalHeading(__('backend.add_worker'))
                ->modalDescription(__('backend.add_worker_description'))
                ->modalSubmitActionLabel(__('backend.add_worker'))
                ->fillForm(fn (array $arguments): array => $arguments)
                ->form([
                    FileUpload::make('picture')
                        ->label(__('backend.picture'))
                        ->avatar()
                        ->directory('form-attachments'),

                    TextInput::make('name')
                        ->label(__('backend.name'))
                        ->required()
                        ->unique('workers', 'name', modifyRuleUsing: fn ($rule) => $rule->where('company_id', Auth::user()->company_id))
                        ->validationMessages([
                            'unique' => __('backend.unavailable_to_use'),
                        ]),

                    Grid::make(2)->schema([
                        TextInput::make('phone')
                            ->label(__('backend.phone'))
                            ->tel(),

                        TextInput::make('ethnicity')
                            ->label(__('backend.ethnicity'))
                            ->required(),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('id_number')
                            ->label(__('backend.id_number')),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('job_title')
                            ->label(__('backend.job_title'))
                            ->required(),

                        Select::make('project_id')
                            ->label(__('backend.linked_project'))
                            ->options(fn () => Project::query()
                                ->where('company_id', Auth::user()->company_id)
                                ->pluck('name', 'id'))
                            ->searchable(),
                    ]),

                    MarkdownEditor::make('job_description')
                        ->label(__('backend.job_description')),
                ])
                ->using(function (array $data): Worker {
                    $companyId = Auth::user()->company_id;

                    $worker = Worker::create([
                        'company_id' => $companyId,
                        'created_by' => Auth::user()->id,
                        'picture' => $data['picture'] ?? null,
                        'name' => $data['name'],
                        'phone' => $data['phone'] ?? null,
                        'ethnicity' => $data['ethnicity'] ?? null,
                        'job_title' => $data['job_title'],
                        'job_description' => $data['job_description'] ?? null,
                        'is_active' => true,
                    ]);

                    if (! empty($data['project_id'])) {
                        ProjectWorker::create([
                            'company_id' => $companyId,
                            'project_id' => $data['project_id'],
                            'worker_id' => $worker->id,
                            'date' => now(),
                            'created_by' => Auth::user()->id,
                        ]);
                    }

                    return $worker;
                })
                ->after(function (Worker $record) {
                    foreach (Auth::user()->company->users ?? [] as $user) {
                        $message = $user->locale === 'en'
                            ? 'New worker "'.$record->name.'" added to the company records'
                            : 'تمت اضافة عامل جديد "'.$record->name.'" الى سجل الشركة';

                        Notification::make()->title($message)->sendToDatabase($user);
                    }
                }),
        ];
    }

    protected function matchNationality(?string $extracted): ?string
    {
        if (blank($extracted)) {
            return null;
        }

        $needle = mb_strtolower(trim($extracted));

        $map = [
            'indian' => __('backend.nationality_indian'),
            'india' => __('backend.nationality_indian'),
            __('backend.nationality_indian') => __('backend.nationality_indian'),
            'pakistani' => __('backend.nationality_pakistani'),
            'pakistan' => __('backend.nationality_pakistani'),
            __('backend.nationality_pakistani') => __('backend.nationality_pakistani'),
            'bangladeshi' => __('backend.nationality_bangladeshi'),
            'bangladesh' => __('backend.nationality_bangladeshi'),
            __('backend.nationality_bangladeshi') => __('backend.nationality_bangladeshi'),
            'omani' => __('backend.nationality_omani'),
            'oman' => __('backend.nationality_omani'),
            __('backend.nationality_omani') => __('backend.nationality_omani'),
        ];

        foreach ($map as $key => $value) {
            if (str_contains($needle, mb_strtolower($key))) {
                return $value;
            }
        }

        return null;
    }
}
