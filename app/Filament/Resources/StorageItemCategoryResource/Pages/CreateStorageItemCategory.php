<?php

namespace App\Filament\Resources\StorageItemCategoryResource\Pages;

use App\Filament\Resources\ItemCategoryResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateStorageItemCategory extends CreateRecord
{
    protected static string $resource = ItemCategoryResource::class;

    public function afterCreate(): void {
        $record= $this->record;

        $Users= Auth::user()->company->users;

        if($Users){
            foreach($Users as $User){
                $message= '';

                if($User->locale == 'en'){
                    $message= 'New item category "'.$record->name.'" added to the company records';
                }
                else if($User->locale == 'ar'){
                    $message= 'تمت اضافة تصنيف عناصر جديد'.' "'.$record->name.'" '.'الى سجل الشركة';
                }

                Notification::make()->title($message)->sendToDatabase($User);
            }
        }
    }
}
