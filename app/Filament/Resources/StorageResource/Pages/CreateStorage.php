<?php

namespace App\Filament\Resources\StorageResource\Pages;

use App\Filament\Resources\StorageResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class CreateStorage extends CreateRecord
{
    protected static string $resource = StorageResource::class;

    public function afterCreate(): void {
        $record= $this->record;

        $Users= Auth::user()->company->users;

        if($Users){
            foreach($Users as $User){
                $message= '';

                if($User->locale == 'en'){
                    $message= 'New storage "'.$record->name.'" added to the company records';
                }
                else if($User->locale == 'ar'){
                    $message= 'تمت اضافة مخزن جديد'.' "'.$record->name.'" '.'الى سجل الشركة';
                }

                Notification::make()->title($message)->sendToDatabase($User);
            }
        }
    }

    public function getTitle(): string|Htmlable
    {
        return __('backend.create_storage');
    }
}
