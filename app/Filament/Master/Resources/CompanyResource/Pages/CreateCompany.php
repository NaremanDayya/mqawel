<?php

namespace App\Filament\Master\Resources\CompanyResource\Pages;

use App\Filament\Master\Resources\CompanyResource;
use App\Models\Master;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    public function afterCreate(): void {
        $record= $this->record;

        $Masters= Master::where('is_active', 1)->get();

        if($Masters){
            foreach($Masters as $Master){
                $message= '';

                if($Master->locale == 'en'){
                    $message= 'New company "'.$record->name.'" added to the system records';
                }
                else if($Master->locale == 'ar'){
                    $message= 'تمت اضافة شركة جديدة'.' "'.$record->name.'" '.'الى سجل النظام';
                }

                Notification::make()->title($message)->sendToDatabase($Master);
            }
        }
    }
}
