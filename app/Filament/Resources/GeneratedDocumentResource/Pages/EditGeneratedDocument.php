<?php

namespace App\Filament\Resources\GeneratedDocumentResource\Pages;

use App\Filament\Resources\GeneratedDocumentResource;
use App\Services\Ai\AiRequestException;
use App\Services\Ai\DocumentDraftingService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditGeneratedDocument extends EditRecord
{
    protected static string $resource = GeneratedDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generateDraftWithAi')
                ->label(__('backend.generate_draft_ai'))
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->requiresConfirmation()
                ->modalDescription(__('backend.generate_draft_ai_confirmation'))
                ->action(function (DocumentDraftingService $draftingService) {
                    try {
                        $draft = $draftingService->draft($this->getRecord());
                    } catch (AiRequestException $e) {
                        Notification::make()->title(__('backend.draft_generation_failed'))->danger()->send();

                        return;
                    }

                    $this->form->fill(array_merge($this->form->getState(), ['content' => $draft]));

                    Notification::make()->title(__('backend.draft_generated_notification'))->success()->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
