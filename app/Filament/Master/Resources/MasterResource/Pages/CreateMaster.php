<?php

namespace App\Filament\Master\Resources\MasterResource\Pages;

use App\Filament\Master\Resources\MasterResource;
use App\Models\Master;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateMaster extends CreateRecord
{
    protected static string $resource = MasterResource::class;

    public function afterCreate(): void {
        $record= $this->record;

        $Masters= Master::where('is_active', 1)->get();

        if($Masters){
            foreach($Masters as $Master){
                $message= '';

                if($Master->locale == 'en'){
                    $message= 'New master "'.$record->name.'" added to the system records';
                }
                else if($Master->locale == 'ar'){
                    $message= 'تمت اضافة مدير جديد'.' "'.$record->name.'" '.'الى سجل النظام';
                }

                Notification::make()->title($message)->sendToDatabase($Master);
            }
        }
    }
}
