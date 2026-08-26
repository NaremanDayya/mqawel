<?php

namespace App\Filament\Resources\DocumentTemplateResource\Pages;

use App\Filament\Resources\DocumentTemplateResource;
use App\Filament\Resources\GeneratedDocumentResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditDocumentTemplate extends EditRecord
{
    protected static string $resource = DocumentTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Templates have no index page of their own — they're managed
            // from the document generator's "template library" tab. A real
            // Actions\DeleteAction would crash here: Filament's EditRecord
            // auto-configures it with ->successRedirectUrl($resource::getUrl('index')),
            // resolved eagerly, and that route doesn't exist. Build the
            // delete behavior manually and redirect to the library instead.
            Actions\Action::make('delete')
                ->label(__('filament-actions::delete.single.label'))
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->delete();

                    Notification::make()
                        ->title(__('filament-actions::delete.single.notifications.deleted.title'))
                        ->success()
                        ->send();

                    $this->redirect(GeneratedDocumentResource::getUrl('index'));
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return GeneratedDocumentResource::getUrl('index');
    }

    public function getBreadcrumbs(): array
    {
        // The default breadcrumb trail links back through
        // DocumentTemplateResource::getUrl(), which resolves to the
        // (non-existent) index route for the same reason as above.
        return [
            GeneratedDocumentResource::getUrl('index') => DocumentTemplateResource::getBreadcrumb(),
            $this->getRecordTitle(),
        ];
    }
}
