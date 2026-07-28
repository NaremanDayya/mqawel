<?php

namespace App\Filament\Resources\ItemMovementResource\Pages;

use App\Filament\Resources\ItemMovementResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateItemMovement extends CreateRecord
{
    protected static string $resource = ItemMovementResource::class;

    public function afterCreate(): void {
        $record= $this->record;

        $Users= Auth::user()->company->users;

        if($Users){
            foreach($Users as $User){
                $message= '';

                if($User->locale == 'en'){
                    $message= 'New movement of "'.$record->name.'" item was recorded to '.$record->address;
                }
                else if($User->locale == 'ar'){
                    $message= 'تم تسجيل حركة جديدة لعنصر'.' "'.$record->item->name.'" '.'الى '.$record->address;
                }

                Notification::make()->title($message)->sendToDatabase($User);
            }
        }
    }
}
