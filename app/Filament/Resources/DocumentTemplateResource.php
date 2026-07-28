<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentTemplateResource\Pages;
use App\Filament\Resources\GeneratedDocumentResource;
use App\Models\DocumentTemplate;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DocumentTemplateResource extends Resource
{
    protected static ?string $model = DocumentTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('company_id')->default(Auth::user()->company_id),
                Hidden::make('created_by')->default(Auth::user()->id),

                TextInput::make('name')
                    ->label(__('backend.document_type'))
                    ->required(),

                Select::make('category')
                    ->label(__('backend.document_section'))
                    ->options(GeneratedDocumentResource::categoryOptions())
                    ->required(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'edit' => Pages\EditDocumentTemplate::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('backend.templates_library');
    }

    public static function getBreadcrumb(): string
    {
        return __('backend.templates_library');
    }

    public static function getModelLabel(): string
    {
        return __('backend.templates_library');
    }

    public static function getPluralLabel(): ?string
    {
        return __('backend.templates_library');
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
