<?php

namespace App\Filament\Master\Resources\SubscriptionPackageResource\Pages;

use App\Filament\Master\Resources\SubscriptionPackageResource;
use App\Models\Master;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptionPackages extends ListRecords
{
    protected static string $resource = SubscriptionPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->after(function ($record): void {
                $Masters= Master::where('is_active', 1)->get();

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
            }),
        ];
    }
}
