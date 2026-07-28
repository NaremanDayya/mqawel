<?php

namespace App\Filament\Master\Resources\SubscriptionPackageResource\Pages;

use App\Filament\Master\Resources\SubscriptionPackageResource;
use App\Models\Master;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSubscriptionPackage extends CreateRecord
{
    protected static string $resource = SubscriptionPackageResource::class;

    public function afterCreate(): void {
        $record= $this->record;

        $Masters= Master::where('is_active', 1)->get();

        if($Masters){
            foreach($Masters as $Master){
                $message= '';

                if($Master->locale == 'en'){
                    $message= 'New subscription package "'.$record->title.'" added to the system records';
                }
                else if($Master->locale == 'ar'){
                    $message= 'تمت اضافة باقة اشتراك جديدة'.' "'.$record->title.'" '.'الى سجل النظام';
                }

                Notification::make()->title($message)->sendToDatabase($Master);
            }
        }
    }
}
