<?php

namespace App\Filament\Resources\GeneratedDocumentResource\Pages;

use App\Filament\Resources\DocumentTemplateResource;
use App\Filament\Resources\GeneratedDocumentResource;
use App\Models\DocumentTemplate;
use App\Models\GeneratedDocument;
use App\Models\Project;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListGeneratedDocuments extends ListRecords
{
    protected static string $resource = GeneratedDocumentResource::class;

    public function getTabs(): array
    {
        $companyId = Auth::user()->company_id;
        $documentsQuery = fn () => GeneratedDocument::query()->where('company_id', $companyId);

        return [
            'mine' => Tab::make(__('backend.my_documents'))
                ->icon('heroicon-o-document')
                ->badge(fn () => $documentsQuery()->count())
                ->badgeColor('gray'),
            'contracts' => Tab::make(__('backend.documents_category_contracts'))
                ->icon('heroicon-o-briefcase')
                ->badge(fn () => $documentsQuery()->where('category', 'contracts')->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', 'contracts')),
            'quotes' => Tab::make(__('backend.documents_category_quotes'))
                ->icon('heroicon-o-currency-dollar')
                ->badge(fn () => $documentsQuery()->where('category', 'quotes')->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', 'quotes')),
            'letters' => Tab::make(__('backend.documents_category_letters'))
                ->icon('heroicon-o-envelope')
                ->badge(fn () => $documentsQuery()->where('category', 'letters')->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', 'letters')),
            'correspondence' => Tab::make(__('backend.documents_category_correspondence'))
                ->icon('heroicon-o-chat-bubble-left-right')
                ->badge(fn () => $documentsQuery()->whereIn('category', ['correspondence', 'minutes'])->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('category', ['correspondence', 'minutes'])),
            'templates' => Tab::make(__('backend.templates_library'))
                ->icon('heroicon-o-folder')
                ->badge(fn () => DocumentTemplate::query()
                    ->where(fn (Builder $q) => $q->where('company_id', $companyId)->orWhereNull('company_id'))
                    ->count())
                ->badgeColor('gray'),
        ];
    }

    public function table(Table $table): Table
    {
        if ($this->activeTab === 'templates') {
            $companyId = Auth::user()->company_id;

            return $table
                ->query(DocumentTemplate::query()->where(fn (Builder $q) => $q->where('company_id', $companyId)->orWhereNull('company_id')))
                ->contentGrid(['default' => 1, 'md' => 2, 'xl' => 3])
                ->columns([
                    TextColumn::make('name')
                        ->weight('bold')
                        ->icon('heroicon-o-document-text')
                        ->label(__('backend.document_type')),
                    TextColumn::make('category')
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => GeneratedDocumentResource::categoryOptions()[$state] ?? $state)
                        ->label(__('backend.document_section')),
                    TextColumn::make('last_used_at')
                        ->date()
                        ->placeholder('—')
                        ->label(__('backend.last_used')),
                ])
                ->actions([
                    Tables\Actions\Action::make('use_template')
                        ->label(__('backend.create'))
                        ->icon('heroicon-o-sparkles')
                        ->color('primary')
                        ->action(fn (DocumentTemplate $record) => $this->mountAction('create', [
                            'name' => $record->name,
                            'category' => $record->category,
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
        return [
            Actions\CreateAction::make()
                ->label(__('backend.new_document'))
                ->modalHeading(__('backend.new_document'))
                ->modalDescription(__('backend.document_creator_description'))
                ->modalSubmitActionLabel(__('backend.create'))
                ->fillForm(fn (array $arguments): array => [
                    'name' => $arguments['name'] ?? null,
                    'category' => $arguments['category'] ?? null,
                ])
                ->form([
                    TextInput::make('name')
                        ->label(__('backend.document_type'))
                        ->required()
                        ->datalist(fn () => DocumentTemplate::query()->pluck('name')->all()),

                    Select::make('category')
                        ->label(__('backend.document_section'))
                        ->options(GeneratedDocumentResource::categoryOptions())
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

                    Textarea::make('details')
                        ->label(__('backend.additional_details')),
                ])
                ->using(function (array $data): GeneratedDocument {
                    return GeneratedDocument::create([
                        'company_id' => Auth::user()->company_id,
                        'created_by' => Auth::user()->id,
                        'name' => $data['name'],
                        'category' => $data['category'],
                        'project_id' => $data['project_id'] ?? null,
                        'related_party' => $data['related_party'] ?? null,
                        'details' => $data['details'] ?? null,
                        'status' => 'draft',
                    ]);
                }),
        ];
    }
}
