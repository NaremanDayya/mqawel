<?php

namespace App\Filament\Resources\WorkerResource\Pages;

use App\Filament\Concerns\TogglesRecordsView;
use App\Filament\Resources\WorkerResource;
use App\Models\Project;
use App\Models\ProjectWorker;
use App\Models\Worker;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ListWorkers extends ListRecords
{
    use TogglesRecordsView;

    protected static string $resource = WorkerResource::class;

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
            ToggleColumn::make('is_active')->onColor('success')->label(__('backend.active')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('backend.add_worker'))
                ->modalHeading(__('backend.add_worker'))
                ->modalDescription(__('backend.add_worker_description'))
                ->modalSubmitActionLabel(__('backend.add_worker'))
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

                        Select::make('ethnicity')
                            ->label(__('backend.ethnicity'))
                            ->required()
                            ->options([
                                __('backend.nationality_indian') => __('backend.nationality_indian'),
                                __('backend.nationality_pakistani') => __('backend.nationality_pakistani'),
                                __('backend.nationality_bangladeshi') => __('backend.nationality_bangladeshi'),
                                __('backend.nationality_omani') => __('backend.nationality_omani'),
                            ]),
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
}
