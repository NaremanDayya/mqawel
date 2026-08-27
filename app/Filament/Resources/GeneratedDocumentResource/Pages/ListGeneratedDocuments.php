<?php

namespace App\Filament\Resources\GeneratedDocumentResource\Pages;

use App\Filament\Concerns\HasMockupPageHeader;
use App\Filament\Resources\DocumentTemplateResource;
use App\Filament\Resources\GeneratedDocumentResource;
use App\Models\DocumentTemplate;
use App\Models\GeneratedDocument;
use App\Models\Project;
use App\Services\Ai\AiRequestException;
use App\Services\Ai\DocumentDraftingService;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Exceptions\Halt;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ListGeneratedDocuments extends ListRecords
{
    use HasMockupPageHeader;

    protected static string $resource = GeneratedDocumentResource::class;

    protected function pageSubtitle(): ?string
    {
        return __('backend.document_generator_subtitle');
    }

    /**
     * table() branches on $activeTab to swap in a completely different table
     * config for the "templates" tab (content grid instead of a plain list).
     * Filament's tab click just does $set('activeTab', ...), which updates
     * the property during hydrate — but the cached table on $this->table was
     * already built during boot on a previous request and won't rebuild on
     * its own. Livewire's updated{Property}() hook forces that rebuild.
     */
    public function updatedActiveTab(): void
    {
        parent::updatedActiveTab();
        $this->resetTable();
    }

    public function getTabs(): array
    {
        $companyId = Auth::user()->company_id;
        $documentsQuery = fn () => GeneratedDocument::query()->where('company_id', $companyId);

        $tabs = [
            'mine' => Tab::make(__('backend.my_documents'))
                ->icon('heroicon-o-document')
                ->badge(fn () => $documentsQuery()->count())
                ->badgeColor('gray'),
        ];

        // Document sections are per-company and user-managed (see the
        // "manage sections" header action below) — companies differ in
        // what sections they need, so this isn't a fixed list.
        foreach (GeneratedDocumentResource::companyCategories() as $category) {
            $key = $category['key'];

            $tabs[$key] = Tab::make($category['label'])
                ->icon($category['icon'] ?? 'heroicon-o-tag')
                ->badge(fn () => $documentsQuery()->where('category', $key)->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', $key));
        }

        $tabs['templates'] = Tab::make(__('backend.templates_library'))
            ->icon('heroicon-o-folder')
            ->badge(fn () => DocumentTemplate::query()
                ->where(fn (Builder $q) => $q->where('company_id', $companyId)->orWhereNull('company_id'))
                ->count())
            ->badgeColor('gray');

        return $tabs;
    }

    public function table(Table $table): Table
    {
        if ($this->activeTab === 'templates') {
            $companyId = Auth::user()->company_id;

            return $table
                ->query(DocumentTemplate::query()->where(fn (Builder $q) => $q->where('company_id', $companyId)->orWhereNull('company_id')))
                ->contentGrid(['default' => 1, 'md' => 2, 'xl' => 3])
                ->columns([
                    Split::make([
                        TextColumn::make('name')
                            ->weight('bold')
                            ->icon('heroicon-o-document-text')
                            ->iconColor('primary')
                            ->label(__('backend.document_type')),
                    ]),
                    Stack::make([
                        TextColumn::make('category')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => GeneratedDocumentResource::categoryOptions()[$state] ?? $state)
                            ->label(__('backend.document_section')),
                        TextColumn::make('last_used_at')
                            ->date()
                            ->placeholder('—')
                            ->size('sm')
                            ->color('gray')
                            ->label(__('backend.last_used')),
                    ])->space(2),
                ])
                ->actions([
                    Tables\Actions\Action::make('use_template')
                        ->label(__('backend.create'))
                        ->icon('heroicon-o-sparkles')
                        ->color('primary')
                        ->action(fn (DocumentTemplate $record) => $this->replaceMountedAction('create', [
                            'name' => $record->name,
                            'category' => $record->category,
                            'file' => $record->file ? $this->copyTemplateFile($record->file) : null,
                        ])),
                    Tables\Actions\Action::make('edit_template')
                        ->label(__('backend.edit'))
                        ->icon('heroicon-o-pencil-square')
                        ->color('gray')
                        ->url(fn (DocumentTemplate $record): string => DocumentTemplateResource::getUrl('edit', ['record' => $record])),
                    Tables\Actions\DeleteAction::make(),
                ])
                ->recordUrl(null);
        }

        return GeneratedDocumentResource::table($table);
    }

    protected function getHeaderActions(): array
    {
        $importAction = Actions\Action::make('importDocument')
            ->label(__('backend.import'))
            ->icon('heroicon-o-arrow-up-tray')
            ->color('gray')
            ->modalHeading(__('backend.import'))
            ->modalDescription(__('backend.import_description'))
            ->modalSubmitActionLabel(__('backend.import'))
            ->form([
                TextInput::make('name')
                    ->label(__('backend.document_type'))
                    ->required(),

                Select::make('category')
                    ->label(__('backend.document_section'))
                    ->options(GeneratedDocumentResource::categoryOptions())
                    ->required(),

                FileUpload::make('file')
                    ->label(__('backend.document'))
                    ->directory('documents')
                    ->maxSize(10240)
                    ->helperText(__('backend.max_file_size_10_mb'))
                    ->openable()
                    ->downloadable()
                    ->required(),
            ])
            ->action(function (array $data) {
                GeneratedDocument::create([
                    'company_id' => Auth::user()->company_id,
                    'created_by' => Auth::user()->id,
                    'name' => $data['name'],
                    'category' => $data['category'],
                    'file' => $data['file'],
                    'status' => 'draft',
                ]);

                Notification::make()->title(__('backend.settings_saved'))->success()->send();
            });

        $manageSectionsAction = Actions\Action::make('manageDocumentSections')
            ->label(__('backend.manage_sections'))
            ->tooltip(__('backend.manage_sections'))
            ->icon('heroicon-o-adjustments-horizontal')
            ->iconButton()
            ->color('gray')
            ->modalHeading(__('backend.manage_sections'))
            ->modalDescription(__('backend.manage_sections_description'))
            ->modalSubmitActionLabel(__('backend.save_settings'))
            ->fillForm(fn (): array => ['categories' => GeneratedDocumentResource::companyCategories()])
            ->form([
                Repeater::make('categories')
                    ->label(__('backend.document_sections'))
                    ->hiddenLabel()
                    ->schema([
                        Hidden::make('key')->default(fn () => (string) Str::uuid()),
                        Hidden::make('icon')->default('heroicon-o-tag'),
                        TextInput::make('label')->label(__('backend.name'))->required(),
                    ])
                    ->addActionLabel(__('backend.add_section'))
                    ->reorderable(false)
                    ->minItems(1)
                    ->columns(1),
            ])
            ->action(function (array $data) {
                Auth::user()->company->update([
                    'document_categories' => collect($data['categories'])
                        ->map(fn (array $row) => [
                            'key' => filled($row['key'] ?? null) ? $row['key'] : (string) Str::uuid(),
                            'label' => $row['label'],
                            'icon' => $row['icon'] ?? 'heroicon-o-tag',
                        ])
                        ->all(),
                ]);

                Notification::make()->title(__('backend.settings_saved'))->success()->send();
            });

        $createAction = Actions\CreateAction::make()
                ->label(__('backend.new_document'))
                ->modalHeading(fn (): string => __('backend.new_document').' — AI')
                ->modalDescription(__('backend.document_creator_description'))
                ->modalSubmitActionLabel(__('backend.create'))
                ->fillForm(fn (array $arguments): array => [
                    'name' => $arguments['name'] ?? null,
                    'category' => $arguments['category'] ?? null,
                ])
                ->steps([
                    Step::make('validate')
                        ->label(__('backend.wizard_step_validate_template'))
                        ->icon('heroicon-o-clipboard-document-check')
                        ->schema([
                            TextInput::make('name')
                                ->label(__('backend.document_type'))
                                ->required()
                                ->datalist(fn () => DocumentTemplate::query()->pluck('name')->all()),

                            Grid::make(2)->schema([
                                Select::make('project_id')
                                    ->label(__('backend.link_project'))
                                    ->options(fn () => Project::query()
                                        ->where('company_id', Auth::user()->company_id)
                                        ->pluck('name', 'id'))
                                    ->searchable()
                                    ->placeholder(__('backend.general')),

                                TextInput::make('related_party')
                                    ->label(__('backend.party')),
                            ]),

                            Select::make('category')
                                ->label(__('backend.document_section'))
                                ->options(GeneratedDocumentResource::categoryOptions())
                                ->required(),

                            Textarea::make('details')
                                ->label(__('backend.additional_details')),
                        ]),

                    Step::make('generate')
                        ->label(__('backend.wizard_step_generate'))
                        ->icon('heroicon-o-sparkles')
                        ->schema([
                            Placeholder::make('generate_notice')
                                ->label('')
                                ->content(__('backend.wizard_generate_description')),
                        ])
                        ->afterValidation(function (Get $get, Set $set) {
                            $document = new GeneratedDocument([
                                'name' => $get('name'),
                                'category' => $get('category'),
                                'project_id' => $get('project_id'),
                                'related_party' => $get('related_party'),
                                'details' => $get('details'),
                            ]);

                            try {
                                $draft = app(DocumentDraftingService::class)->draft($document);
                            } catch (AiRequestException $e) {
                                Notification::make()->title(__('backend.draft_generation_failed'))->danger()->send();

                                throw new Halt;
                            }

                            $set('content', $draft);

                            Notification::make()->title(__('backend.draft_generated_notification'))->success()->send();
                        }),

                    Step::make('review')
                        ->label(__('backend.wizard_step_review'))
                        ->icon('heroicon-o-pencil-square')
                        ->schema([
                            MarkdownEditor::make('content')
                                ->label(__('backend.document_content'))
                                ->required(),
                        ]),

                    Step::make('final')
                        ->label(__('backend.wizard_step_final_output'))
                        ->icon('heroicon-o-check-circle')
                        ->schema([
                            Placeholder::make('final_notice')
                                ->label('')
                                ->content(__('backend.wizard_final_output_description')),

                            Select::make('status')
                                ->label(__('backend.status'))
                                ->options(GeneratedDocumentResource::statusOptions())
                                ->default('draft')
                                ->required(),

                            FileUpload::make('letterhead')
                                ->label(__('backend.letterhead'))
                                ->helperText(__('backend.letterhead_hint'))
                                ->acceptedFileTypes(['application/pdf', 'image/png', 'image/jpeg', 'image/webp'])
                                ->directory('letterheads')
                                ->maxSize(10240)
                                ->openable()
                                ->default(fn () => Auth::user()->company->letterhead),

                            FileUpload::make('file')
                                ->label(__('backend.document'))
                                ->helperText(__('backend.template_file_prefill_hint'))
                                ->directory('documents')
                                ->maxSize(10240)
                                ->openable()
                                ->downloadable(),
                        ]),
                ])
                ->using(function (array $data): GeneratedDocument {
                    if (array_key_exists('letterhead', $data)) {
                        Auth::user()->company->update(['letterhead' => $data['letterhead']]);
                    }

                    return GeneratedDocument::create([
                        'company_id' => Auth::user()->company_id,
                        'created_by' => Auth::user()->id,
                        'name' => $data['name'],
                        'category' => $data['category'],
                        'project_id' => $data['project_id'] ?? null,
                        'related_party' => $data['related_party'] ?? null,
                        'details' => $data['details'] ?? null,
                        'content' => $data['content'] ?? null,
                        'file' => $data['file'] ?? null,
                        'status' => $data['status'] ?? 'draft',
                    ]);
                });

        $addTemplateAction = Actions\Action::make('addTemplate')
            ->label(__('backend.add_template'))
            ->icon('heroicon-o-folder-plus')
            ->color('gray')
            ->visible(fn (): bool => $this->activeTab === 'templates')
            ->url(fn (): string => DocumentTemplateResource::getUrl('create'));

        return [
            $manageSectionsAction,
            $addTemplateAction,
            $importAction,
            $createAction,
        ];
    }

    protected function copyTemplateFile(string $templatePath): ?string
    {
        $extension = pathinfo($templatePath, PATHINFO_EXTENSION);
        $newPath = 'documents/'.Str::uuid().($extension ? ".{$extension}" : '');

        if (! Storage::disk('public')->exists($templatePath)) {
            return null;
        }

        Storage::disk('public')->copy($templatePath, $newPath);

        return $newPath;
    }
}
