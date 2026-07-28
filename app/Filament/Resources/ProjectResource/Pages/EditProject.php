<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function afterSave(): void {
        $record= $this->record;

        if($record->wasChanged('status')){
            $previous_status= $record->getOriginal('status');
            $new_status= $record->status;

            $Users= Auth::user()->company->users;

            if($Users){
                foreach($Users as $User){
                    $message= '';

                    if($User->locale == 'en'){
                        $message= 'The project "'.$record->name.'" status updated to '.$new_status;
                    }
                    else if($User->locale == 'ar'){
                        $message= 'حالة المشروع'.' "'.$record->name.'" '.'تم تحديثها الى'.' '.__('backend.'.$new_status);
                    }

                    Notification::make()->title($message)->sendToDatabase($User);
                }
            }
        }
    }
}
