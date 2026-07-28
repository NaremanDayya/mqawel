<?php

namespace App\Filament\Master\Resources\UserResource\Pages;

use App\Filament\Master\Resources\UserResource;
use App\Models\Master;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function afterCreate(): void {
        $record= $this->record;

        $Masters= Master::where('is_active', 1)->get();

        if($Masters){
            foreach($Masters as $Master){
                $message= '';

                if($Master->locale == 'en'){
                    $message= 'New user "'.$record->name.'" added to the system records';
                }
                else if($Master->locale == 'ar'){
                    $message= 'تمت اضافة مستخدم جديد'.' "'.$record->name.'" '.'الى سجل النظام';
                }

                Notification::make()->title($message)->sendToDatabase($Master);
            }
        }
    }
}
