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

class GeneratedDocumentResource extends Resource
{
    protected static ?string $model = GeneratedDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?int $navigationSort = 4;

    public static function categoryOptions(): array
    {
        return [
            'contracts' => __('backend.documents_category_contracts'),
            'quotes' => __('backend.documents_category_quotes'),
            'letters' => __('backend.documents_category_letters'),
            'correspondence' => __('backend.documents_category_correspondence'),
            'minutes' => __('backend.documents_category_minutes'),
        ];
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
}
