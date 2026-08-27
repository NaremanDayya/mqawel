<?php

namespace App\Filament\Resources\WorkerResource\Pages;

use App\Filament\Concerns\HasSectionNotificationSettings;
use App\Filament\Resources\WorkerResource;
use App\Models\Worker;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorker extends EditRecord
{
    use HasSectionNotificationSettings;

    protected static string $resource = WorkerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function (Worker $record) {
                    $this->notifyIfEnabled(
                        'workers',
                        'delete',
                        'تم حذف العامل "'.$record->name.'" من سجل الشركة',
                        'Worker "'.$record->name.'" was removed from the company records',
                    );
                }),
        ];
    }

    protected function afterSave(): void
    {
        $this->notifyIfEnabled(
            'workers',
            'update',
            'تم تعديل بيانات العامل "'.$this->record->name.'"',
            'Worker "'.$this->record->name.'" was updated',
        );
    }
}
