<?php

namespace App\Filament\Resources\DocumentTemplateResource\Pages;

use App\Filament\Resources\DocumentTemplateResource;
use App\Filament\Resources\GeneratedDocumentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDocumentTemplate extends CreateRecord
{
    protected static string $resource = DocumentTemplateResource::class;

    protected function getRedirectUrl(): string
    {
        // Templates have no index page of their own — they're managed from
        // the document generator's "template library" tab.
        return GeneratedDocumentResource::getUrl('index');
    }

    public function getBreadcrumbs(): array
    {
        return [
            GeneratedDocumentResource::getUrl('index') => DocumentTemplateResource::getBreadcrumb(),
            __('backend.create'),
        ];
    }
}
