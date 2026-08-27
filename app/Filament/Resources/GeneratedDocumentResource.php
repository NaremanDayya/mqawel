<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GeneratedDocumentResource\Pages;
use App\Models\GeneratedDocument;
use App\Models\Project;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GeneratedDocumentResource extends Resource
{
    protected static ?string $model = GeneratedDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?int $navigationSort = 4;

    /**
     * @return array<int, array{key: string, label: string, icon: string}>
     */
    public static function defaultCategories(): array
    {
        return [
            ['key' => 'contracts', 'label' => __('backend.documents_category_contracts'), 'icon' => 'heroicon-o-briefcase'],
            ['key' => 'quotes', 'label' => __('backend.documents_category_quotes'), 'icon' => 'heroicon-o-currency-dollar'],
            ['key' => 'letters', 'label' => __('backend.documents_category_letters'), 'icon' => 'heroicon-o-envelope'],
            ['key' => 'correspondence', 'label' => __('backend.documents_category_correspondence'), 'icon' => 'heroicon-o-chat-bubble-left-right'],
        ];
    }

    /**
     * Document sections are per-company and user-managed (see the "manage
     * sections" action on the document generator list), since the sections
     * a company needs vary with its size and business — falls back to a
     * sensible default set for companies that haven't customized it yet.
     *
     * @return array<int, array{key: string, label: string, icon: string}>
     */
    public static function companyCategories(): array
    {
        $stored = Auth::user()?->company?->document_categories;

        return filled($stored) ? $stored : static::defaultCategories();
    }

    public static function categoryOptions(): array
    {
        return collect(static::companyCategories())->pluck('label', 'key')->all();
    }

    public static function statusOptions(): array
    {
        return [
            'draft' => __('backend.document_status_draft'),
            'in_review' => __('backend.document_status_in_review'),
            'sent' => __('backend.document_status_sent'),
            'signed' => __('backend.document_status_signed'),
            'completed' => __('backend.document_status_completed'),
        ];
    }

    public static function statusColor(string $status): string
    {
        return match ($status) {
            'in_review' => 'warning',
            'sent' => 'info',
            'signed', 'completed' => 'success',
            default => 'gray',
        };
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('company_id')->default(Auth::user()->company_id),
                Hidden::make('created_by')->default(Auth::user()->id),

                TextInput::make('name')
                    ->label(__('backend.document_type'))
                    ->required()
                    ->columnSpanFull(),

                Select::make('category')
                    ->label(__('backend.document_section'))
                    ->options(self::categoryOptions())
                    ->required(),

                Select::make('project_id')
                    ->label(__('backend.link_project'))
                    ->options(fn () => Project::query()
                        ->where('company_id', Auth::user()->company_id)
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->placeholder(__('backend.general')),

                TextInput::make('related_party')
                    ->label(__('backend.party')),

                Select::make('status')
                    ->label(__('backend.status'))
                    ->options(self::statusOptions())
                    ->default('draft')
                    ->required(),

                TextInput::make('value')
                    ->label(__('backend.value'))
                    ->numeric(),

                Textarea::make('details')
                    ->label(__('backend.additional_details'))
                    ->columnSpanFull(),

                MarkdownEditor::make('content')
                    ->label(__('backend.document_content'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query->where('company_id', Auth::user()->company_id)->where('company_id', '<>', null);
            })
            ->columns([
                TextColumn::make('index')->rowIndex()->label(__('backend.row_number')),
                TextColumn::make('name')->searchable()->sortable()->label(__('backend.document')),
                TextColumn::make('project.name')
                    ->default(__('backend.general'))
                    ->label(__('backend.project')),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => self::statusColor($state))
                    ->label(__('backend.status')),
                TextColumn::make('related_party')->label(__('backend.party')),
                TextColumn::make('value')->numeric()->label(__('backend.value')),
                TextColumn::make('created_at')->date()->sortable()->label(__('backend.date')),
            ])
            ->emptyStateHeading(__('backend.no_documents_yet'))
            ->actionsColumnLabel(__('backend.actions'))
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton(),
                Tables\Actions\Action::make('download')
                    ->label(__('backend.download'))
                    ->tooltip(__('backend.download'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->iconButton()
                    ->color('gray')
                    ->action(fn (GeneratedDocument $record) => static::downloadDocument($record)),
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGeneratedDocuments::route('/'),
            'edit' => Pages\EditGeneratedDocument::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('backend.document_creator');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('backend.company');
    }

    public static function getBreadcrumb(): string
    {
        return __('backend.document_creator');
    }

    public static function getModelLabel(): string
    {
        return __('backend.document_creator');
    }

    public static function getPluralLabel(): ?string
    {
        return __('backend.document_creator');
    }

    public static function canAccess(): bool
    {
        $Role = Auth::user()->role;

        return $Role->can_read_document_creator == true || $Role->can_write_document_creator == true || $Role->can_edit_document_creator == true;
    }

    public static function canView(Model $record): bool
    {
        $Role = Auth::user()->role;

        return $Role->can_read_document_creator;
    }

    public static function canCreate(): bool
    {
        $Role = Auth::user()->role;

        return $Role->can_write_document_creator;
    }

    public static function canEdit(Model $record): bool
    {
        $Role = Auth::user()->role;

        return $Role->can_edit_document_creator;
    }

    public static function canDelete(Model $record): bool
    {
        $Role = Auth::user()->role;

        return $Role->can_edit_document_creator;
    }

    public static function downloadDocument(GeneratedDocument $record): StreamedResponse
    {
        if ($record->file) {
            $filePath = 'public/'.$record->file;

            if (! Storage::exists($filePath)) {
                abort(404, __('backend.file_not_found'));
            }

            $extension = pathinfo((string) $record->file, PATHINFO_EXTENSION);
            $safeName = trim(preg_replace('/[\\\\\/:*?"<>|]+/u', ' ', (string) $record->name)) ?: 'document';

            return Storage::download($filePath, $extension ? "{$safeName}.{$extension}" : $safeName);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'tempDir' => storage_path('app/mpdf/tmp'),
        ]);

        if (session('current_lang') === 'ar') {
            $mpdf->SetDirectionality('rtl');
        }

        $mpdf->WriteHTML(Str::markdown((string) $record->content));

        return response()->streamDownload(function () use ($mpdf) {
            echo $mpdf->Output('', Destination::STRING_RETURN);
        }, $record->name.'.pdf');
    }
}
