<?php

namespace App\Filament\Resources\WorkerResource\RelationManagers;

use App\Services\Ai\AiRequestException;
use App\Services\Ai\DocumentExtractionService;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FilesRelationManager extends RelationManager
{
    protected static string $relationship = 'Files';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('company_id')->default(Auth::user()->company_id),

                Hidden::make('created_by')->default(Auth::user()->id),

                Hidden::make('parent_table')->default('workers'),

                Hidden::make('parent_id')->default($this->ownerRecord->id),

                Group::make()->schema([
                    Section::make()->schema([
                        TextInput::make('name')->unique(ignoreRecord:true, modifyRuleUsing: function($rule, $context, $record){
                            return $rule->where('company_id', Auth::user()->company_id);
                        })->validationMessages([
                            'unique' => __('backend.unavailable_to_use'),
                        ])->required()->label(__('backend.name')),
                        DatePicker::make('issue_date')->label(__('backend.issue_date')),

                        Select::make('validity_type')->options([
                            'permanent' => __('backend.validity_type_permanent'),
                            'temporary' => __('backend.validity_type_temporary'),
                        ])->default('permanent')->label(__('backend.validity_type')),

                        DatePicker::make('expiry_date')->label(__('backend.expiry_date')),
                        MarkdownEditor::make('description')->columnSpanFull()->label(__('backend.description')),
                    ])->columns(2),
                ]),

                Group::make()->schema([
                    Section::make()->schema([
                        FileUpload::make('file')->directory(fn () => 'documents/'.\Illuminate\Support\Str::uuid())->preserveFilenames()->maxSize(10240)->helperText(__('backend.max_file_size_10_mb'))->openable()->downloadable()->required()->label(__('backend.document')),
                    ]),

                    Section::make()->schema([
                        Toggle::make('is_active')->default(true)->label(__('backend.active')),
                    ]),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('index')->rowIndex()->label(__('backend.row_number')),
                TextColumn::make('name')->label(__('backend.name')),
                TextColumn::make('expiry_date')->date()->label(__('backend.expiry_date')),
                TextColumn::make('validity_type')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'temporary' => __('backend.validity_type_temporary'),
                    default => __('backend.validity_type_permanent'),
                })->badge()->toggleable(isToggledHiddenByDefault: true)->label(__('backend.validity_type')),
                IconColumn::make('is_active')->boolean()->label(__('backend.active')),
            ])
            ->emptyStateHeading(__('backend.not_found').' '.__('backend.files'))
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('aiScan')
                    ->label(__('backend.ai_scan_document'))
                    ->icon('heroicon-o-sparkles')
                    ->color('primary')
                    ->modalHeading(__('backend.ai_scan_document'))
                    ->modalDescription(__('backend.ai_scan_description'))
                    ->modalSubmitActionLabel(__('backend.ai_scan_action'))
                    ->form([
                        FileUpload::make('scan')
                            ->label(__('backend.ai_scan_upload_label'))
                            ->image()
                            ->directory(fn () => 'documents/'.\Illuminate\Support\Str::uuid())
                            ->preserveFilenames()
                            ->maxSize(10240)
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $path = $data['scan'];
                        $absolutePath = Storage::disk('public')->path($path);
                        $mime = Storage::disk('public')->mimeType($path) ?: 'image/jpeg';

                        try {
                            $extracted = app(DocumentExtractionService::class)->extract($absolutePath, $mime);
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
                            'description' => __('backend.extracted_data_summary', [
                                'name' => $extracted['full_name'] ?? '—',
                                'id_number' => $extracted['id_number'] ?? '—',
                                'nationality' => $extracted['nationality'] ?? '—',
                            ]),
                            'expiry_date' => $extracted['expiry_date'],
                            'file' => $path,
                        ]);
                    }),
                Tables\Actions\CreateAction::make()
                    ->label(__('backend.register_entry'))
                    ->fillForm(fn (array $arguments): array => $arguments)
                    ->before(function () {
                        if ($this->ownerRecord->files()->count() >= 10) {
                            Notification::make()->title(__('backend.file_limit_reached', ['limit' => 10]))->danger()->send();

                            throw (new \Filament\Support\Exceptions\Halt());
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('download_file')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function(array $data, $record) : StreamedResponse {
                        $filePath= 'public/'.$record->file;

                        if(!Storage::exists($filePath)){
                            abort(404, __('backend.file_not_found'));
                        }

                        return Storage::download($filePath, $record->downloadFilename());
                    })->label(__('backend.download')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('backend.files');
    }

    public static function getModelLabel(): string
    {
        return __('backend.files');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('backend.worker_documents');
    }
}
