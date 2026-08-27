<?php

namespace App\Filament\Resources\CompanyFileResource\Pages;

use App\Filament\Concerns\HasSectionNotificationSettings;
use App\Filament\Resources\CompanyFileResource;
use App\Models\File;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompanyFile extends EditRecord
{
    use HasSectionNotificationSettings;

    protected static string $resource = CompanyFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function (File $record) {
                    static::notifyIfEnabled(
                        'documents',
                        'delete',
                        'تم حذف المستند "'.$record->name.'"',
                        'Document "'.$record->name.'" was deleted',
                    );
                }),
        ];
    }

    protected function afterSave(): void
    {
        static::notifyIfEnabled(
            'documents',
            'update',
            'تم تعديل المستند "'.$this->record->name.'"',
            'Document "'.$this->record->name.'" was updated',
        );
    }
}
